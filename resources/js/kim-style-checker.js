/**
 * Kim Komando Style Checker
 * Analyzes text output against Kim's voice profile rules and renders a score panel.
 */

/**
 * Detect which style checks should be overridden based on node system text.
 * Returns an object keyed by check name → true for checks that should be relaxed.
 * @param {string} systemText - The node's system text
 * @returns {object}
 */
function detectNodeOverrides(systemText) {
    if (!systemText) return {};

    const t = systemText.toLowerCase();
    const overrides = {};

    if (/detailed|comprehensive|in-depth|thorough|elaborate/.test(t)) {
        overrides.sentenceLength = true;
    }
    if (/third person|summary|summarize|objective|formal tone|overview|no first person|no second person/.test(t)) {
        overrides.youDensity = true;
    }
    if (/single paragraph|one paragraph|\d[\s-]?word|brief paragraph/.test(t)) {
        overrides.paragraphLength = true;
    }
    if (/no questions|avoid questions|don't include questions|declarative/.test(t)) {
        overrides.questions = true;
    }
    if (/start with|begin with|open with|format:/.test(t)) {
        overrides.openingHook = true;
    }
    if (/summary|summarize|overview|no call to action|no cta|description|recap/.test(t)) {
        overrides.actionStep = true;
    }
    if (/no numbers|no stats|avoid statistics/.test(t)) {
        overrides.numbers = true;
    }
    if (/technical|academic|professional audience|expert audience/.test(t)) {
        overrides.readability = true;
    }

    return overrides;
}

/**
 * Analyze text against Kim Komando style rules.
 * @param {string} text - Plain text to analyze
 * @param {string} [systemText] - Optional node system text for override detection
 * @returns {{ score: number, grade: string, gradeColor: string, feedback: Array<{good: boolean, msg: string, overridden?: boolean}>, stats: object }}
 */
