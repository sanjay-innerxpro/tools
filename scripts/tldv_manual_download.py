#!/usr/bin/env python3
import json
import os
import re
import shutil
import subprocess
import sys
import time
import urllib.request
from concurrent.futures import ThreadPoolExecutor, as_completed


PKGS = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", "storage", "python-packages"))
if PKGS:
    sys.path.insert(0, PKGS)

import imageio_ffmpeg


UA = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
WORKERS = 8
RETRIES = 5
TIMEOUT = 60


def fetch(url):
    req = urllib.request.Request(url, headers={"User-Agent": UA})
    with urllib.request.urlopen(req, timeout=TIMEOUT) as response:
        return response.read()


def rot_decode(text, shift):
    out = []
    for char in text:
        if char.isalpha():
            base = ord("A") if char.isupper() else ord("a")
            out.append(chr((ord(char) - base + shift) % 26 + base))
        else:
            out.append(char)
    return "".join(out)


def segment_urls(manifest_url):
    content = fetch(manifest_url).decode("utf-8", errors="replace")
    match = re.search(r"^#TLDVCONF:(\d+),(\d+),(.+?)$", content, re.MULTILINE)
    if not match:
        raise RuntimeError("Manifest did not contain #TLDVCONF")

    shift = int(match.group(2))
    base_url = match.group(3).strip()
    urls = []
    for line in content.splitlines():
        stripped = line.strip()
        if stripped and not stripped.startswith("#") and not stripped.startswith("http"):
            urls.append(base_url + rot_decode(stripped, shift))

    if not urls:
        raise RuntimeError("No tl;dv segments found in manifest")
    return urls


def download_one(task):
    idx, url, seg_dir = task
    path = os.path.join(seg_dir, f"{idx:06d}.ts")
    last_error = ""
    for attempt in range(RETRIES):
        try:
            data = fetch(url)
            with open(path, "wb") as handle:
                handle.write(data)
            return idx, True, ""
        except Exception as exc:
            last_error = f"{type(exc).__name__}: {exc}"
            time.sleep(min(2**attempt, 10))
    return idx, False, last_error


def download_all(urls, seg_dir, progress_path):
    os.makedirs(seg_dir, exist_ok=True)
    failed = []
    errors = []
    done = 0
    with ThreadPoolExecutor(max_workers=WORKERS) as pool:
        futures = [pool.submit(download_one, (idx, url, seg_dir)) for idx, url in enumerate(urls)]
        for future in as_completed(futures):
            idx, ok, error = future.result()
            if ok:
                done += 1
            else:
                failed.append(idx)
                if len(errors) < 10:
                    errors.append(f"{idx}: {error}")
            if (done + len(failed)) % 50 == 0 or done + len(failed) == len(urls):
                write_progress(progress_path, "downloading", done=done, total=len(urls))

    retry_failed = []
    if failed:
        write_progress(progress_path, "retrying", done=done, total=len(urls), failed=len(failed))
        for idx in failed:
            _, ok, error = download_one((idx, urls[idx], seg_dir))
            if ok:
                done += 1
            else:
                retry_failed.append(idx)
                if len(errors) < 10:
                    errors.append(f"{idx}: {error}")
            if done % 50 == 0 or done == len(urls):
                write_progress(progress_path, "downloading", done=done, total=len(urls))

    if retry_failed:
        raise RuntimeError(f"{len(retry_failed)} segments failed: {'; '.join(errors)}")


def write_progress(path, status, **extra):
    with open(path, "w", encoding="utf-8") as handle:
        json.dump({"status": status, **extra}, handle)


