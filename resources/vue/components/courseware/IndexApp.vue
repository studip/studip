<template>
    <div v-if="courseware">
        <courseware-structural-element></courseware-structural-element>
        <MountingPortal mountTo="#courseware-action-widget" name="sidebar-actions">
            <courseware-action-widget></courseware-action-widget>
        </MountingPortal>
        <MountingPortal mountTo="#courseware-view-widget" name="sidebar-views">
            <courseware-view-widget></courseware-view-widget>
        </MountingPortal>
    </div>
    <div v-else>TODO: Loading</div>
</template>

<script>
import CoursewareStructuralElement from './CoursewareStructuralElement.vue';
import CoursewareViewWidget from './CoursewareViewWidget.vue';
import CoursewareActionWidget from './CoursewareActionWidget.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    components: {
        CoursewareStructuralElement,
        CoursewareViewWidget,
        CoursewareActionWidget,
    },
    computed: {
        ...mapGetters(['courseware', 'userId']),
    },
    methods: {
        ...mapActions(['loadCoursewareStructure', 'loadTeacherStatus']),
    },
    async mounted() {
        await this.loadCoursewareStructure();
        await this.loadTeacherStatus(this.userId);
        // console.debug('IndexApp mounted for courseware:', this.courseware, this.$store);
    },
};
</script>
