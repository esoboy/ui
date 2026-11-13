// Heavy browser engines load only for a rendered component. Their documents and
// canvas objects stay outside Alpine's reactive proxy; Alpine owns their lifetime.
export default binding => {
    let controller = null, alive = true
    return {
        async init() {
            const { default: create } = await import(this.$el.dataset.engine)
            if (!alive) return
            controller = create(binding)
            for (const name of ['$el', '$wire', '$refs', '$dispatch', '$watch', '$nextTick', '$id']) Object.defineProperty(controller, name, { get: () => this[name] })
            await controller.init()
        },
        destroy() { alive = false; controller?.destroy?.(); controller = null },
    }
}
