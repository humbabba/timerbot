/**
 * Timer Runner — Meeting timer with per-speaker countdown and Web Audio API warnings.
 * Reads timerConfig global: { name, end_time, participant_count, warnings[] }
 */

(function () {
    'use strict';

    // ── DOM refs ──
    const meetingCountdownEl = document.getElementById('meeting-countdown');
    const timePerPersonLabel = document.getElementById('time-per-person-label');
    const speakerPanel       = document.getElementById('speaker-panel');
    const speakerNumberEl    = document.getElementById('speaker-number');
    const speakerTotalEl     = document.getElementById('speaker-total');
    const speakerCountdownEl = document.getElementById('speaker-countdown');
    const speakerStatusEl    = document.getElementById('speaker-status');
    const btnStart           = document.getElementById('btn-start');
    const btnNext            = document.getElementById('btn-next');
    const btnPause           = document.getElementById('btn-pause');
    const historySection     = document.getElementById('history-section');
    const historyBody        = document.getElementById('history-body');
    const completedSection   = document.getElementById('completed-section');

    // ── Config ──
    const config        = window.timerConfig;
    const totalSpeakers = config.participant_count;

    // end_time is a time-of-day string like "14:30" or "14:30:00" — combine with today's date
    const [h, m, s] = config.end_time.split(':').map(Number);
    const endDate = new Date();
    endDate.setHours(h, m, s || 0, 0);
    const endTime = endDate.getTime();
    const warnings     = (config.warnings || []).slice().sort((a, b) => b.seconds_before - a.seconds_before);

    // ── State ──
    let currentSpeaker       = 0;   // 0-indexed
    let speakerStartMs       = 0;
    let speakerAllottedMs    = 0;
    let paused               = false;
    let pauseStartMs         = 0;
    let totalPausedMs        = 0;
    let running              = false;
    let meetingTick          = null;
    let speakerTick          = null;
    let firedWarnings        = new Set();
    const history            = [];
    let audioCtx             = null;

    // ── Helpers ──
    function formatTime(ms) {
        const negative = ms < 0;
        const abs = Math.abs(ms);
        const totalSec = Math.floor(abs / 1000);
        const h = Math.floor(totalSec / 3600);
        const m = Math.floor((totalSec % 3600) / 60);
        const s = totalSec % 60;
        const pad = (n) => String(n).padStart(2, '0');
        const str = h > 0 ? `${pad(h)}:${pad(m)}:${pad(s)}` : `${pad(m)}:${pad(s)}`;
        return negative ? `-${str}` : str;
    }

    function remainingMeetingMs() {
        return endTime - Date.now();
    }

    function calcTimePerSpeaker() {
        const remaining = remainingMeetingMs();
        const speakersLeft = totalSpeakers - currentSpeaker;
        return speakersLeft > 0 ? Math.max(0, remaining / speakersLeft) : 0;
    }

    // ── Web Audio API Sounds ──
    function getAudioCtx() {
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
        gain.gain.setValueAtTime(0.4, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
        osc.connect(gain).connect(ctx.destination);
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + 0.3);
    }

    function playBuzzer() {
        const ctx = getAudioCtx();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'square';
        osc.frequency.value = 220;
        gain.gain.setValueAtTime(0.3, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.5);
        osc.connect(gain).connect(ctx.destination);
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + 0.5);
    }

    function playChime() {
        const ctx = getAudioCtx();
        const notes = [523.25, 659.25, 783.99]; // C5, E5, G5
        notes.forEach((freq, i) => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.value = freq;
            const start = ctx.currentTime + i * 0.2;
            gain.gain.setValueAtTime(0.3, start);
            gain.gain.exponentialRampToValueAtTime(0.01, start + 0.3);
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
        gain.gain.setValueAtTime(0.5, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 1.0);
        osc.connect(gain).connect(ctx.destination);
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + 1.0);
    }

    function playHorn() {
        const ctx = getAudioCtx();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sawtooth';
        osc.frequency.value = 150;
        gain.gain.setValueAtTime(0.4, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.8);
        osc.connect(gain).connect(ctx.destination);
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + 0.8);
    }

    const soundMap = { beep: playBeep, buzzer: playBuzzer, chime: playChime, bell: playBell, horn: playHorn };

    function playSound(name) {
        const fn = soundMap[name];
        if (fn) fn();
    }

    // ── Visual feedback ──
    function flashSpeakerPanel() {
        speakerPanel.classList.add('animate-pulse');
        setTimeout(() => speakerPanel.classList.remove('animate-pulse'), 600);
    }

    function updateSpeakerColor(remainMs) {
        speakerCountdownEl.classList.remove('text-timerbot-green', 'text-timerbot-orange', 'text-timerbot-red');
        speakerPanel.classList.remove('border-timerbot-green', 'border-timerbot-orange', 'border-timerbot-red');

        if (remainMs <= 0) {
            speakerCountdownEl.classList.add('text-timerbot-red');
            speakerPanel.classList.add('border-timerbot-red');
        } else if (remainMs <= 10000) {
            speakerCountdownEl.classList.add('text-timerbot-red');
            speakerPanel.classList.add('border-timerbot-red');
        } else if (remainMs <= 30000) {
            speakerCountdownEl.classList.add('text-timerbot-orange');
            speakerPanel.classList.add('border-timerbot-orange');
        } else {
            speakerCountdownEl.classList.add('text-timerbot-green');
            speakerPanel.classList.add('border-timerbot-green');
        }
    }

    // ── Meeting countdown (always ticking) ──
    function updateMeetingCountdown() {
        const remaining = remainingMeetingMs();
        meetingCountdownEl.textContent = formatTime(remaining);
        if (remaining <= 0) {
            meetingCountdownEl.classList.add('text-timerbot-red');
        } else {
            meetingCountdownEl.classList.remove('text-timerbot-red');
        }
    }

    function updatePreStartInfo() {
        const perPerson = calcTimePerSpeaker();
        timePerPersonLabel.textContent = `${formatTime(perPerson)} per person (${totalSpeakers} participants)`;
    }

    // ── Speaker tick ──
    function speakerElapsedMs() {
        if (paused) {
            return pauseStartMs - speakerStartMs - totalPausedMs;
        }
        return Date.now() - speakerStartMs - totalPausedMs;
    }

    function speakerRemainingMs() {
        return speakerAllottedMs - speakerElapsedMs();
    }

    function tickSpeaker() {
        if (paused) return;

        const remainMs = speakerRemainingMs();
        speakerCountdownEl.textContent = formatTime(remainMs);
        updateSpeakerColor(remainMs);

        if (remainMs <= 0) {
            speakerStatusEl.textContent = 'Over time!';
            speakerCountdownEl.classList.add('animate-pulse');
        } else {
            speakerCountdownEl.classList.remove('animate-pulse');
            speakerStatusEl.textContent = '';
        }

        // Check warnings
        const secsRemaining = remainMs / 1000;
        for (const w of warnings) {
            const key = `${currentSpeaker}-${w.seconds_before}`;
            if (!firedWarnings.has(key) && secsRemaining <= w.seconds_before && secsRemaining > 0) {
                firedWarnings.add(key);
                playSound(w.sound);
                flashSpeakerPanel();
            }
        }
    }

    // ── Actions ──
    function startSpeaker() {
        speakerAllottedMs = calcTimePerSpeaker();
        speakerStartMs    = Date.now();
        totalPausedMs     = 0;
        paused            = false;

        speakerNumberEl.textContent = currentSpeaker + 1;
        speakerPanel.classList.remove('hidden');
        speakerCountdownEl.classList.remove('animate-pulse');
        speakerStatusEl.textContent = '';

        tickSpeaker();
    }

    function recordHistory() {
        const elapsed  = speakerElapsedMs();
        const allotted = speakerAllottedMs;
        const over     = elapsed > allotted;

        history.push({
            speaker: currentSpeaker + 1,
            allotted,
            actual: elapsed,
            over,
        });

        // Render row
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-timerbot-panel-light transition-colors';
        tr.innerHTML = `
            <td class="p-4 border-b border-gray/50">Speaker ${currentSpeaker + 1}</td>
            <td class="p-4 border-b border-gray/50 text-text-muted">${formatTime(allotted)}</td>
            <td class="p-4 border-b border-gray/50 ${over ? 'text-timerbot-red' : 'text-timerbot-green'}">${formatTime(elapsed)}</td>
            <td class="p-4 border-b border-gray/50">
                <span class="badge ${over ? 'badge-peach' : 'badge-lavender'}">${over ? 'Over' : 'On time'}</span>
            </td>
        `;
        historyBody.appendChild(tr);
        historySection.classList.remove('hidden');
    }

    function finishMeeting() {
        running = false;
        clearInterval(speakerTick);
        speakerPanel.classList.add('hidden');
        btnNext.classList.add('hidden');
        btnPause.classList.add('hidden');
        completedSection.classList.remove('hidden');
    }

    // ── Public API (attached to window) ──
    window.timerApp = {
        start() {
            running = true;
            btnStart.classList.add('hidden');
            btnNext.classList.remove('hidden');
            btnPause.classList.remove('hidden');

            currentSpeaker = 0;
            startSpeaker();
            speakerTick = setInterval(tickSpeaker, 100);
        },

        nextSpeaker() {
            if (!running) return;
            recordHistory();

            currentSpeaker++;
            if (currentSpeaker >= totalSpeakers) {
                finishMeeting();
                return;
            }

            clearInterval(speakerTick);
            startSpeaker();
            speakerTick = setInterval(tickSpeaker, 100);
        },

        togglePause() {
            if (!running) return;

            if (paused) {
                // Resume
                totalPausedMs += Date.now() - pauseStartMs;
                paused = false;
                btnPause.textContent = 'Pause';
                btnPause.classList.remove('bg-timerbot-orange', 'text-timerbot-black');
                btnPause.classList.add('bg-timerbot-panel', 'text-timerbot-cyan');
                speakerStatusEl.textContent = '';
            } else {
                // Pause
                pauseStartMs = Date.now();
                paused = true;
                btnPause.textContent = 'Resume';
                btnPause.classList.remove('bg-timerbot-panel', 'text-timerbot-cyan');
                btnPause.classList.add('bg-timerbot-orange', 'text-timerbot-black');
                speakerStatusEl.textContent = 'Paused';
            }
        },
    };

    // ── Init ──
    updateMeetingCountdown();
    updatePreStartInfo();
    meetingTick = setInterval(() => {
        updateMeetingCountdown();
        if (!running) updatePreStartInfo();
    }, 1000);
})();
