/*
 * What the Editor inserts for attachments ({ url, name, mime | type, size, alt, width,
 * height, poster }) and video links.
 */

const EXTENSIONS = {
    image: /\.(avif|bmp|gif|jpe?g|png|svg|webp)$/i,
    audio: /\.(aac|flac|m4a|mp3|oga|ogg|opus|wav)$/i,
    video: /\.(m4v|mov|mp4|ogv|webm)$/i,
}

// image, audio, video or file: by its MIME type, else its address's extension.
export function kindOf(attachment) {
    const mime = String(attachment.mime ?? attachment.type ?? '').toLowerCase()
    const kind = mime.split('/')[0]

    if (['image', 'audio', 'video'].includes(kind)) return kind
    if (mime) return 'file'

    const path = String(attachment.url ?? '').split(/[?#]/)[0]

    return Object.keys(EXTENSIONS).find(name => EXTENSIONS[name].test(path)) ?? 'file'
}

// The kinds an editor takes: its media features, files for any kind besides.
export function accepted(features) {
    return ['image', 'audio', 'video', 'file'].filter(kind => features.includes(kind))
}

const number = value => Number.isFinite(Number(value)) && Number(value) > 0 ? Math.round(Number(value)) : null

function basename(url) {
    try {
        return decodeURIComponent(new URL(url, document.baseURI).pathname.split('/').pop()) || url
    } catch {
        return url
    }
}

// 1.2 MB: the number in the page's language, the unit's symbol.
export function formatSize(bytes) {
    bytes = Number(bytes)

    if (! Number.isFinite(bytes) || bytes < 0) return null

    const units = ['B', 'KB', 'MB', 'GB', 'TB']
    const exponent = Math.min(units.length - 1, Math.floor(Math.log(Math.max(bytes, 1)) / Math.log(1024)))
    const number = new Intl.NumberFormat(document.documentElement.lang || undefined, { maximumFractionDigits: exponent ? 1 : 0 }).format(bytes / 1024 ** exponent)

    return `${number} ${units[exponent]}`
}

// What the editor gives the media it inserts: blocks no wider than the page, centered.
export const MEDIA_STYLE = 'display: block; max-width: 100%; height: auto; margin-left: auto; margin-right: auto'

export const EMBED_STYLE = 'display: block; width: 100%; max-width: 100%; height: auto; aspect-ratio: 16 / 9; border: 0'

/**
 * The node for an attachment, as the format allows: a kind it leaves out goes in as a
 * file, or not at all without files. Addresses must be http(s) or relative.
 */
export function attachmentNode(attachment, features, card) {
    const url = String(attachment?.url ?? '').trim()

    if (! url || ! safeUrl(url)) return null

    let kind = kindOf(attachment)

    if (! features.includes(kind)) kind = 'file'
    if (! features.includes(kind)) return null

    const name = attachment.name ? String(attachment.name) : null

    if (kind === 'image') return { type: 'image', attrs: { src: url, alt: attachment.alt ?? name ?? '', width: number(attachment.width), height: number(attachment.height), style: MEDIA_STYLE } }
    if (kind === 'video') return { type: 'video', attrs: { src: url, poster: attachment.poster && safeUrl(attachment.poster) ? attachment.poster : null, width: number(attachment.width), height: number(attachment.height), title: name, style: MEDIA_STYLE } }
    if (kind === 'audio') return { type: 'audio', attrs: { src: url, title: name, style: 'display: block; width: 100%' } }

    return { type: 'file', attrs: { href: url, name: name ?? basename(url), mime: attachment.mime ?? attachment.type ?? null, size: attachment.size == null ? null : formatSize(attachment.size), style: card } }
}

// http(s) or relative; never data:, javascript:, … (data: images are images.js's).
export function safeUrl(url) {
    const scheme = String(url).trim().match(/^([a-z][a-z\d+.-]*):/i)?.[1]

    return ! scheme || ['http', 'https'].includes(scheme.toLowerCase())
}

/**
 * The player address for a share link, a player address, or the src of pasted embed
 * code; null when no platform plays it.
 */
export function embedSource(input, platforms) {
    let url = String(input ?? '').trim()

    url = secure(url.match(/<iframe[^>]*\ssrc=["']([^"']+)["']/i)?.[1]?.replaceAll('&amp;', '&') ?? url)

    for (const [src, pattern] of platforms) {
        const match = pattern && url.match(new RegExp(pattern))

        if (match) return still(src.replace(/\$(\d)/g, (_, index) => match[index] ?? ''), platforms)
    }

    return playable(url, platforms) ? still(url, platforms) : null
}

// An embed's src, https when it leaves the scheme to the page (Bilibili's code does).
export const secure = src => String(src ?? '').trim().replace(/^\/\//, 'https://')

const hostOf = url => {
    try {
        return new URL(url.replace(/\$\d/g, '')).host
    } catch {
        return null
    }
}

// Whether an address is on one of the platforms' players.
export function playable(url, platforms) {
    try {
        const { protocol, host } = new URL(url)

        return protocol === 'https:' && platforms.some(([src]) => hostOf(src) === host)
    } catch {
        return false
    }
}

/**
 * A player's address that doesn't play by itself: its platform's still query (autoplay=0,
 * or the platform's own) set on it, over what it said, the rest as it was; as the server
 * sets it (RichText::still()).
 */
export function still(url, platforms) {
    const platform = platforms.find(([src]) => hostOf(src) === hostOf(url))

    for (const pair of platform?.[2]?.split('&') ?? []) {
        const pattern = new RegExp(`([?&])${pair.split('=')[0].replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}=[^&#]*`)

        url = pattern.test(url) ? url.replace(pattern, `$1${pair}`) : url.replace(/(?=#)|$/, `${url.includes('?') ? '&' : '?'}${pair}`)
    }

    return url
}

// Players' shapes: embed code's width and height snap to these when about the same.
export const RATIOS = ['16 / 9', '4 / 3', '1 / 1', '9 / 16']

// An embed's aspect-ratio from the width and height its code gave, if it gave both.
function ratioOf(code) {
    const [width, height] = ['width', 'height'].map(name => Number(code.match(new RegExp(`<iframe[^>]*\\s${name}=["']?(\\d+)`, 'i'))?.[1]))

    if (! (width > 0 && height > 0)) return null

    return RATIOS.find(ratio => {
        const [across, down] = ratio.split(' / ').map(Number)

        return Math.abs(across / down / (width / height) - 1) < 0.02
    }) ?? `${width} / ${height}`
}

/**
 * What a video link inserts, as the format allows: an embed for a platform's share link,
 * its player's address or its embed code (shaped as the code is), a video for the
 * address of a video file; null for anything else.
 */
export function videoNode(input, features, platforms) {
    const text = String(input ?? '').trim()
    const src = features.includes('embed') ? embedSource(text, platforms) : null

    if (src) {
        const ratio = /^<iframe/i.test(text) ? ratioOf(text) : null

        return { type: 'embed', attrs: { src, style: ratio && ratio !== RATIOS[0] ? EMBED_STYLE.replace('16 / 9', ratio) : null } }
    }

    const path = text.split(/[?#]/)[0]

    if (features.includes('video') && /^https?:\/\/\S+$/i.test(text) && EXTENSIONS.video.test(path)) return { type: 'video', attrs: { src: text, style: MEDIA_STYLE } }

    return null
}
