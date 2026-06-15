# Newsletter banner images

Assets used by `NewsletterWelcomeBanner.vue`.

| File | Format | Notes |
|------|--------|--------|
| `newsletter-banner.webp` | WebP ~72 quality | Primary (smaller) |
| `newsletter-banner.jpg` | JPEG progressive | Fallback |

## Re-optimize

1. Add a source file as `newsletter-banner-src.jpg` (recommended width ≥ 1600px).
2. Run from `feedsforless-front`:

```bash
npm run optimize:newsletter-banner
```

Uses [sharp](https://sharp.pixelplumbing.com/) (devDependency) — same engine many online compressors use locally.
