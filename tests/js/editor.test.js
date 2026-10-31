import { afterEach, beforeAll, describe, expect, it, vi } from 'vitest'
import full from '../Fixtures/editor-full.json'

/*
 * ProseMirror measures the selection to scroll it into view; jsdom lays nothing out.
 */
beforeAll(async () => {
    const rect = () => ({ top: 0, left: 0, right: 0, bottom: 0, width: 0, height: 0, x: 0, y: 0 })

    // jsdom has no canvas to read color names with.
    HTMLCanvasElement.prototype.getContext = () => null
    Range.prototype.getClientRects = () => []
    Range.prototype.getBoundingClientRect = rect
    Element.prototype.getClientRects = () => []
    document.elementFromPoint = () => null

    await import('../../resources/js/heavy/editor.js')
})

afterEach(() => {
    document.body.innerHTML = ''
    vi.restoreAllMocks()
    vi.unstubAllGlobals()
})

// What the view writes into data-format (RichTextFormat::client()): the full format's,
// kept in step with the server by EditorTest; a list of features narrows its whitelist.
const format = (features, allow = null) => ({
    ...full,
    features,
    allow: allow ?? (features.length === full.features.length ? full.allow : { p: [], br: [], div: [] }),
})

const COMMANDS = ['undo', 'redo', 'bold', 'italic', 'underline', 'strike', 'code', 'superscript', 'subscript', 'clear-formatting', 'indent', 'outdent', 'text-indent', 'bullet-list', 'ordered-list', 'task-list', 'blockquote', 'code-block', 'horizontal-rule', 'table', 'details']
const TOGGLES = ['bold', 'italic', 'underline', 'strike', 'code', 'superscript', 'subscript', 'text-indent', 'bullet-list', 'ordered-list', 'task-list', 'blockquote', 'code-block', 'details']

function mount(html = '<h2>Notes</h2><p>Hello world</p>', { features = full.features, attributes = '', whitelist = null } = {}) {
    document.body.innerHTML = `
        <form>
            <eso-editor id="body" data-slot="editor" data-editor-name="body" data-labels='{"keyboard":"Alt+F10","failed":"Upload failed"}' data-format='${JSON.stringify({ ...format(features), ...whitelist }).replaceAll("'", '&#39;')}' data-placeholder="Write" data-label="Notes" data-name="body" ${attributes}>
                <div data-slot="editor-toolbar" role="toolbar">
                    ${COMMANDS.map(command => `<button type="button" data-editor-command="${command}" aria-label="${command}" ${TOGGLES.includes(command) ? 'aria-pressed="false"' : ''}></button>`).join('')}
                    <button type="button" data-editor-action="media" aria-label="Media"></button>
                    <button type="button" data-editor-action="find" aria-label="Find" aria-pressed="false"></button>
                    <button type="button" data-editor-action="source" aria-label="Source" aria-pressed="false"></button>
                    <button type="button" data-editor-action="fullscreen" aria-label="Full screen" aria-pressed="false"></button>
                    <button type="button" data-editor-action="format-painter" aria-label="Format painter" aria-pressed="false"></button>
                    <button type="button" data-editor-opens="link" commandfor="link" aria-pressed="false" aria-label="Link"></button>
                    <div id="link" popover="auto" data-editor-field-content>
                        <input type="url" data-editor-link-input>
                        <button type="button" data-editor-action="link-apply"></button>
                        <button type="button" data-editor-action="link-remove"></button>
                    </div>
                    <button type="button" data-editor-menu="heading" data-dropdown-menu-trigger command="toggle-popover" commandfor="" aria-label="Style"><svg data-editor-icon=""></svg><svg data-editor-icon="2" hidden></svg></button>
                    <div id="headings" popover="auto" data-slot="dropdown-menu-content">
                        <button type="button" role="menuitemradio" aria-checked="false" data-editor-command="paragraph" data-editor-value=""></button>
                        <button type="button" role="menuitemradio" aria-checked="false" data-editor-command="heading" data-editor-value="2"></button>
                    </div>
                    <button type="button" data-editor-menu="font-size" data-dropdown-menu-trigger command="toggle-popover" commandfor="" aria-label="Size"><span data-editor-current data-editor-default="Default size"></span></button>
                    <div id="sizes" popover="auto" data-slot="dropdown-menu-content">
                        <button type="button" role="menuitemradio" aria-checked="false" data-editor-command="font-size" data-editor-value="" data-editor-label="Default size"></button>
                        <button type="button" role="menuitemradio" aria-checked="false" data-editor-command="font-size" data-editor-value="18px" data-editor-label="18"></button>
                    </div>
                    <div id="colors" popover="auto">
                        <button type="button" data-editor-command="color" data-editor-value="#dc2626" aria-pressed="false"></button>
                        <button type="button" data-editor-command="highlight" data-editor-value="#bbf7d0" aria-pressed="false"></button>
                    </div>
                </div>
                <div data-slot="editor-find" hidden>
                    <input type="search" data-editor-find-input>
                    <span data-editor-find-count></span>
                    <button type="button" data-editor-action="find-case" aria-pressed="false"></button>
                    <button type="button" data-editor-action="find-previous"></button>
                    <button type="button" data-editor-action="find-next"></button>
                    <input data-editor-replace-input>
                    <button type="button" data-editor-action="replace"></button>
                    <button type="button" data-editor-action="replace-all"></button>
                    <button type="button" data-editor-action="find-close"></button>
                </div>
                <div data-slot="editor-content">${html}</div>
                <textarea data-slot="editor-source" hidden></textarea>
                <div data-slot="editor-footer"><span data-editor-busy data-editor-label=":count uploading" data-editor-refused=":count still uploading" role="status" hidden></span><span data-editor-unsaved data-editor-label=":count unsaved" hidden><span data-editor-unsaved-label></span><button type="button" data-editor-action="save-images"></button></span><span data-editor-count></span></div>
                <template data-template="uploading"><span data-slot="editor-uploading"><span>Uploading…</span><span data-editor-upload-progress></span><button type="button" data-editor-action="dismiss-upload"></button></span></template>
                <template data-template="upload-failed"><span data-slot="editor-uploading" data-editor-failed role="alert"><span data-editor-upload-message>Upload failed</span><button type="button" data-editor-action="dismiss-upload"></button></span></template>
                <template data-template="missing-image"><span data-slot="editor-uploading" data-editor-missing><span data-editor-upload-message hidden></span><button type="button" data-editor-action="upload-missing"></button><button type="button" data-editor-action="dismiss-upload"></button></span></template>
                <div popover="manual" data-slot="tooltip-content" data-editor-tip><span data-editor-tip-label></span><kbd data-editor-tip-keys hidden></kbd></div>
            </eso-editor>
        </form>`

    const element = document.querySelector('eso-editor')
    const events = []

    element.addEventListener('input', () => events.push('input'))
    element.addEventListener('change', () => events.push('change'))

    return [element, events]
}

const control = (element, selector) => element.querySelector(selector)

// A command's control, made for the test: clicked to run it, or asked whether it is on.
function commandControl(element, command, value) {
    const button = Object.assign(document.createElement('button'), { type: 'button' })

    button.dataset.editorCommand = command
    button.dataset.editorValue = value
    button.setAttribute('aria-pressed', 'false')
    element.querySelector('[data-slot="editor-toolbar"]').append(button)

    return button
}

const run = (element, command, value = '') => commandControl(element, command, value).click()

function active(element, command, value = '') {
    const button = commandControl(element, command, value)

    element.editor.view.dispatch(element.editor.state.tr)

    return button.getAttribute('aria-pressed') === 'true'
}
const button = (element, command) => control(element, `[data-editor-command="${command}"]`)
const select = (element, text) => {
    let from = 0

    element.editor.state.doc.descendants((node, position) => {
        if (node.isText && node.text.includes(text)) from = position + node.text.indexOf(text)
    })
    element.editor.commands.setTextSelection({ from, to: from + text.length })
}

// Pastes clipboard data as the browser would.
// The view's attributes with an uploader (config ui.editor.uploader).
const UPLOADS = 'data-upload="/upload" data-upload-token="token" data-upload-context="sealed"'

/**
 * The uploader's route, answered by the test: each post waits for answer(attachment) or
 * answer(null, status, errors). Images' own addresses answer as their sites would: those
 * in readable as pictures, the rest not to pages (CORS).
 */
