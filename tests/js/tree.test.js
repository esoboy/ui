import { afterEach, beforeAll, describe, expect, it, vi } from 'vitest'
import { register as registerTree } from '../../resources/js/components/tree.js'

beforeAll(() => registerTree())

afterEach(() => {
    document.body.innerHTML = ''
})

const item = (value, label, children = '', attributes = '') => `
    <li role="treeitem" tabindex="-1" data-slot="tree-item" data-value="${value}" ${children ? 'aria-expanded="false"' : ''} ${attributes}>
        <div data-slot="tree-item-row"><span data-slot="tree-item-toggle"></span><input type="checkbox" data-tree-check><span data-slot="tree-item-label">${label}</span></div>
        ${children ? `<ul role="group" data-slot="tree-group" hidden>${children}</ul>` : ''}
    </li>`

const permissions = item('orders', 'Orders', item('view', 'View') + item('refund', 'Refund') + item('export', 'Export', '', 'aria-disabled="true"')) + item('users', 'Users', item('ban', 'Ban')) + item('settings', 'Settings')

function mount(attributes = '') {
    document.body.innerHTML = `<eso-tree ${attributes}>${permissions}</eso-tree>`

    return document.querySelector('eso-tree')
}

const node = (tree, value) => tree.querySelector(`[data-value="${value}"]`)
const row = (tree, value) => node(tree, value).querySelector(':scope > [data-slot="tree-item-row"]')
const announced = tree => {
    const seen = []
    tree.addEventListener('change', event => event.target === tree && seen.push(tree.value))
    return seen
}

describe('checkable', () => {
    it('cascades a checked parent down on load, and settles parents', () => {
        const tree = mount(`data-checkable data-value='["users","view"]'`)

        expect(tree.value).toEqual(['users', 'ban', 'view'].sort((a, b) => ['orders', 'view', 'refund', 'export', 'users', 'ban', 'settings'].indexOf(a) - ['orders', 'view', 'refund', 'export', 'users', 'ban', 'settings'].indexOf(b)))
        expect(node(tree, 'orders').getAttribute('aria-checked')).toBe('mixed')
        expect(node(tree, 'orders').querySelector('[data-tree-check]').indeterminate).toBe(true)
        expect(node(tree, 'users').getAttribute('aria-checked')).toBe('true')
    })

    it('checks a parent and every enabled child, announcing from the tree', () => {
        const tree = mount('data-checkable')
        const seen = announced(tree)

        row(tree, 'orders').click()

        expect(tree.value).toEqual(['orders', 'view', 'refund'])
        expect(seen).toEqual([['orders', 'view', 'refund']])

        row(tree, 'refund').click()
        expect(tree.value).toEqual(['view'])
        expect(node(tree, 'orders').getAttribute('aria-checked')).toBe('mixed')
    })

    it('ignores disabled items', () => {
        const tree = mount('data-checkable')

        row(tree, 'export').click()

        expect(tree.value).toEqual([])
    })
})

describe('single', () => {
    it('chooses an item and opens a parent from its row', () => {
        const tree = mount()

        row(tree, 'orders').click()

        expect(tree.value).toBe('orders')
        expect(node(tree, 'orders').getAttribute('aria-expanded')).toBe('true')
        expect(node(tree, 'orders').querySelector('[data-slot="tree-group"]').hidden).toBe(false)
        expect(node(tree, 'orders').getAttribute('aria-selected')).toBe('true')
    })

    it('opens and closes with the toggle alone', () => {
        const tree = mount()

        row(tree, 'users').querySelector('[data-slot="tree-item-toggle"]').click()

        expect(node(tree, 'users').getAttribute('aria-expanded')).toBe('true')
        expect(tree.value).toBeNull()
    })

    it('follows the ARIA tree keys', () => {
        const tree = mount()
        const key = name => document.activeElement.dispatchEvent(new KeyboardEvent('keydown', { key: name, bubbles: true, cancelable: true }))

        node(tree, 'orders').focus()
        key('ArrowRight')
        expect(node(tree, 'orders').getAttribute('aria-expanded')).toBe('true')

        key('ArrowRight')
        expect(document.activeElement).toBe(node(tree, 'view'))

        key('ArrowDown')
        expect(document.activeElement).toBe(node(tree, 'refund'))

        key('ArrowLeft')
        expect(document.activeElement).toBe(node(tree, 'orders'))

        key('End')
        expect(document.activeElement).toBe(node(tree, 'settings'))

        key('Enter')
        expect(tree.value).toBe('settings')
        expect(node(tree, 'view').getAttribute('aria-level')).toBe('2')
    })

    it('keeps its open items through a morph', async () => {
        const tree = mount()

        tree.expand(node(tree, 'orders'), true)
        node(tree, 'orders').setAttribute('aria-expanded', 'false')

        await vi.waitFor(() => expect(node(tree, 'orders').getAttribute('aria-expanded')).toBe('true'))
    })
})

