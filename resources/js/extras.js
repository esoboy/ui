/*
 * esoboy/ui extras
 *
 * The components shadcn doesn't have (Ant Design's and others'), bundled into
 * dist/extras.js: a module their views load through @assets, so only pages showing one
 * download it.
 */

import anchor from './alpine/anchor.js'
import backToTop from './alpine/back-to-top.js'
import cascader from './alpine/cascader.js'
import colorPicker from './alpine/color-picker.js'
import countdown from './alpine/countdown.js'
import cropper from './alpine/cropper.js'
import emojiPicker from './alpine/emoji-picker.js'
import fileUpload from './alpine/file-upload.js'
import imagePreview from './alpine/image-preview.js'
import mentions from './alpine/mentions.js'
import { numberInput, rating } from './alpine/inputs.js'
import passwordInput from './alpine/password-input.js'
import tour from './alpine/tour.js'
import tree from './alpine/tree.js'
import watermark from './alpine/watermark.js'


window.Alpine.data('uiNumberInput', numberInput)
window.Alpine.data('uiRating', rating)
window.Alpine.data('uiTree', tree)
window.Alpine.data('uiCascader', cascader)
window.Alpine.data('uiColorPicker', colorPicker)
window.Alpine.data('uiCountdown', countdown)
window.Alpine.data('uiEmojiPicker', emojiPicker)

window.Alpine.data('uiAnchor', anchor)

window.Alpine.data('uiBackToTop', backToTop)

window.Alpine.data('uiFileUpload', fileUpload)

window.Alpine.data('uiImagePreview', imagePreview)

window.Alpine.data('uiPasswordInput', passwordInput)

window.Alpine.data('uiMentions', mentions)

window.Alpine.data('uiTour', tour)

window.Alpine.data('uiCropper', cropper)

window.Alpine.data('uiWatermark', watermark)
