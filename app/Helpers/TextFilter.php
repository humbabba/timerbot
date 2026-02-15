<?php

namespace App\Helpers;

class TextFilter
{
    /**
     * Common American English profanity words to filter.
     * Each word maps to its obscured version with asterisks.
     */
    private static array $profanityMap = [
        'fuck' => 'f**k',
        'fucking' => 'f**king',
        'fucked' => 'f**ked',
        'fucker' => 'f**ker',
        'fuckers' => 'f**kers',
        'fucks' => 'f**ks',
        'shit' => 'sh*t',
        'shits' => 'sh*ts',
        'shitting' => 'sh*tting',
        'shitty' => 'sh*tty',
        'bullshit' => 'bullsh*t',
        'horseshit' => 'horsesh*t',
        'dogshit' => 'dogsh*t',
        'batshit' => 'batsh*t',
        'apeshit' => 'apesh*t',
        'ass' => 'a**',
        'asses' => 'a**es',
        'asshole' => 'a**hole',
        'assholes' => 'a**holes',
        'bitch' => 'b*tch',
        'bitches' => 'b*tches',
        'bitchy' => 'b*tchy',
        'bitching' => 'b*tching',
        'damn' => 'd*mn',
        'damned' => 'd*mned',
        'goddamn' => 'godd*mn',
        'goddamned' => 'godd*mned',
        'crap' => 'cr*p',
        'crappy' => 'cr*ppy',
        'piss' => 'p*ss',
        'pissed' => 'p*ssed',
        'pissing' => 'p*ssing',
        'cock' => 'c*ck',
        'cocks' => 'c*cks',
        'dick' => 'd*ck',
        'dicks' => 'd*cks',
        'dickhead' => 'd*ckhead',
        'pussy' => 'p***y',
        'pussies' => 'p***ies',
        'cunt' => 'c**t',
        'cunts' => 'c**ts',
        'whore' => 'wh*re',
        'whores' => 'wh*res',
        'slut' => 'sl*t',
        'sluts' => 'sl*ts',
        'bastard' => 'b*stard',
        'bastards' => 'b*stards',
    ];

    /**
     * Filter profanity from text by replacing with asterisk-obscured versions.
     * Preserves the original case pattern of the matched word.
     */
    public static function filterProfanity(string $text): string
    {
        foreach (self::$profanityMap as $word => $replacement) {
            // Match word boundaries, case-insensitive
            $pattern = '/\b' . preg_quote($word, '/') . '\b/i';

            $text = preg_replace_callback($pattern, function ($matches) use ($word, $replacement) {
                return self::matchCase($matches[0], $replacement);
            }, $text);
        }

        return $text;
    }

    /**
     * Match the case pattern of the original word to the replacement.
     */
    private static function matchCase(string $original, string $replacement): string
    {
        // All uppercase
        if (ctype_upper(str_replace(['*', ' '], '', $original))) {
            return strtoupper($replacement);
        }

        // Title case (first letter uppercase)
        if (ctype_upper($original[0])) {
            return ucfirst($replacement);
        }

        // Default: lowercase
        return $replacement;
    }
}
