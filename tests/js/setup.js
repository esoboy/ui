/*
 * jsdom has neither the Popover API, :modal, matchMedia nor ResizeObserver. Just enough for the tests:
 * open popovers carry data-popover-open, modal dialogs data-modal.
 */

const matches = Element.prototype.matches
const closest = Element.prototype.closest
const querySelector = Document.prototype.querySelector
const querySelectorAll = Element.prototype.querySelectorAll
const documentQuerySelectorAll = Document.prototype.querySelectorAll

const translate = selector => selector.replaceAll(':popover-open', '[data-popover-open]').replaceAll(':modal', '[data-modal]')

Element.prototype.matches = function (selector) { return matches.call(this, translate(selector)) }
Element.prototype.closest = function (selector) { return closest.call(this, translate(selector)) }
Element.prototype.querySelectorAll = function (selector) { return querySelectorAll.call(this, translate(selector)) }
Document.prototype.querySelector = function (selector) { return querySelector.call(this, translate(selector)) }
Document.prototype.querySelectorAll = function (selector) { return documentQuerySelectorAll.call(this, translate(selector)) }

// As the platform does: beforetoggle, the change, then toggle.
const toggle = (element, open) => {
    if (element.hasAttribute('data-popover-open') === open) return

    const newState = open ? 'open' : 'closed'

    element.dispatchEvent(Object.assign(new Event('beforetoggle'), { newState }))
    element.toggleAttribute('data-popover-open', open)
    element.dispatchEvent(Object.assign(new Event('toggle'), { newState }))
}

Element.prototype.scrollIntoView ||= function () {}

HTMLElement.prototype.showPopover = function () { toggle(this, true) }
HTMLElement.prototype.hidePopover = function () { toggle(this, false) }
HTMLElement.prototype.togglePopover = function (force) { toggle(this, force ?? ! this.hasAttribute('data-popover-open')) }

HTMLDialogElement.prototype.showModal = function () {
    this.setAttribute('open', '')
    this.setAttribute('data-modal', '')
}

HTMLDialogElement.prototype.close = function (value) {
    if (value !== undefined) this.returnValue = value
    this.removeAttribute('open')
    this.removeAttribute('data-modal')
    this.dispatchEvent(new Event('close'))
}

// Invoker commands: a click on a button with command runs it on the element commandfor
// names, or its commandForElement.
document.addEventListener('click', event => {
    const button = event.target.closest?.('button[command]')

    if (! button || button.disabled || event.defaultPrevented) return

    const target = button.commandForElement ?? document.getElementById(button.getAttribute('commandfor'))
    const command = button.getAttribute('command')

    if (! target) return

    if (command === 'toggle-popover') target.togglePopover()
    else if (command === 'show-popover') target.showPopover()
    else if (command === 'hide-popover') target.hidePopover()
    else if (command === 'show-modal') target.showModal()
    else if (command === 'close') target.close()
    else if (command.startsWith('--')) target.dispatchEvent(Object.assign(new Event('command'), { command, source: button }))
})

// Desktop unless a test swaps it.
window.matchMedia ||= query => ({ media: query, matches: false, addEventListener() {}, removeEventListener() {} })

// jsdom does no layout, so there is nothing to observe.
window.ResizeObserver ||= class { observe() {} unobserve() {} disconnect() {} }
