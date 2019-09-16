import ActionMenu from './lib/actionmenu.js';
import admin_sem_class from './lib/admin_sem_class.js';
import Admission from './lib/admission.js';
import Arbeitsgruppen from './lib/arbeitsgruppen.js';
import Archive from './lib/archive.js';
import Audio from './lib/audio.js';
import Avatar from './lib/avatar.js';
import BigImageHandler from './lib/big_image_handler.js';
import Browse from './lib/browse.js';
import Cache from './lib/cache.js';
import Calendar from './lib/calendar.js';
import CalendarDialog from './lib/calendar_dialog.js';
import Cookie from './lib/cookie.js';
import CourseWizard from './lib/course_wizard.js';
import createURLHelper from './lib/url_helper.js';
import CSS from './lib/css.js';
import Dates from './lib/dates.js';
import Dialog from './lib/dialog.js';
import Dialogs from './lib/dialogs.js';
import DragAndDropUpload from './lib/drag_and_drop_upload.js';
import enrollment from './lib/enrollment.js';
import extractCallback from './lib/extract_callback.js';
import Files from './lib/files.js';
import Filesystem from './lib/filesystem.js';
import FilesDashboard from './lib/files_dashboard.js';
import Folders from './lib/folders.js';
import Forms from './lib/forms.js';
import GlobalSearch from './lib/global_search.js';
import HeaderMagic from './lib/header_magic.js';
import i18n from './lib/i18n.js';
import Instschedule from './lib/instschedule.js';
import JSUpdater from './lib/jsupdater.js';
import Lightbox from './lib/lightbox.js';
import Markup from './lib/markup.js';
import Members from './lib/members.js';
import Messages from './lib/messages.js';
import MultiPersonSearch from './lib/multi_person_search.js';
import MultiSelect from './lib/multi_select.js';
import NavigationShrinker from './lib/navigation_shrinker.js';
import News from './lib/news.js';
import OldUpload from './lib/old_upload.js';
import Overlay from './lib/overlay.js';
import parseOptions from './lib/parse_options.js';
import PersonalNotifications from './lib/personal_notifications.js';
import QRCode from './lib/qr_code.js';
import Questionnaire from './lib/questionnaire.js';
import QuickSearch from './lib/quick_search.js';
import Raumzeit from './lib/raumzeit.js';
import {ready, domReady, dialogReady} from './lib/ready.js';
import register from './lib/register.js';
import RESTAPI, { api } from './lib/restapi.js';
import Schedule from './lib/schedule.js';
import Scroll from './lib/scroll.js';
import Search from './lib/search.js';
import Sidebar from './lib/sidebar.js';
import SkipLinks from './lib/skip_links.js';
import SmileyPicker from './lib/smiley_picker.js';
import startpage from './lib/startpage.js';
import Statusgroups from './lib/statusgroups.js';
import study_area_selection from './lib/study_area_selection.js';
import Table from './lib/table.js';
import Toolbar from './lib/toolbar.js';
import Tooltip from './lib/tooltip.js';
import Tour from './lib/tour.js';
import UserFilter from './lib/user_filter.js';
import WidgetSystem from './lib/widget_system.js';
import wysiwyg from './lib/wysiwyg.js';

const configURLHelper = _.get(window, 'STUDIP.URLHelper', {});
const URLHelper = createURLHelper(configURLHelper);

window.STUDIP = _.assign(window.STUDIP || {}, {
    ActionMenu,
    admin_sem_class,
    Admission,
    api,
    Arbeitsgruppen,
    Archive,
    Audio,
    Avatar,
    BigImageHandler,
    Browse,
    Cache,
    Calendar,
    CalendarDialog,
    Cookie,
    CourseWizard,
    CSS,
    Dates,
    Dialog,
    Dialogs,
    DragAndDropUpload,
    enrollment,
    extractCallback,
    Files,
    Filesystem,
    FilesDashboard,
    Folders,
    Forms,
    GlobalSearch,
    HeaderMagic,
    i18n,
    Instschedule,
    JSUpdater,
    Lightbox,
    Markup,
    Members,
    Messages,
    MultiPersonSearch,
    MultiSelect,
    NavigationShrinker,
    News,
    OldUpload,
    Overlay,
    parseOptions,
    PersonalNotifications,
    QRCode,
    Questionnaire,
    QuickSearch,
    Raumzeit,
    register,
    RESTAPI,
    Schedule,
    Scroll,
    Search,
    Sidebar,
    SkipLinks,
    SmileyPicker,
    startpage,
    Statusgroups,
    study_area_selection,
    Table,
    Toolbar,
    Tooltip,
    Tour,
    URLHelper,
    UserFilter,
    WidgetSystem,
    wysiwyg,

    ready,
    domReady,
    dialogReady
});
