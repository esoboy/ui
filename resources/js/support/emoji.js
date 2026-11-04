/*
 * Emojibase's emojis as dist/emoji/<locale>.json holds them, for the Emoji Picker and
 * the Editor's :shortcode: suggestions.
 */

const FLAG = /^[\u{1F1E6}-\u{1F1FF}]{2}$/u

const loads = new Map()
let canvas = null

/**
 * The data at a URL, cut down to what the device draws; fetched once per page.
 */
export function load(url) {
    if (! loads.has(url)) {
        loads.set(url, fetch(url)
            .then(response => (response.ok ? response.json() : Promise.reject(new Error(`${response.status} ${url}`))))
            .then(drawable)
            .catch(error => {
                loads.delete(url)
                throw error
            }))
    }

    return loads.get(url)
}

/**
 * Leaves out the Emoji versions newer than the device draws (tested on each version's
 * first emoji) and, where it shows country flags as letters, those flags.
 */
function drawable(data) {
    const samples = new Map()

    for (const emojis of Object.values(data)) {
        for (const [emoji, , , version] of emojis) if (! samples.has(version)) samples.set(version, emoji)
    }

    const newest = [...samples.keys()].sort((a, b) => b - a).find(version => draws(samples.get(version))) ?? Infinity
    const flags = draws('\u{1F1FA}\u{1F1F3}')

    return Object.fromEntries(Object.entries(data).map(([category, emojis]) => [
        category,
        emojis.filter(([emoji, , , version]) => version <= newest && (flags || ! FLAG.test(emoji))),
    ]))
}

/**
 * Whether the device draws an emoji as one colour glyph. A missing emoji comes out as a
 * box in the text colour, a missing sequence as several glyphs. Without a canvas to ask,
 * it says yes.
 */
function draws(emoji) {
    canvas ??= document.createElement('canvas')

    const context = canvas.getContext('2d', { willReadFrequently: true })

    if (! context) return true

    const size = 20

    canvas.width = size * 4
    canvas.height = size * 2
    context.font = `${size}px "Apple Color Emoji", "Segoe UI Emoji", "Noto Color Emoji", sans-serif`
    context.textBaseline = 'top'

    if (context.measureText(emoji).width > context.measureText('\u{1F600}').width * 1.5) return false

    const pixels = colour => {
        context.clearRect(0, 0, canvas.width, canvas.height)
        context.fillStyle = colour
        context.fillText(emoji, 0, 0)

        return context.getImageData(0, 0, canvas.width, canvas.height).data
    }

    const red = pixels('#f00')
    const blue = pixels('#00f')

    return red.some(value => value) && red.every((value, index) => value === blue[index])
}

/**
 * How well an emoji ({ label, keywords }, lower case) matches a lower-case query: a label
 * holding it counts 10, each keyword holding it 1, as Frimousse ranks them.
 */
export function score({ label, keywords }, query) {
    return (label.includes(query) ? 10 : 0) + keywords.filter(keyword => keyword.includes(query)).length
}