function uploader({ readable = [] } = {}) {
    const posts = []

    // Uploads go by XMLHttpRequest, for its progress: each one sent waits here.
    vi.stubGlobal('XMLHttpRequest', class {
        upload = new EventTarget()
        headers = {}
        listeners = {}

        open(method, url) {
            this.url = url
        }

        setRequestHeader(name, value) {
            this.headers[name] = value
        }

        addEventListener(type, listener) {
            this.listeners[type] = listener
        }

        send(body) {
            posts.push({
                url: this.url,
                headers: this.headers,
                form: Object.fromEntries(body.entries()),
                progress: (loaded, total) => this.upload.dispatchEvent(Object.assign(new Event('progress'), { lengthComputable: true, loaded, total })),
                answer: (attachment, status = 200, errors = {}) => {
                    this.status = status
                    this.responseText = JSON.stringify(status === 200 ? { attachment } : { message: 'The given data was invalid.', errors })
                    this.listeners.load()
                },
            })
        }
    })

    // The browser reading another site's image.
    vi.stubGlobal('fetch', vi.fn(url => readable.includes(url) ? Promise.resolve(new Response('PNG', { headers: { 'Content-Type': 'image/png' } })) : Promise.reject(new TypeError('Failed to fetch'))))

    return posts
}

// The file inputs media buttons open, to choose files in with choose().
function chooser() {
    const inputs = []

    vi.spyOn(HTMLInputElement.prototype, 'click').mockImplementation(function () {
        if (this.type === 'file') inputs.push(this)
    })

    return inputs
}

function choose(input, files) {
    Object.defineProperty(input, 'files', { value: files })
    input.dispatchEvent(new Event('change'))
}

// Uploads and their answers take a few turns of the event loop.
const settle = async () => {
    for (let turn = 0; turn < 6; turn++) await new Promise(resolve => setTimeout(resolve, 0))
}

// A media button of a kind, as the toolbar has them.
function mediaButton(element, kind) {
    const button = Object.assign(document.createElement('button'), { type: 'button' })

    button.dataset.editorAction = 'media'
    button.dataset.editorValue = kind
    element.querySelector('[data-slot="editor-toolbar"]').append(button)

    return button
}

function paste(element, data) {
    const clipboardData = { getData: type => data[type] ?? '', files: data.files ?? [], types: Object.keys(data) }
    const event = new Event('paste', { bubbles: true, cancelable: true })

    Object.defineProperty(event, 'clipboardData', { value: clipboardData })
    element.editor.view.dom.dispatchEvent(event)
}

describe('mounting', () => {
    it('edits the HTML its view rendered, as a labelled text box', () => {
        const [element, events] = mount()
        const input = element.querySelector('[data-slot="editor-input"]')

        expect(element.value).toBe('<h2>Notes</h2><p>Hello world</p>')
        expect(input.getAttribute('contenteditable')).toBe('true')
        expect(input.getAttribute('role')).toBe('textbox')
        expect(input.getAttribute('aria-multiline')).toBe('true')
        expect(input.getAttribute('aria-label')).toBe('Notes')
        expect(events).toEqual([])
    })

    it('is empty as an empty string, with its placeholder', () => {
        const [element] = mount('')

        expect(element.value).toBe('')
        expect(element.querySelector('p.is-editor-empty').dataset.placeholder).toBe('Write')
    })

    it('makes its toolbar one tab stop, with tooltips and shortcuts', () => {
        const [element] = mount()
        const undo = button(element, 'undo')
        const bold = button(element, 'bold')

        expect([...element.querySelectorAll('[data-slot="editor-toolbar"] > button')].filter(control => control.tabIndex === 0)).toEqual([bold])
        expect(bold.hasAttribute('data-editor-tooltip')).toBe(true)
        expect(undo.disabled).toBe(true)

        bold.dataset.editorKeys = 'Mod-b'
        bold.dispatchEvent(new Event('pointerover', { bubbles: true }))

        expect(element.querySelector('[data-editor-tip-label]').textContent).toBe('bold')
        expect(element.querySelector('[data-editor-tip-keys]').textContent).toBe('Ctrl+B')
        expect(element.querySelector('[data-editor-tip-keys]').hidden).toBe(false)
        expect(element.querySelector('[data-editor-tip]').matches(':popover-open')).toBe(true)

        bold.focus()
        bold.dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowRight', bubbles: true }))

        expect(document.activeElement).toBe(button(element, 'italic'))
        expect(bold.tabIndex).toBe(-1)
        expect(button(element, 'italic').tabIndex).toBe(0)
    })
})

describe('formats', () => {
    it('writes only what its format has, whatever it is given', () => {
        const [text] = mount('<h2>Title</h2><p><strong>Bold</strong> <a href="https://x.test">link</a></p><ul><li><p>Item</p></li></ul>', { features: [] })

        expect(text.value).toBe('<p>Title</p><p>Bold link</p><p>Item</p>')

        const [comment] = mount('<h2>Title</h2><p><strong>Bold</strong> <em>i</em> <span style="color: #dc2626">red</span></p>', { features: ['bold'] })

        expect(comment.value).toBe('<p>Title</p><p><strong>Bold</strong> i red</p>')
    })
})

describe('commands', () => {
    it('toggles marks and blocks, and shows what is on', () => {
        const [element, events] = mount()

        select(element, 'world')
        button(element, 'bold').click()
        button(element, 'superscript').click()

        expect(element.value).toBe('<h2>Notes</h2><p>Hello <strong><sup>world</sup></strong></p>')
        expect(button(element, 'bold').getAttribute('aria-pressed')).toBe('true')
        expect(events).toContain('input')

        button(element, 'task-list').click()

        expect(element.value).toContain('<ul style="list-style-type: none; padding-left: 0px;"><li style="display: flex; align-items: baseline; gap: 0.5em;"><input type="checkbox" disabled="disabled"><div><p>Hello')
    })

    it('checks the current value in its menus, and shows it on their button', () => {
        const [element] = mount()
        const style = control(element, '[data-editor-menu="heading"]')

        select(element, 'Notes')

        expect(control(element, '[data-editor-command="heading"]').getAttribute('aria-checked')).toBe('true')
        expect(style.querySelector('[data-editor-icon="2"]').hasAttribute('hidden')).toBe(false)
        expect(style.querySelector('[data-editor-icon=""]').hasAttribute('hidden')).toBe(true)

        select(element, 'world')
        control(element, '[data-editor-command="font-size"][data-editor-value="18px"]').click()

        expect(element.value).toBe('<h2>Notes</h2><p>Hello <span style="font-size: 18px;">world</span></p>')
        expect(control(element, '[data-editor-current]').textContent).toBe('18')
    })
})

