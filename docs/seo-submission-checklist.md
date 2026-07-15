# SEO Submission Checklist

Use this checklist to submit and monitor your sitemap in search engines.

## 1) Confirm sitemap is reachable

- URL: `/sitemap.xml`
- Full local/dev URL example: `http://localhost/sitemap.xml`
- Expected: XML with `<urlset>` and page entries.

## 2) Google Search Console submission

1. Open Google Search Console.
2. Select your verified property.
3. Go to `Sitemaps`.
4. In `Add a new sitemap`, enter: `sitemap.xml`.
5. Click `Submit`.
6. Check status after processing and recheck in 24-48 hours.

## 3) Bing Webmaster Tools submission

1. Open Bing Webmaster Tools.
2. Select your verified site.
3. Go to `Sitemaps`.
4. Submit: `https://your-domain.com/sitemap.xml`.
5. Verify status is `Success`.

## 4) Post-submission checks

- Confirm indexed pages increase over time.
- Watch for crawl anomalies and fix broken URLs.
- Resubmit sitemap after major content updates.
- Keep `robots.txt` pointing to sitemap.

## 5) Optional quick ping endpoints

Some workflows also use ping URLs after deploy:

- `https://www.google.com/ping?sitemap=https://your-domain.com/sitemap.xml`
- `https://www.bing.com/ping?sitemap=https://your-domain.com/sitemap.xml`

Note: Ping endpoints are optional and may not replace dashboard submission.
