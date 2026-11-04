import { afterEach, beforeAll, describe, expect, it, vi } from 'vitest'

/*
 * jsdom draws nothing: the canvas gets a context that records drawImage and scale, a
 * fixed PNG for toDataURL, and a CSS size; images "load" at 800×200.
 */
let size = { width: 400, height: 160 }
let calls = []

beforeAll(async () => {
    HTMLCanvasElement.prototype.getContext = function () {
        return this.context ??= new Proxy({}, {
            get: (target, name) => (name in target ? target[name] : (...args) => {
                if (name === 'drawImage' || name === 'scale') calls.push([name, ...args.filter(arg => typeof arg === 'number')])
            }),
            set: (target, name, value) => Reflect.set(target, name, value),
        })
    }
    HTMLCanvasElement.prototype.toDataURL = () => 'data:image/png;base64,UE5H'
    Object.defineProperty(HTMLCanvasElement.prototype, 'offsetWidth', { get: () => size.width })
    Object.defineProperty(HTMLCanvasElement.prototype, 'offsetHeight', { get: () => size.height })

    window.Image = class {
        naturalWidth = 800
        naturalHeight = 200

        set src(value) {
            setTimeout(() => this.onload?.())
        }
    }

    await import('../../resources/js/heavy/signature.js')
})

afterEach(() => {
    document.body.innerHTML = ''
    size = { width: 400, height: 160 }
    calls = []
})

const SAVED = 'data:image/png;base64,U0FWRUQ='

function mount(attributes = '', type = 'image/png') {
    document.body.innerHTML = `
        <eso-signature data-slot="signature" data-type="${type}" data-name="sig" ${attributes}>
            <canvas data-slot="signature-canvas"></canvas>
            <span data-slot="signature-placeholder">Sign here</span>
            <button type="button" data-signature-clear hidden>Clear</button>
        </eso-signature>`

    const signature = document.querySelector('eso-signature')
    const events = []

    signature.addEventListener('input', () => events.push('input'))
    signature.addEventListener('change', () => events.push('change'))

    return [signature, events]
}

// signature_pad listens to the mouse where jsdom has no PointerEvent.
function stroke(signature, points = [[20, 60], [80, 40], [140, 70], [200, 50]]) {
    const at = (type, [x, y], target = document) => target.dispatchEvent(new MouseEvent(type, { bubbles: true, cancelable: true, clientX: x, clientY: y, buttons: type === 'mouseup' ? 0 : 1, which: 1 }))

    at('mousedown', points[0], signature.canvas)
    points.slice(1).forEach(point => at('mousemove', point))
    at('mouseup', points.at(-1))
}

const settle = () => new Promise(resolve => setTimeout(resolve, 10))
const drawn = (...args) => vi.waitFor(() => expect(calls).toContainEqual(['drawImage', ...args]))
const shown = (signature, selector) => ! signature.querySelector(selector).hidden
const hidden = signature => signature.querySelector('input[type="hidden"]')?.value

describe('drawing', () => {
    it('reports each stroke as a PNG data URL', async () => {
        const [signature, events] = mount()

        await settle()
        stroke(signature)

        expect(signature.value).toBe('data:image/png;base64,UE5H')
        expect(events).toEqual(['input', 'change'])
        expect(hidden(signature)).toBe('data:image/png;base64,UE5H')
        expect(shown(signature, '[data-slot="signature-placeholder"]')).toBe(false)
        expect(shown(signature, '[data-signature-clear]')).toBe(true)
    })

    it('exports SVG in the CSS ink colour, with the server\'s signature under the strokes', async () => {
        const [signature] = mount(`data-value="${SAVED}"`, 'image/svg+xml')

        signature.canvas.style.color = 'rgb(30, 64, 175)'
        await drawn(0, 30, 400, 100)
        stroke(signature)

        const svg = atob(signature.value.replace('data:image/svg+xml;base64,', ''))

        expect(svg).toContain(`href="${SAVED}"`)
        expect(svg).toContain('stroke="rgb(30, 64, 175)"')
    })

    it('draws nothing while disabled', async () => {
        const [signature, events] = mount('data-disabled')

        await settle()
        stroke(signature)

        expect(signature.value).toBe(null)
        expect(events).toEqual([])
    })
})

describe('clearing', () => {
    it('empties the pad and says so', async () => {
        const [signature, events] = mount(`data-value="${SAVED}"`)

        await settle()
        signature.querySelector('[data-signature-clear]').click()

        expect(signature.value).toBe(null)
        expect(events).toEqual(['input', 'change'])
        expect(hidden(signature)).toBe('')
        expect(shown(signature, '[data-slot="signature-placeholder"]')).toBe(true)
        expect(shown(signature, '[data-signature-clear]')).toBe(false)
    })
})

describe('a signature from the server', () => {
    it('is drawn fitted and centred in the pad, sized for its pixels', async () => {
        const [signature] = mount(`data-value="${SAVED}"`)

        // 800×200 into 400×160: half size, 30px from the top.
        await drawn(0, 30, 400, 100)

        expect(signature.value).toBe(SAVED)
        expect(signature.canvas.width).toBe(400)
        expect(calls).toContainEqual(['scale', 1, 1])
    })

    it('is fitted again when the pad changes size', async () => {
        const [signature] = mount(`data-value="${SAVED}"`)

        await drawn(0, 30, 400, 100)
        size = { width: 200, height: 160 }
        signature.remove()
        document.body.append(signature)

        await drawn(0, 55, 200, 50)
    })

    it('replaces the drawing when wire:model brings a new one, and not when it echoes ours', async () => {
        const [signature, events] = mount()

        await settle()
        stroke(signature)
        calls = []
        signature.value = signature.value
        await settle()

        expect(calls).toEqual([])

        signature.value = SAVED

        await drawn(0, 30, 400, 100)

        expect(signature.value).toBe(SAVED)
        expect(hidden(signature)).toBe(SAVED)
        expect(events).toEqual(['input', 'change'])

        signature.value = null

        expect(shown(signature, '[data-slot="signature-placeholder"]')).toBe(true)
    })
})
