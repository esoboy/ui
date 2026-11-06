/*
 * The Editor's uploads, to the app's uploader (config ui.editor.uploader) through the
 * package's route (data-upload): one file a request, three at a time, each answered with
 * the attachment the uploader made of it ({ url, name, mime, … }) or null, saying how
 * far it has got as it goes. An image on another site the browser couldn't read goes as
 * its address, for the server to fetch.
 */

import axios from 'axios'

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

// Transport stays outside the DOM event layer; the Alpine editor owns its lifecycle.
function send(endpoint, { file, url, source, context, token, progress }) {
    const body = new FormData()

    body.append('source', source)
    if (file) body.append('file', file, file.name)
    if (url) body.append('url', url)
    if (context) body.append('context', context)

    progress?.(0, file?.size ?? 1)
    return axios.post(endpoint, body, {
        withCredentials: true,
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...(token ? { 'X-CSRF-TOKEN': token } : {}) },
        onUploadProgress: event => progress?.(event.loaded, event.total ?? file?.size ?? 1),
        validateStatus: () => true,
    }).then(response => {
        const answer = response.data && typeof response.data === 'object' ? response.data : {}
        if (response.status >= 200 && response.status < 300) { progress?.(file?.size ?? 1, file?.size ?? 1); return answer.attachment ?? null }
        const reason = response.status === 422 ? Object.values(answer.errors ?? {}).flat()[0] ?? answer.message : response.status < 500 && response.status !== 419 ? answer.message : null
        throw new UploadError(typeof reason === 'string' ? reason : '')
    })
}
