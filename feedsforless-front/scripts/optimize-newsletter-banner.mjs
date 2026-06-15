import sharp from 'sharp';
import { statSync, unlinkSync } from 'fs';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const images = join(root, 'public', 'images');
const src = join(images, 'newsletter-banner-src.jpg');

const webpOut = join(images, 'newsletter-banner.webp');
const jpgOut = join(images, 'newsletter-banner.jpg');

await sharp(src)
  .resize(1600, null, { withoutEnlargement: true })
  .webp({ quality: 72, effort: 6 })
  .toFile(webpOut);

await sharp(src)
  .resize(1600, null, { withoutEnlargement: true })
  .jpeg({ quality: 78, mozjpeg: true, progressive: true })
  .toFile(jpgOut);

unlinkSync(src);

for (const file of [webpOut, jpgOut]) {
  const kb = (statSync(file).size / 1024).toFixed(1);
  console.log(`${file.split(/[/\\]/).pop()}: ${kb} KB`);
}
