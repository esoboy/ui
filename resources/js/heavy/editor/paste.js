/*
 * Lists pasted from Word and WPS: paragraphs marked mso-list (the list, their level in
 * it), each opening with a marker the app drew (a bullet, "1.", "a)") and indented to
 * look like a list. They become real lists, nested by level, numbered when the marker
 * was a number or letter, without the marker or the indent.
 */

const LEVEL = /mso-list:\s*l\d+\s+level(\d+)/i
const LIST = /mso-list:\s*(l\d+)/i
const ORDERED = /^\s*(\d+|[a-z]|[ivxlcdm]+)[.)、．]/i

const levelOf = paragraph => Number(paragraph.getAttribute('style')?.match(LEVEL)?.[1] ?? 1)
const listOf = paragraph => paragraph.getAttribute('style')?.match(LIST)?.[1] ?? null

const isItem = node => node instanceof Element && node.matches('p') && (LEVEL.test(node.getAttribute('style') ?? '') || /MsoListParagraph/i.test(node.className))

// Takes the marker out of an item, saying whether it numbered it.
function unmark(paragraph) {
    const marker = paragraph.querySelector('[style*="mso-list:" i][style*="Ignore" i]')
    const text = marker?.textContent ?? ''

    let outer = marker

    while (outer?.parentElement && outer.parentElement !== paragraph && ! outer.parentElement.textContent.replace(text, '').trim()) outer = outer.parentElement

    outer?.remove()

    return ORDERED.test(text)
}

export function wordLists(html) {
    if (! /mso-list|MsoListParagraph/i.test(html)) return html

    const template = document.createElement('template')

    template.innerHTML = html

    for (const first of [...template.content.querySelectorAll('p')].filter(isItem)) {
        // Each run once, from its first item; later items are in a list by then.
        if (first.parentElement?.matches('li') || isItem(first.previousElementSibling)) continue

        // The run of items from here, as lists by level.
        const stack = []
        let current = null
        let paragraph = first

        while (isItem(paragraph)) {
            const next = paragraph.nextElementSibling
            const level = levelOf(paragraph)
            const list = listOf(paragraph)
            const ordered = unmark(paragraph)
            const item = document.createElement('li')

            // Another list (Word's next one, say numbered after bullets) starts afresh.
            if (list !== current) stack.length = 0
            current = list

            while (stack.length > level) stack.pop()

            // A list of the other kind at this level ends here.
            if (stack.length === level && stack.at(-1).tagName !== (ordered ? 'OL' : 'UL')) stack.pop()

            // A deeper level opens a list in the item before it.
            while (stack.length < level) {
                const opened = document.createElement(ordered ? 'ol' : 'ul')
                const parent = stack.at(-1)

                if (parent) (parent.lastElementChild ?? parent).append(opened)
                else paragraph.before(opened)

                stack.push(opened)
            }

            // The list indents it now.
            paragraph.style.removeProperty('margin-left')
            paragraph.style.removeProperty('text-indent')
            if (! paragraph.style.length) paragraph.removeAttribute('style')
            paragraph.removeAttribute('class')
            item.append(paragraph)
            stack.at(-1).append(item)
            paragraph = next
        }
    }

    return template.innerHTML
}
