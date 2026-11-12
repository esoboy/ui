export function register(Alpine) {
    const root = document.documentElement
    const saved = () => { try { return JSON.parse(decodeURIComponent(document.cookie.split('; ').find(entry => entry.startsWith('ui_theme='))?.slice(9) ?? '{}')) } catch { return {} } }
    Alpine.store('uiTheme', {
        choice: root.dataset.theme ?? 'system',
        systemDark: matchMedia('(prefers-color-scheme: dark)').matches,
        get dark() { return this.choice === 'dark' || this.choice === 'system' && this.systemDark },
        refresh() { this.systemDark = matchMedia('(prefers-color-scheme: dark)').matches },
        pick(choice) {
            if (!['light', 'dark', 'system'].includes(choice)) return
            this.choice = choice; this.refresh()
            root.setAttribute('data-theme-switching', '')
            choice === 'system' ? root.removeAttribute('data-theme') : root.setAttribute('data-theme', choice)
            getComputedStyle(root).colorScheme
            setTimeout(() => root.removeAttribute('data-theme-switching'))
            const choices = saved(), scope = root.dataset.themeScope ?? 'default'
            if (choice === 'system') delete choices[scope]; else choices[scope] = choice
            document.cookie = `ui_theme=${encodeURIComponent(JSON.stringify(choices))}; path=/; max-age=31536000; samesite=lax`
        },
    })
    Alpine.data('uiThemeControl', () => ({
        get on() { return this.$el.dataset.themeValue === 'toggle' ? this.$store.uiTheme.dark : this.$store.uiTheme.choice === this.$el.dataset.themeValue },
        sync() {
            if (this.$el.matches('input')) this.$el.checked = this.on
            else this.$el.setAttribute(['menuitemradio', 'menuitemcheckbox', 'option', 'radio', 'switch', 'checkbox'].includes(this.$el.getAttribute('role')) ? 'aria-checked' : 'aria-pressed', String(this.on))
        },
        click() { if (!this.$el.matches('input')) this.pick() },
        change() { if (this.$el.type === 'checkbox' || this.$el.checked) this.pick() },
        pick() {
            const value = this.$el.dataset.themeValue
            this.$store.uiTheme.pick(value === 'toggle' ? (this.$store.uiTheme.dark ? 'light' : 'dark') : this.$el.type === 'checkbox' && !this.$el.checked ? 'system' : value)
        },
    }))
}