describe('tree select', () => {
    function picker(checkable = false) {
        document.body.innerHTML = `
            <eso-tree-select data-placeholder="Pick" ${checkable ? 'data-checkable' : ''}>
                <button data-tree-select-trigger command="toggle-popover" commandfor=""><span data-slot="select-value">Pick</span></button>
                <div data-slot="tree-select-content" popover="auto"><eso-tree ${checkable ? 'data-checkable' : ''}>${permissions}</eso-tree></div>
            </eso-tree-select>`

        return document.querySelector('eso-tree-select')
    }

    it('closes after one choice and shows its label', () => {
        const element = picker()
        const seen = []
        element.addEventListener('change', event => event.target === element && seen.push(element.value))

        element.trigger.click()
        row(element.tree, 'settings').click()

        expect(element.content.matches(':popover-open')).toBe(false)
        expect(element.trigger.textContent).toBe('Settings')
        expect(seen).toEqual(['settings'])
    })

    it('stays open for checkable choices and lists their labels', () => {
        const element = picker(true)

        element.trigger.click()
        row(element.tree, 'users').click()

        expect(element.content.matches(':popover-open')).toBe(true)
        expect(element.trigger.textContent).toBe('Users, Ban')
    })
})

describe('search', () => {
    function searchable() {
        document.body.innerHTML = `
            <eso-tree>
                <div data-slot="tree-search"><input data-tree-search></div>
                <ul role="tree">${permissions}</ul>
                <div data-slot="tree-empty" hidden>No results</div>
            </eso-tree>`

        return document.querySelector('eso-tree')
    }

    const search = (tree, text) => {
        const input = tree.querySelector('[data-tree-search]')
        input.value = text
        input.dispatchEvent(new Event('input', { bubbles: true }))
    }

    const shown = tree => tree.items.filter(item => ! item.hidden).map(item => item.dataset.value)

    it('keeps the matches and their ancestors, opened, and brings back what was open when cleared', async () => {
        const tree = searchable()

        search(tree, 'BA')
        await Promise.resolve()

        expect(shown(tree)).toEqual(['users', 'ban'])
        expect(node(tree, 'users').getAttribute('aria-expanded')).toBe('true')
        expect(tree.querySelector('[data-slot="tree-empty"]').hidden).toBe(true)

        search(tree, 'nothing')
        await Promise.resolve()
        expect(tree.querySelector('[data-slot="tree-empty"]').hidden).toBe(false)

        search(tree, '')
        await Promise.resolve()

        expect(shown(tree)).toEqual(['orders', 'view', 'refund', 'export', 'users', 'ban', 'settings'])
        expect(node(tree, 'users').getAttribute('aria-expanded')).toBe('false')
        expect(tree.querySelector('[data-slot="tree-empty"]').hidden).toBe(true)
    })

    it('moves between the search box and the items with the arrows', () => {
        const tree = searchable()
        const input = tree.querySelector('[data-tree-search]')

        search(tree, 'set')
        input.dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowDown', bubbles: true, cancelable: true }))
        expect(document.activeElement).toBe(node(tree, 'settings'))

        node(tree, 'settings').dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowUp', bubbles: true, cancelable: true }))
        expect(document.activeElement).toBe(input)
    })
})