describe('styles', () => {
    it('writes colors, highlights, spacing and text layout as inline style', () => {
        const [element] = mount('<p>Hello world</p>')

        select(element, 'Hello')
        control(element, '[data-editor-command="color"]').click()
        select(element, 'world')
        control(element, '[data-editor-command="highlight"]').click()
        element.editor.chain().setBlockStyle('text-align', 'center').setBlockStyle('line-height', '1.75').setBlockStyle('margin-bottom', '10px').setBlockStyle('letter-spacing', '1px').indent().indent().toggleTextIndent().run()

        expect(element.value).toBe('<p style="text-align: center; line-height: 1.75; margin-bottom: 10px; letter-spacing: 1px; margin-left: 4em; text-indent: 2em;"><span style="color: rgb(220, 38, 38);">Hello</span> <mark style="background-color: rgb(187, 247, 208); color: rgb(23, 23, 23);">world</mark></p>')
        expect(active(element, 'text-align', 'center')).toBe(true)

        element.editor.chain().selectAll().clearFormatting().run()

        expect(element.value).toBe('<p>Hello world</p>')
    })

    it('changes one property of a style, keeping the others', () => {
        const [element] = mount('<p><span style="font-size: 20px; color: rgb(255, 0, 0);">Hello</span> world</p>')

        element.editor.commands.setTextSelection({ from: 1, to: 12 })
        control(element, '[data-editor-command="color"]').click()

        expect(element.value).toBe('<p><span style="font-size: 20px; color: rgb(220, 38, 38);">Hello</span><span style="color: rgb(220, 38, 38);"> world</span></p>')
    })

    it('keeps the whitelisted CSS of what it is given, as written, and nothing else', () => {
        const [element] = mount('<p style="text-align: right; position: fixed; line-height: 2;"><span style="color: #111; font-size: 10.5pt; font-family: Songti SC, SimSun; z-index: 9; background: url(javascript:x)">a</span> <span style="background-color: yellow">b</span> <span data-x="1">c</span></p>')

        expect(element.value).toBe('<p style="text-align: right; line-height: 2;"><span style="color: rgb(17, 17, 17); font-size: 10.5pt; font-family: Songti SC, SimSun;">a</span> <span style="background-color: yellow;">b</span> c</p>')
    })

    it('keeps tags no feature writes when the whitelist has them, as they are', () => {
        const [element] = mount('<section style="padding: 8px; position: fixed;"><p>One</p><section style="color: red;">Two <small>small</small> <abbr title="Laravel">LV</abbr></section></section><div>Three</div><marquee>Four</marquee>')

        expect(element.value).toBe('<section style="padding: 8px;"><p>One</p><section style="color: red;">Two <small>small</small> <abbr title="Laravel">LV</abbr></section></section><div>Three</div><p>Four</p>')
        expect(element.editor.getJSON().content.map(node => node.type)).toEqual(['container', 'textContainer', 'paragraph'])
    })

    it('makes lists pasted from Word and WPS lists', async () => {
        const { wordLists } = await import('../../resources/js/heavy/editor/paste.js')
        const word = '<p class="MsoListParagraph" style="mso-list:l0 level1 lfo1"><!--[if !supportLists]--><span style="mso-list:Ignore">1.<span>&nbsp;</span></span><!--[endif]-->One</p>'
            + '<p class="MsoListParagraph" style="mso-list:l0 level2 lfo1"><span style="font-family:Symbol"><span style="mso-list:Ignore">·</span></span>Inner</p>'
            + '<p class="MsoListParagraph" style="mso-list:l0 level1 lfo1"><span style="mso-list:Ignore">2.</span>Two</p><p>After</p>'

        expect(wordLists(word).replace(/<!--.*?-->/g, '')).toBe('<ol><li><p>One</p><ul><li><p>Inner</p></li></ul></li><li><p>Two</p></li></ol><p>After</p>')
        expect(wordLists('<p>Plain</p>')).toBe('<p>Plain</p>')
    })

    it('keeps Word lists apart, without the indent that drew them', async () => {
        const { wordLists } = await import('../../resources/js/heavy/editor/paste.js')
        const item = (list, level, marker, text) => `<p class="MsoListParagraphCxSpMiddle" style="margin-left:${18 * level}pt;text-indent:-18pt;line-height:150%;mso-list:${list} level${level} lfo1"><span style="mso-list:Ignore">${marker}</span>${text}</p>`
        const word = item('l0', 1, '·', 'Bullet') + item('l1', 1, '1.', 'First') + item('l1', 2, '·', 'Inner') + item('l1', 1, '2.', 'Second') + item('l1', 1, '·', 'Bullet after')

        expect(wordLists(word)).toBe('<ul><li><p style="line-height: 150%;">Bullet</p></li></ul>'
            + '<ol><li><p style="line-height: 150%;">First</p><ul><li><p style="line-height: 150%;">Inner</p></li></ul></li><li><p style="line-height: 150%;">Second</p></li></ol>'
            + '<ul><li><p style="line-height: 150%;">Bullet after</p></li></ul>')
    })

    it('keeps the style of blocks pasted on an empty line, and runs them into text elsewhere', () => {
        const [element] = mount('<p>Hello</p><p></p>')
        const html = '<p style="text-align: center;">One</p><p style="text-align: right;">Two</p>'

        element.editor.commands.setTextSelection(8)
        paste(element, { 'text/html': html })

        expect(element.value).toBe('<p>Hello</p><p style="text-align: center;">One</p><p style="text-align: right;">Two</p>')

        element.editor.commands.setTextSelection(3)
        paste(element, { 'text/html': html })

        expect(element.value).toBe('<p>HeOne</p><p style="text-align: right;">Twollo</p><p style="text-align: center;">One</p><p style="text-align: right;">Two</p>')
    })
})

