/*
 * The Editor's uploads, to the app's uploader (config ui.editor.uploader) through the
 * package's route (data-upload): one file a request, three at a time, each answered with
 * the attachment the uploader made of it ({ url, name, mime, … }) or null, saying how
 * far it has got as it goes. An image on another site the browser couldn't read goes as
 * its address, for the server to fetch.
 */

const AT_ONCE = 3

const waiting = []
let running = 0

// Why an upload failed, as the app said it (its validation message), or '' for no reason given.
export class UploadError extends Error {}

/**
 * Sends { file | url, source } (and the page's sealed context) to the endpoint, with
 * the page's CSRF token; progress(sent, total) as the file goes.
 */
export function upload(endpoint, { file = null, url = null, source, context = null, token = null, progress = null }) {
    return new Promise((resolve, reject) => {
        waiting.push(() => send(endpoint, { file, url, source, context, token, progress }).then(resolve, reject))
        next()
    })
}

function next() {
    while (running < AT_ONCE && waiting.length) {
        running++
        waiting.shift()().finally(() => {
            running--
            next()
        })
    }
}

// XMLHttpRequest rather than fetch: only it tells how much of a body has gone.
function send(endpoint, { file, url, source, context, token, progress }) {
    const body = new FormData()

    body.append('source', source)
    if (file) body.append('file', file, file.name)
    if (url) body.append('url', url)
    if (context) body.append('context', context)

    return new Promise((resolve, reject) => {
        const request = new XMLHttpRequest()

        request.open('POST', endpoint)
        request.withCredentials = true
        request.setRequestHeader('Accept', 'application/json')
        request.setRequestHeader('X-Requested-With', 'XMLHttpRequest')
        if (token) request.setRequestHeader('X-CSRF-TOKEN', token)

        request.upload.addEventListener('progress', event => event.lengthComputable && progress?.(event.loaded, event.total))
        request.addEventListener('error', () => reject(new UploadError('')))
        request.addEventListener('load', () => {
            let answer = {}

            try {
                answer = JSON.parse(request.responseText || '{}') ?? {}
            } catch {
                answer = {}
            }

            if (request.status >= 200 && request.status < 300) return resolve(answer.attachment ?? null)

            // The app's reason: a validation message, an authorization one; none from a server error.
            const status = request.status
            const reason = status === 422 ? Object.values(answer.errors ?? {}).flat()[0] ?? answer.message : status < 500 && status !== 419 ? answer.message : null

            reject(new UploadError(typeof reason === 'string' ? reason : ''))
        })

        request.send(body)
    })
}
