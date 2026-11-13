// Thin lifecycle helpers around Alpine's own directives. They own no application
// state and do not register native DOM listeners or custom elements.
const bindings = new WeakMap()
const owners = new WeakMap()

export function listen(owner, target, type, callback, options = {}) {
    if (! target) return
    if (options === true) options = { capture: true }
    const suffix = target === window ? '.window' : target === document ? '.document' : ''
    const element = suffix ? owner.$el : target
    const modifiers = `${options.capture ? '.capture' : ''}${options.passive ? '.passive' : ''}${options.once ? '.once' : ''}`
    const stop = window.Alpine.bind(element, { [`x-on:${type}${suffix}${modifiers}`]: callback })
    const root = owner.$el
    const dispose = () => {
        stop()
        bindings.set(target, (bindings.get(target) ?? []).filter(item => item.dispose !== dispose))
        owners.set(root, (owners.get(root) ?? []).filter(item => item !== dispose))
    }
    owners.set(root, [...(owners.get(root) ?? []), dispose])

    const records = bindings.get(target) ?? []
    records.push({ type, callback, dispose })
    bindings.set(target, records)

    return dispose
}

export const bind = (element, type, callback) => window.Alpine.bind(element, { ['x-on:' + type]: callback })

export function unlisten(target, type, callback) {
    const records = bindings.get(target) ?? []
    for (const record of records.filter(record => record.type === type && record.callback === callback)) record.dispose()
    bindings.set(target, records.filter(record => record.type !== type || record.callback !== callback))
}

export function disconnected(owner) {
    owners.get(owner.$el)?.slice().forEach(dispose => dispose())
    owners.delete(owner.$el)
}

export function emit(element, name, detail) {
    if (detail === undefined && ['input', 'change'].includes(name)) detail = element.value;
    return window.Alpine.evaluate(element, '$dispatch(name, detail)', { scope: { name, detail } })
}

export function notifyModel(owner) {
    owner.$nextTick(() => {
        emit(owner.$el, 'input', owner.$wire.value)
        emit(owner.$el, 'change', owner.$wire.value)
        if (! owner.$el.contains(document.activeElement)) emit(owner.$el, 'blur')
    })
}