describe('blocks', () => {
    it('reads task lists from boxes, as Markdown, GitHub and Tiptap write them', () => {
        const [element] = mount('<ul><li><input type="checkbox" checked> Done</li><li><label><input type="checkbox"></label><div><p>Todo</p></div></li></ul><ul data-type="taskList"><li data-type="taskItem" data-checked="true"><label><input type="checkbox" checked><span></span></label><div><p>Tiptap</p></div></li></ul><ul><li><p>Plain</p></li></ul>')
        const tasks = element.editor.getJSON().content

        expect(tasks.map(node => node.type)).toEqual(['taskList', 'taskList', 'bulletList', 'paragraph'])
        expect(tasks[0].content.map(item => [item.attrs.checked, item.content[0].content[0].text])).toEqual([[true, 'Done'], [false, 'Todo']])
        expect(tasks[1].content[0].attrs.checked).toBe(true)
    })

    // From the start: on phones Tiptap focuses in the middle of a command, which the
    // paragraph added then would fail.
    it('keeps a paragraph after a last table or image to write in, out of its value', () => {
        const [element, events] = mount('<p>Intro</p><img src="/a.png">')

        expect(element.editor.state.doc.lastChild.type.name).toBe('paragraph')

        element.editor.commands.setTextSelection(1)
        expect(element.value).toBe('<p>Intro</p><img src="/a.png">')
        expect(events).toEqual([])

        element.value = '<p>Intro</p><img src="/a.png">'

        expect(element.editor.state.selection.from).toBe(1)
    })

    it('writes six levels of headings, and indents code with Tab', () => {
        const [element] = mount('<h5>Five</h5><h6>Six</h6><pre><code>a</code></pre>')

        expect(element.value).toBe('<h5>Five</h5><h6>Six</h6><pre><code>a</code></pre>')

        select(element, 'a')
        element.editor.commands.setTextSelection(element.editor.state.selection.from)
        element.editor.view.dom.dispatchEvent(new KeyboardEvent('keydown', { key: 'Tab', bubbles: true, cancelable: true }))

        expect(element.value).toContain('<pre><code>    a</code></pre>')
    })

    it('resizes media by their handles, as a width in px or the full width', () => {
        const [element] = mount('<img src="/a.png" style="width: 200px; margin-left: auto; margin-right: auto;">')
        const media = element.querySelector('[data-editor-media="image"]')
        const box = media.querySelector('[data-editor-media-box]')
        const right = media.querySelector('[data-editor-resize="right"]')
        const drag = (handle, to) => {
            handle.dispatchEvent(new MouseEvent('pointerdown', { bubbles: true, cancelable: true, clientX: 100 }))
            handle.dispatchEvent(new MouseEvent('pointermove', { bubbles: true, clientX: to }))
            handle.dispatchEvent(new MouseEvent('pointerup', { bubbles: true, clientX: to }))
        }

        Object.defineProperty(media, 'clientWidth', { value: 600 })
        box.getBoundingClientRect = () => ({ width: 200 })

        expect(media.dataset.align).toBe('center')
        expect(box.style.width).toBe('200px')

        drag(right, 150)

        expect(element.value).toBe('<img style="width: 300px; margin-left: auto; margin-right: auto;" src="/a.png">')

        box.getBoundingClientRect = () => ({ width: 300 })
        drag(right, 400)

        expect(element.value).toContain('width: 100%;')
    })

    it('writes collapsible blocks as native details, and code blocks without a language', () => {
        const [element] = mount('<details open><summary>More</summary><div><p>Hidden</p></div></details><pre><code class="language-php">echo 1;</code></pre>')

        expect(element.value).toBe('<details><summary>More</summary><div><p>Hidden</p></div></details><pre><code>echo 1;</code></pre>')
    })

    it('writes tables with inline borders and column widths', () => {
        const [element] = mount('<table><colgroup><col style="width: 120px"><col></colgroup><tbody><tr><th><p>A</p></th><th><p>B</p></th></tr><tr><td><p>1</p></td><td><p>2</p></td></tr></tbody></table>')

        expect(element.value).toBe('<div style="overflow-x: auto;"><table style="border-collapse: collapse; width: 100%;"><colgroup><col style="width: 120px;"><col style="min-width: 48px;"></colgroup><tbody><tr>'
            + '<th style="border: 1px solid rgba(128, 128, 128, 0.4); padding: 6px 10px; vertical-align: top; background-color: rgba(128, 128, 128, 0.1); text-align: left;" colwidth="120"><p>A</p></th>'.replace(' colwidth="120"', '')
            + '<th style="border: 1px solid rgba(128, 128, 128, 0.4); padding: 6px 10px; vertical-align: top; background-color: rgba(128, 128, 128, 0.1); text-align: left;"><p>B</p></th></tr><tr>'
            + '<td style="border: 1px solid rgba(128, 128, 128, 0.4); padding: 6px 10px; vertical-align: top;"><p>1</p></td>'
            + '<td style="border: 1px solid rgba(128, 128, 128, 0.4); padding: 6px 10px; vertical-align: top;"><p>2</p></td></tr></tbody></table></div>')
        expect(element.editor.getJSON().content[0].content[0].content[0].attrs.colwidth).toEqual([120])
    })

    it('keeps media on web addresses with their style, and data: images until uploaded', () => {
        const [element] = mount('<img src="/a.png" alt="A" width="1200" height="800" style="width: 50%; margin-left: auto; border-radius: 8px;"><img src="data:image/png;base64,AAAA"><img src="data:image/svg+xml;base64,PHN2Zz4="><video src="data:video/mp4;base64,AAAA"></video><video src="https://cdn.test/v.mp4" poster="/p.jpg"></video><audio src="/a.mp3"></audio><a href="/f.pdf" download="f.pdf" type="application/pdf"><span>📎</span><span>f.pdf</span><span>1 KB</span></a><iframe src="https://www.youtube-nocookie.com/embed/abc?autoplay=1"></iframe><iframe src="https://evil.test/x"></iframe><iframe src="//player.vimeo.com/video/1"></iframe>')

        expect(element.value).toBe('<img style="width: 50%; margin-left: auto; border-radius: 8px;" src="/a.png" alt="A" width="1200" height="800"><img src="data:image/png;base64,AAAA">'
            + '<video src="https://cdn.test/v.mp4" poster="/p.jpg" controls="controls" preload="metadata"></video>'
            + '<audio src="/a.mp3" controls="controls" preload="metadata"></audio>'
            + '<a href="/f.pdf" download="f.pdf" type="application/pdf" style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; border: 1px solid rgba(128, 128, 128, 0.4); border-radius: 8px; color: inherit; text-decoration: none;"><span style="flex-shrink: 0;">📎</span><span style="min-width: 0px; overflow-wrap: anywhere;">f.pdf</span><span style="flex-shrink: 0; white-space: nowrap; opacity: 0.6;">1 KB</span></a>'
            + '<iframe style="display: block; width: 100%; max-width: 100%; height: auto; aspect-ratio: 16 / 9; border: 0px;" src="https://www.youtube-nocookie.com/embed/abc?autoplay=0" allowfullscreen="allowfullscreen" loading="lazy"></iframe>'
            + '<iframe style="display: block; width: 100%; max-width: 100%; height: auto; aspect-ratio: 16 / 9; border: 0px;" src="https://player.vimeo.com/video/1?autoplay=0" allowfullscreen="allowfullscreen" loading="lazy"></iframe>')
    })

    it('captions images, links them, and wraps blocks in callouts', () => {
        const [element] = mount('<figure><a href="https://x.test"><img src="/a.png"></a><figcaption>Cap</figcaption></figure><img src="/b.png"><p>Note</p>')
        let image = null

        element.editor.state.doc.descendants((node, position) => {
            if (node.attrs.src === '/b.png') image = position
        })
        element.editor.chain().setNodeSelection(image).toggleCaption().run()

        expect(element.value).toBe('<figure style="margin: 0px;"><a href="https://x.test"><img src="/a.png"></a><figcaption style="margin-top: 6px; text-align: center; font-size: 0.875em; color: rgb(107, 114, 128);">Cap</figcaption></figure>'
            + '<figure style="margin: 0px;"><img src="/b.png"><figcaption style="margin-top: 6px; text-align: center; font-size: 0.875em; color: rgb(107, 114, 128);"></figcaption></figure><p>Note</p>')

        element.editor.chain().setTextSelection(element.editor.state.doc.content.size - 2).setCallout(full.callouts.info).run()

        expect(element.value).toContain('<aside style="padding: 12px 16px; border-radius: 6px; background-color: rgba(59, 130, 246, 0.1); border-left: 4px solid rgb(59, 130, 246);"><p>Note</p></aside>')
        expect(active(element, 'callout', full.callouts.info)).toBe(true)
    })

    it('colors and aligns table cells', () => {
        const [element] = mount('<table><tbody><tr><td><p>1</p></td><td><p>2</p></td></tr></tbody></table>')

        element.editor.commands.setTextSelection(3)
        run(element, 'cell-background', '#fef08a')
        run(element, 'cell-align', 'middle')

        expect(element.value).toContain('<td style="border: 1px solid rgba(128, 128, 128, 0.4); padding: 6px 10px; vertical-align: middle; background-color: rgb(254, 240, 138);"><p>1</p></td><td style="border: 1px solid rgba(128, 128, 128, 0.4); padding: 6px 10px; vertical-align: top;"><p>2</p></td>')
        expect(active(element, 'cell-align', 'middle')).toBe(true)
    })

    it('keeps formulas as MathML with their LaTeX, written by KaTeX', async () => {
        const { toMathML } = await import('../../resources/js/heavy/editor/nodes.js')
        const mathml = await toMathML('x^2', false)
        const [element] = mount(`<p>Area ${mathml}</p>`)

        expect(mathml).toMatch(/^<math xmlns="http:\/\/www\.w3\.org\/1998\/Math\/MathML"><semantics><mrow><msup><mi>x<\/mi><mn>2<\/mn><\/msup><\/mrow><annotation encoding="application\/x-tex">x\^2<\/annotation><\/semantics><\/math>$/)
        expect(element.editor.getJSON().content[0].content[1]).toMatchObject({ type: 'inlineMath', attrs: { latex: 'x^2' } })
        expect(element.value).toBe(`<p>Area ${mathml}</p>`)
        await expect(toMathML('\\frac{', true)).rejects.toThrow('KaTeX parse error')
    })
})

