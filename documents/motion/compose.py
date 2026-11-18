"""Builds the site trailer (16:9, Persian voiceover) and a short silent loop for the banner background."""
import json, os, subprocess

HERE = os.path.dirname(os.path.abspath(__file__))
A = '/home/user/micro-studio/public/assets'
W, H, FPS, XF = 1280, 720, 30, 0.6
G = lambda slug, n: f'{A}/games/{slug}/shot-{n:02d}.webp'

SCENES = {
    'intro': {'images': [f'{A}/img/canva/world-map.webp']},
    'two-skies': {'images': [f'{A}/img/scenes/hero-two-skies.webp', G('two-skies', 1), G('two-skies', 3), G('two-skies', 5)]},
    'razino': {'images': [G('razino', 1), G('razino', 4), G('razino', 7)]},
    'pilo': {'images': [f'{A}/img/scenes/pilo-world.webp', G('pilo', 1), G('pilo', 3)]},
    'pashmak': {'videos': [(f'{A}/games/pashmak/trailer.mp4', 6)]},
    'popo-goshyar': {'videos': [(f'{A}/games/popo/trailer.mp4', 1), (f'{A}/games/goshyar/trailer.mp4', 10)]},
    'hoho': {'videos': [(f'{A}/games/hoho-farsi-3/trailer.mp4', 6), (f'{A}/games/hoho-olom-3/trailer.mp4', 12)]},
    'outro': {'color': True},
}


def run(args):
    subprocess.run(['ffmpeg', '-y', '-loglevel', 'error', *args], check=True)


def dur(f):
    return float(subprocess.check_output(['ffprobe', '-v', 'error', '-show_entries', 'format=duration', '-of', 'csv=p=0', f]))


def scene_video(key, spec, d, out, with_card=True):
    card = os.path.join(HERE, f'card-{key}.png' if with_card else 'card-blank.png')
    fade = f"[card]format=rgba,fade=t=in:st=0.35:d=0.5:alpha=1[c];"
    if 'images' in spec:
        imgs = spec['images']
        seg = d / len(imgs) + XF * (len(imgs) - 1) / len(imgs)
        inputs, chains = [], []
        for i, im in enumerate(imgs):
            inputs += ['-loop', '1', '-t', f'{seg:.3f}', '-i', im]
            dirx = '1' if i % 2 == 0 else '-1'
            # cover-scale 16% larger, then slow pan + push-in
            chains.append(
                f"[{i}:v]scale={int(W*1.16)}:{int(H*1.16)}:force_original_aspect_ratio=increase,crop={int(W*1.16)}:{int(H*1.16)},"
                f"fps={FPS},crop={W}:{H}:x='(in_w-{W})/2+{dirx}*(in_w-{W})/2*(t/{seg:.3f}-0.5)':y='(in_h-{H})*t/{seg:.3f}',setsar=1,format=yuv420p[v{i}]")
        graph = ';'.join(chains) + ';'
        last = 'v0'
        for i in range(1, len(imgs)):
            off = i * (seg - XF)
            graph += f"[{last}][v{i}]xfade=transition=fade:duration={XF}:offset={off:.3f}[x{i}];"
            last = f'x{i}'
        n = len(imgs)
    elif 'videos' in spec:
        vids = spec['videos']
        seg = d / len(vids) + XF * (len(vids) - 1) / len(vids)
        inputs, chains = [], []
        for i, (v, ss) in enumerate(vids):
            inputs += ['-ss', str(ss), '-t', f'{seg:.3f}', '-i', v]
            x = 210 if i % 2 == 0 else W - 210 - 405
            chains.append(
                f"[{i}:v]fps={FPS},split[a{i}][b{i}];"
                f"[a{i}]scale={W}:{H}:force_original_aspect_ratio=increase,crop={W}:{H},boxblur=24:2,eq=brightness=-0.18:saturation=1.2[bg{i}];"
                f"[b{i}]scale=-2:640,pad=iw+12:ih+12:6:6:color=white@0.9[fg{i}];"
                f"[bg{i}][fg{i}]overlay={x}:(H-h)/2,setsar=1,format=yuv420p[v{i}]")
        graph = ';'.join(chains) + ';'
        last = 'v0'
        for i in range(1, len(vids)):
            off = i * (seg - XF)
            graph += f"[{last}][v{i}]xfade=transition=slideleft:duration={XF}:offset={off:.3f}[x{i}];"
            last = f'x{i}'
        n = len(vids)
    else:  # outro: brand gradient with drifting glow
        inputs = ['-f', 'lavfi', '-t', f'{d:.3f}', '-i',
                  f"gradients=s={W}x{H}:c0=0x15162a:c1=0x2a1f6e:c2=0x15162a:x0=0:y0=0:x1={W}:y1={H}:speed=0.02:r={FPS}"]
        graph = '[0:v]format=yuv420p,setsar=1[v0];'
        last, n = 'v0', 1
    inputs += ['-loop', '1', '-t', f'{d:.3f}', '-i', card]
    graph += f"[{n}:v]copy[card];" + fade + f"[{last}][c]overlay=0:0:format=auto,format=yuv420p[out]"
    run([*inputs, '-filter_complex', graph, '-map', '[out]', '-t', f'{d:.3f}', '-r', str(FPS),
         '-c:v', 'libx264', '-preset', 'medium', '-crf', '20', '-pix_fmt', 'yuv420p', out])


