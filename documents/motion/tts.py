"""Persian voiceover per scene via Gemini TTS. Keys are read from ../gkeys.txt (never committed)."""
import base64, json, os, subprocess, sys, urllib.request

HERE = os.path.dirname(os.path.abspath(__file__))
KEYS = [k for k in os.environ.get('GEMINI_API_KEYS', '').split(',') if k]  # comma-separated, never commit keys
MODEL = sys.argv[1] if len(sys.argv) > 1 else 'gemini-2.5-pro-preview-tts'
VOICE = sys.argv[2] if len(sys.argv) > 2 else 'Puck'

LINES = json.load(open(os.path.join(HERE, 'script.json'), encoding='utf-8'))
STYLE = 'Say in Persian, as an energetic and warm game-trailer narrator: '


def tts(text, key):
    body = {
        'contents': [{'parts': [{'text': STYLE + text}]}],
        'generationConfig': {
            'responseModalities': ['AUDIO'],
            'speechConfig': {'voiceConfig': {'prebuiltVoiceConfig': {'voiceName': VOICE}}},
        },
    }
    req = urllib.request.Request(
        f'https://generativelanguage.googleapis.com/v1beta/models/{MODEL}:generateContent',
        data=json.dumps(body).encode(), headers={'Content-Type': 'application/json', 'x-goog-api-key': key})
    with urllib.request.urlopen(req, timeout=180) as r:
        d = json.load(r)
    part = d['candidates'][0]['content']['parts'][0]['inlineData']
    return base64.b64decode(part['data']), part.get('mimeType', '')


for i, line in enumerate(LINES):
    out = os.path.join(HERE, f'vo-{i:02d}.wav')
    if os.path.exists(out):
        continue
    last = None
    for key in KEYS[i % len(KEYS):] + KEYS[:i % len(KEYS)]:
        try:
            pcm, mime = tts(line['text'], key)
            break
        except Exception as e:  # rotate keys on quota errors
            last = e
            pcm = None
    if pcm is None:
        sys.exit(f'line {i} failed: {last}')
    if pcm[:4] == b'RIFF':  # newer models return a WAV container
        open(out, 'wb').write(pcm)
    else:  # raw 24 kHz 16-bit mono PCM
        raw = out + '.pcm'
        open(raw, 'wb').write(pcm)
        subprocess.run(['ffmpeg', '-y', '-loglevel', 'error', '-f', 's16le', '-ar', '24000', '-ac', '1', '-i', raw, out], check=True)
        os.remove(raw)
    print(i, mime, line['text'][:40])
