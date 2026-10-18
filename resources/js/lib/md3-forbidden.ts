// md3-exception-file: this file lists the forbidden patterns, so it contains them.

/** Colour and type patterns that MD3-migrated files must not use. The guard test and the codemod CLI share this list. */
export const FORBIDDEN: [string, RegExp][] = [
    [
        'Tailwind palette colour',
        /\b(?:bg|text|border(?:-[xytrbl])?|ring|outline|divide|fill|stroke|from|via|to|placeholder|decoration|accent|caret|shadow)-(?:slate|gray|zinc|neutral|stone|red|orange|amber|yellow|lime|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose|black|white)(?:-\d{2,3})?\b/,
    ],
    // 3, 4, 6 or 8 digits. The lookahead keeps in-page links such as #add-item out.
    ['hex colour', /#(?:[0-9a-fA-F]{8}|[0-9a-fA-F]{6}|[0-9a-fA-F]{3,4})(?![\w-])/],
    ['pixel font size', /\btext-\[\d+px\]/],
    ['uppercase micro-label', /\buppercase\b/],
    ['legacy muted token', /\b(?:text-muted-foreground|bg-muted|bg-accent|text-accent-foreground)\b/],
];

export interface Violation {
    line: number;
    rule: string;
    match: string;
}

/** Lists forbidden matches. A line is skipped when it, or the line above it, contains `md3-exception:`. */
export function findViolations(src: string): Violation[] {
    if (src.includes('md3-exception-file:')) return [];

    const lines = src.split('\n');
    const violations: Violation[] = [];
    lines.forEach((text, i) => {
        if (text.includes('md3-exception:') || lines[i - 1]?.includes('md3-exception:')) return;
        for (const [rule, re] of FORBIDDEN) {
            const match = text.match(re);
            if (match) violations.push({ line: i + 1, rule, match: match[0] });
        }
    });
    return violations;
}
