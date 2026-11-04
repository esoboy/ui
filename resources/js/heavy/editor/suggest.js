/*
 * Menus while typing. / lists what the format can insert (the view's slash template,
 * narrowed by each item's label and data-keywords); : and a word list the emojis it
 * names. Arrow keys move the active item, Enter or a click picks it, Escape closes; the
 * text box points at the menu and its active item, so a screen reader follows them.
 */

import { Extension } from '@tiptap/core'
import { PluginKey } from '@tiptap/pm/state'
import Suggestion from '@tiptap/suggestion'
import { load, score } from '../../support/emoji.js'

let lists = 0

/**
 * A list's active item and keys: the items are its [role="option"] elements not hidden.
 */
class Options {
    constructor(menu, pick, input) {
        this.menu = menu
        this.pick = pick
        this.input = input
        this.active = null

        menu.id ||= `editor-suggestion-${++lists}`
        input.setAttribute('aria-controls', menu.id)

        menu.addEventListener('pointerdown', event => event.preventDefault())
        menu.addEventListener('click', event => {
            const option = event.target.closest('[role="option"]')

            if (option) this.pick(option)
        })
        menu.addEventListener('pointermove', event => {
            const option = event.target.closest('[role="option"]')

            if (option) this.activate(option)
        })
    }

    get options() {
        return [...this.menu.querySelectorAll('[role="option"]:not([hidden])')]
    }

    // The first option, or none (and no menu) when nothing matches.
    reset() {
        const [first] = this.options

        this.menu.hidden = ! first
        this.activate(first ?? null)
    }

    activate(option) {
        this.active?.setAttribute('aria-selected', 'false')
        this.active = option
        option?.setAttribute('aria-selected', 'true')
        option?.scrollIntoView({ block: 'nearest' })

        if (option) option.id ||= `${this.menu.id}-${++lists}`
        option ? this.input.setAttribute('aria-activedescendant', option.id) : this.input.removeAttribute('aria-activedescendant')
    }

    close() {
        this.input.removeAttribute('aria-activedescendant')
        this.input.removeAttribute('aria-controls')
    }

    key(event) {
        const options = this.options

        if (! options.length) return false

        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            const index = options.indexOf(this.active) + (event.key === 'ArrowDown' ? 1 : -1)

            this.activate(options[(index + options.length) % options.length])

            return true
        }

        if (event.key === 'Enter' || event.key === 'Tab') {
            if (this.active) this.pick(this.active)

            return true
        }

        return false
    }
}

// A suggestion whose menu is built and narrowed by build(props) and filter(menu, props).
function suggestion({ name, char, editor, build, filter, pick, minQueryLength = 0 }) {
    return Suggestion({
        editor,
        pluginKey: new PluginKey(name),
        char,
        minQueryLength,
        render: () => {
            let menu = null
            let options = null
            let cleanup = null
            let current = null

            return {
                onStart: props => {
                    current = props
                    menu = build()
                    options = new Options(menu, option => current.command(option), props.editor.view.dom)
                    filter(menu, props).then(() => options?.reset())
                    cleanup = props.mount(menu)
                },
                onUpdate: props => {
                    current = props
                    filter(menu, props).then(() => options?.reset())
                },
                onKeyDown: ({ event }) => event.key !== 'Escape' && !! menu && ! menu.hidden && options.key(event),
                onExit: () => {
                    options?.close()
                    cleanup?.()
                    menu?.remove()
                    menu = options = cleanup = null
                },
            }
        },
        command: ({ editor, range, props: option }) => {
            editor.chain().focus().deleteRange(range).run()
            pick(option)
        },
    })
}

/**
 * The slash menu: its items are toolbar controls (data-editor-command and the rest),
 * done as a click on them would be.
 */
export function slashMenu(template, run) {
    return Extension.create({
        name: 'slashMenu',

        addProseMirrorPlugins() {
            return [suggestion({
                name: 'editorSlash',
                char: '/',
                editor: this.editor,
                build: () => template.content.firstElementChild.cloneNode(true),
                filter: async (menu, { query }) => {
                    query = query.toLocaleLowerCase()

                    for (const option of menu.querySelectorAll('[role="option"]')) {
                        option.hidden = !! query && ! `${option.textContent} ${option.dataset.keywords ?? ''}`.toLocaleLowerCase().includes(query)
                    }
                },
                pick: run,
            })]
        },
    })
}

/**
 * Emoji shortcodes: the best eight matches of the Emoji Picker's data at a URL.
 */
export function emojiMenu(template, url) {
    let emojis = null

    const all = () => emojis ??= load(url).then(data => Object.values(data).flat().map(([emoji, label, keywords]) => ({ emoji, label, search: { label: label.toLowerCase(), keywords: keywords.toLowerCase().split('|') } })))

    return Extension.create({
        name: 'emojiMenu',

        addProseMirrorPlugins() {
            const editor = this.editor

            return [suggestion({
                name: 'editorEmoji',
                char: ':',
                editor,
                minQueryLength: 1,
                build: () => template.content.firstElementChild.cloneNode(true),
                filter: async (menu, { query }) => {
                    const option = menu.querySelector('template')
                    const list = await all().catch(() => [])

                    query = query.toLowerCase()

                    const matches = list
                        .map(emoji => ({ emoji, score: score(emoji.search, query) }))
                        .filter(({ score }) => score)
                        .sort((a, b) => b.score - a.score)
                        .slice(0, 8)

                    menu.querySelectorAll('[role="option"]').forEach(element => element.remove())
                    menu.append(...matches.map(({ emoji }) => {
                        const element = option.content.firstElementChild.cloneNode(true)

                        element.querySelector('[data-editor-emoji]').textContent = emoji.emoji
                        element.querySelector('[data-editor-emoji-label]').textContent = emoji.label
                        element.dataset.emoji = emoji.emoji

                        return element
                    }))
                },
                pick: option => editor.chain().focus().insertContent(option.dataset.emoji).run(),
            })]
        },
    })
}
