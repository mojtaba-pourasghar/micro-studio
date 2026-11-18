"""Mixes voice lines onto the music (with ducking) and writes per-frame loudness for the visuals."""
import json, os, subprocess, wave
import numpy as np

HERE = os.path.dirname(os.path.abspath(__file__))
VO_AT = [4.0, 9.7, 17.2, 24.9, 30.4, 41.7, 47.6]   # seconds, one per line-N.wav
DUR = 54.9
FPS = 30

inputs, graph = ['-i', os.path.join(HERE, 'music.wav')], ''
for i, t in enumerate(VO_AT):
    inputs += ['-i', os.path.join(HERE, f'line-{i}.wav')]
    ms = int(t * 1000)
    graph += f"[{i + 1}:a]aresample=44100,pan=stereo|c0=c0|c1=c0,adelay={ms}|{ms},apad[v{i}];"
graph += ''.join(f'[v{i}]' for i in range(len(VO_AT))) + f"amix=inputs={len(VO_AT)}:normalize=0,atrim=0:{DUR}," \
         "highpass=f=80,equalizer=f=3000:t=q:w=1.2:g=3,acompressor=threshold=-20dB:ratio=3.5:attack=4:release=150,volume=2.0,asplit=2[vk][vm];"
graph += "[0:a]volume=0.55[mu];[mu][vk]sidechaincompress=threshold=0.03:ratio=8:attack=10:release=350[duck];" \
         f"[duck][vm]amix=inputs=2:normalize=0,atrim=0:{DUR},afade=t=out:st={DUR - 1.2}:d=1.2,alimiter=limit=0.93,loudnorm=I=-14:TP=-1.2:LRA=9[out]"
out = os.path.join(HERE, 'mix.wav')
subprocess.run(['ffmpeg', '-y', '-loglevel', 'error', *inputs, '-filter_complex', graph, '-map', '[out]', '-ar', '44100', out], check=True)

# per-frame RMS (0..1) for the equalizer
with wave.open(out) as w:
    sr, ch = w.getframerate(), w.getnchannels()
    a = np.frombuffer(w.readframes(w.getnframes()), '<i2').reshape(-1, ch).mean(1) / 32768
hop = sr // FPS
rms = [float(np.sqrt(np.mean(a[i:i + hop] ** 2))) for i in range(0, len(a), hop)]
mx = max(rms) or 1
json.dump([round(r / mx, 3) for r in rms], open(os.path.join(HERE, 'amp.json'), 'w'))
print('mix ok', len(rms), 'frames')
