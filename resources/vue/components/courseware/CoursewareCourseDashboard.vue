<template>
    <div class="cw-dashboard cw-course-dashboard">
        <courseware-collapsible-box :title="$gettext('Überblick')" :open="true" class="cw-dashboard-box cw-dashboard-box-full">
            <div class="cw-dashboard-overview">
                <courseware-oblong :name="textChapterFinished" :icon="'accept'" :size="'small'">
                    <template v-slot:oblongValue> {{ chapterCounter.finished }} </template>
                </courseware-oblong>
                <courseware-oblong :name="textChapterStarted" :icon="'play'" :size="'small'">
                    <template v-slot:oblongValue> {{ chapterCounter.started }} </template>
                </courseware-oblong>
                <courseware-oblong :name="textChapterAhead" :icon="'timetable'" :size="'small'">
                    <template v-slot:oblongValue> {{ chapterCounter.ahead }} </template>
                </courseware-oblong>
            </div>
        </courseware-collapsible-box>
        <courseware-collapsible-box :title="$gettext('Fortschritt')" :open="true" class="cw-dashboard-box cw-dashboard-box-half">
            <courseware-dashboard-progress />
        </courseware-collapsible-box>
        <courseware-collapsible-box :title="$gettext('Aktivitäten')" :open="true" class="cw-dashboard-box cw-dashboard-box-half">
            <courseware-dashboard-activities :activitiesList="activitiesList"></courseware-dashboard-activities>
        </courseware-collapsible-box>
    </div>
</template>

<script>
import CoursewareCollapsibleBox from './CoursewareCollapsibleBox.vue';
import CoursewareDashboardProgress from './CoursewareDashboardProgress.vue';
import CoursewareDashboardActivities from './CoursewareDashboardActivities.vue';
import CoursewareOblong from './CoursewareOblong.vue';

export default {
    name: 'courseware-course-dashboard',
    components: {
        CoursewareCollapsibleBox,
        CoursewareOblong,
        CoursewareDashboardProgress,
        CoursewareDashboardActivities,
    },
    data() {
        return {
            textChapterAhead: this.$gettext('bevorstehende Seiten'),
            textChapterStarted: this.$gettext('angefangene Seiten'),
            textChapterFinished: this.$gettext('abgeschlossene Seiten'),
        };
    },
    computed: {
        chapterCounter() {
            return STUDIP.courseware_chapter_counter;
        },

        activitiesList() {
            // todo in 5.1
            return [];
        },
    },
};
</script>
