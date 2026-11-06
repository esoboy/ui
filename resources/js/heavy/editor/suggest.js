import { bind } from '../../alpine/interaction.js'
// Position the Blade suggestions at the editor caret and insert the selected option.
import { Extension } from '@tiptap/core'
import { PluginKey } from '@tiptap/pm/state'
import Suggestion from '@tiptap/suggestion'

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

        this.listeners = [
            bind(menu, 'pointerdown', event => event.preventDefault()),
            bind(menu, 'click', event => {
                const option = event.target.closest('[role="option"]')
                if (option) this.pick(option)
            }),
            bind(menu, 'pointermove', event => {
                const option = event.target.closest('[role="option"]')
                if (option) this.activate(option)
            }),
        ]
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
        this.activate(null)
        this.listeners.forEach(dispose => dispose())
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

export function suggestionMenu(kind, menuFor, search, pick) {
    const char = kind === 'slash' ? '/' : ':'

    return Extension.create({
        name: `uiEditor-${kind}`,
        addProseMirrorPlugins() {
            return [Suggestion({
                editor: this.editor,
                pluginKey: new PluginKey(`uiEditor-${kind}`),
                char,
                minQueryLength: kind === 'emoji' ? 1 : 0,
                render: () => {
                    let options = null
                    let cleanup = null
                    let current = null
                    let revision = 0
                    let rendered = 0
                    let timer = null
                    let stopMorph = null

                    const close = () => {
                        if (options) options.menu.hidden = true
                        options?.close()
                        cleanup?.()
                        options = cleanup = null
                    }

                    const show = () => {
                        const menu = menuFor()
                        if (!menu || !current || rendered !== revision) return
                        if (menu !== options?.menu || !menu.style.width) {
                            close()
                            options = new Options(menu, option => current.command(option), current.editor.view.dom)
                            cleanup = current.mount(menu)
                        }
                        options.reset()
                    }

                    const update = (props, delay = 0) => {
                        current = props
                        const request = ++revision
                        if (options) options.menu.hidden = true
                        clearTimeout(timer)
                        timer = setTimeout(async () => {
                            try {
                                await search(kind, props.query)
                                await new Promise(resolve => requestAnimationFrame(resolve))
                                if (request !== revision) return
                                rendered = request
                                show()
                            } catch {}
                        }, delay)
                    }

                    return {
                        onStart: props => {
                            stopMorph ??= window.Livewire?.hook('morphed', ({ el }) => {
                                const menu = menuFor()
                                if (menu && el.contains(menu) && (menu !== options?.menu || menu.hidden || !menu.style.width)) show()
                            })
                            update(props)
                        },
                        onUpdate: props => update(props, 100),
                        onKeyDown: ({ event }) => event.key !== 'Escape' && !!options && !options.menu.hidden && options.key(event),
                        onExit: () => {
                            revision++
                            clearTimeout(timer)
                            stopMorph?.()
                            stopMorph = null
                            close()
                            current = null
                        },
                    }
                },
                command: ({ editor, range, props: option }) => {
                    editor.chain().focus().deleteRange(range).run()
                    pick(editor, option)
                },
            })]
        },
    })
}
