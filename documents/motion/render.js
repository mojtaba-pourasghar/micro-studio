// node render.js preview t1,t2,...   → frames/p-<t>.jpg
// node render.js video               → motion-silent.mp4 (30 fps, piped into ffmpeg)
const { chromium } = require('playwright');
const { spawn } = require('child_process');
const fs = require('fs');
const path = require('path');
const HERE = __dirname;
const FPS = 30, DUR = 54.9;

(async () => {
  const mode = process.argv[2] || 'preview';
  const b = await chromium.launch({ args: ['--allow-file-access-from-files'] });
  const p = await b.newPage({ viewport: { width: 1280, height: 720 } });
  p.on('pageerror', (e) => console.error('PAGE ERROR', e.message));
  await p.goto('file://' + path.join(HERE, 'motion.html'));
  await p.evaluate(() => document.fonts.ready);
  await p.evaluate(() => Promise.all(Array.from(document.images).map((i) => i.decode().catch(() => {}))));
  const amp = JSON.parse(fs.readFileSync(path.join(HERE, 'amp.json'), 'utf8'));
  await p.evaluate((a) => window.setAmp(a), amp);

  if (mode === 'preview') {
    fs.mkdirSync(path.join(HERE, 'frames'), { recursive: true });
    for (const t of process.argv[3].split(',').map(Number)) {
      await p.evaluate((tt) => window.render(tt), t);
      await p.screenshot({ path: path.join(HERE, 'frames', `p-${t}.jpg`), type: 'jpeg', quality: 80 });
    }
  } else {
    const out = path.join(HERE, 'motion-silent.mp4');
    const ff = spawn('ffmpeg', ['-y', '-loglevel', 'error', '-f', 'image2pipe', '-framerate', String(FPS), '-c:v', 'mjpeg', '-i', '-',
      '-c:v', 'libx264', '-preset', 'medium', '-crf', '18', '-pix_fmt', 'yuv420p', out], { stdio: ['pipe', 'inherit', 'inherit'] });
    const n = Math.round(DUR * FPS);
    for (let f = 0; f < n; f++) {
      await p.evaluate((tt) => window.render(tt), f / FPS);
      const buf = await p.screenshot({ type: 'jpeg', quality: 92 });
      if (!ff.stdin.write(buf)) await new Promise((r) => ff.stdin.once('drain', r));
      if (f % 150 === 0) console.log('frame', f, '/', n);
    }
    ff.stdin.end();
    await new Promise((r) => ff.on('close', r));
    console.log('done', out);
  }
  await b.close();
})();
