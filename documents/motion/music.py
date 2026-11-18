"""Energetic 128 BPM electronic track, synthesized from scratch with numpy (no samples).
Sections are aligned to the motion's scene cuts (one bar = 1.875 s)."""
import numpy as np, wave, os

SR = 44100
BPM = 128
B = 60 / BPM            # beat
BAR = 4 * B
TOTAL = 29 * BAR + 0.6  # ~54.98 s
N = int(TOTAL * SR)
rng = np.random.default_rng(7)
L = np.zeros(N); R = np.zeros(N)      # instruments (ducked by the kick)
DL = np.zeros(N); DR = np.zeros(N)    # drums and hits

# scene cut times (bars): intro 0-2, idea 2-5, story 5-9, ui 9-13, sound 13-16, test 16-22, release 22-25, outro 25-29
def bar(n): return n * BAR

def put(sig, t, gl=1.0, gr=None, drum=False):
    gr = gl if gr is None else gr
    i = int(t * SR)
    if i >= N: return
    s = sig[: N - i]
    (DL if drum else L)[i:i + len(s)] += s * gl
    (DR if drum else R)[i:i + len(s)] += s * gr

def env(n, a=0.002, d=0.2, curve=6.0):
    t = np.arange(n) / SR
    e = np.exp(-curve * t / max(d, 1e-4))
    att = int(a * SR)
    if att > 0: e[:att] *= np.linspace(0, 1, att)
    return e

def fft_filter(x, lo=None, hi=None):
    X = np.fft.rfft(x)
    f = np.fft.rfftfreq(len(x), 1 / SR)
    m = np.ones_like(f)
    if hi: m *= 1 / np.sqrt(1 + (f / hi) ** 4)
    if lo: m *= 1 / np.sqrt(1 + (lo / np.maximum(f, 1)) ** 4)
    return np.fft.irfft(X * m, len(x))

def saw(freq, n, harmonics=None):
    t = np.arange(n) / SR
    h = harmonics or max(1, int(min(40, 9000 / freq)))
    s = np.zeros(n)
    for k in range(1, h + 1):
        s += ((-1) ** (k + 1)) * np.sin(2 * np.pi * freq * k * t) / k
    return s * (2 / np.pi)

def note(n):  # midi -> Hz
    return 440 * 2 ** ((n - 69) / 12)

# ---------- instruments ----------
def kick(n=int(0.42 * SR)):
    t = np.arange(n) / SR
    f = 50 + 100 * np.exp(-t * 20)
    ph = 2 * np.pi * np.cumsum(f) / SR
    s = np.sin(ph) * np.exp(-t * 5.5) + 0.55 * np.sin(2 * np.pi * 52 * t) * np.exp(-t * 5)
    s[:90] += rng.normal(0, 0.25, 90) * np.linspace(1, 0, 90)  # soft click
    return np.tanh(s * 1.8) * 1.0

def clap(n=int(0.25 * SR)):
    s = fft_filter(rng.normal(0, 1, n), lo=900, hi=5000)
    e = np.zeros(n)
    for k, off in enumerate([0, 0.011, 0.022]):
        i = int(off * SR)
        e[i:] += np.exp(-np.arange(n - i) / SR * (60 if k < 2 else 16))
    return s * e * 0.55

def hat(open_=False):
    n = int((0.22 if open_ else 0.05) * SR)
    s = fft_filter(rng.normal(0, 1, n), lo=6500, hi=10500)
    return s * env(n, 0.0005, 0.22 if open_ else 0.04) * (0.17 if open_ else 0.12)

def tom(freq=110):
    n = int(0.3 * SR); t = np.arange(n) / SR
    f = freq * (1 + 0.6 * np.exp(-t * 25))
    return np.sin(2 * np.pi * np.cumsum(f) / SR) * np.exp(-t * 9) * 0.5

def bass_note(m, dur):
    n = int(dur * SR)
    s = fft_filter(saw(note(m), n) * 0.9 + 0.25 * np.sin(2 * np.pi * note(m) * np.arange(n) / SR), lo=90, hi=620)
    return np.tanh(s * 1.8) * env(n, 0.003, dur * 0.9, 3) * 0.32

def supersaw(ms, dur, cutoff=2600, amp=0.09):
    n = int(dur * SR)
    s = np.zeros(n)
    for m in ms:
        for det in (-0.12, -0.05, 0, 0.06, 0.13):
            s += saw(note(m + det), n, harmonics=24)
    s = fft_filter(s, lo=260, hi=cutoff)
    a = np.minimum(1, np.arange(n) / (0.05 * SR)) * np.minimum(1, (n - np.arange(n)) / (0.12 * SR))
    return s * a * amp / len(ms)

