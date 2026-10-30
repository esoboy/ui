/*
 * <eso-message-scroller>
 *
 * The scroll behaviour of shadcn's MessageScroller (@shadcn/react), for a transcript
 * Livewire renders and morphs:
 * - opens at the end, the start, or the last anchored turn (data-default-scroll-position),
 *   hidden by data-pending-scroll until then;
 * - an appended scroll anchor (data-scroll-anchor="true") settles near the top, keeping a
 *   peek of the row before it, with a spacer below so it can get there;
 * - with data-auto-scroll it follows the live edge while the reader is there, and lets go
 *   as soon as they scroll, use the keyboard or jump;
 * - rows prepended above keep the visible row in place;
 * - Scroll buttons are active only when there is content in their direction.
 * Its state is mirrored on data-scrollable and data-autoscrolling.
 *
 * scrollToEnd(), scrollToStart() and scrollToMessage(id) drive it from anywhere.
 */

import { connected, define, disconnected } from '../support/element.js'

const EPSILON = 0.5
const AUTOSCROLL_TIMEOUT = 180
const SCROLL_KEYS = new Set(['ArrowDown', 'ArrowUp', 'End', 'Home', 'PageDown', 'PageUp', ' '])

const FREE = 'free-scrolling'
const FOLLOWING = 'following-bottom'
const ANCHORED = 'anchored-to-message'
const JUMPING = 'settling-jump'

