import { afterEach, expect, it, vi } from 'vitest'
import { Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm.js'
import { register } from '../../resources/js/alpine/theme.js'
afterEach(()=>{document.documentElement.removeAttribute('data-theme');document.documentElement.removeAttribute('data-theme-scope');document.cookie='ui_theme=;max-age=0;path=/';vi.restoreAllMocks()})
it('persists the theme per PHP scope and lets system mode use CSS',()=>{
    document.documentElement.dataset.themeScope='admin';document.cookie='ui_theme='+encodeURIComponent(JSON.stringify({shop:'light'}))+';path=/'
    register(Alpine);const theme=Alpine.store('uiTheme');theme.pick('dark');expect(document.documentElement.dataset.theme).toBe('dark')
    const saved=JSON.parse(decodeURIComponent(document.cookie.split('; ').find(c=>c.startsWith('ui_theme=')).slice(9)));expect(saved).toEqual({shop:'light',admin:'dark'})
    theme.pick('system');expect(document.documentElement.hasAttribute('data-theme')).toBe(false)
})
it('follows system preference and ignores invalid choices',()=>{
    vi.spyOn(window,'matchMedia').mockReturnValue({matches:true});register(Alpine);const theme=Alpine.store('uiTheme');expect(theme.dark).toBe(true);theme.pick('invalid');expect(theme.choice).toBe('system');theme.pick('light');expect(theme.dark).toBe(false)
})
