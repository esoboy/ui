/*
 * <eso-message-scroller>
 *
 * The scroll behaviour of shadcn's MessageScroller (@shadcn/react), for a transcript
 * Livewire renders and morphs:
 * - opens at the end, the start, or the last anchored turn (data-scroll-position),
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

import { disconnected, listen } from './interaction.js'

const EPSILON = 0.5
const AUTOSCROLL_TIMEOUT = 180
const SCROLL_KEYS = new Set(['ArrowDown', 'ArrowUp', 'End', 'Home', 'PageDown', 'PageUp', ' '])

const FREE = 'free-scrolling'
const FOLLOWING = 'following-bottom'
const ANCHORED = 'anchored-to-message'
const JUMPING = 'settling-jump'

export default () => ({
    _anchors: null,
    _applied: null,
    _autoscrollTimer: null,
    _autoscrolling: null,
    _count: null,
    _frame: null,
    _keep: null,
    _lastTop: null,
    _mode: null,
    _mutations: null,
    _pending: null,
    _resize: null,
    _resizeFrame: null,
    _spacerHeight: null,
    _turn: null,
    init() {
        this._mode = FREE
        this._turn = null
        this._count = 0
        this._anchors = new WeakSet()
        this._spacerHeight = 0
        this._keep = null
        this._autoscrolling = false
        this._autoscrollTimer = 0
        this._lastTop = 0
        this._applied = false
        this._pending = null
        this._frame = 0
        this._resizeFrame = 0
        this._mutations = new MutationObserver(() => this._onContentChange())
        this._resize = new ResizeObserver(() => {
        cancelAnimationFrame(this._resizeFrame)
        this._resizeFrame = requestAnimationFrame(() => this._onResize())
    })

        const viewport = this.viewport
        const content = this.content

        if (! viewport || ! content) return

        listen(this, viewport, 'scroll', () => this._syncAfterScroll(), { passive: true })
        listen(this, viewport, 'wheel', () => this._userIntent(), { passive: true })
        listen(this, viewport, 'touchmove', () => this._userIntent(), { passive: true })
        listen(this, viewport, 'keydown', event => SCROLL_KEYS.has(event.key) && this._userIntent(), {})

        listen(this, this.$el, 'click', event => {
            const button = event.target.closest?.('[data-message-scroller-button]')

            if (! button || button.closest('eso-message-scroller') !== this.$el || button.dataset.active !== 'true') return

            const behavior = button.dataset.behavior || 'smooth'

            button.blur()
            button.dataset.messageScrollerButton === 'start' ? this.scrollToStart({ behavior }) : this.scrollToEnd({ behavior })
        }, {})

        this._mutations.observe(content, { childList: true })
        this._resize.observe(viewport)
        this._resize.observe(content)

        this._onContentChange()

    },
    destroy() {
        disconnected(this)
        this._mutations.disconnect()
        this._resize.disconnect()
        cancelAnimationFrame(this._frame)
        cancelAnimationFrame(this._resizeFrame)
        clearTimeout(this._autoscrollTimer)

        disconnected(this)
    },
    get viewport() {
        return this.$el.querySelector('[data-slot="message-scroller-viewport"]')
    },

    get content() {
        return this.$el.querySelector('[data-slot="message-scroller-content"]')
    },

    get spacer() {
        return this.content?.querySelector(':scope > [data-message-scroller-spacer]') ?? null
    },

    get items() {
        const spacer = this.spacer

        return [...(this.content?.children ?? [])].filter(element => element !== spacer)
    },

    get scrollable() {
        const viewport = this.viewport

        if (! viewport || ! this.content) return { start: false, end: false }

        const threshold = this._number('scrollEdgeThreshold', 8)

        return {
            start: viewport.scrollTop > threshold,
            end: this._contentBottom() - viewport.scrollTop - viewport.clientHeight > threshold,
        }
    },

    scrollToStart({ behavior = 'auto' } = {}) {
        if (! this.viewport) return false

        this._setSpacer(0)
        this._turn = null
        this._mode = FREE
        this._scrollTo(0, { behavior })

        return true
    },

    scrollToEnd({ behavior = 'auto' } = {}) {
        const viewport = this.viewport

        if (! viewport) return false

        this._setSpacer(0)
        this._turn = null
        this._mode = this._autoScroll ? FOLLOWING : FREE
        this._scrollTo(Math.max(0, viewport.scrollHeight - viewport.clientHeight), { behavior, autoscrolling: true })

        return true
    },

    scrollToMessage(messageId, options = {}) {
        const row = this.items.find(item => item.dataset.messageId === String(messageId))

        if (row) {
            this._markApplied()
            this._scrollToElement(row, options)
            this._pending = null

            return true
        }

        // Before any row exists, the jump waits for the transcript.
        if (this._count === 0) {
            this._pending = { messageId: String(messageId), options }
            this._markApplied()

            return true
        }

        return false
    },

    get _autoScroll() {
        return this.$el.hasAttribute('data-auto-scroll')
    },

    _number(name, fallback) {
        const value = Number(this.$el.dataset[name])

        return Number.isFinite(value) ? value : fallback
    },

    _padding(element) {
        const style = getComputedStyle(element)

        return { start: parseFloat(style.paddingTop) || 0, end: parseFloat(style.paddingBottom) || 0 }
    },

    _offset(element) {
        return element.getBoundingClientRect().top - this.viewport.getBoundingClientRect().top
    },

    _contentBottom() {
        const viewport = this.viewport
        const padding = this._padding(this.content)
        const top = viewport.getBoundingClientRect().top
        let bottom = padding.start + padding.end

        for (const item of this.items) {
            bottom = Math.max(bottom, item.getBoundingClientRect().bottom - top + viewport.scrollTop + padding.end)
        }

        return bottom
    },

    _commit() {
        const state = this.scrollable
        const top = this.viewport.scrollTop
        const movedUp = top < this._lastTop - EPSILON

        this._lastTop = top

        // At the live edge a following reader keeps following; scrolling up lets go.
        if (this._autoScroll && ! state.end && this._mode !== JUMPING && this._mode !== ANCHORED) this._mode = FOLLOWING
        else if (this._mode === FOLLOWING && state.end && movedUp && ! this._autoscrolling) this._mode = FREE

        const shown = this._mode === FOLLOWING ? { ...state, end: false } : state
        const scrollable = [shown.start && 'start', shown.end && 'end'].filter(Boolean).join(' ')

        for (const element of [this.$el, this.viewport]) {
            scrollable ? element.setAttribute('data-scrollable', scrollable) : element.removeAttribute('data-scrollable')
            element.toggleAttribute('data-autoscrolling', this._autoscrolling)
        }

        for (const button of this.$el.querySelectorAll('[data-message-scroller-button]')) {
            if (button.closest('eso-message-scroller') !== this.$el) continue

            const active = button.dataset.messageScrollerButton === 'start' ? shown.start : shown.end

            button.dataset.active = String(active)
            button.inert = ! active
            button.tabIndex = active ? 0 : -1
        }
    },

    _scheduleCommit() {
        cancelAnimationFrame(this._frame)
        this._frame = requestAnimationFrame(() => this._commit())
    },

    _setAutoscrolling(on) {
        clearTimeout(this._autoscrollTimer)

        if (this._autoscrolling !== on) {
            this._autoscrolling = on
            this._commit()
        }

        if (on) {
            this._autoscrollTimer = setTimeout(() => {
                this._autoscrolling = false
                this._commit()
            }, AUTOSCROLL_TIMEOUT)
        }
    },

    _setSpacer(height) {
        const spacer = this.spacer
        const value = Math.max(0, Math.ceil(height))

        if (! spacer || this._spacerHeight === value) return

        const gap = parseFloat(getComputedStyle(this.content).rowGap) || 0

        this._spacerHeight = value
        spacer.hidden = value === 0
        spacer.style.height = `${value}px`
        spacer.style.marginTop = value > 0 ? `${-gap}px` : ''
    },

    _scrollTo(top, { behavior = 'auto', autoscrolling = false } = {}) {
        const viewport = this.viewport
        const target = Math.max(0, top)

        if (Math.abs(viewport.scrollTop - target) <= EPSILON) {
            viewport.scrollTop = target
            this._commit()

            return
        }

        if (autoscrolling) this._setAutoscrolling(true)
        viewport.scrollTo({ top: target, behavior })
        this._scheduleCommit()
    },

    _targetTop(element, align, margin) {
        const viewport = this.viewport
        const top = this._offset(element) + viewport.scrollTop
        const height = element.getBoundingClientRect().height
        const padding = this._padding(this.content)

        if (align === 'center') return top - padding.start - (Math.max(0, viewport.clientHeight - padding.start - padding.end) - height) / 2 - margin
        if (align === 'end') return top - viewport.clientHeight + height + padding.end + margin

        if (align === 'nearest') {
            const visibleTop = viewport.scrollTop + padding.start
            const visibleBottom = viewport.scrollTop + viewport.clientHeight - padding.end

            if (top >= visibleTop && top + height <= visibleBottom) return viewport.scrollTop

            return top < visibleTop ? top - padding.start - margin : top + height - viewport.clientHeight + padding.end + margin
        }

        return top - padding.start - margin
    },

    _scrollToElement(element, { align = 'start', behavior = 'auto', scrollMargin } = {}, { keepPreviousPeek = false } = {}) {
        if (! this.content?.contains(element)) return false

        const margin = (scrollMargin ?? this._number('scrollMargin', 0)) + (keepPreviousPeek ? this._number('scrollPreviousItemPeek', 64) : 0)
        const top = this._targetTop(element, align, margin)

        this._setSpacer(top + this.viewport.clientHeight - this._contentBottom())
        this._keep = { element, index: this.items.indexOf(element), viewportTop: this._offset(element) }
        this._mode = keepPreviousPeek ? ANCHORED : JUMPING
        this._turn = keepPreviousPeek ? element : null
        this._scrollTo(top, { behavior })

        return true
    },

    _reanchor() {
        const turn = this._turn

        if (! turn?.isConnected || this._mode !== ANCHORED) return false

        return this._scrollToElement(turn, { align: 'start' }, { keepPreviousPeek: true })
    },

    _flushPending() {
        const pending = this._pending
        const row = pending && this.items.find(item => item.dataset.messageId === pending.messageId)

        if (! row || ! this._scrollToElement(row, pending.options)) return false

        this._pending = null
        this._markApplied()

        return true
    },

    _markApplied() {
        this._applied = true
        this._clearPendingScroll()
    },

    _clearPendingScroll() {
        this.$el.removeAttribute('data-pending-scroll')
        this.viewport?.removeAttribute('data-pending-scroll')
    },

    _applyDefaultPosition() {
        const position = this.$el.dataset.scrollPosition || 'end'

        if (this._applied || this._count === 0) return false

        if (position === 'last-anchor') {
            const anchor = this.items.findLast(item => item.dataset.scrollAnchor === 'true')
            const fits = anchor && this._contentBottom() - (this._offset(anchor) + this.viewport.scrollTop) <= this.viewport.clientHeight

            anchor && ! fits ? this._scrollToElement(anchor, { align: 'start' }, { keepPreviousPeek: true }) : this.scrollToEnd()
        } else {
            position === 'start' ? this.scrollToStart() : this.scrollToEnd()
        }

        this._markApplied()

        return true
    },

    _restorePosition() {
        const keep = this._keep

        if (! keep?.element.isConnected) return false

        const shift = this._offset(keep.element) - keep.viewportTop

        if (Math.abs(shift) <= EPSILON) return false

        this.viewport.scrollTop += shift
        keep.viewportTop = this._offset(keep.element)
        this._scheduleCommit()

        return true
    },

    _rememberVisibleRow() {
        const box = this.viewport.getBoundingClientRect()
        const items = this.items
        const row = items.find(item => {
            if (! item.dataset.messageId) return false

            const rect = item.getBoundingClientRect()

            return rect.bottom > box.top && rect.top < box.bottom
        })

        this._keep = row ? { element: row, index: items.indexOf(row), viewportTop: this._offset(row) } : null
    },

    _onContentChange() {
        const spacer = this.spacer

        // Rows added after the spacer (by other scripts) would sit below the room it makes.
        if (spacer && spacer !== this.content.lastElementChild) this.content.append(spacer)

        const items = this.items
        const before = this._count
        const kept = this._keep

        this._count = items.length

        if (! this._flushPending()) {
            if (before === 0) {
                // Turns already there when the transcript opens are not new turns.
                items.forEach(item => item.dataset.scrollAnchor === 'true' && this._anchors.add(item))

                if (! this._applyDefaultPosition() && ! (items.length > 0 && this._autoScroll && this.scrollToEnd())) {
                    if (items.length === 0) this._clearPendingScroll()
                    this._commit()
                }
            } else if (this.viewport.dataset.preserveScrollOnPrepend !== 'false' && kept?.element.isConnected && items.indexOf(kept.element) > kept.index) {
                // Rows arrived above the one being read (even below a fixed "Load earlier" row).
                this._restorePosition()
            } else {
                const isAnchor = item => item.dataset.scrollAnchor === 'true'
                const appended = items.length > before ? items.slice(before).filter(isAnchor) : []

                // A new turn: an appended anchor, or a row that just became one.
                const anchor = items.length > before
                    ? appended[0]
                    : items.length === before ? items.find(item => isAnchor(item) && ! this._anchors.has(item)) : null

                if (anchor) {
                    this._anchors.add(anchor)

                    // Several turns at once while following: just stay at the edge.
                    this._autoScroll && this._mode === FOLLOWING && appended.length > 1
                        ? this.scrollToEnd()
                        : this._scrollToElement(anchor, { align: 'start' }, { keepPreviousPeek: true })
                } else if (this._mode === FOLLOWING && this._autoScroll) {
                    this.scrollToEnd()
                } else {
                    this._commit()
                }
            }
        }

        this._rememberVisibleRow()
    },

    _onResize() {
        if (this._mode === FOLLOWING && this._autoScroll) {
            this.scrollToEnd()

            return
        }

        const spacer = this._spacerHeight

        if (this._reanchor()) {
            // The reply outgrew the room below its turn: follow it from here.
            if (this._autoScroll && spacer > 0 && this._spacerHeight === 0) this.scrollToEnd()

            return
        }

        this._scheduleCommit()
    },

    _userIntent() {
        if (this._mode !== FREE) {
            this._turn = null
            this._mode = FREE
        }
    },

    _syncAfterScroll() {
        this._commit()
        this._rememberVisibleRow()
    },
})