describe('media', () => {
    it('uploads what a media button picks, three at a time, and inserts the answers where the selection was', async () => {
        const [element] = mount('<p>Hello world</p>', { attributes: UPLOADS })
        const posts = uploader()
        const inputs = chooser()

        select(element, 'Hello')
        mediaButton(element, 'file').click()
        element.editor.chain().setTextSelection(12).insertContent('!').run()

        expect(inputs.map(input => [input.accept, input.multiple])).toEqual([['', true]])

        choose(inputs[0], ['a.png', 'b.mp3', 'c.zip', 'x.png'].map(name => new File(['x'], name)))
        await settle()

        expect(element.querySelectorAll('[data-slot="editor-uploading"]')).toHaveLength(1)
        expect(posts.map(post => [post.url, post.form.source, post.form.file.name, post.form.context, post.headers['X-CSRF-TOKEN']])).toEqual([
            ['/upload', 'pick', 'a.png', 'sealed', 'token'],
            ['/upload', 'pick', 'b.mp3', 'sealed', 'token'],
            ['/upload', 'pick', 'c.zip', 'sealed', 'token'],
        ])

        posts[0].answer({ url: '/a.png', name: 'a.png', mime: 'image/png', alt: 'A', width: 10, height: 20 })
        await settle()

        expect(posts).toHaveLength(4)

        posts[1].answer({ url: '/b.mp3', name: 'b.mp3' })
        posts[2].answer({ url: '/c.zip', name: 'c.zip', size: 1536 })
        posts[3].answer({ url: 'javascript:alert(1)', name: 'x.png' })
        await settle()

        expect(element.value).toBe('<img style="display: block; max-width: 100%; height: auto; margin-left: auto; margin-right: auto;" src="/a.png" alt="A" width="10" height="20">'
            + '<audio style="display: block; width: 100%;" src="/b.mp3" title="b.mp3" controls="controls" preload="metadata"></audio>'
            + '<a href="/c.zip" download="c.zip" style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; border: 1px solid rgba(128, 128, 128, 0.4); border-radius: 8px; color: inherit; text-decoration: none;"><span style="flex-shrink: 0;">📎</span><span style="min-width: 0px; overflow-wrap: anywhere;">c.zip</span><span style="flex-shrink: 0; white-space: nowrap; opacity: 0.6;">1.5 KB</span></a>'
            + '<p> world!</p>')

        // The one the uploader wouldn't take: a placeholder says so, to dismiss.
        expect(element.querySelector('[data-editor-failed]').textContent).toBe('Upload failed')

        element.querySelector('[data-editor-failed] [data-editor-action="dismiss-upload"]').click()
        expect(element.querySelectorAll('[data-slot="editor-uploading"]')).toHaveLength(0)
    })

    it('inserts what the server gives, and HTML, to the editor of that name in that component', () => {
        const [element] = mount('<p>Hello</p>', { features: ['file'] })
        const send = detail => window.dispatchEvent(new CustomEvent('ui-editor', { detail: { owner: 'posts', attachments: [], html: null, ...detail } }))

        element.closest('form').setAttribute('wire:id', 'posts')
        element.editor.commands.setTextSelection(6)
        send({ name: 'body', action: 'insert' })

        expect(element.value).toBe('<p>Hello</p>')

        send({ name: 'other', action: 'insert', attachments: [{ url: '/x.png' }] })
        send({ name: 'body', owner: 'comments', action: 'insert', attachments: [{ url: '/x.png' }] })

        expect(element.value).toBe('<p>Hello</p>')

        send({ name: 'body', action: 'insert', attachments: [{ url: '/x.png', mime: 'image/png' }] })

        expect(element.value).toBe('<p>Hello</p><a href="/x.png" download="x.png" type="image/png" style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; border: 1px solid rgba(128, 128, 128, 0.4); border-radius: 8px; color: inherit; text-decoration: none;"><span style="flex-shrink: 0;">📎</span><span style="min-width: 0px; overflow-wrap: anywhere;">x.png</span></a>')

        send({ name: 'body', action: 'insert-content', html: '<p><strong>Hi</strong></p>' })

        expect(element.value).toContain('<p>Hi</p>')
    })

    it('inserts attachments from the app, and HTML, where the cursor is', () => {
        const [element] = mount('<p>Hello</p>', { features: ['file'] })

        element.editor.commands.setTextSelection(6)
        element.insert([{ url: '/x.png', mime: 'image/png' }])

        expect(element.value).toBe('<p>Hello</p><a href="/x.png" download="x.png" type="image/png" style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; border: 1px solid rgba(128, 128, 128, 0.4); border-radius: 8px; color: inherit; text-decoration: none;"><span style="flex-shrink: 0;">📎</span><span style="min-width: 0px; overflow-wrap: anywhere;">x.png</span></a>')

        element.insertContent('<p><strong>Hi</strong></p>')

        expect(element.value).toContain('<p>Hi</p>')
    })

    it('uploads pasted files, and turns pasted links into what they are', async () => {
        const [element] = mount('<p>Hello</p>', { attributes: UPLOADS })
        const posts = uploader()

        element.editor.commands.setTextSelection(6)
        paste(element, { files: [new File(['x'], 'shot.png', { type: 'image/png' })] })
        await settle()

        expect(posts.map(post => [post.form.source, post.form.file.name])).toEqual([['paste', 'shot.png']])

        posts[0].answer({ url: '/shot.png', mime: 'image/png' })
        await settle()

        paste(element, { 'text/plain': '## Heading\n\n- [x] Done\n\n**bold**' })
        paste(element, { 'text/plain': 'https://youtu.be/dQw4w9WgXcQ' })

        expect(element.value).toContain('src="https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?autoplay=0"')
        expect(element.value).toContain('<h2>Heading</h2><ul style="list-style-type: none; padding-left: 0px;"><li style="display: flex; align-items: baseline; gap: 0.5em;"><input type="checkbox" disabled="disabled" checked="checked"><div><p>Done</p></div></li></ul><p><strong>bold</strong></p>')
    })

    it('shows where files will go until they are there, why one would not upload, and nothing once dismissed', async () => {
        const [element] = mount('<p>Hello</p>', { attributes: UPLOADS })
        const posts = uploader()
        const placeholders = () => element.querySelectorAll('[data-slot="editor-uploading"]')

        element.editor.commands.setTextSelection(6)
        paste(element, { files: [new File(['x'], 'a.png', { type: 'image/png' })] })
        paste(element, { files: [new File(['y'], 'b.png', { type: 'image/png' })] })
        paste(element, { files: [new File(['z'], 'c.png', { type: 'image/png' })] })
        await settle()

        expect(placeholders()).toHaveLength(3)
        expect(element.value).toBe('<p>Hello</p>')

        posts[0].answer({ url: '/a.png', mime: 'image/png' })
        posts[1].answer(null, 422, { file: ['Files up to 20 MB only.'] })
        await settle()

        expect(element.value).toContain('src="/a.png"')
        expect(element.querySelector('[data-editor-failed]').textContent).toBe('Files up to 20 MB only.')

        element.querySelector('[data-slot="editor-uploading"]:not([data-editor-failed]) [data-editor-action="dismiss-upload"]').click()
        posts[2].answer({ url: '/c.png', mime: 'image/png' })
        await settle()

        expect(element.value).not.toContain('/c.png')
        expect(placeholders()).toHaveLength(1)
    })

    it('picks media of the kind its button says, or of the selected one to replace it; none without an uploader', async () => {
        const [element] = mount('<p>Hello</p><video src="/v.mp4"></video>', { attributes: UPLOADS })
        const inputs = chooser()

        mediaButton(element, 'image').click()
        element.editor.commands.setNodeSelection(7)
        control(element, '[data-editor-action="media"]').click()

        expect(inputs.map(input => [input.accept, input.multiple])).toEqual([['image/*', true], ['video/*', false]])

        const [plain] = mount('<p>Hello</p>')
        const posts = uploader()

        mediaButton(plain, 'image').click()
        plain.editor.commands.setTextSelection(6)
        paste(plain, { files: [new File(['x'], 'a.png', { type: 'image/png' })] })
        await settle()

        expect(inputs).toHaveLength(2)
        expect(posts).toEqual([])
        expect(plain.querySelectorAll('[data-slot="editor-uploading"]')).toHaveLength(0)
    })

    it('saves images it has no copy of when asked, one or all, each a file: as read, or for the server to fetch', async () => {
        const [element] = mount('<p>Hello</p>', { attributes: `${UPLOADS} data-image-hosts="cdn.app.test *.img.test"` })
        const posts = uploader({ readable: ['https://other.test/a.png'] })
        const save = Object.assign(document.createElement('button'), { type: 'button' })
        const error = Object.assign(document.createElement('p'), { hidden: true })
        const notice = element.querySelector('[data-editor-unsaved]')
        const images = () => element.value.match(/src="[^"]+"/g)
        const at = src => {
            let found = null

            element.editor.state.doc.descendants((node, position) => {
                if (found === null && node.attrs.src === src) found = position
            })

            return found
        }

        save.dataset.editorAction = 'save-image'
        error.toggleAttribute('data-editor-image-error', true)
        element.querySelector('[data-slot="editor-toolbar"]').append(save, error)
        element.editor.commands.setTextSelection(6)
        paste(element, { 'text/html': '<p><img src="https://other.test/a.png"><img src="https://other.test/a.png"><img src="https://cdn.app.test/b.png"><img src="/c.png"><img src="https://other.test/d.png"><img src="data:image/png;base64,iVBORw0KGgo="><img src="https://x.img.test/f.png"></p>' })
        await settle()

        // Nothing goes until someone asks: the footer says how many wait.
        expect(posts).toEqual([])
        expect(notice.hidden).toBe(false)
        expect(notice.textContent).toBe('3 unsaved')

        element.editor.commands.setNodeSelection(at('https://cdn.app.test/b.png'))
        expect(save.hidden).toBe(true)

        element.editor.commands.setNodeSelection(at('https://x.img.test/f.png'))
        expect(save.hidden).toBe(true)

        element.editor.commands.setNodeSelection(at('https://other.test/a.png'))
        expect(save.hidden).toBe(false)
        save.click()
        await settle()

        // Its site shares it: the browser reads it, and sends it as a file.
        expect(posts.map(post => [post.form.source, post.form.url, post.form.file?.name])).toEqual([['external', 'https://other.test/a.png', 'a.png']])
        expect(element.querySelectorAll('[data-editor-saving]')).toHaveLength(2)

        posts[0].answer({ url: 'https://files.app.test/a.png', name: 'a.png', mime: 'image/png' })
        await settle()

        expect(images()).toEqual(['src="https://files.app.test/a.png"', 'src="https://files.app.test/a.png"', 'src="https://cdn.app.test/b.png"', 'src="/c.png"', 'src="https://other.test/d.png"', 'src="data:image/png;base64,iVBORw0KGgo="', 'src="https://x.img.test/f.png"'])
        expect(element.querySelectorAll('[data-editor-saving]')).toHaveLength(0)
        expect(notice.textContent).toBe('2 unsaved')

        // All at once: a data one as a file; one its site doesn't share by its address,
        // for the server to fetch.
        control(element, '[data-editor-action="save-images"]').click()
        await settle()

        expect(posts.slice(1).map(post => [post.form.source, post.form.url ?? null, post.form.file?.name ?? null, post.form.file?.type ?? null])).toEqual([['data', null, 'image-1.png', 'image/png'], ['external', 'https://other.test/d.png', null, null]])
        expect(control(element, '[data-editor-action="save-images"]').disabled).toBe(true)

        posts[1].answer({ url: '/uploads/e.png' })
        posts[2].answer(null, 422, { file: ['This image couldn’t be read.'] })
        await settle()

        expect(images().slice(4, 6)).toEqual(['src="https://other.test/d.png"', 'src="/uploads/e.png"'])
        expect(notice.textContent).toBe('1 unsaved')

        // The one that wouldn't save says why, in its menu.
        element.editor.commands.setNodeSelection(at('https://other.test/d.png'))
        expect([error.hidden, error.textContent]).toEqual([false, 'This image couldn’t be read.'])

        element.editor.commands.setNodeSelection(at('https://cdn.app.test/b.png'))
        expect(error.hidden).toBe(true)

        // Undone, it is as it was; saved again, it takes the copy it had at once.
        element.editor.commands.undo()
        expect(images()[5]).toBe('src="data:image/png;base64,iVBORw0KGgo="')

        element.editor.commands.setNodeSelection(at('data:image/png;base64,iVBORw0KGgo='))
        save.click()
        await settle()

        expect(posts).toHaveLength(3)
        expect(images()[5]).toBe('src="/uploads/e.png"')
    })

    it('saves images as they come in, as auto_upload says: not those it loaded with, one undone, or one that failed', async () => {
        const [element] = mount('<p><img src="https://other.test/old.png"></p>', { attributes: `${UPLOADS} data-auto-upload="external"` })
        const posts = uploader()
        const images = () => element.value.match(/src="[^"]+"/g)

        element.editor.commands.setTextSelection(element.editor.state.doc.content.size - 1)
        paste(element, { 'text/html': '<p><img src="https://other.test/new.png"><img src="https://other.test/bad.png"><img src="data:image/png;base64,iVBORw0KGgo="></p>' })
        await settle()

        expect(posts.map(post => post.form.url)).toEqual(['https://other.test/new.png', 'https://other.test/bad.png'])

        posts[0].answer({ url: '/uploads/new.png' })
        posts[1].answer(null, 500)
        await settle()

        expect(images()).toEqual(['src="https://other.test/old.png"', 'src="/uploads/new.png"', 'src="https://other.test/bad.png"', 'src="data:image/png;base64,iVBORw0KGgo="'])

        element.editor.commands.undo()
        element.editor.chain().setTextSelection(1).insertContent('!').run()
        await settle()

        expect(images()[1]).toBe('src="https://other.test/new.png"')
        expect(posts).toHaveLength(2)
    })

    it('puts back the pictures of what Word copied, from its RTF', async () => {
        const { rtfImages, wordImages } = await import('../../resources/js/heavy/editor/images.js')
        const png = `89504e470d0a1a0a${'00'.repeat(70)}`
        const jpeg = `ffd8ffe0${'11'.repeat(70)}`
        const rtf = `{\\rtf1{\\*\\shppict{\\pict{\\*\\picprop\\shplid1025{\\sp{\\sn shapeType}{\\sv 75}}}\\picscalex100\\picw10\\pich10\\pngblip\\bliptag-12{\\*\\blipuid 0123456789abcdef0123456789abcdef}\n${png}}}{\\nonshppict{\\pict\\wmetafile8\\picw10 0102}}{\\pict\\jpegblip\\picw2 ${jpeg}}}`
        const base64 = hex => btoa(hex.match(/../g).map(pair => String.fromCharCode(parseInt(pair, 16))).join(''))
        const [element] = mount('<p>Hello</p>')

        expect(rtfImages(rtf)).toEqual([`data:image/png;base64,${base64(png)}`, `data:image/jpeg;base64,${base64(jpeg)}`])
        expect(wordImages('<p><img src="file:///C:/a.png"></p>', rtf)).toBe('<p>\uFFFC</p>')

        element.editor.commands.setTextSelection(6)
        paste(element, { 'text/html': '<p>A<img src="file:///C:/x/clip_image001.png" width="10" height="10">B<img src="file:///C:/x/clip_image002.jpg"></p>', 'text/rtf': rtf })

        expect(element.value).toContain(`<img src="data:image/png;base64,${base64(png)}" width="10" height="10">`)
        expect(element.value).toContain(`<img src="data:image/jpeg;base64,${base64(jpeg)}">`)
    })

    it('leaves a placeholder, never content, where a pasted picture could not be read, to upload it there', async () => {
        const [element] = mount('<p>Hello</p>', { attributes: UPLOADS })
        const posts = uploader()
        const inputs = chooser()

        element.editor.commands.setTextSelection(6)
        paste(element, { 'text/html': '<p>A<img src="file:///C:/x/chart.png">B</p><p><img src="file:///C:/x/two.png"></p>' })

        const placeholders = () => element.querySelectorAll('[data-editor-missing]')

        expect(element.value).toBe('<p>HelloAB</p>')
        expect(element.value).not.toContain('\uFFFC')
        expect(placeholders()).toHaveLength(2)

        // Uploaded where it stands, one picture; while it uploads it says so.
        placeholders()[0].querySelector('[data-editor-action="upload-missing"]').click()
        expect(inputs.map(input => [input.accept, input.multiple])).toEqual([['image/*', false]])

        choose(inputs[0], [new File(['x'], 'chart.png', { type: 'image/png' })])
        await settle()

        expect(placeholders()).toHaveLength(1)
        expect(posts.map(post => post.form.source)).toEqual(['pick'])

        // Failed, it stands there again, saying why.
        posts[0].answer(null, 422, { file: ['Not a picture.'] })
        await settle()

        expect(placeholders()).toHaveLength(2)
        expect(placeholders()[0].querySelector('[data-editor-upload-message]').textContent).toBe('Not a picture.')

        placeholders()[0].querySelector('[data-editor-action="upload-missing"]').click()
        choose(inputs[1], [new File(['x'], 'chart.png', { type: 'image/png' })])
        await settle()
        posts[1].answer({ url: '/chart.png', mime: 'image/png' })
        await settle()

        expect(placeholders()).toHaveLength(1)
        expect(element.value).toContain('<p>HelloA</p><img style="display: block; max-width: 100%; height: auto; margin-left: auto; margin-right: auto;" src="/chart.png" alt=""><p>B</p>')

        placeholders()[0].querySelector('[data-editor-action="dismiss-upload"]').click()
        expect(placeholders()).toHaveLength(0)
    })

    it('shows how far uploads have got, and keeps a form around it from submitting until they are done', async () => {
        const [element] = mount('<p>Hello</p>', { attributes: UPLOADS })
        const posts = uploader()
        const busy = []
        const form = element.closest('form')
        const submitted = vi.fn()
        const notice = element.querySelector('[data-editor-busy]')

        form.addEventListener('submit', submitted)
        element.addEventListener('editor-busy', event => busy.push(event.detail.busy))
        element.editor.commands.setTextSelection(6)
        paste(element, { files: [new File(['x'.repeat(100)], 'a.png', { type: 'image/png' }), new File(['y'.repeat(300)], 'b.png', { type: 'image/png' })] })
        await settle()

        expect([element.busy, element.getAttribute('aria-busy'), busy]).toEqual([true, 'true', [true]])
        expect([notice.hidden, notice.textContent, notice.getAttribute('role')]).toEqual([false, '2 uploading', 'status'])

        // Together: a's 100 bytes all sent, b's 300 half.
        posts[0].progress(10, 10)
        posts[1].progress(5, 10)

        const placeholder = element.querySelector('[data-slot="editor-uploading"]')

        expect(placeholder.querySelector('[data-editor-upload-progress]').textContent).toBe('63%')
        expect(placeholder.style.getPropertyValue('--editor-upload-progress')).toBe('63%')

        // Submitted meanwhile: it isn't, and the footer says why.
        const event = new Event('submit', { bubbles: true, cancelable: true })

        form.dispatchEvent(event)

        expect([event.defaultPrevented, submitted.mock.calls.length]).toEqual([true, 0])
        expect([notice.textContent, notice.getAttribute('role')]).toEqual(['2 still uploading', 'alert'])

        posts[0].answer({ url: '/a.png', mime: 'image/png' })
        await settle()

        expect(notice.textContent).toBe('1 still uploading')

        posts[1].answer({ url: '/b.png', mime: 'image/png' })
        await settle()

        expect([element.busy, element.hasAttribute('aria-busy'), notice.hidden, busy]).toEqual([false, false, true, [true, false]])

        form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }))
        expect(submitted).toHaveBeenCalledTimes(1)
    })

    it('puts images side by side, and takes one out of its row', () => {
        const style = 'display: block; max-width: 100%; height: auto; margin-left: auto; margin-right: auto;'
        const [element] = mount(`<img src="/a.png" style="${style}"><img src="/b.png" style="${style}"><p>x</p><img src="/c.png" style="${style}"><img src="/d.png" style="${style}">`)
        const button = Object.assign(document.createElement('button'), { type: 'button' })
        const at = src => {
            let found = null

            element.editor.state.doc.descendants((node, position) => {
                if (found === null && node.attrs.src === src) found = position
            })

            return found
        }

        button.dataset.editorCommand = 'image-row'
        button.setAttribute('aria-pressed', 'false')
        element.querySelector('[data-slot="editor-toolbar"]').append(button)

        // With the next image; then the last image joins with the one before.
        element.editor.commands.setNodeSelection(at('/a.png'))
        button.click()
        element.editor.commands.setNodeSelection(at('/d.png'))
        button.click()

        expect(element.value).toBe('<div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; align-items: start;"><img style="display: block; max-width: 100%; height: auto; width: 100%;" src="/a.png"><img style="display: block; max-width: 100%; height: auto; width: 100%;" src="/b.png"></div><p>x</p><div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; align-items: start;"><img style="display: block; max-width: 100%; height: auto; width: 100%;" src="/c.png"><img style="display: block; max-width: 100%; height: auto; width: 100%;" src="/d.png"></div>')
        expect(button.getAttribute('aria-pressed')).toBe('true')
        expect(element.querySelectorAll('[data-editor-row]')).toHaveLength(2)

        // Out again: under its row, which, left with one image, is that image.
        button.click()

        expect(element.value).toContain('<p>x</p><img style="display: block; max-width: 100%; height: auto; margin-left: auto; margin-right: auto;" src="/c.png"><img style="display: block; max-width: 100%; height: auto; margin-left: auto; margin-right: auto;" src="/d.png">')
        expect(element.editor.state.selection.node.attrs.src).toBe('/d.png')

        // Its HTML reads back as a row.
        const [again] = mount(element.value)

        expect(again.editor.state.doc.firstChild.type.name).toBe('imageRow')
    })

    it('reads attachments and video links', async () => {
        const { EMBED_STYLE, MEDIA_STYLE, attachmentNode, embedSource, formatSize, kindOf, videoNode } = await import('../../resources/js/heavy/editor/media.js')
        const platforms = format([]).platforms

        expect([{ mime: 'image/webp' }, { type: 'video/mp4' }, { url: '/a/b.MP3?x=1' }, { url: '/a.pdf' }, { mime: 'application/zip', url: '/a.png' }].map(kindOf)).toEqual(['image', 'video', 'audio', 'file', 'file'])
        expect([0, 1023, 1536, 5 * 1024 ** 2].map(formatSize)).toEqual(['0 B', '1,023 B', '1.5 KB', '5 MB'])
        expect(attachmentNode({ url: '/v.mp4', mime: 'video/mp4' }, ['image', 'file'])).toMatchObject({ type: 'file', attrs: { name: 'v.mp4' } })
        expect(attachmentNode({ url: '/v.mp4', mime: 'video/mp4' }, ['image'])).toBeNull()
        expect(attachmentNode({ url: 'data:image/png;base64,AA', mime: 'image/png' }, ['image'])).toBeNull()
        expect(embedSource('https://www.youtube.com/watch?t=1&v=dQw4w9WgXcQ', platforms)).toBe('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?autoplay=0')
        expect(embedSource('https://www.bilibili.com/video/BV1GJ411x7h7?spm=1', platforms)).toBe('https://player.bilibili.com/player.html?bvid=BV1GJ411x7h7&autoplay=0')
        expect(embedSource('<iframe src="https://player.bilibili.com/player.html?bvid=BV1&amp;autoplay=1&amp;page=1#t=3" allowfullscreen></iframe>', platforms)).toBe('https://player.bilibili.com/player.html?bvid=BV1&autoplay=0&page=1#t=3')
        expect(embedSource('https://v.qq.com/x/page/abc.html', platforms)).toBe('https://v.qq.com/txp/iframe/player.html?vid=abc&autoplay=false&auto=0')
        expect(embedSource('https://evil.test/x', platforms)).toBeNull()

        // A video file's address plays in a player; a platform's link or code in its own,
        // shaped as its code is.
        expect(videoNode('https://cdn.test/a.mp4?t=1', ['video', 'embed'], platforms)).toEqual({ type: 'video', attrs: { src: 'https://cdn.test/a.mp4?t=1', style: MEDIA_STYLE } })
        expect(videoNode('https://cdn.test/watch', ['video', 'embed'], platforms)).toBeNull()
        expect(videoNode('https://youtu.be/dQw4w9WgXcQ', ['video'], platforms)).toBeNull()
        expect(videoNode('https://www.acfun.cn/v/ac123', ['embed'], platforms)).toEqual({ type: 'embed', attrs: { src: 'https://www.acfun.cn/player/ac123?autoplay=false', style: null } })
        expect(videoNode('<iframe src="https://open.iqiyi.com/developer/player_js/coopPlayerIndex.html?vid=1&amp;tvId=2" width="640" height="360"></iframe>', ['embed'], platforms)).toEqual({ type: 'embed', attrs: { src: 'https://open.iqiyi.com/developer/player_js/coopPlayerIndex.html?vid=1&tvId=2&autoplay=false', style: null } })
        expect(videoNode('<iframe width="360" height="640" src="//player.bilibili.com/player.html?bvid=BV1&amp;page=1"></iframe>', ['embed'], platforms)).toEqual({ type: 'embed', attrs: { src: 'https://player.bilibili.com/player.html?bvid=BV1&page=1&autoplay=0', style: EMBED_STYLE.replace('16 / 9', '9 / 16') } })
        expect(videoNode('<iframe width="500" height="300" src="https://player.vimeo.com/video/1"></iframe>', ['embed'], platforms).attrs.style).toBe(EMBED_STYLE.replace('16 / 9', '500 / 300'))
        expect(videoNode('<iframe src="https://evil.test/x"></iframe>', ['video', 'embed'], platforms)).toBeNull()
    })

    it('puts what the toolbar has no room for into More, the least used first', async () => {
        const { overflow } = await import('../../resources/js/heavy/editor/toolbar.js')
        const control = (name, priority) => `<div data-slot="editor-control" data-priority="${priority}"><button type="button">${name}</button></div>`

        document.body.innerHTML = `<div data-slot="editor-toolbar">${control('undo', 1)}<div data-slot="separator"></div>${control('bold', 1)}${control('code', 3)}${control('color', 2)}<div data-slot="separator"></div>${control('table', 2)}<button type="button" data-editor-more data-popover-trigger command="toggle-popover" commandfor="" hidden></button><div id="more" popover data-slot="popover-content"></div></div>`

        const toolbar = document.querySelector('[data-slot="editor-toolbar"]')
        const more = toolbar.querySelector('[data-editor-more]')
        const panel = document.getElementById('more')
        const shown = () => [...toolbar.children].filter(child => ! child.hidden && ! child.matches('[popover]')).map(child => child.textContent || (child === more ? '…' : '|'))
        const rows = () => [...panel.children].map(row => row.textContent)
        let width = 100

        // 20px a part.
        Object.defineProperty(toolbar, 'clientWidth', { get: () => width })
        Object.defineProperty(toolbar, 'scrollWidth', { get: () => shown().length * 20 })
        globalThis.ResizeObserver ??= class { observe() {} disconnect() {} }

        const { layout } = overflow(toolbar)

        expect(shown()).toEqual(['undo', '|', 'bold', 'color', '…'])
        expect(rows()).toEqual(['code', 'table'])

        width = 60
        layout()

        expect(shown()).toEqual(['undo', '…'])
        expect(rows()).toEqual(['boldcodecolor', 'table'])

        width = 1000
        layout()

        expect(shown()).toEqual(['undo', '|', 'bold', 'code', 'color', '|', 'table'])
        expect(more.hidden).toBe(true)
        expect(rows()).toEqual([])
    })
})