def convert(seg_dir, total, output_path, log_path):
    concat_path = output_path + ".txt"
    with open(concat_path, "w", encoding="utf-8") as handle:
        for idx in range(total):
            path = os.path.join(seg_dir, f"{idx:06d}.ts").replace("\\", "/")
            handle.write(f"file '{path}'\n")

    ffmpeg = imageio_ffmpeg.get_ffmpeg_exe()
    attempts = [
        [ffmpeg, "-y", "-f", "concat", "-safe", "0", "-i", concat_path, "-c", "copy", "-movflags", "+faststart", "-bsf:a", "aac_adtstoasc", output_path],
        [ffmpeg, "-y", "-f", "concat", "-safe", "0", "-i", concat_path, "-c", "copy", "-movflags", "+faststart", output_path],
    ]

    with open(log_path, "w", encoding="utf-8") as log:
        for cmd in attempts:
            log.write("$ " + " ".join(cmd) + "\n")
            result = subprocess.run(cmd, stdout=log, stderr=log, timeout=900)
            log.write(f"\nexit={result.returncode}\n\n")
            if result.returncode == 0 and os.path.exists(output_path) and os.path.getsize(output_path) > 1024:
                return

    remux_with_pyav(seg_dir, total, output_path, log_path)


def optimize_mp4(input_path, output_path, log_path):
    if not os.path.exists(input_path):
        raise RuntimeError(f"Input file not found for optimization: {input_path}")

    ffmpeg = imageio_ffmpeg.get_ffmpeg_exe()
    temp_path = output_path + ".optimized.mp4"
    cmd = [ffmpeg, "-y", "-i", input_path, "-c", "copy", "-movflags", "+faststart", temp_path]

    with open(log_path, "a", encoding="utf-8") as log:
        log.write("$ " + " ".join(cmd) + "\n")
        result = subprocess.run(cmd, stdout=log, stderr=log, timeout=1800)
        log.write(f"\nexit={result.returncode}\n\n")

    if result.returncode != 0:
        raise RuntimeError(f"MP4 optimization failed; see {log_path}")

    if os.path.exists(output_path):
        os.remove(output_path)
    os.replace(temp_path, output_path)


def remux_with_pyav(seg_dir, total, output_path, log_path):
    import av

    joined_path = output_path + ".joined.ts"
    with open(joined_path, "wb") as joined:
        for idx in range(total):
            seg_path = os.path.join(seg_dir, f"{idx:06d}.ts")
            with open(seg_path, "rb") as segment:
                shutil.copyfileobj(segment, joined)

    with open(log_path, "a", encoding="utf-8") as log:
        log.write(f"$ PyAV remux {joined_path} -> {output_path}\n")
        input_container = av.open(joined_path)
        output_container = av.open(output_path, "w")
        stream_map = {
            stream: output_container.add_stream_from_template(stream)
            for stream in input_container.streams
        }
        packet_count = 0
        for packet in input_container.demux():
            if packet.stream not in stream_map or packet.dts is None:
                continue
            packet.stream = stream_map[packet.stream]
            output_container.mux(packet)
            packet_count += 1
        output_container.close()
        input_container.close()
        log.write(f"PyAV packets={packet_count}\n\n")

    if os.path.exists(joined_path):
        os.remove(joined_path)

    if not os.path.exists(output_path) or os.path.getsize(output_path) <= 1024:
        raise RuntimeError(f"ffmpeg/PyAV conversion failed; see {log_path}")


def main():
    if len(sys.argv) < 3:
        print("Usage: tldv_manual_download.py MANIFEST_URL OUTPUT_MP4 [LIMIT]", file=sys.stderr)
        return 2

    manifest_url = sys.argv[1]
    output_path = os.path.abspath(sys.argv[2])
    limit = int(sys.argv[3]) if len(sys.argv) > 3 else 0
    progress_path = output_path + ".progress"
    log_path = output_path + ".ffmpeg.log"
    seg_dir = output_path + "_segments"

    urls = segment_urls(manifest_url)
    if limit:
        urls = urls[:limit]

    write_progress(progress_path, "downloading", done=0, total=len(urls))
    download_all(urls, seg_dir, progress_path)
    write_progress(progress_path, "converting", total=len(urls))
    convert(seg_dir, len(urls), output_path, log_path)
    optimize_mp4(output_path, output_path, log_path)
    write_progress(progress_path, "done", fileSize=os.path.getsize(output_path))

    if not limit:
        shutil.rmtree(seg_dir, ignore_errors=True)
        if os.path.exists(output_path + ".txt"):
            os.remove(output_path + ".txt")
        if os.path.exists(output_path + ".joined.ts"):
            os.remove(output_path + ".joined.ts")


if __name__ == "__main__":
    raise SystemExit(main())
