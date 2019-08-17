import './public-path.js'

// promise polyfill needed for IE11 to load tablesorter
import 'es6-promise/auto'

import "../stylesheets/studip-jquery-ui.less"
import "chartist/dist/chartist.css"
import "../stylesheets/studip.less"
// Basic scss support
import "../stylesheets/studip.scss"

import lodash from "lodash"
window._ = lodash

import "./l10n.js"

import chartist from "chartist"
window.Chartist = chartist

import QRCode from "./vendor/qrcode-04f46c6.js"
window.QRCode = QRCode

import "./jquery-bundle.js"

import "./init.js"
import "./chunk-loader.js"

import "./studip-ui.js"
import "./bootstrap/tfa.js"
import "./bootstrap/tables.js"
import "./bootstrap/studip_helper_attributes.js"
import "./bootstrap/header_magic.js"
import "./bootstrap/header_navigation.js"
import "./bootstrap/personal_notifications.js"
import "./bootstrap/sidebar.js"
import "./bootstrap/smiley_picker.js"
import "./bootstrap/dialog.js"
import "./bootstrap/responsive.js"
import "./bootstrap/jsupdater.js"
import "./bootstrap/files.js"
import "./bootstrap/news.js"
import "./bootstrap/messages.js"
import "./bootstrap/quick_search.js"
import "./bootstrap/multi_select.js"
import "./bootstrap/multi_person_search.js"
import "./bootstrap/skip_links.js"
import "./bootstrap/i18n_input.js"
import "./bootstrap/forms.js"
import "./bootstrap/calendar_dialog.js"
import "./bootstrap/drag_and_drop_upload.js"
import "./bootstrap/admin_sem_classes.js"
import "./bootstrap/cronjobs.js"
import "./bootstrap/contentbox.js"
import "./bootstrap/dates.js"
import "./bootstrap/tour.js"
import "./bootstrap/questionnaire.js"
import "./bootstrap/qr_code.js"
import "./bootstrap/startpage.js"
import "./bootstrap/wiki.js"
import "./bootstrap/course_wizard.js"
import "./bootstrap/smiley.js"
import "./bootstrap/big_image_handler.js"
import "./bootstrap/opengraph.js"
import "./bootstrap/actionmenu.js"
import "./bootstrap/article.js"
import "./bootstrap/copyable_links.js"
import "./bootstrap/selection.js"
import "./bootstrap/data_secure.js"
import "./bootstrap/tooltip.js"
import "./bootstrap/lightbox.js"
import "./bootstrap/application.js"
import "./bootstrap/global_search.js"
import "./bootstrap/search.js"
import "./bootstrap/mvv_difflog.js"
import "./bootstrap/members.js"
import "./bootstrap/avatar.js"
import "./bootstrap/raumzeit.js"
import "./bootstrap/settings.js"
import "./bootstrap/subcourses.js"
import "./bootstrap/widgets.js"
import "./bootstrap/tabbable_widget.js"
import "./bootstrap/gradebook.js"
import "./bootstrap/layout.js"

import "./mvv_course_wizard.js"
import "./mvv.js"