export function analyzeKimStyle(text, systemText) {
    if (!text || !text.trim()) {
        return { score: 0, grade: 'No Content', gradeColor: 'gray', feedback: [], stats: {} };
    }

    const trimmed = text.trim();
    const sentences = trimmed.split(/[.!?]+/).filter(s => s.trim());
    const words = trimmed.split(/\s+/).filter(w => w.length > 0);
    const paragraphs = trimmed.split(/\n\s*\n/).filter(p => p.trim());
    const wordCount = words.length;
    const sentenceCount = sentences.length;
    const avgSentLen = sentenceCount > 0 ? Math.round(wordCount / sentenceCount) : 0;

    const overrides = detectNodeOverrides(systemText);

    let score = 50;
    const feedback = [];

    // 1. Sentence Length (target <15 avg words)
    if (avgSentLen <= 15) {
        score += 12;
        feedback.push({ good: true, msg: `Sentences are punchy (avg ${avgSentLen} words). Nice.` });
    } else if (avgSentLen <= 22) {
        if (overrides.sentenceLength) {
            feedback.push({ good: true, overridden: true, msg: `Sentence length OK per node instructions (avg ${avgSentLen} words).` });
        } else {
            score += 5;
            feedback.push({ good: false, msg: `Tighten those sentences. Avg is ${avgSentLen} words, aim for under 15.` });
        }
    } else {
        if (overrides.sentenceLength) {
            feedback.push({ good: true, overridden: true, msg: `Sentence length OK per node instructions (avg ${avgSentLen} words).` });
        } else {
            score -= 5;
            feedback.push({ good: false, msg: `Way too wordy. Avg ${avgSentLen} words per sentence. Chop them in half.` });
        }
    }

    // 2. You/Your Density
    const youMatches = trimmed.match(/\byou\b|\byour\b|\byou're\b|\byourself\b/gi) || [];
    const youCount = youMatches.length;
    if (youCount >= 3) {
        score += 12;
        feedback.push({ good: true, msg: `${youCount} uses of "you/your." You're talking TO them.` });
    } else if (youCount >= 1) {
        if (overrides.youDensity) {
            feedback.push({ good: true, overridden: true, msg: `"You/your" density OK per node instructions (${youCount}x).` });
        } else {
            score += 5;
            feedback.push({ good: false, msg: `Only ${youCount} "you/your." Needs more. Make it personal.` });
        }
    } else {
        if (overrides.youDensity) {
            feedback.push({ good: true, overridden: true, msg: `"You/your" density OK per node instructions (${youCount}x).` });
        } else {
            score -= 5;
            feedback.push({ good: false, msg: `Zero "you" or "your"? You're talking AT them, not TO them.` });
        }
    }

    // 3. Em Dashes (real em dashes, en dashes, and hyphens used as em dashes)
    const realEmDashes = (trimmed.match(/\u2014/g) || []).length;
    const realEnDashes = (trimmed.match(/\u2013/g) || []).length;
    const doubleDashes = (trimmed.match(/--/g) || []).length;
    // Hyphens with spaces on both sides: " - " used mid-sentence as an em dash
    const spacedDashes = (trimmed.match(/ - /g) || []).length;
    const totalDashIssues = realEmDashes + realEnDashes + doubleDashes + spacedDashes;
    if (totalDashIssues > 0) {
        const parts = [];
        if (realEmDashes) parts.push(`${realEmDashes} em dash(es)`);
        if (realEnDashes) parts.push(`${realEnDashes} en dash(es)`);
        if (doubleDashes) parts.push(`${doubleDashes} double dash(es)`);
        if (spacedDashes) parts.push(`${spacedDashes} spaced dash(es)`);
        score -= 10;
        feedback.push({ good: false, msg: `Dash as separator spotted (${parts.join(', ')})! Kill it. Use a period instead.` });
    } else {
        score += 8;
        feedback.push({ good: true, msg: 'No em dashes or dash separators. Kim would be proud.' });
    }

    // 4. Questions
    const hasQuestion = trimmed.includes('?');
    if (hasQuestion) {
        score += 8;
        feedback.push({ good: true, msg: 'Questions pull readers in. Smart.' });
    } else {
        if (overrides.questions) {
            feedback.push({ good: true, overridden: true, msg: 'No questions OK per node instructions.' });
        } else {
            feedback.push({ good: false, msg: 'Try adding a question. It creates a conversation.' });
        }
    }

    // 5. Paragraph Length (max ~40 words or 6 sentences)
    const longParas = paragraphs.filter(p => {
        const pWords = p.split(/\s+/).filter(w => w.length > 0).length;
        const pSentences = p.split(/[.!?]+/).filter(s => s.trim()).length;
        return pWords > 40 || pSentences > 6;
    });
    if (longParas.length > 0) {
        if (overrides.paragraphLength) {
            feedback.push({ good: true, overridden: true, msg: 'Paragraph length OK per node instructions.' });
        } else {
            score -= 8;
            feedback.push({ good: false, msg: `${longParas.length} paragraph(s) too long. Break 'em up.` });
        }
    } else {
        score += 8;
        feedback.push({ good: true, msg: 'Paragraphs are tight. Easy to scan.' });
    }

    // 6. Numbers/Stats
    const hasNumber = /\d/.test(trimmed) || /\$/.test(trimmed);
    if (hasNumber) {
        score += 8;
        feedback.push({ good: true, msg: 'Numbers = credibility. Good call.' });
    } else {
        if (overrides.numbers) {
            feedback.push({ good: true, overridden: true, msg: 'No numbers OK per node instructions.' });
        } else {
            feedback.push({ good: false, msg: 'Add a stat or number for that "wow" factor.' });
        }
    }

    // 7. Passive Voice
    const passivePattern = /\b(was|were|is|are|been|being|be)\s+(being\s+)?([\w]+ed|[\w]+en|made|built|given|taken|found|known|seen|shown|told|written|broken|chosen|driven|eaten|fallen|forgotten|frozen|gotten|hidden|ridden|risen|spoken|stolen|sworn|thrown|worn|woken)\b/gi;
    const passiveMatches = trimmed.match(passivePattern) || [];
    const passiveCount = passiveMatches.length;
    if (passiveCount === 0) {
        score += 6;
        feedback.push({ good: true, msg: 'No passive voice detected. Active and direct.' });
    } else if (passiveCount <= 2) {
        score -= 3;
        feedback.push({ good: false, msg: `${passiveCount} passive voice hit(s). Try rewriting in active voice.` });
    } else {
        score -= 8;
        feedback.push({ good: false, msg: `${passiveCount} passive voice hits! Kim never writes passively. Rewrite.` });
    }

    // 8. Readability (Flesch-Kincaid approximation)
    const syllableCount = countSyllables(trimmed);
    let fkGrade = 0;
    if (sentenceCount > 0 && wordCount > 0) {
        fkGrade = 0.39 * (wordCount / sentenceCount) + 11.8 * (syllableCount / wordCount) - 15.59;
        fkGrade = Math.max(0, Math.round(fkGrade * 10) / 10);
    }
    if (fkGrade <= 7) {
        score += 6;
        feedback.push({ good: true, msg: `Readability grade ${fkGrade}. Easy to understand.` });
    } else if (fkGrade <= 10) {
        if (overrides.readability) {
            feedback.push({ good: true, overridden: true, msg: `Readability grade ${fkGrade} OK per node instructions.` });
        } else {
            score -= 2;
            feedback.push({ good: false, msg: `Readability grade ${fkGrade}. Aim for 7 or below. Simpler words help.` });
        }
    } else {
        if (overrides.readability) {
            feedback.push({ good: true, overridden: true, msg: `Readability grade ${fkGrade} OK per node instructions.` });
        } else {
            score -= 5;
            feedback.push({ good: false, msg: `Readability grade ${fkGrade}. Way too complex. Kim keeps it grade-school simple.` });
        }
    }

    // 9. Opening Hook
    const firstSentence = sentences[0]?.trim().toLowerCase() || '';
    const blandOpeners = [
        /^(in (today's|this|the|recent|light of))/,
        /^(it is|it's) (important|worth|recommended|essential|crucial)/,
        /^(this (article|piece|report|guide|post))/,
        /^(the (purpose|goal|aim|objective))/,
        /^(as (we|you) (know|may|might|can))/,
        /^(according to)/,
        /^(in order to)/,
        /^(when it comes to)/,
    ];
    const hookPatterns = [
        /\?/,                    // Questions
        /\byou\b|\byour\b/,      // Direct address
        /\d/,                     // Numbers/stats
        /^(here'?s|forget|stop|run|look|ever wonder|imagine|picture this)/,
    ];

    const isBland = blandOpeners.some(p => p.test(firstSentence));
    const isHook = hookPatterns.some(p => p.test(firstSentence));

    if (isBland) {
        if (overrides.openingHook) {
            feedback.push({ good: true, overridden: true, msg: 'Opening format OK per node instructions.' });
        } else {
            score -= 5;
            feedback.push({ good: false, msg: 'Opening is bland. Lead with a hook, stat, or question.' });
        }
    } else if (isHook) {
        score += 6;
        feedback.push({ good: true, msg: 'Strong opening hook. That grabs attention.' });
    } else {
        score += 2;
        feedback.push({ good: true, msg: 'Opening is OK. Consider making it punchier.' });
    }

    // 10. Action Step / CTA in last paragraph
    const lastPara = paragraphs[paragraphs.length - 1]?.toLowerCase() || '';
    const ctaPatterns = [
        /\bdo this\b/, /\bhere'?s (how|what)\b/, /\btry (this|it)\b/, /\bstep \d/,
        /\bforward this\b/, /\bshare (this|it)\b/, /\bopen settings\b/,
        /\bgo to\b/, /\bclick\b/, /\btap\b/, /\bdownload\b/, /\bsign up\b/,
        /\bcheck out\b/, /\bgrab\b/, /\bstart\b/, /\bswitch\b/, /\bturn (on|off)\b/,
    ];
    const hasCta = ctaPatterns.some(p => p.test(lastPara));
    if (hasCta) {
        score += 6;
        feedback.push({ good: true, msg: 'Ends with a clear action step. Kim always delivers the "do this."' });
    } else {
        if (overrides.actionStep) {
            feedback.push({ good: true, overridden: true, msg: 'No action step OK per node instructions.' });
        } else {
            score -= 3;
            feedback.push({ good: false, msg: 'No clear action step at the end. Kim always gives the "do this."' });
        }
    }

    // 11. Quotation Marks (scare quotes / emphasis quotes)
    const quoteIssues = detectScareQuotes(trimmed);
    if (quoteIssues.length === 0) {
        score += 4;
        feedback.push({ good: true, msg: 'No scare quotes or emphasis quotes. Clean.' });
    } else {
        score -= 5;
        feedback.push({ good: false, msg: `${quoteIssues.length} possible scare/emphasis quote(s): ${quoteIssues.slice(0, 3).map(q => '"' + q + '"').join(', ')}${quoteIssues.length > 3 ? '...' : ''}. Only quote direct speech.` });
    }

    // 12. Overuse of "just"
    const justMatches = trimmed.match(/\bjust\b/gi) || [];
    const justCount = justMatches.length;
    if (justCount === 0) {
        score += 4;
        feedback.push({ good: true, msg: 'No "just" usage. Clean and direct.' });
    } else if (justCount === 1) {
        score += 2;
        feedback.push({ good: true, msg: 'One "just" \u2014 that\'s fine. Kim uses it sparingly.' });
    } else {
        score -= 4;
        feedback.push({ good: false, msg: `"Just" appears ${justCount} times. Kim prefers "only" when needed. Cut most of them.` });
    }

    // 13. Never call people "users"
    const userMatches = trimmed.match(/\busers?\b/gi) || [];
    const userCount = userMatches.length;
    if (userCount === 0) {
        score += 4;
        feedback.push({ good: true, msg: 'No "user/users." People are people, not users.' });
    } else {
        score -= 8;
        feedback.push({ good: false, msg: `"User(s)" appears ${userCount} time(s). Never call people "users." Say people, customers, or folks.` });
    }

    // Clamp score
    score = Math.max(0, Math.min(100, score));

    // Grade
    let grade, gradeColor;
    if (score >= 85) {
        grade = 'Kim Approved';
        gradeColor = 'green';
    } else if (score >= 65) {
        grade = 'Getting There';
        gradeColor = 'orange';
    } else if (score >= 45) {
        grade = 'Needs Work';
        gradeColor = 'peach';
    } else {
        grade = 'Start Over';
        gradeColor = 'red';
    }

    return {
        score,
        grade,
        gradeColor,
        feedback,
        stats: {
            wordCount,
            sentenceCount,
            avgSentLen,
            paragraphCount: paragraphs.length,
            youCount,
            passiveCount,
            fkGrade,
            justCount,
            userCount,
        }
    };
}

/**
 * Count syllables in text (rough approximation).
 */
function countSyllables(text) {
    const words = text.toLowerCase().replace(/[^a-z\s]/g, '').split(/\s+/).filter(w => w);
    let total = 0;
    for (const word of words) {
        let count = word.replace(/(?:[^laeiouy]es|ed|[^laeiouy]e)$/, '')
                        .match(/[aeiouy]{1,2}/g);
        total += count ? count.length : 1;
    }
    return total;
}

/**
 * Detect scare quotes / emphasis quotes (short phrases in quotes not preceded by attribution verbs).
 */
function detectScareQuotes(text) {
    const issues = [];
    // Match both "straight" and \u201c\u201d curly quotes
    const quoteRegex = /(?:"|[\u201C])([^"\u201D]{1,40})(?:"|[\u201D])/g;
    const attributionVerbs = /\b(said|says|wrote|writes|told|tells|asked|asks|called|calls|noted|notes|explained|explains|added|adds|stated|states|according to|described as|known as|titled|called|named)\b/i;

    let match;
    while ((match = quoteRegex.exec(text)) !== null) {
        const quoted = match[1].trim();
        const wordsBefore = text.substring(Math.max(0, match.index - 60), match.index);

        // Skip if preceded by attribution verb
        if (attributionVerbs.test(wordsBefore)) continue;

        // Skip if it looks like a full sentence (has verb + subject, longer content)
        const quotedWords = quoted.split(/\s+/).length;
        if (quotedWords > 6) continue;

        // Skip if it looks like a proper name, title, or app/menu path
        if (/^[A-Z][a-z]+ [A-Z]/.test(quoted)) continue;
        if (/[>]/.test(quoted)) continue;

        // Short phrases without attribution = likely scare/emphasis quote
        if (quotedWords <= 4) {
            issues.push(quoted);
        }
    }
    return issues;
}

/**
 * Render the style check panel into a container.
 * @param {string} containerId - DOM id of the container element
 * @param {object} analysis - Result from analyzeKimStyle()
 */
export function renderStylePanel(containerId, analysis) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const { score, grade, gradeColor, feedback, stats } = analysis;

    // Map gradeColor to Tailwind classes (static, not interpolated for JIT)
    let pillBg, pillText, pillBorder, badgeBorder;
    switch (gradeColor) {
        case 'green':
            pillBg = 'bg-green-900/30';
            pillText = 'text-green-400';
            pillBorder = 'border-green-700/50';
            badgeBorder = 'border-green-500';
            break;
        case 'orange':
            pillBg = 'bg-orange-900/30';
            pillText = 'text-orange-400';
            pillBorder = 'border-orange-700/50';
            badgeBorder = 'border-orange-500';
            break;
        case 'peach':
            pillBg = 'bg-red-900/20';
            pillText = 'text-red-300';
            pillBorder = 'border-red-700/40';
            badgeBorder = 'border-red-400';
            break;
        case 'red':
            pillBg = 'bg-red-900/40';
            pillText = 'text-red-400';
            pillBorder = 'border-red-600/50';
            badgeBorder = 'border-red-500';
            break;
        default:
            pillBg = 'bg-gray-800/30';
            pillText = 'text-gray-400';
            pillBorder = 'border-gray-700/50';
            badgeBorder = 'border-gray-500';
    }

    const panelId = containerId + '-panel';

    let feedbackHtml = '';
    for (const item of feedback) {
        let icon, rowBg, iconColor;
        if (item.overridden) {
            icon = '&#8505;';  // info icon
            rowBg = 'bg-blue-900/10 border-blue-800/20';
            iconColor = 'text-blue-400';
        } else if (item.good) {
            icon = '&#10003;';
            rowBg = 'bg-green-900/10 border-green-800/20';
            iconColor = 'text-green-400';
        } else {
            icon = '&#9888;';
            rowBg = 'bg-red-900/10 border-red-800/20';
            iconColor = 'text-yellow-400';
        }
        feedbackHtml += `<div class="flex items-start gap-2 px-3 py-2 rounded border ${rowBg}">
            <span class="${iconColor} text-sm flex-shrink-0 mt-0.5">${icon}</span>
            <span class="text-gray-300 text-sm">${escapeHtml(item.msg)}</span>
        </div>`;
    }

    const statsHtml = `<div class="flex flex-wrap gap-3 text-xs text-gray-500 mt-3 pt-3 border-t border-gray-700/30">
        <span>${stats.wordCount} words</span>
        <span>${stats.sentenceCount} sentences</span>
        <span>${stats.paragraphCount} paragraphs</span>
        <span>Avg ${stats.avgSentLen} wds/sent</span>
        <span>FK grade ${stats.fkGrade}</span>
        <span>${stats.youCount} you/your</span>
        <span>${stats.passiveCount} passive</span>
        <span>${stats.justCount} "just"</span>
    </div>`;

    container.innerHTML = `
        <div class="mt-3">
            <button type="button" onclick="window.toggleStylePanel('${panelId}')"
                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border ${pillBg} ${pillText} ${pillBorder} text-xs font-semibold cursor-pointer hover:opacity-80 transition-opacity"
                style="font-family: var(--font-display);">
                <span class="min-w-5 h-5 px-1 rounded-full border-2 ${badgeBorder} flex items-center justify-center font-bold" style="font-size: 0.6rem;">${score}</span>
                <span>${escapeHtml(grade)}</span>
                <span class="text-gray-500 text-xs ml-1">&#9660;</span>
            </button>
            <div id="${panelId}" class="hidden mt-2 p-4 rounded-lg bg-cortex-panel border border-gray-700/50 space-y-2">
                ${feedbackHtml}
                ${statsHtml}
            </div>
        </div>
    `;
}

/**
 * Register global toggle function.
 */
export function initStyleCheckerGlobals() {
    window.toggleStylePanel = function(panelId) {
        const panel = document.getElementById(panelId);
        if (!panel) return;
        panel.classList.toggle('hidden');
    };
}

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
