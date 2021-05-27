<template>
    <div id="mycourses">
        <studip-message-box v-if="isEmpty" type="info" :hideClose="true">
            {{ $gettext('Es wurden keine Veranstaltungen gefunden.') }}
            {{ $gettext('Mögliche Ursachen:') }}
            <template #details>
                <ul>
                    <li v-translate>
                        Sie haben zur Zeit keine Veranstaltungen belegt, an denen Sie teilnehmen können.
                        <br>
                        Bitte nutzen Sie <a :href="searchCoursesUrl"> <strong>Veranstaltung suchen / hinzufügen</strong> </a> um sich für Veranstaltungen anzumelden.
                    </li>

                    <li v-translate>
                        In dem ausgewählten <strong>Semester</strong> wurden keine Veranstaltungen belegt.
                        <br>
                        Wählen Sie links im <strong>Semesterfilter</strong> ein anderes Semester aus!
                    </li>
                </ul>
            </template>
        </studip-message-box>
        <component v-else :is="displayComponent" :icon-size="iconSize"></component>

        <MountingPortal mount-to="#tiled-courses-sidebar-switch .sidebar-widget-content .widget-list" name="sidebar-switch">
            <my-courses-sidebar-switch></my-courses-sidebar-switch>
        </MountingPortal>

        <MountingPortal mount-to="#tiled-courses-new-contents-toggle .sidebar-widget-content .widget-list" name="sidebar-content-toggle">
            <my-courses-new-content-toggle></my-courses-new-content-toggle>
        </MountingPortal>
    </div>
</template>

<script>
import { sprintf } from 'sprintf-js';
import MyCoursesTables from './MyCoursesTables.vue';
import MyCoursesTiles from './MyCoursesTiles.vue';
import MyCoursesMixin from '../mixins/MyCoursesMixin.js';
import MyCoursesSidebarSwitch from "./MyCoursesSidebarSwitch.vue";
import MyCoursesNewContentToggle from "./MyCoursesNewContentToggle.vue";

export default {
    name: 'MyCourses',
    mixins: [MyCoursesMixin],
    components: {
        MyCoursesTables,
        MyCoursesTiles,
        MyCoursesSidebarSwitch,
        MyCoursesNewContentToggle,
    },
    computed: {
        displayComponent () {
            return this.displayedType === 'tiles'
                 ? MyCoursesTiles
                 : MyCoursesTables;
        },
        displayedType () {
            return this.getConfig(this.viewConfig);
        },
        iconSize () {
            if (this.displayedType !== 'tiles' && !this.responsiveDisplay) {
                return 20;
            }
            return 24;
        },
        searchCoursesUrl () {
            return STUDIP.URLHelper.getURL('dispatch.php/search/courses');
        },
        isEmpty () {
            return this.groups.length === 0;
        }
    }
}
</script>
