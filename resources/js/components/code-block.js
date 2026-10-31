/*
 * Code Block copy buttons copy their data-value, or the code of their block, then show
 * a check (and announce it) for two seconds, as shadcn's CodeBlockCopyButton does.
 */

const COPIED_FOR = 2000

/** The text of a code block, one line per row. */
export function codeOf(block) {
    const lines = block?.querySelectorAll('[data-slot="code-block-line-content"]') ?? []

    if (lines.length) return [...lines].map(line => (line.textContent === '\n' ? '' : line.textContent)).join('\n')

    return block?.querySelector('[data-slot="code-block-code"]')?.textContent ?? ''
}

export function register() {
    const timers = new WeakMap()

    document.addEventListener('click', event => {
        const button = event.target.closest?.('[data-code-block-copy]')

        if (! button) return

        const value = button.dataset.value ?? codeOf(button.closest('[data-slot="code-block"]'))
        const status = button.nextElementSibling?.matches('[data-code-block-status]') ? button.nextElementSibling : null

        window.navigator.clipboard?.writeText(value).then(() => {
            button.setAttribute('data-copied', '')
            button.setAttribute('aria-label', button.dataset.labelCopied ?? '')
            if (status) status.textContent = button.dataset.labelCopied ?? ''

            window.clearTimeout(timers.get(button))
            timers.set(button, window.setTimeout(() => {
                button.removeAttribute('data-copied')
                button.setAttribute('aria-label', button.dataset.labelCopy ?? '')
                if (status) status.textContent = ''
            }, COPIED_FOR))
        }).catch(() => {})
    })
}
