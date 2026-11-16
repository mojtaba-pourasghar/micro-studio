// Renders transparent PNGs from the brand SVGs.
const { chromium } = require('playwright');
const fs = require('fs');
const OUT = process.argv[2];
const jobs = [
  ['logo-horizontal-dark.svg', 'logo-horizontal-dark.png', 1600],
  ['logo-horizontal-light.svg', 'logo-horizontal-light.png', 1600],
  ['logo-stacked-dark.svg', 'logo-stacked-dark.png', 1000],
  ['logo-stacked-light.svg', 'logo-stacked-light.png', 1000],
  ['mark.svg', 'mark.png', 1024],
  ['mark-white.svg', 'mark-white.png', 1024],
  ['icon.svg', 'icon-1024.png', 1024],
  ['icon.svg', 'icon-512.png', 512],
  ['icon.svg', 'icon-192.png', 192],
  ['icon.svg', 'apple-touch-icon.png', 180],
  ['icon-dark.svg', 'icon-dark-1024.png', 1024],
  ['favicon.svg', 'favicon-32.png', 32],
];
(async () => {
  const b = await chromium.launch();
  const p = await b.newPage();
  for (const [src, dst, width] of jobs) {
    const svg = fs.readFileSync(`${OUT}/${src}`, 'utf8');
    const vb = svg.match(/viewBox="([\d.\s-]+)"/)[1].split(/\s+/).map(Number);
    const height = Math.round(width * vb[3] / vb[2]);
    await p.setViewportSize({ width, height });
    await p.setContent(`<html><body style="margin:0;background:transparent"><img src="data:image/svg+xml;base64,${Buffer.from(svg).toString('base64')}" width="${width}" height="${height}" style="display:block"></body></html>`);
    await p.waitForTimeout(100);
    await p.screenshot({ path: `${OUT}/${dst}`, omitBackground: true, clip: { x: 0, y: 0, width, height } });
    console.log(dst, width + 'x' + height);
  }
  await b.close();
})();