def main():
    lines = json.load(open(os.path.join(HERE, 'script.json'), encoding='utf-8'))
    parts, starts, t = [], [], 0.0
    for i, line in enumerate(lines):
        vo = os.path.join(HERE, f'vo-{i:02d}.wav')
        d = dur(vo) + 0.9 + (XF if 0 < i < len(lines) - 1 else 0.4)
        out = os.path.join(HERE, f'scene-{i:02d}.mp4')
        scene_video(line['scene'], SCENES[line['scene']], d, out)
        parts.append((out, d))
        starts.append(t + 0.45)
        t += d - XF
        print('scene', i, line['scene'], round(d, 2))
    total = t + XF

    # chain scenes with crossfades
    inputs, graph, last, off = [], '', '0:v', 0.0
    for i, (p, d) in enumerate(parts):
        inputs += ['-i', p]
    for i in range(1, len(parts)):
        off += parts[i - 1][1] - XF
        trans = 'fadeblack' if i in (1, len(parts) - 1) else 'smoothleft'
        graph += f"[{last}][{i}:v]xfade=transition={trans}:duration={XF}:offset={off:.3f}[m{i}];"
        last = f'm{i}'
    # voice: each clip delayed to its scene start; soft synth pad underneath
    nv = len(parts)
    for i in range(len(lines)):
        inputs += ['-i', os.path.join(HERE, f'vo-{i:02d}.wav')]
        graph += f"[{nv + i}:a]aresample=48000,adelay={int(starts[i] * 1000)}|{int(starts[i] * 1000)},apad[vo{i}];"
    graph += ''.join(f'[vo{i}]' for i in range(len(lines))) + f"amix=inputs={len(lines)}:normalize=0,atrim=0:{total:.3f}[voice];"
    pad = ("aevalsrc='0.10*sin(2*PI*130.81*t)+0.07*sin(2*PI*196*t)+0.06*sin(2*PI*261.63*t)*(0.6+0.4*sin(2*PI*0.25*t))"
           "+0.05*sin(2*PI*329.63*t)*(0.5+0.5*sin(2*PI*0.5*t))':s=48000:d=" + f'{total:.3f}')
    inputs += ['-f', 'lavfi', '-i', pad]
    graph += (f"[{nv + len(lines)}:a]lowpass=f=1200,volume=0.35,afade=t=in:d=1.5,afade=t=out:st={total - 2.5:.3f}:d=2.5[bed];"
              f"[voice]highpass=f=70,acompressor=threshold=-18dB:ratio=3:attack=5:release=120,volume=1.6,asplit=2[v2][v3];"
              f"[bed][v2]sidechaincompress=threshold=0.05:ratio=6:release=400[duck];"
              f"[duck][v3]amix=inputs=2:normalize=0,alimiter=limit=0.95[aout]")
    out = os.path.join(HERE, 'trailer.mp4')
    run([*inputs, '-filter_complex', graph, '-map', f'[{last}]', '-map', '[aout]', '-t', f'{total:.3f}',
         '-c:v', 'libx264', '-preset', 'slow', '-crf', '26', '-pix_fmt', 'yuv420p', '-movflags', '+faststart',
         '-c:a', 'aac', '-b:a', '128k', out])
    print('trailer', round(total, 2), 's', os.path.getsize(out) // 1024, 'KB')

    banner_loop()
    run(['-ss', '1.2', '-i', out, '-frames:v', '1', '-vf', 'scale=1280:-2', '-q:v', '3', os.path.join(HERE, 'trailer-poster.jpg')])


def banner_loop():
    """Silent, text-free ~12 s loop of game footage for the banner background."""
    from PIL import Image
    Image.new('RGBA', (W, H), (0, 0, 0, 0)).save(os.path.join(HERE, 'card-blank.png'))
    keys = ['two-skies', 'razino', 'pilo', 'pashmak']
    clips = []
    for k in keys:
        f = os.path.join(HERE, f'loop-{k}.mp4')
        scene_video(k, SCENES[k], 3.6, f, with_card=False)
        clips.append(f)
    inputs, graph, last, off = [], '', '0:v', 0.0
    for c in clips:
        inputs += ['-i', c]
    for i in range(1, len(clips)):
        off += 3.6 - XF
        graph += f"[{last}][{i}:v]xfade=transition=fade:duration={XF}:offset={off:.3f}[l{i}];"
        last = f'l{i}'
    graph += f"[{last}]scale=960:-2,eq=brightness=-0.04[o]"
    run([*inputs, '-filter_complex', graph, '-map', '[o]', '-an', '-c:v', 'libx264', '-preset', 'slow', '-crf', '30',
         '-pix_fmt', 'yuv420p', '-movflags', '+faststart', os.path.join(HERE, 'banner-loop.mp4')])


if __name__ == '__main__':
    main()
