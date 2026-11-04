/*
 * esoboy/ui extras
 *
 * The components shadcn doesn't have (Ant Design's and others'), bundled into
 * dist/extras.js: a module their views load through @assets, so only pages showing one
 * download it.
 */

import * as anchor from './components/anchor.js'
import * as backToTop from './components/back-to-top.js'
import * as cascader from './components/cascader.js'
import * as colorPicker from './components/color-picker.js'
import * as countdown from './components/countdown.js'
import * as cropper from './components/cropper.js'
import * as emojiPicker from './components/emoji-picker.js'
import * as fileUpload from './components/file-upload.js'
import * as imagePreview from './components/image-preview.js'
import * as mentions from './components/mentions.js'
import * as numberInput from './components/number-input.js'
import * as passwordInput from './components/password-input.js'
import * as rating from './components/rating.js'
import * as tagsInput from './components/tags-input.js'
import * as tour from './components/tour.js'
import * as transfer from './components/transfer.js'
import * as tree from './components/tree.js'
import * as verificationCodeButton from './components/verification-code-button.js'
import * as watermark from './components/watermark.js'

const components = [
    anchor, backToTop, cascader, colorPicker, countdown, cropper, emojiPicker, fileUpload,
    imagePreview, mentions, numberInput, passwordInput, rating, tagsInput, tour, transfer,
    tree, verificationCodeButton, watermark,
]

for (const component of components) component.register()
