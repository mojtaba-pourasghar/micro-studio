// Builds the final Micro Studio brand SVGs with text converted to outlines (HarfBuzz shaping).
import fs from 'node:fs';
import * as hb from 'harfbuzzjs';

const OUT = process.argv[2] || 'out';
fs.mkdirSync(OUT, { recursive: true });

function loadFont(file) {
  const blob = new hb.Blob(fs.readFileSync(file).buffer);
  const face = new hb.Face(blob);
  const font = new hb.Font(face);
  return { font, upem: face.upem };
}
const lalezar = loadFont('Lalezar-Regular.ttf');
const fredoka = loadFont('Fredoka-600.ttf');

/** Shape text and return { body, width } — glyph paths placed with the baseline at y=0, starting at x=0. */
function shape({ font, upem }, text, size, tracking = 0) {
  const buf = new hb.Buffer();
  buf.addText(text);
  buf.guessSegmentProperties();
  hb.shape(font, buf);
  const s = size / upem;
  let x = 0;
  const parts = [];
  const infos = buf.getGlyphInfos();
  const pos = buf.getGlyphPositions();
  infos.forEach((g, i) => {
    const d = font.glyphToPath(g.codepoint);
    if (d) parts.push(`<path transform="translate(${(x + pos[i].xOffset * s).toFixed(2)} ${(-pos[i].yOffset * s).toFixed(2)}) scale(${s.toFixed(5)} ${(-s).toFixed(5)})" d="${d}"/>`);
    x += pos[i].xAdvance * s + (i < infos.length - 1 ? tracking * size : 0);
  });
  return { body: parts.join(''), width: x };
}

const DEFS = `<defs>
  <linearGradient id="ms-g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#ab8dff"/><stop offset=".45" stop-color="#7457ff"/><stop offset="1" stop-color="#5b3ef5"/></linearGradient>
  <linearGradient id="ms-gt" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".32"/><stop offset=".55" stop-color="#fff" stop-opacity="0"/></linearGradient>
</defs>`;
const PAD = 'M22 28H72L100 64L128 28H178Q192 28 192 44L194 118Q194 146 168 140L146 118Q138 108 126 108H74Q62 108 54 118L32 140Q6 146 6 118L8 44Q8 28 22 28Z';
const PAD_TOP = 'M22 28H72L100 64L128 28H178Q192 28 192 44L193 74H7L8 44Q8 28 22 28Z';

/** The M-pad mark in a 200x160 box. variant: color | white | mono(currentColor) */
function mark(variant = 'color') {
  const KEYS = '<rect x="34" y="56" width="12" height="36"/><rect x="22" y="68" width="36" height="12"/><rect x="150" y="54" width="14" height="14"/><rect x="164" y="70" width="14" height="14"/><rect x="136" y="70" width="14" height="14"/><rect x="150" y="86" width="14" height="14"/>';
  if (variant === 'mono') {
    // single colour (currentColor) with the d-pad and buttons cut out
    return `<mask id="ms-holes"><rect x="-10" y="-10" width="220" height="180" fill="#fff"/><g fill="#000">${KEYS}</g></mask>`
      + `<path d="${PAD}" fill="currentColor" stroke="currentColor" stroke-width="10" stroke-linejoin="round" mask="url(#ms-holes)"/>`;
  }
  if (variant === 'white') {
    return `<path d="${PAD}" fill="#fff" stroke="#fff" stroke-width="10" stroke-linejoin="round"/>`
      + `<g fill="#7457ff"><rect x="34" y="56" width="12" height="36"/><rect x="22" y="68" width="36" height="12"/></g><rect x="150" y="54" width="14" height="14" fill="#ffb547"/><rect x="164" y="70" width="14" height="14" fill="#7457ff"/><rect x="136" y="70" width="14" height="14" fill="#ab8dff"/><rect x="150" y="86" width="14" height="14" fill="#ab8dff"/>`;
  }
  return `<path d="${PAD}" fill="url(#ms-g)" stroke="url(#ms-g)" stroke-width="10" stroke-linejoin="round"/><path d="${PAD_TOP}" fill="url(#ms-gt)"/>`
    + `<g fill="#fff"><rect x="34" y="56" width="12" height="36"/><rect x="22" y="68" width="36" height="12"/></g>`
    + `<rect x="150" y="54" width="14" height="14" fill="#ffb547"/><rect x="164" y="70" width="14" height="14" fill="#fff"/><rect x="136" y="70" width="14" height="14" fill="#d9ccff"/><rect x="150" y="86" width="14" height="14" fill="#d9ccff"/>`;
}
// visible bounds of the mark inside its 200x160 box (incl. the 10px stroke)
const MB = { x: 1, y: 23, w: 198, h: 124 };

const THEMES = {
  dark: { fa1: '#ffffff', fa2: '#ab8dff', en: '#ffffff', bg: '#15162a' },   // for dark backgrounds
  light: { fa1: '#15162a', fa2: '#5b3ef5', en: '#15162a', bg: '#f2f2f5' },  // for light backgrounds
};

