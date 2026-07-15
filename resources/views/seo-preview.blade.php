<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SEO Preview</title>
    <style>
        body { font-family: Segoe UI, Arial, sans-serif; margin: 0; background: #0f172a; color: #e2e8f0; }
        .wrap { max-width: 1200px; margin: 0 auto; padding: 24px; }
        h1 { margin: 0 0 12px; font-size: 28px; }
        p { margin: 0 0 18px; color: #94a3b8; }
        table { width: 100%; border-collapse: collapse; background: #111827; border: 1px solid #1f2937; }
        th, td { border-bottom: 1px solid #1f2937; padding: 10px; text-align: left; vertical-align: top; }
        th { font-size: 13px; color: #93c5fd; background: #0b1220; position: sticky; top: 0; }
        td { font-size: 13px; }
        a { color: #67e8f9; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .path { white-space: nowrap; font-weight: 600; color: #cbd5e1; }
        .desc { max-width: 420px; }
        .img-preview { width: 180px; border-radius: 8px; border: 1px solid #334155; background: #020617; }
        .badge { display: inline-block; font-size: 11px; padding: 2px 8px; border-radius: 999px; background: #1e293b; color: #93c5fd; }
    </style>
</head>
<body>
    <div class="wrap">
        <h1>SEO Metadata Preview</h1>
        <p>Review computed metadata per route before deploy. This view is for QA and can be removed or protected in production.</p>
        <p><span class="badge">Routes checked: {{ $rows->count() }}</span></p>

        <table>
            <thead>
                <tr>
                    <th>Path</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Canonical</th>
                    <th>OG/Twitter Image</th>
                    <th>Image Preview</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        <td class="path"><a href="{{ $row['canonical'] }}" target="_blank" rel="noopener">{{ $row['path'] }}</a></td>
                        <td>{{ $row['title'] }}</td>
                        <td class="desc">{{ $row['description'] }}</td>
                        <td><a href="{{ $row['canonical'] }}" target="_blank" rel="noopener">{{ $row['canonical'] }}</a></td>
                        <td><a href="{{ $row['og_image'] }}" target="_blank" rel="noopener">{{ $row['og_image'] }}</a></td>
                        <td><img class="img-preview" src="{{ $row['og_image'] }}" alt="OG image preview for {{ $row['path'] }}"></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