def pluck(m, dur=0.22, amp=0.13):
    n = int(dur * SR); t = np.arange(n) / SR
    s = np.sign(np.sin(2 * np.pi * note(m) * t)) * 0.5 + saw(note(m) * 1.005, n, 12) * 0.5
    return fft_filter(s, lo=200, hi=3000) * np.exp(-t * 14) * amp * 0.85

def blip(m, amp=0.25):
    n = int(0.16 * SR); t = np.arange(n) / SR
    f = note(m) * (1 + 0.5 * np.exp(-t * 40))
    return np.sin(2 * np.pi * np.cumsum(f) / SR) * np.exp(-t * 18) * amp

def riser(dur, amp=0.25):
    n = int(dur * SR); t = np.arange(n) / SR
    noise = rng.normal(0, 1, n)
    out = np.zeros(n)
    seg = 8
    for k in range(seg):  # stepped high-pass sweep for the noise
        a, b = k * n // seg, (k + 1) * n // seg
        out[a:b] = fft_filter(noise[a:b], lo=300 * 2 ** (k * 0.7), hi=12000)
    sweep = np.sin(2 * np.pi * np.cumsum(200 + 1800 * (t / dur) ** 2) / SR) * 0.25
    return (out + sweep) * (t / dur) ** 2 * amp

def impact(amp=0.9):
    n = int(2.4 * SR); t = np.arange(n) / SR
    boom = np.sin(2 * np.pi * np.cumsum(38 + 60 * np.exp(-t * 6)) / SR) * np.exp(-t * 2.2)
    crash = fft_filter(rng.normal(0, 1, n), lo=3000) * np.exp(-t * 2.8) * 0.35
    return (np.tanh(boom * 2) * 0.8 + crash) * amp

def reverse_cymbal(dur=1.6):
    n = int(dur * SR)
    s = fft_filter(rng.normal(0, 1, n), lo=4000) * np.exp(-np.arange(n) / SR * 3)
    return s[::-1] * 0.3

def lead(m, dur, amp=0.12):
    n = int(dur * SR); t = np.arange(n) / SR
    vib = 1 + 0.004 * np.sin(2 * np.pi * 5.5 * t) * np.minimum(1, t / 0.15)
    f = note(m) * vib
    ph = 2 * np.pi * np.cumsum(f) / SR
    s = sum(((-1) ** (k + 1)) * np.sin(k * ph) / k for k in range(1, 18)) + 0.5 * np.sign(np.sin(ph * 1.003))
    a = np.minimum(1, t / 0.01) * np.exp(-t * 1.2) * np.minimum(1, (n - np.arange(n)) / (0.03 * SR))
    return fft_filter(s, lo=250, hi=4200) * a * amp

# melody (beats from bar start, midi, length in beats) over Am F C G
HOOK = [(0, 76, 1), (1, 79, .5), (1.5, 81, 1.5), (3, 79, 1), (4, 77, 1), (5, 76, .5), (5.5, 72, 1.5), (7, 74, 1),
        (8, 76, 1), (9, 79, .5), (9.5, 84, 1.5), (11, 83, 1), (12, 81, 1.5), (13.5, 79, .5), (14, 83, 2)]

def hook(b0, amp):
    for at, m, ln in HOOK:
        tt = bar(b0) + at * B
        put(lead(m, ln * B * 0.95, amp), tt, 0.85, 1.0)
        put(lead(m, ln * B * 0.95, amp * 0.35), tt + 3 * B / 4, 1.0, 0.2)   # stereo echo
        put(lead(m + 12, ln * B * 0.95, amp * 0.25), tt + B / 2 * 0 + 0.012, 0.3, 1.0)  # octave double, slightly late

# ---------- arrangement ----------
CH = [(57, [57, 60, 64]), (53, [53, 57, 60]), (48, [55, 60, 64]), (55, [55, 59, 62])]  # Am F C G (bass root, chord)

# intro (bars 0-2): reverse cymbal + logo blips on the button pops + riser into the downbeat
put(reverse_cymbal(1.8), bar(2) - 1.8, 0.8)
put(riser(bar(2) - 0.2, 0.18), 0.1)
for k, (tm, m) in enumerate([(1.0, 76), (1.25, 79), (1.5, 81), (1.75, 84), (2.0, 88), (2.25, 86)]):
    put(blip(m), tm * BAR * 0.62 + 0.4, 0.7 + 0.3 * (k % 2), 1.0 - 0.3 * (k % 2))
put(supersaw([57, 64, 69], bar(2) - 0.5, cutoff=900, amp=0.12), 0.4)

pump = np.ones(N)  # sidechain envelope from the kick

