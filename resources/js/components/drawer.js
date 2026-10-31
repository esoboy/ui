/*
 * Drawers open and close as native dialogs (see dialog.js). This adds swiping one
 * back towards its edge: past a quarter of its size, or flicked over 20px, it closes;
 * otherwise it springs back.
 */

const IGNORE = 'input, textarea, select, button, a[href], [contenteditable], [data-no-swipe]'
const SIGNS = { down: 1, up: -1, right: 1, left: -1 }

export function register() {
    document.addEventListener('pointerdown', event => {
        const drawer = event.target.closest?.('dialog[open][data-slot="drawer-popup"]')

        if (! drawer || event.button !== 0 || event.target.closest(IGNORE) || scrolledAway(event.target, drawer)) return

        const direction = drawer.dataset.swipeDirection ?? 'down'
        const vertical = drawer.dataset.swipeAxis !== 'x'
        const sign = SIGNS[direction]
        const size = vertical ? drawer.offsetHeight : drawer.offsetWidth
        const property = vertical ? '--drawer-swipe-movement-y' : '--drawer-swipe-movement-x'
        const start = vertical ? event.clientY : event.clientX
        const startedAt = event.timeStamp
        let movement = 0

        const move = e => {
            // Pulling away from the edge resists; pushing towards it follows the pointer.
            const delta = ((vertical ? e.clientY : e.clientX) - start) * sign
            movement = delta > 0 ? delta : delta / 8

            if (Math.abs(movement) > 2) drawer.setAttribute('data-swiping', '')
            drawer.style.setProperty(property, `${movement * sign}px`)
        }

        const end = e => {
            window.removeEventListener('pointermove', move)
            window.removeEventListener('pointerup', end)
            window.removeEventListener('pointercancel', end)

            const swiped = drawer.hasAttribute('data-swiping')
            const velocity = movement / Math.max(e.timeStamp - startedAt, 1)

            drawer.removeAttribute('data-swiping')

            if (swiped && (movement > size / 4 || (velocity > 0.5 && movement > 20))) {
                drawer.close()
                drawer.addEventListener('transitionend', () => drawer.style.removeProperty(property), { once: true })
            } else {
                drawer.style.removeProperty(property)
            }
        }

        window.addEventListener('pointermove', move)
        window.addEventListener('pointerup', end)
        window.addEventListener('pointercancel', end)
    })
}

// Content scrolled away from its start scrolls rather than swipes.
function scrolledAway(element, drawer) {
    for (let node = element; node && node !== drawer; node = node.parentElement) {
        if (node.scrollTop > 0 || Math.abs(node.scrollLeft) > 0) return true
    }

    return false
}