describe('whitelist', () => {
    it('keeps each tag as the whitelist says: its attributes, their values, its CSS', () => {
        const [element] = mount('', {
            features: ['ordered-list', 'layout'],
            whitelist: { allow: { p: { style: 0 }, br: [], div: [], section: '*', ol: { start: '*', type: ['1', 'a'] }, li: [] }, css: [['text-align']], attributes: ['class', 'title'] },
        })

        element.editor.commands.setTextSelection(1)
        paste(element, { 'text/html': '<p style="text-align: center; color: red; position: fixed">A</p><section class="box" title="T" data-x="1" onclick="steal()"><p>B</p></section><ol type="Q" start="3"><li><p>C</p></li></ol><ol type="a"><li><p>D</p></li></ol>' })

        expect(element.value).toBe('<p style="text-align: center;">A</p><section class="box" title="T"><p>B</p></section><ol start="3"><li><p>C</p></li></ol><ol type="a"><li><p>D</p></li></ol>')
    })
})

describe('keyboard', () => {
    it('goes from the text to the toolbar with Alt+F10, and back with Escape, where it was', () => {
        const [element] = mount()
        const text = element.editor.view.dom

        select(element, 'world')
        text.focus()
        text.dispatchEvent(new KeyboardEvent('keydown', { key: 'F10', altKey: true, bubbles: true, cancelable: true }))

        expect(document.activeElement).toBe(button(element, 'bold'))
        expect(text.getAttribute('aria-description')).toBe('Alt+F10')

        document.activeElement.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true, cancelable: true }))

        expect(element.editor.view.hasFocus()).toBe(true)
        expect(element.editor.state.doc.textBetween(element.editor.state.selection.from, element.editor.state.selection.to)).toBe('world')
    })

    it('moves the block the selection is in with Mod-Shift-Up and Down', () => {
        const [element] = mount('<p>One</p><p>Two</p><p>Three</p>')
        const press = key => element.editor.view.dom.dispatchEvent(new KeyboardEvent('keydown', { key, ctrlKey: true, shiftKey: true, bubbles: true, cancelable: true }))

        select(element, 'Tw')
        press('ArrowDown')

        expect(element.value).toBe('<p>One</p><p>Three</p><p>Two</p>')
        expect(element.editor.state.doc.textBetween(element.editor.state.selection.from, element.editor.state.selection.to)).toBe('Tw')

        press('ArrowUp')
        press('ArrowUp')
        press('ArrowUp')

        expect(element.value).toBe('<p>Two</p><p>One</p><p>Three</p>')
    })
})