def section(b0, b1, *, clap_on=False, arp=False, hats=True, open_hats=False, pad_cut=2400, toms_last=False, kick_on=True, bass=True):
    for b in range(b0, b1):
        root, chord = CH[b % 4]
        t0 = bar(b)
        if bass:
            for e in range(8):
                m = root - 12 if e % 2 == 0 else root
                put(bass_note(m, B / 2 * 0.95), t0 + e * B / 2)
        put(supersaw(chord, BAR, cutoff=pad_cut), t0, 0.9, 1.0)
        for q in range(4):
            tq = t0 + q * B
            if kick_on:
                put(kick(), tq, drum=True)
                i = int(tq * SR); n = int(0.3 * SR)
                if i < N:
                    seg = pump[i:i + n]
                    seg *= 1 - 0.6 * np.exp(-np.arange(len(seg)) / SR * 9)
            if clap_on and q in (1, 3):
                put(clap(), tq, drum=True)
            if hats:
                put(hat(open_hats and q % 2 == 1), tq + B / 2, 0.8, 1.0, drum=True)
                put(hat(), tq + B / 4, 1.0, 0.7, drum=True)
                put(hat(), tq + 3 * B / 4, 0.7, 1.0, drum=True)
        if arp:
            seq = chord + [chord[1] + 12, chord[2] + 12, chord[0] + 12, chord[2], chord[1]]
            for s in range(16):
                pl = pluck(seq[s % len(seq)] + 12)
                put(pl, t0 + s * B / 4, 0.75, 0.75)
                put(pl * 0.45, t0 + s * B / 4 + 3 * B / 4, 1.0, 0.15)   # ping
                put(pl * 0.3, t0 + s * B / 4 + 6 * B / 4, 0.15, 1.0)    # pong
        if toms_last and b == b1 - 1:
            for k, f in enumerate([180, 160, 140, 120, 100, 90, 80, 70]):
                put(tom(f), t0 + 2 * B + k * B / 4, drum=True)

put(impact(0.7), bar(2), drum=True)
section(2, 5, pad_cut=1400)                                  # idea
section(5, 9, pad_cut=2000, clap_on=True)                    # story
section(9, 13, pad_cut=2600, clap_on=True, arp=True)         # ui
section(13, 16, pad_cut=2600, clap_on=True, arp=True, open_hats=True)  # sound
section(16, 21, pad_cut=3400, clap_on=True, arp=True, open_hats=True)  # test (build)
# last test bar: snare roll + riser, no kick
for k in range(16):
    put(clap() * (0.4 + 0.6 * k / 16), bar(21) + k * B / 4, drum=True)
put(riser(BAR, 0.3), bar(21))
section(21, 22, pad_cut=3800, kick_on=False, bass=False, hats=False, arp=True)
put(impact(1.0), bar(22), drum=True)
hook(22, 0.105)
hook(18, 0.055)
section(22, 25, pad_cut=5200, clap_on=True, arp=True, open_hats=True)  # release (drop)
put(riser(BAR * 0.9, 0.2), bar(24) + BAR * 0.1)
# outro: logo hit, then a big sustained chord that fades
put(impact(1.0), bar(25), drum=True)
for k, m in enumerate([81, 84, 88, 93]):
    put(blip(m, 0.22), bar(25) + 0.5 + k * B / 2)
put(supersaw([57, 64, 69, 72, 76], bar(4), cutoff=3000, amp=0.16), bar(25))
put(bass_note(45, BAR * 2) * 1.2, bar(25))
for b in range(25, 27):
    for q in range(4):
        put(hat(q % 2 == 1), bar(b) + q * B + B / 2, 0.7, drum=True)
put(impact(0.6), bar(27), drum=True)

# apply sidechain pump to everything except transients is overkill; pump the whole bed lightly
pump = 1 - (1 - pump) * 1.25
L *= np.clip(pump, 0.2, 1); R *= np.clip(pump, 0.2, 1)
L += DL * 1.15; R += DR * 1.15
# final fade + soft clip + normalize
fade = np.ones(N); fl = int(3.0 * SR); fade[-fl:] = np.linspace(1, 0, fl) ** 2
L *= fade; R *= fade
mx = max(np.abs(L).max(), np.abs(R).max())
L = np.tanh(L / mx * 1.3) * 0.89
R = np.tanh(R / mx * 1.3) * 0.89
out = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'music.wav')
data = (np.stack([L, R], 1) * 32767).astype('<i2')
with wave.open(out, 'wb') as w:
    w.setnchannels(2); w.setsampwidth(2); w.setframerate(SR); w.writeframes(data.tobytes())
print('music', round(TOTAL, 2), 's')
