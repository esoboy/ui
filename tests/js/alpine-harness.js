import { Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm.js'

window.Alpine = Alpine
const controllers = new Set()

// SDK tests inspect a test driver, while production exposes only Blade/wire:model.
export function engineDriver(create, element) {
    let model = element.dataset.value ?? null
    if (model && /^[\[{"]/.test(model)) { try { model = JSON.parse(model) } catch {} }
    const watchers = []
    const input = document.createElement('input')
    input.type = 'hidden'; input.name = element.dataset.name ?? ''; input.setAttribute('x-ref', 'input'); input.setAttribute('data-editor-model', '')
    input.value = model ?? ''; element.append(input)
    const controller = create({ property: 'value', live: true, blur: false })
    Object.assign(controller, {
        $el: element, $refs: { input },
        $wire: { $get: () => model, $set: (_, value) => { model = value; input.value = value ?? '' } },
        $watch: (_, callback) => { watchers.push(callback); return () => watchers.splice(watchers.indexOf(callback), 1) },
        $dispatch: (type, detail = {}) => element.dispatchEvent(new CustomEvent(type, { bubbles: true, detail })),
        $nextTick: callback => queueMicrotask(callback), $id: name => name + '-test',
    })
    controller.init()
    controllers.add(controller)
    for (const name of Object.getOwnPropertyNames(controller).filter(name => !name.startsWith('_') && !name.startsWith('$') && !['init', 'destroy'].includes(name))) {
        if (!(name in controller)) continue
        Object.defineProperty(element, name, {
            configurable: true,
            get: () => typeof controller[name] === 'function' ? controller[name].bind(controller) : controller[name],
            set: value => {
                if (name !== 'value') {
                    const setter = Object.getOwnPropertyDescriptor(controller, name)?.set
                    if (setter) return setter.call(controller, value)
                    controller[name] = value
                    return
                }
                model = value; input.value = value ?? ''; watchers.forEach(callback => callback(value))
            },
        })
    }
    element.remeasure = () => controller._resize?.()
    return element
}

export function cleanupControllers() {
    for (const controller of controllers) controller.destroy?.()
    controllers.clear()
}

export function provider(create, html, { binding = {}, wire = {} } = {}) {
    document.body.innerHTML = html
    const root = document.body.firstElementChild
    const data = create(binding)
    const cleanup = []
    Object.assign(data, {
        $el: root, $refs: Object.fromEntries([...root.querySelectorAll('[x-ref]')].map(element => [element.getAttribute('x-ref'), element])),
        $wire: wire, $nextTick: callback => callback(), $id: (name, suffix = '') => `${name}-${suffix}`,
        $dispatch: (type, detail = {}) => root.dispatchEvent(new CustomEvent(type, { bubbles: true, detail })),
        $watch: (_, callback) => { cleanup.push(callback); return () => {} },
    })
    controllers.add(data)
    data.init?.()
    return data
}
