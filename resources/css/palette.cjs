const flattenColorPalette = require('tailwindcss/lib/util/flattenColorPalette')

// Blade can receive a color from a database after Tailwind has finished scanning the host.
module.exports = function componentPalette({ addBase, theme }) {
    const colors = flattenColorPalette(theme('colors'))
    const rules = {
        ':where([data-esoboy-ui][data-ui-palette])': { '--ui-palette-color': 'initial' },
    }
    for (const [name, value] of Object.entries(colors)) {
        if (typeof value !== 'string' || !/^[\w-]+$/.test(name)) continue
        // Tailwind resolves normal tokens through CSS variables and @theme inline at the element.
        const color = `--theme(--color-${name}, ${value})`
        rules[`:where([data-esoboy-ui])[data-ui-palette="${name}"]`] = {
            '--ui-palette-color': color,
        }
        const family = name.replace(/-500$/, '')
        if (family !== name && !Object.hasOwn(colors, family)) {
            rules[`:where([data-esoboy-ui])[data-ui-palette="${family}"]`] = {
                '--ui-palette-color': `var(--color-${family}, ${color})`,
            }
        }
    }
    addBase(rules)
}