describe('lazy items', () => {
    const lazy = (value, label) => `
        <li role="treeitem" tabindex="-1" data-slot="tree-item" data-value="${value}" data-lazy aria-expanded="false">
            <div data-slot="tree-item-row"><span data-slot="tree-item-toggle"></span><input type="checkbox" data-tree-check><span data-slot="tree-item-label">${label}</span></div>
            <ul role="group" data-slot="tree-group" hidden></ul>
        </li>`

    afterEach(() => delete window.Livewire)

    it('asks the load method for the children of an item it opens, busy meanwhile, and checks them under a checked item', async () => {
        document.body.innerHTML = `<div wire:id="page"><eso-tree data-checkable data-load="loadChildren" data-value='["design"]'><ul role="tree">${lazy('design', 'Design')}</ul></eso-tree></div>`

        const tree = document.querySelector('eso-tree')
        const call = vi.fn(async () => {
            expect(node(tree, 'design').getAttribute('aria-busy')).toBe('true')
            node(tree, 'design').querySelector('[data-slot="tree-group"]').innerHTML = item('design.brand', 'Brand') + item('design.product', 'Product')
            await Promise.resolve()
        })
        const seen = announced(tree)

        window.Livewire = { find: id => (id === 'page' ? { call } : null) }

        node(tree, 'design').querySelector('[data-slot="tree-item-toggle"]').click()
        await vi.waitFor(() => expect(node(tree, 'design').hasAttribute('aria-busy')).toBe(false))

        expect(call).toHaveBeenCalledWith('loadChildren', 'design')
        expect(tree.value).toEqual(['design', 'design.brand', 'design.product'])
        expect(seen.at(-1)).toEqual(['design', 'design.brand', 'design.product'])

        // Loaded once: opening it again asks for nothing.
        tree.expand(node(tree, 'design'), false)
        tree.expand(node(tree, 'design'), true)
        expect(call).toHaveBeenCalledTimes(1)
    })
})

describe('dragging', () => {
    // jsdom has no PointerEvent: a MouseEvent with what the tree reads of one.
    const pointer = (type, init = {}) => Object.assign(new MouseEvent(type, { bubbles: true, ...init }), { pointerType: 'mouse' })

    it('dispatches move for where an item is dropped, and not into its own children', () => {
        const tree = mount('data-draggable')
        const moves = []
        tree.addEventListener('move', event => moves.push(event.detail))

        const drag = (from, to, offset) => {
            const target = row(tree, to)

            target.getBoundingClientRect = () => ({ top: 0, height: 40 })
            document.elementFromPoint = () => target

            row(tree, from).dispatchEvent(pointer('pointerdown', { button: 0, clientX: 0, clientY: 100 }))
            document.dispatchEvent(pointer('pointermove', { clientX: 0, clientY: offset }))
            const drop = target.closest('[data-slot="tree-item"]').dataset.drop
            document.dispatchEvent(pointer('pointerup', { clientX: 0, clientY: offset }))

            return drop
        }

        expect(drag('settings', 'users', 4)).toBe('before')
        expect(drag('settings', 'users', 36)).toBe('after')
        expect(drag('settings', 'users', 20)).toBe('inside')
        expect(drag('orders', 'view', 20)).toBe(undefined)

        expect(moves).toEqual([
            { value: 'settings', target: 'users', position: 'before' },
            { value: 'settings', target: 'users', position: 'after' },
            { value: 'settings', target: 'users', position: 'inside' },
        ])
        expect(node(tree, 'users').getAttribute('aria-expanded')).toBe('true')
        expect(tree.hasAttribute('data-dragging')).toBe(false)
    })

    it('ends without choosing the item, and Escape drops nothing', () => {
        const tree = mount('data-draggable')
        const moves = []
        tree.addEventListener('move', event => moves.push(event.detail))
        row(tree, 'users').getBoundingClientRect = () => ({ top: 0, height: 40 })
        document.elementFromPoint = () => row(tree, 'users')

        row(tree, 'settings').dispatchEvent(pointer('pointerdown', { button: 0, clientX: 0, clientY: 100 }))
        document.dispatchEvent(pointer('pointermove', { clientX: 0, clientY: 20 }))
        document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true }))
        document.dispatchEvent(pointer('pointerup'))
        row(tree, 'settings').click()

        expect(moves).toEqual([])
        expect(tree.value).toBe(null)
    })
})
