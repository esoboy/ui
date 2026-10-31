/*
 * Livewire hooks, whichever of the two scripts the page ran first.
 */

/**
 * Calls back with Livewire before it starts: right away when Livewire's script already
 * ran (it starts on DOMContentLoaded), otherwise on livewire:init.
 */
export function onLivewire(callback) {
    if (window.Livewire) {
        callback(window.Livewire)
    } else {
        document.addEventListener('livewire:init', () => callback(window.Livewire), { once: true })
    }
}
