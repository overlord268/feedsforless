/** Short two-tone alert via Web Audio + HTML5 fallback. */
let sharedCtx = null;
let unlocked = false;

export function unlockNotificationSound() {
  if (typeof window === 'undefined') return Promise.resolve();

  unlocked = true;

  try {
    const AudioCtx = window.AudioContext || window.webkitAudioContext;
    if (AudioCtx) {
      if (!sharedCtx) sharedCtx = new AudioCtx();
      if (sharedCtx.state === 'suspended') return sharedCtx.resume();
    }
  } catch {
    /* ignore */
  }

  return Promise.resolve();
}

function playWebAudio() {
  const AudioCtx = window.AudioContext || window.webkitAudioContext;
  if (!AudioCtx) return false;

  if (!sharedCtx) sharedCtx = new AudioCtx();
  if (sharedCtx.state !== 'running') return false;

  const ctx = sharedCtx;
  const now = ctx.currentTime;

  const playTone = (freq, start, duration) => {
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.type = 'sine';
    osc.frequency.value = freq;
    gain.gain.setValueAtTime(0.0001, start);
    gain.gain.exponentialRampToValueAtTime(0.15, start + 0.02);
    gain.gain.exponentialRampToValueAtTime(0.0001, start + duration);
    osc.connect(gain);
    gain.connect(ctx.destination);
    osc.start(start);
    osc.stop(start + duration + 0.05);
  };

  playTone(880, now, 0.12);
  playTone(1174.66, now + 0.14, 0.18);
  return true;
}

function playBeepFallback() {
  try {
    const AudioCtx = window.AudioContext || window.webkitAudioContext;
    if (!AudioCtx) return;
    const ctx = new AudioCtx();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.frequency.value = 880;
    gain.gain.value = 0.12;
    osc.connect(gain);
    gain.connect(ctx.destination);
    osc.start();
    osc.stop(ctx.currentTime + 0.2);
    ctx.close().catch(() => {});
  } catch {
    /* ignore */
  }
}

export function playNotificationSound() {
  if (typeof window === 'undefined') return;

  try {
    if (!unlocked) {
      playBeepFallback();
      return;
    }

    if (!sharedCtx || sharedCtx.state === 'suspended') {
      unlockNotificationSound().then(() => {
        if (!playWebAudio()) playBeepFallback();
      });
      return;
    }

    if (!playWebAudio()) playBeepFallback();
  } catch {
    playBeepFallback();
  }
}

/** Attach at app level so the first admin click unlocks audio. */
export function installNotificationSoundUnlock() {
  if (typeof window === 'undefined') return () => {};

  const unlock = () => unlockNotificationSound();

  window.addEventListener('pointerdown', unlock, { passive: true });
  window.addEventListener('keydown', unlock);

  return () => {
    window.removeEventListener('pointerdown', unlock);
    window.removeEventListener('keydown', unlock);
  };
}
