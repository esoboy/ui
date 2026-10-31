/*
 * What Countdown and Verification Code Button share.
 */

/**
 * When each named countdown ends, in this page's clock. Ui::countdown('name') (the
 * ui-countdown event) restarts the elements of that name, and the end outlives morphs.
 * Ends come from the time left the server sent, so a wrong device clock doesn't matter.
 */
export const countdowns = new Map()

const UNITS = [['D', 86400], ['H', 3600], ['m', 60], ['s', 1]]

/**
 * Formats the time left as the server does (Support\Countdown): D, H, m and s, padded
 * to their length; the largest one in the format takes all above it. [text] is literal.
 */
export function format(milliseconds, pattern) {
    const tokens = pattern.match(/\[[^\]]*\]|D+|H+|m+|s+|[^DHms]/gu) ?? []
    const values = {}
    let rest = Math.ceil(Math.max(0, milliseconds) / 1000)

    for (const [unit, size] of UNITS) {
        if (tokens.some(token => token[0] === unit)) {
            values[unit] = Math.floor(rest / size)
            rest -= values[unit] * size
        }
    }

    return tokens.map(token => {
        if (token[0] === '[') return token.slice(1, -1)

        return token[0] in values ? String(values[token[0]]).padStart(token.length, '0') : token
    }).join('')
}