export class MessageScrollerElement extends HTMLElement {
    #mode = FREE
    #turn = null
    #count = 0
    #anchors = new WeakSet()
    #spacerHeight = 0
    #keep = null
    #autoscrolling = false
    #autoscrollTimer = 0
    #lastTop = 0
    #applied = false
    #pending = null
    #frame = 0
    #resizeFrame = 0
    #mutations = new MutationObserver(() => this.#onContentChange())
    #resize = new ResizeObserver(() => {
        cancelAnimationFrame(this.#resizeFrame)
        this.#resizeFrame = requestAnimationFrame(() => this.#onResize())
    })

    connectedCallback() {
        const signal = connected(this)

        const viewport = this.viewport
        const content = this.content

        if (! viewport || ! content) return

        viewport.addEventListener('scroll', () => this.#syncAfterScroll(), { passive: true, signal })
        viewport.addEventListener('wheel', () => this.#userIntent(), { passive: true, signal })
        viewport.addEventListener('touchmove', () => this.#userIntent(), { passive: true, signal })
        viewport.addEventListener('keydown', event => SCROLL_KEYS.has(event.key) && this.#userIntent(), { signal })

        this.addEventListener('click', event => {
            const button = event.target.closest?.('[data-message-scroller-button]')

            if (! button || button.closest('eso-message-scroller') !== this || button.dataset.active !== 'true') return

            const behavior = button.dataset.behavior || 'smooth'

            button.blur()
            button.dataset.messageScrollerButton === 'start' ? this.scrollToStart({ behavior }) : this.scrollToEnd({ behavior })
        }, { signal })

        this.#mutations.observe(content, { childList: true })
        this.#resize.observe(viewport)
        this.#resize.observe(content)

        this.#onContentChange()
    }

    disconnectedCallback() {
        disconnected(this)
        this.#mutations.disconnect()
        this.#resize.disconnect()
        cancelAnimationFrame(this.#frame)
        cancelAnimationFrame(this.#resizeFrame)
        clearTimeout(this.#autoscrollTimer)
    }

    get viewport() {
        return this.querySelector('[data-slot="message-scroller-viewport"]')
    }

    get content() {
        return this.querySelector('[data-slot="message-scroller-content"]')
    }

    get spacer() {
        return this.content?.querySelector(':scope > [data-message-scroller-spacer]') ?? null
    }

    /** The transcript rows: the content's children but the spacer. */
    get items() {
        const spacer = this.spacer

        return [...(this.content?.children ?? [])].filter(element => element !== spacer)
    }

    /** Which edges the viewport can still scroll towards. */
    get scrollable() {
        const viewport = this.viewport

        if (! viewport || ! this.content) return { start: false, end: false }

        const threshold = this.#number('scrollEdgeThreshold', 8)

        return {
            start: viewport.scrollTop > threshold,
            end: this.#contentBottom() - viewport.scrollTop - viewport.clientHeight > threshold,
        }
    }

    scrollToStart({ behavior = 'auto' } = {}) {
        if (! this.viewport) return false

        this.#setSpacer(0)
        this.#turn = null
        this.#mode = FREE
        this.#scrollTo(0, { behavior })

        return true
    }

    scrollToEnd({ behavior = 'auto' } = {}) {
        const viewport = this.viewport

        if (! viewport) return false

        this.#setSpacer(0)
        this.#turn = null
        this.#mode = this.#autoScroll ? FOLLOWING : FREE
        this.#scrollTo(Math.max(0, viewport.scrollHeight - viewport.clientHeight), { behavior, autoscrolling: true })

        return true
    }

    scrollToMessage(messageId, options = {}) {
        const row = this.items.find(item => item.dataset.messageId === String(messageId))

        if (row) {
            this.#markApplied()
            this.#scrollToElement(row, options)
            this.#pending = null

            return true
        }

        // Before any row exists, the jump waits for the transcript.
        if (this.#count === 0) {
            this.#pending = { messageId: String(messageId), options }
            this.#markApplied()

            return true
        }

        return false
    }

    get #autoScroll() {
        return this.hasAttribute('data-auto-scroll')
    }

    #number(name, fallback) {
        const value = Number(this.dataset[name])

        return Number.isFinite(value) ? value : fallback
    }

    #padding(element) {
        const style = getComputedStyle(element)

        return { start: parseFloat(style.paddingTop) || 0, end: parseFloat(style.paddingBottom) || 0 }
    }

    #offset(element) {
        return element.getBoundingClientRect().top - this.viewport.getBoundingClientRect().top
    }

    // The bottom of the last row in scroll coordinates, without the spacer.
    #contentBottom() {
        const viewport = this.viewport
        const padding = this.#padding(this.content)
        const top = viewport.getBoundingClientRect().top
        let bottom = padding.start + padding.end

        for (const item of this.items) {
            bottom = Math.max(bottom, item.getBoundingClientRect().bottom - top + viewport.scrollTop + padding.end)
        }

        return bottom
    }

    #commit() {
        const state = this.scrollable
        const top = this.viewport.scrollTop
        const movedUp = top < this.#lastTop - EPSILON

        this.#lastTop = top

        // At the live edge a following reader keeps following; scrolling up lets go.
        if (this.#autoScroll && ! state.end && this.#mode !== JUMPING && this.#mode !== ANCHORED) this.#mode = FOLLOWING
        else if (this.#mode === FOLLOWING && state.end && movedUp && ! this.#autoscrolling) this.#mode = FREE

        const shown = this.#mode === FOLLOWING ? { ...state, end: false } : state
        const scrollable = [shown.start && 'start', shown.end && 'end'].filter(Boolean).join(' ')

        for (const element of [this, this.viewport]) {
            scrollable ? element.setAttribute('data-scrollable', scrollable) : element.removeAttribute('data-scrollable')
            element.toggleAttribute('data-autoscrolling', this.#autoscrolling)
        }

        for (const button of this.querySelectorAll('[data-message-scroller-button]')) {
            if (button.closest('eso-message-scroller') !== this) continue

            const active = button.dataset.messageScrollerButton === 'start' ? shown.start : shown.end

            button.dataset.active = String(active)
            button.inert = ! active
            button.tabIndex = active ? 0 : -1
        }
    }

    #scheduleCommit() {
        cancelAnimationFrame(this.#frame)
        this.#frame = requestAnimationFrame(() => this.#commit())
    }

    #setAutoscrolling(on) {
        clearTimeout(this.#autoscrollTimer)

        if (this.#autoscrolling !== on) {
            this.#autoscrolling = on
            this.#commit()
        }

        if (on) {
            this.#autoscrollTimer = setTimeout(() => {
                this.#autoscrolling = false
                this.#commit()
            }, AUTOSCROLL_TIMEOUT)
        }
    }

    // The spacer lets the last anchored turn reach the top of a viewport it doesn't fill.
    #setSpacer(height) {
        const spacer = this.spacer
        const value = Math.max(0, Math.ceil(height))

        if (! spacer || this.#spacerHeight === value) return

        const gap = parseFloat(getComputedStyle(this.content).rowGap) || 0

        this.#spacerHeight = value
        spacer.hidden = value === 0
        spacer.style.height = `${value}px`
        spacer.style.marginTop = value > 0 ? `${-gap}px` : ''
    }

    #scrollTo(top, { behavior = 'auto', autoscrolling = false } = {}) {
        const viewport = this.viewport
        const target = Math.max(0, top)

        if (Math.abs(viewport.scrollTop - target) <= EPSILON) {
            viewport.scrollTop = target
            this.#commit()

            return
        }

        if (autoscrolling) this.#setAutoscrolling(true)
        viewport.scrollTo({ top: target, behavior })
        this.#scheduleCommit()
    }

    #targetTop(element, align, margin) {
        const viewport = this.viewport
        const top = this.#offset(element) + viewport.scrollTop
        const height = element.getBoundingClientRect().height
        const padding = this.#padding(this.content)

        if (align === 'center') return top - padding.start - (Math.max(0, viewport.clientHeight - padding.start - padding.end) - height) / 2 - margin
        if (align === 'end') return top - viewport.clientHeight + height + padding.end + margin

        if (align === 'nearest') {
            const visibleTop = viewport.scrollTop + padding.start
            const visibleBottom = viewport.scrollTop + viewport.clientHeight - padding.end

            if (top >= visibleTop && top + height <= visibleBottom) return viewport.scrollTop

            return top < visibleTop ? top - padding.start - margin : top + height - viewport.clientHeight + padding.end + margin
        }

        return top - padding.start - margin
    }

    #scrollToElement(element, { align = 'start', behavior = 'auto', scrollMargin } = {}, { keepPreviousPeek = false } = {}) {
        if (! this.content?.contains(element)) return false

        const margin = (scrollMargin ?? this.#number('scrollMargin', 0)) + (keepPreviousPeek ? this.#number('scrollPreviousItemPeek', 64) : 0)
        const top = this.#targetTop(element, align, margin)

        this.#setSpacer(top + this.viewport.clientHeight - this.#contentBottom())
        this.#keep = { element, index: this.items.indexOf(element), viewportTop: this.#offset(element) }
        this.#mode = keepPreviousPeek ? ANCHORED : JUMPING
        this.#turn = keepPreviousPeek ? element : null
        this.#scrollTo(top, { behavior })

        return true
    }

    // Keeps an anchored turn in place while its reply grows below it.
    #reanchor() {
        const turn = this.#turn

        if (! turn?.isConnected || this.#mode !== ANCHORED) return false

        return this.#scrollToElement(turn, { align: 'start' }, { keepPreviousPeek: true })
    }

    #flushPending() {
        const pending = this.#pending
        const row = pending && this.items.find(item => item.dataset.messageId === pending.messageId)

        if (! row || ! this.#scrollToElement(row, pending.options)) return false

        this.#pending = null
        this.#markApplied()

        return true
    }

    #markApplied() {
        this.#applied = true
        this.#clearPendingScroll()
    }

    #clearPendingScroll() {
        this.removeAttribute('data-pending-scroll')
        this.viewport?.removeAttribute('data-pending-scroll')
    }

    #applyDefaultPosition() {
        const position = this.dataset.defaultScrollPosition || 'end'

        if (this.#applied || this.#count === 0) return false

        if (position === 'last-anchor') {
            const anchor = this.items.findLast(item => item.dataset.scrollAnchor === 'true')
            const fits = anchor && this.#contentBottom() - (this.#offset(anchor) + this.viewport.scrollTop) <= this.viewport.clientHeight

            anchor && ! fits ? this.#scrollToElement(anchor, { align: 'start' }, { keepPreviousPeek: true }) : this.scrollToEnd()
        } else {
            position === 'start' ? this.scrollToStart() : this.scrollToEnd()
        }

        this.#markApplied()

        return true
    }

    // Prepended rows: keep the remembered row where it was on screen.
    #restorePosition() {
        const keep = this.#keep

        if (! keep?.element.isConnected) return false

        const shift = this.#offset(keep.element) - keep.viewportTop

        if (Math.abs(shift) <= EPSILON) return false

        this.viewport.scrollTop += shift
        keep.viewportTop = this.#offset(keep.element)
        this.#scheduleCommit()

        return true
    }

    // Remembers the first row on screen, for when rows are prepended.
    #rememberVisibleRow() {
        const box = this.viewport.getBoundingClientRect()
        const items = this.items
        const row = items.find(item => {
            if (! item.dataset.messageId) return false

            const rect = item.getBoundingClientRect()

            return rect.bottom > box.top && rect.top < box.bottom
        })

        this.#keep = row ? { element: row, index: items.indexOf(row), viewportTop: this.#offset(row) } : null
    }

    #onContentChange() {
        const spacer = this.spacer

        // Rows added after the spacer (by other scripts) would sit below the room it makes.
        if (spacer && spacer !== this.content.lastElementChild) this.content.append(spacer)

        const items = this.items
        const before = this.#count
        const kept = this.#keep

        this.#count = items.length

        if (! this.#flushPending()) {
            if (before === 0) {
                // Turns already there when the transcript opens are not new turns.
                items.forEach(item => item.dataset.scrollAnchor === 'true' && this.#anchors.add(item))

                if (! this.#applyDefaultPosition() && ! (items.length > 0 && this.#autoScroll && this.scrollToEnd())) {
                    if (items.length === 0) this.#clearPendingScroll()
                    this.#commit()
                }
            } else if (this.viewport.dataset.preserveScrollOnPrepend !== 'false' && kept?.element.isConnected && items.indexOf(kept.element) > kept.index) {
                // Rows arrived above the one being read (even below a fixed "Load earlier" row).
                this.#restorePosition()
            } else {
                const isAnchor = item => item.dataset.scrollAnchor === 'true'
                const appended = items.length > before ? items.slice(before).filter(isAnchor) : []

                // A new turn: an appended anchor, or a row that just became one.
                const anchor = items.length > before
                    ? appended[0]
                    : items.length === before ? items.find(item => isAnchor(item) && ! this.#anchors.has(item)) : null

                if (anchor) {
                    this.#anchors.add(anchor)

                    // Several turns at once while following: just stay at the edge.
                    this.#autoScroll && this.#mode === FOLLOWING && appended.length > 1
                        ? this.scrollToEnd()
                        : this.#scrollToElement(anchor, { align: 'start' }, { keepPreviousPeek: true })
                } else if (this.#mode === FOLLOWING && this.#autoScroll) {
                    this.scrollToEnd()
                } else {
                    this.#commit()
                }
            }
        }

        this.#rememberVisibleRow()
    }

    #onResize() {
        if (this.#mode === FOLLOWING && this.#autoScroll) {
            this.scrollToEnd()

            return
        }

        const spacer = this.#spacerHeight

        if (this.#reanchor()) {
            // The reply outgrew the room below its turn: follow it from here.
            if (this.#autoScroll && spacer > 0 && this.#spacerHeight === 0) this.scrollToEnd()

            return
        }

        this.#scheduleCommit()
    }

    #userIntent() {
        if (this.#mode !== FREE) {
            this.#turn = null
            this.#mode = FREE
        }
    }

    #syncAfterScroll() {
        this.#commit()
        this.#rememberVisibleRow()
    }
}

export function register() {
    define('eso-message-scroller', MessageScrollerElement)
}
