import { afterEach, expect, it, vi } from 'vitest'
import { provider, cleanupControllers } from './alpine-harness.js'
import sidebar from '../../resources/js/alpine/sidebar.js'
afterEach(()=>{cleanupControllers();document.body.innerHTML='';vi.restoreAllMocks()})
const markup='<div data-slot="sidebar-wrapper"><button data-sidebar="trigger">Toggle</button><input><div data-slot="sidebar-container" popover><button>Menu</button></div></div>'
it('initializes from PHP and saves its scoped preference',()=>{
    const data=provider(()=>sidebar({open:false,cookie:'sidebar_admin'}),markup)
    expect(data.expanded).toBe(false);data.toggle();expect(data.expanded).toBe(true);expect(document.cookie).toContain('sidebar_admin=true')
})
it('handles shortcuts only for the focused provider and ignores editable fields',()=>{
    const first=provider(sidebar,markup);document.body.insertAdjacentHTML('beforeend',markup)
    const second=sidebar();second.$el=document.body.lastElementChild
    const event={target:second.$el.querySelector('button'),key:'b',ctrlKey:true,preventDefault:vi.fn()}
    first.shortcut(event);second.shortcut(event);expect(first.expanded).toBe(true);expect(second.expanded).toBe(false)
    first.shortcut({...event,target:first.$el.querySelector('input')});expect(first.expanded).toBe(true)
})
it('opens the mobile sheet, moves focus inside and restores it',()=>{
    vi.spyOn(window,'matchMedia').mockReturnValue({matches:true})
    const data=provider(sidebar,markup);const trigger=data.$el.querySelector('button');trigger.focus();data.toggle()
    expect(data.mobileOpen).toBe(true);expect(document.activeElement.textContent).toBe('Menu');expect(data.containers[0].matches(':popover-open')).toBe(true)
    data.showMobile(false);expect(document.activeElement).toBe(trigger);expect(data.expanded).toBe(true)
})