describe('format painter', () => {
    it('puts the marks and block style it picked up on the next text selected, once', () => {
        const [element] = mount('<p style="text-align: center;"><strong><span style="color: rgb(220, 38, 38);">Bold</span></strong> <a href="https://x.test">plain</a></p><p>Other text</p>')
        const painter = control(element, '[data-editor-action="format-painter"]')

        element.editor.commands.setTextSelection(2)
        painter.click()

        expect(painter.getAttribute('aria-pressed')).toBe('true')
        expect(element.hasAttribute('data-painting')).toBe(true)

        select(element, 'Other')
        element.editor.view.dom.dispatchEvent(new Event('pointerup', { bubbles: true }))

        expect(element.value).toBe('<p style="text-align: center;"><span style="color: rgb(220, 38, 38);"><strong>Bold</strong></span> <a target="_blank" rel="noopener noreferrer nofollow" href="https://x.test">plain</a></p><p style="text-align: center;"><span style="color: rgb(220, 38, 38);"><strong>Other</strong></span> text</p>')
        expect(painter.getAttribute('aria-pressed')).toBe('false')
        expect(element.hasAttribute('data-painting')).toBe(false)
    })
})

describe('find and replace', () => {
    it('finds matches, moves between them and replaces them', () => {
        const [element] = mount('<p>Cat cat CAT dog</p>')
        const find = control(element, '[data-editor-find-input]')
        const count = control(element, '[data-editor-find-count]')

        control(element, '[data-editor-action="find"]').click()

        expect(control(element, '[data-slot="editor-find"]').hidden).toBe(false)
        expect(document.activeElement).toBe(find)

        find.value = 'cat'
        find.dispatchEvent(new Event('input', { bubbles: true }))

        expect(count.textContent).toBe('1/3')
        expect(element.querySelectorAll('[data-editor-match]')).toHaveLength(3)

        find.dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter', bubbles: true }))

        expect(count.textContent).toBe('2/3')

        control(element, '[data-editor-action="find-case"]').click()

        expect(count.textContent).toBe('1/1')

        control(element, '[data-editor-replace-input]').value = 'bird'
        control(element, '[data-editor-action="replace-all"]').click()

        expect(element.value).toBe('<p>Cat bird CAT dog</p>')
        expect(count.textContent).toBe('0/0')

        find.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true }))

        expect(control(element, '[data-slot="editor-find"]').hidden).toBe(true)
        expect(element.querySelectorAll('[data-editor-match]')).toHaveLength(0)
    })
})

