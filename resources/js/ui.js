/*
 * esoboy/ui
 *
 * The components shadcn has, bundled into dist/ui.js and loaded by @uiScripts. The
 * others are in extras.js, and the heavy ones in resources/js/heavy: modules their
 * views load through @assets, so a page downloads them only when it shows one.
 *
 * Each module registers its custom elements or document listeners; the order matters
 * where one element uses another (Calendar before Date Picker).
 */

import * as accordion from './components/accordion.js'
import * as avatar from './components/avatar.js'
import * as calendar from './components/calendar.js'
import * as carousel from './components/carousel.js'
import * as codeBlock from './components/code-block.js'
import * as collapsible from './components/collapsible.js'
import * as combobox from './components/combobox.js'
import * as command from './components/command.js'
import * as confirm from './components/confirm.js'
import * as dataTable from './components/data-table.js'
import * as datePicker from './components/date-picker.js'
import * as dialog from './components/dialog.js'
import * as drawer from './components/drawer.js'
import * as dropdownMenu from './components/dropdown-menu.js'
import * as hoverCard from './components/hover-card.js'
import * as inputGroup from './components/input-group.js'
import * as inputOtp from './components/input-otp.js'
import * as messageScroller from './components/message-scroller.js'
import * as navigationMenu from './components/navigation-menu.js'
import * as popover from './components/popover.js'
import * as questionnaire from './components/questionnaire.js'
import * as radioGroup from './components/radio-group.js'
import * as resizable from './components/resizable.js'
import * as scrollArea from './components/scroll-area.js'
import * as select from './components/select.js'
import * as sidebar from './components/sidebar.js'
import * as slider from './components/slider.js'
import * as tabs from './components/tabs.js'
import * as theme from './components/theme.js'
import * as toast from './components/toast.js'
import * as tooltip from './components/tooltip.js'

const components = [
    radioGroup, select, command, combobox, calendar, datePicker, slider, inputOtp, tabs,
    sidebar, accordion, collapsible, scrollArea, resizable, carousel, messageScroller,
    dataTable, avatar, codeBlock, confirm, dialog, drawer, dropdownMenu, hoverCard,
    inputGroup, navigationMenu, popover, questionnaire, theme, toast, tooltip,
]

for (const component of components) component.register()
