/*
 * Syntax highlighting for Code Block, with Shiki like shadcn's use-highlighted-code:
 * every <x-ui::code-block.content language="..."> gets GitHub light and dark colours,
 * and is highlighted again whenever Livewire morphs or streams new code into it.
 *
 * Built into dist/code-block.js; code-block.content loads it through @assets, so a
 * page downloads Shiki only when it shows highlighted code, and each language only
 * when that language is on the page.
 */

import { createHighlighterCore } from 'shiki/core'
import { createJavaScriptRegexEngine } from 'shiki/engine/javascript'

// shadcn's languages, plus PHP and Blade. Each loads the first time it is used.
const LANGUAGES = {
    bash: () => import('shiki/langs/shellscript.mjs'),
    blade: () => import('shiki/langs/blade.mjs'),
    css: () => import('shiki/langs/css.mjs'),
    diff: () => import('shiki/langs/diff.mjs'),
    html: () => import('shiki/langs/html.mjs'),
    javascript: () => import('shiki/langs/javascript.mjs'),
    json: () => import('shiki/langs/json.mjs'),
    jsx: () => import('shiki/langs/jsx.mjs'),
    markdown: () => import('shiki/langs/markdown.mjs'),
    php: () => import('shiki/langs/php.mjs'),
    python: () => import('shiki/langs/python.mjs'),
    sql: () => import('shiki/langs/sql.mjs'),
    tsx: () => import('shiki/langs/tsx.mjs'),
    typescript: () => import('shiki/langs/typescript.mjs'),
    yaml: () => import('shiki/langs/yaml.mjs'),
}

const ALIASES = { js: 'javascript', md: 'markdown', py: 'python', sh: 'bash', shell: 'bash', shellscript: 'bash', ts: 'typescript', yml: 'yaml', zsh: 'bash' }

const PENDING = '[data-slot="code-block-content"][data-language] [data-slot="code-block-line-content"]:not([data-highlighted])'

let shiki = null

function highlighter() {
    shiki ??= createHighlighterCore({
        themes: [import('shiki/themes/github-light.mjs'), import('shiki/themes/github-dark.mjs')],
        langs: [],
        engine: createJavaScriptRegexEngine({ forgiving: true }),
    })

    return shiki
}

async function highlight(pre) {
    const name = pre.dataset.language.toLowerCase()
    const lang = LANGUAGES[name] ? name : ALIASES[name]

    if (! lang) return

    const core = await highlighter()

    if (! core.getLoadedLanguages().includes(lang)) await core.loadLanguage(LANGUAGES[lang])

    const rows = [...pre.querySelectorAll('[data-slot="code-block-line-content"]')]
    const code = rows.map(row => (row.textContent === '\n' ? '' : row.textContent)).join('\n')
    const lines = core.codeToTokens(code, { lang, themes: { light: 'github-light', dark: 'github-dark' }, defaultColor: false }).tokens

    rows.forEach((row, index) => {
        const tokens = lines[index] ?? []

        // The text may have changed while Shiki loaded; the next pass picks it up.
        if (tokens.map(token => token.content).join('') !== (row.textContent === '\n' ? '' : row.textContent)) return

        if (tokens.length) {
            row.replaceChildren(...tokens.map(token => {
                const span = document.createElement('span')

                span.dataset.token = ''
                span.textContent = token.content
                for (const [name, value] of Object.entries(token.htmlStyle ?? {})) span.style.setProperty(name, value)

                return span
            }))
        }

        row.setAttribute('data-highlighted', '')
    })
}

export function initCodeHighlighting(root = document) {
    let timer = 0

    // Batches a burst of mutations (a morph, a streamed chunk) into one pass.
    const run = () => {
        clearTimeout(timer)
        timer = setTimeout(() => {
            const blocks = new Set([...root.querySelectorAll(PENDING)].map(row => row.closest('[data-slot="code-block-content"]')))

            blocks.forEach(pre => highlight(pre).catch(() => {}))
        })
    }

    new MutationObserver(run).observe(root, { childList: true, subtree: true, characterData: true })
    run()
}

initCodeHighlighting()