describe('views', () => {
    it('shows and edits its HTML source, as far as its format allows', () => {
        const [element, events] = mount('<h2>Notes</h2><p>Hello</p>')
        const source = control(element, '[data-slot="editor-source"]')

        control(element, '[data-editor-action="source"]').click()

        expect(source.hidden).toBe(false)
        expect(source.value).toBe('<h2>Notes</h2>\n<p>Hello</p>')
        expect(element.hasAttribute('data-source')).toBe(true)
        expect(button(element, 'bold').disabled).toBe(true)

        source.value = '<h2>Notes</h2><p>Hi <b>there</b></p><script>alert(1)</script>'
        source.dispatchEvent(new Event('input', { bubbles: true }))

        expect(element.value).toBe('<h2>Notes</h2><p>Hi <strong>there</strong></p>')
        expect(events).toContain('input')

        control(element, '[data-editor-action="source"]').click()

        expect(source.hidden).toBe(true)
        expect(element.hasAttribute('data-source')).toBe(false)
    })

    it('goes full screen, and back with Escape', () => {
        const [element] = mount()
        const toggle = control(element, '[data-editor-action="fullscreen"]')

        toggle.click()

        expect(element.hasAttribute('data-fullscreen')).toBe(true)
        expect(toggle.getAttribute('aria-pressed')).toBe('true')
        expect(document.documentElement.style.overflow).toBe('hidden')

        element.editor.view.dom.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true }))

        expect(element.hasAttribute('data-fullscreen')).toBe(false)
        expect(document.documentElement.style.overflow).toBe('')
    })

    it('counts characters as graphemes, as the server does: no spaces, line breaks or media', () => {
        const [element] = mount('<p>汉字 👍🏽</p><p>a b<br>c</p><img src="/a.png"><a href="/f.pdf" download="f.pdf">f.pdf</a>')

        expect(control(element, '[data-editor-count]').textContent).toBe('6')

        const [limited] = mount('<p>abc</p>', { attributes: 'data-max-length="3"' })

        expect(control(limited, '[data-editor-count]').textContent).toBe('3 / 3')
        expect(control(limited, '[data-editor-count]').hasAttribute('data-full')).toBe(true)
    })
})

describe('links', () => {
    it('opens its link popover with Mod-K', () => {
        const [element] = mount()
        const opened = vi.fn()

        control(element, '[data-editor-opens="link"]').addEventListener('click', opened)
        select(element, 'world')
        element.editor.view.dom.dispatchEvent(new KeyboardEvent('keydown', { key: 'k', ctrlKey: true, bubbles: true, cancelable: true }))

        expect(opened).toHaveBeenCalledOnce()
    })

    it('sets and removes the selection link from its popover, Enter applying it in place', () => {
        const [element] = mount()
        const input = control(element, '[data-editor-link-input]')
        const form = element.closest('form')
        const submitted = vi.fn(event => event.preventDefault())

        form.addEventListener('submit', submitted)
        select(element, 'world')

        expect(control(element, '[data-editor-opens="link"]').disabled).toBe(false)

        input.value = 'https://laravel.com'
        input.dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter', bubbles: true, cancelable: true }))

        expect(element.value).toBe('<h2>Notes</h2><p>Hello <a target="_blank" rel="noopener noreferrer nofollow" href="https://laravel.com">world</a></p>')
        expect(submitted).not.toHaveBeenCalled()

        control(element, '[data-editor-action="link-remove"]').click()

        expect(element.value).toBe('<h2>Notes</h2><p>Hello world</p>')
    })
})

describe('the value', () => {
    it('takes the server HTML without reporting it back, and leaves the same content be', () => {
        const [element, events] = mount('<p style="text-align: center;"><span style="color: rgb(220, 38, 38);">Hello</span></p>')

        element.editor.commands.setTextSelection(3)
        element.value = '<p style="text-align:center"><span style="color:#dc2626">Hello</span></p>'

        expect(element.editor.state.selection.from).toBe(3)

        // Content that differs replaces it, the cursor where it was.
        element.value = '<p style="text-align: right;">Hello</p>'

        expect(element.editor.state.selection.from).toBe(3)

        element.value = '<p>New</p>'

        expect(element.value).toBe('<p>New</p>')
        expect(element.querySelector('input[name="body"]').value).toBe('<p>New</p>')
        expect(events).toEqual([])
    })

    it('reports change once editing ends, if it changed anything', () => {
        const [element, events] = mount()

        element.editor.view.dom.dispatchEvent(new FocusEvent('focus'))
        element.editor.commands.insertContent('!')
        element.editor.view.dom.dispatchEvent(new FocusEvent('focusout', { bubbles: true, relatedTarget: document.body }))
        element.editor.commands.blur()

        expect(events.filter(event => event === 'change')).toHaveLength(1)
    })
})
