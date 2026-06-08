<?php

return [

    /*
    |--------------------------------------------------------------------------
    | External binaries & package paths
    |--------------------------------------------------------------------------
    |
    | These power the Python/Node backed tools (yt-dlp, image/PDF/video
    | conversion, HLS download, headless browser). They are read through
    | config() — NOT env() directly — so they keep working after
    | `php artisan config:cache`, which makes env() return null at runtime.
    |
    */

    // Python interpreter. On shared hosting use the full path, e.g.
    // /opt/alt/python311/bin/python3.11
    'python' => env('PYTHON_EXECUTABLE', PHP_OS_FAMILY === 'Windows' ? 'python' : 'python3'),

    // Folder where `pip install --target=...` placed yt-dlp/Pillow/etc.
    'python_packages_path' => env('PYTHON_PACKAGES_PATH', ''),

    // ffmpeg binary. Leave empty to auto-resolve via the imageio-ffmpeg
    // Python package (bundled static binary, no system install needed).
    'ffmpeg' => env('FFMPEG_PATH', ''),

    // Headless browser (only used for JS-rendered scanning).
    'node_binary' => env('NODE_BINARY', 'node'),
    'node_project_root' => env('NODE_PROJECT_ROOT', ''),
    'node_modules_path' => env('NODE_MODULES_PATH', ''),
    'chrome_path' => env('CHROME_PATH', ''),
    'npm_binary' => env('NPM_BINARY', ''),

    // Persistent Chrome profile dir. When set, the headless browser reuses the
    // cookies/login stored here — so after a one-time `php artisan browser:login`
    // it can render login-gated pages (e.g. tl;dv) as the signed-in user.
    // Local use only; the live server has no interactive login.
    'chrome_user_data_dir' => env('CHROME_USER_DATA_DIR', ''),

];
