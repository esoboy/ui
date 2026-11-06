import engine from './alpine/engine.js'
/*
 * esoboy/ui
 *
 * The components shadcn has, bundled into dist/ui.js and loaded by @uiScripts. The
 * others are in extras.js. Heavy SDK adapters load through uiEngine only when the
 * corresponding Blade component is mounted. All interaction lives in Alpine
 * lifecycles; PHP and Livewire own application data.
 */

import { accordion, collapsible } from './alpine/disclosure.js'
import calendar from './alpine/calendar.js'
import carousel from './alpine/carousel.js'
import combobox from './alpine/combobox.js'
import command from './alpine/command.js'
import { register as registerConfirm } from './alpine/confirm.js'
import dataTable from './alpine/data-table.js'
import dialog from './alpine/dialog.js'
import { trigger as overlayTrigger, content as overlayContent, registerTrigger } from './alpine/overlay.js'
import { slider, otp, inputCount, copyCode } from './alpine/inputs.js'
import messageScroller from './alpine/message-scroller.js'
import questionnaire from './alpine/questionnaire.js'
import resizable from './alpine/resizable.js'
import scrollArea from './alpine/scroll-area.js'
import select from './alpine/select.js'
import sidebar from './alpine/sidebar.js'
import tabs from './alpine/tabs.js'
import timePicker, { timeHourChooser } from './alpine/time-picker.js'
import toggleGroup from './alpine/toggle-group.js'
import { register as registerTheme } from './alpine/theme.js'
import toast from './alpine/toast.js'


window.Alpine.data('uiSidebar', sidebar)
window.Alpine.data('uiSelect', select)
window.Alpine.data('uiTabs', tabs)
window.Alpine.data('uiToggleGroup', toggleGroup)
window.Alpine.data('uiAccordion', accordion)
window.Alpine.data('uiCollapsible', collapsible)
window.Alpine.data('uiSlider', slider)
window.Alpine.data('uiOtp', otp)
window.Alpine.data('uiInputCount', inputCount)
window.Alpine.data('uiCopyCode', copyCode)
window.Alpine.data('uiOverlayTrigger', overlayTrigger)
registerTrigger(window.Alpine)
window.Alpine.data('uiOverlayContent', overlayContent)

window.Alpine.data('uiCarousel', carousel)

window.Alpine.data('uiMessageScroller', messageScroller)

window.Alpine.data('uiResizable', resizable)

window.Alpine.data('uiScrollArea', scrollArea)

window.Alpine.data('uiCalendar', calendar)
window.Alpine.data('uiTimePicker', timePicker)
window.Alpine.data('uiTimeHourChooser', timeHourChooser)
window.Alpine.data('uiCommand', command)
window.Alpine.data('uiCombobox', combobox)
window.Alpine.data('uiQuestionnaire', questionnaire)
window.Alpine.data('uiDialog', dialog)

window.Alpine.data('uiDataTable', dataTable)

registerTheme(window.Alpine)

registerConfirm(window.Livewire, window.Alpine)

window.Alpine.data('uiToast', toast)

window.Alpine.data('uiEngine', engine)