const svg = (w, h, body, title) => `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${w.toFixed(1)} ${h.toFixed(1)}" width="${Math.round(w)}" height="${Math.round(h)}" role="img" aria-label="${title}"><title>${title}</title>${DEFS}${body}</svg>\n`;
const write = (name, content) => { fs.writeFileSync(`${OUT}/${name}`, content); console.log(name, content.length); };
const TITLE = 'میکرو استودیو — Micro Studio';

// texts
const fa1 = shape(lalezar, 'میکرو', 94);
const fa2 = shape(lalezar, 'استودیو', 68);
const en = shape(fredoka, 'MICRO STUDIO', 20, 0.3);
const faLine = shape(lalezar, 'میکرو استودیو', 60);
const faLine2 = shape(lalezar, 'استودیو', 60);
const faLine1 = shape(lalezar, 'میکرو ', 60);
const enSmall = shape(fredoka, 'MICRO STUDIO', 18, 0.3);

for (const [theme, c] of Object.entries(THEMES)) {
  // ---- horizontal: mark left, text block right-aligned (RTL)
  const markScale = 1.3;            // mark drawn 260x208 (visible ~257x161)
  const gap = 44;
  const textW = Math.max(fa1.width, fa2.width, en.width);
  const blockH = 210;               // approx height of the 3-line block
  const markH = MB.h * markScale;
  const H = Math.max(blockH, markH) + 20;
  const markX = -MB.x * markScale, markY = (H - markH) / 2 - MB.y * markScale;
  const tx = MB.w * markScale + gap; // left of text column
  const right = tx + textW;
  const top = (H - blockH) / 2;
  const hBody = `<g transform="translate(${markX.toFixed(1)} ${markY.toFixed(1)}) scale(${markScale})">${mark('color')}</g>`
    + `<g fill="${c.fa1}" transform="translate(${(right - fa1.width).toFixed(1)} ${(top + 78).toFixed(1)})">${fa1.body}</g>`
    + `<g fill="${c.fa2}" transform="translate(${(right - fa2.width).toFixed(1)} ${(top + 150).toFixed(1)})">${fa2.body}</g>`
    + `<g fill="${c.en}" transform="translate(${(right - en.width).toFixed(1)} ${(top + 205).toFixed(1)})">${en.body}</g>`;
  write(`logo-horizontal-${theme}.svg`, svg(right, H, hBody, TITLE));

  // ---- stacked: mark on top, one Persian line, Latin line
  const sScale = 1.2;
  const sW = Math.max(MB.w * sScale, faLine1.width + faLine2.width, enSmall.width) + 20;
  const sMarkH = MB.h * sScale;
  const sH = sMarkH + 26 + 64 + 40 + 20;
  const sBody = `<g transform="translate(${((sW - MB.w * sScale) / 2 - MB.x * sScale).toFixed(1)} ${(10 - MB.y * sScale).toFixed(1)}) scale(${sScale})">${mark('color')}</g>`
    // RTL order: "میکرو" on the right, "استودیو" on the left
    + `<g transform="translate(${((sW - faLine1.width - faLine2.width) / 2).toFixed(1)} ${(10 + sMarkH + 26 + 50).toFixed(1)})"><g fill="${c.fa2}">${faLine2.body}</g><g fill="${c.fa1}" transform="translate(${faLine2.width.toFixed(1)} 0)">${faLine1.body}</g></g>`
    + `<g fill="${c.en}" transform="translate(${((sW - enSmall.width) / 2).toFixed(1)} ${(sH - 22).toFixed(1)})">${enSmall.body}</g>`;
  write(`logo-stacked-${theme}.svg`, svg(sW, sH, sBody, TITLE));
}

// ---- marks and icons
write('mark.svg', svg(200, 132, `<g transform="translate(0 -18)">${mark('color')}</g>`, TITLE));
write('mark-white.svg', svg(200, 132, `<g transform="translate(0 -18)">${mark('white')}</g>`, TITLE));
write('mark-mono.svg', svg(200, 132, `<g transform="translate(0 -18)">${mark('mono')}</g>`, TITLE).replace('<svg ', '<svg fill="currentColor" '));
const icon = (bg) => svg(512, 512, (bg === 'gradient' ? '<rect width="512" height="512" rx="120" fill="url(#ms-g)"/>' : '<rect width="512" height="512" rx="120" fill="#15162a"/>')
  + `<g transform="translate(56 ${256 - 85 * 2.0 - 0}) scale(2)">${mark(bg === 'gradient' ? 'white' : 'color')}</g>`, TITLE);
write('icon.svg', icon('gradient'));
write('icon-dark.svg', icon('dark'));
// favicon: tighter, no title text needed
write('favicon.svg', `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">${DEFS}<rect width="64" height="64" rx="15" fill="url(#ms-g)"/><g transform="translate(5 3.5) scale(0.27)">${mark('white')}</g></svg>\n`);
