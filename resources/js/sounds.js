/**
 * Shared Web Audio API sound library.
 * Single source of truth for all timer sounds — used by timer-runner.js (live)
 * and blade preview buttons (via window.previewSound).
 */

let audioCtx = null;

export function getAudioCtx() {
    if (!audioCtx) {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }
    return audioCtx;
}

function playBeep() {
    const ctx = getAudioCtx();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.type = 'sine';
    osc.frequency.value = 880;
    gain.gain.setValueAtTime(1, ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.8);
    osc.connect(gain).connect(ctx.destination);
    osc.start(ctx.currentTime);
    osc.stop(ctx.currentTime + 0.8);
}

function playDing() {
    const ctx = getAudioCtx();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.type = 'square';
    osc.frequency.value = 220;
    gain.gain.setValueAtTime(1, ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.8);
    osc.connect(gain).connect(ctx.destination);
    osc.start(ctx.currentTime);
    osc.stop(ctx.currentTime + 0.8);
}

function playChime() {
    const ctx = getAudioCtx();
    const notes = [523.25, 659.25, 783.99, 523.25, 659.25, 783.99];
    notes.forEach((freq, i) => {
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.value = freq;
        const start = ctx.currentTime + i * 0.2;
        gain.gain.setValueAtTime(1, start);
        gain.gain.exponentialRampToValueAtTime(0.1, start + 0.3);
        osc.connect(gain).connect(ctx.destination);
        osc.start(start);
        osc.stop(start + 0.3);
    });
}

function playBell() {
    const ctx = getAudioCtx();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.type = 'sine';
    osc.frequency.value = 1046;
    gain.gain.setValueAtTime(1, ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 1.0);
    osc.connect(gain).connect(ctx.destination);
    osc.start(ctx.currentTime);
    osc.stop(ctx.currentTime + 1.0);
}

function playTwang() {
    const ctx = getAudioCtx();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.type = 'sawtooth';
    osc.frequency.value = 150;
    gain.gain.setValueAtTime(1, ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 1.2);
    osc.connect(gain).connect(ctx.destination);
    osc.start(ctx.currentTime);
    osc.stop(ctx.currentTime + 1.2);
}

function playClockRadio() {
    const ctx = getAudioCtx();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();

    osc.type = 'square';
    osc.connect(gain);
    gain.connect(ctx.destination);

    const now = ctx.currentTime;

    for (let i = 0; i < 8; i++) {
        let time = now + (i * 0.15);
        osc.frequency.setValueAtTime(1000, time);
        osc.frequency.setValueAtTime(1200, time + 0.1);

        gain.gain.setValueAtTime(1, time);
        gain.gain.setValueAtTime(0, time + 0.1);
    }

    osc.start(now);
    osc.stop(now + 1.8);
}

function playAlarm() {
    const ctx = getAudioCtx();

    function beep(startTime) {
        const harmonics = [
            { freq: 920, gain: 0.95 },
            { freq: 1440, gain: 0.98 },
            { freq: 1700, gain: 0.96 },
            { freq: 2224, gain: 1.00 },
            { freq: 2748, gain: 0.94 },
            { freq: 3009, gain: 0.97 },
        ];

        harmonics.forEach(({ freq, gain: vol }) => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.type = "sine";
            osc.frequency.setValueAtTime(freq, startTime);

            gain.gain.setValueAtTime(0.0001, startTime);
            gain.gain.exponentialRampToValueAtTime(vol, startTime + 0.01);
            gain.gain.setValueAtTime(vol, startTime + 0.25);
            gain.gain.exponentialRampToValueAtTime(0.0001, startTime + 0.27);

            osc.connect(gain).connect(ctx.destination);
            osc.start(startTime);
            osc.stop(startTime + 0.28);
        });
    }

    const now = ctx.currentTime;

    beep(now);
    beep(now + 0.5);
    beep(now + 1.0);
}

function playDandelion() {
    const ctx = getAudioCtx();
    const notes = [523.25, 659.25, 783.99, 1046.50, 523.25, 659.25, 783.99, 1046.50];
    const now = ctx.currentTime;

    notes.forEach((freq, i) => {
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();

        osc.type = 'triangle';
        osc.frequency.value = freq;

        osc.connect(gain);
        gain.connect(ctx.destination);

        let startTime = now + (i * 0.12);
        gain.gain.setValueAtTime(0, startTime);
        gain.gain.linearRampToValueAtTime(1, startTime + 0.05);
        gain.gain.exponentialRampToValueAtTime(0.0001, startTime + 0.5);

        osc.start(startTime);
        osc.stop(startTime + 0.5);
    });
}

function playWarning() {
    const ctx = new (window.AudioContext || window.webkitAudioContext)();

    function note(startTime, freq, duration, peakGain) {
        const partials = [
            { ratio: 1, gain: peakGain },
            { ratio: 4, gain: peakGain * 0.25 },
            { ratio: 0.5, gain: peakGain * 0.3 },
        ];

        partials.forEach(({ ratio, gain: vol }) => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.type = "sine";
            osc.frequency.setValueAtTime(freq * ratio, startTime);

            gain.gain.setValueAtTime(0.0001, startTime);
            gain.gain.exponentialRampToValueAtTime(vol, startTime + 0.01);
            gain.gain.setValueAtTime(vol, startTime + duration * 0.3);
            gain.gain.exponentialRampToValueAtTime(vol * 0.4, startTime + duration * 0.7);
            gain.gain.exponentialRampToValueAtTime(0.0001, startTime + duration);

            osc.connect(gain).connect(ctx.destination);
            osc.start(startTime);
            osc.stop(startTime + duration + 0.01);
        });
    }

    const now = ctx.currentTime;

    note(now + 0.05,  800,  0.16, 0.78);
    note(now + 0.22, 1165,  0.16, 0.95);
    note(now + 0.39, 1045,  0.15, 0.82);
    note(now + 0.55, 1400,  0.13, 0.98);
    note(now + 0.72, 1045,  0.12, 0.85);
    note(now + 0.88, 2075,  0.10, 0.92);
}

const soundMap = {
    beep: playBeep,
    ding: playDing,
    chime: playChime,
    bell: playBell,
    twang: playTwang,
    alarm: playAlarm,
    clockRadio: playClockRadio,
    dandelion: playDandelion,
    warning: playWarning,
};

export function playSound(name) {
    const fn = soundMap[name];
    if (fn) fn();
}

// Expose for blade preview buttons
window.previewSound = playSound;
