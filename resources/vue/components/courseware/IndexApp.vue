<template>
    <div v-if="courseware">
        <courseware-structural-element></courseware-structural-element>
        <MountingPortal mountTo="#courseware-view-widget" name="sidebar">
            <courseware-view-widget></courseware-view-widget>
        </MountingPortal>
    </div>
    <div v-else>TODO: Loading</div>
</template>

<script>
import CoursewareStructuralElement from './CoursewareStructuralElement.vue';
import CoursewareViewWidget from './CoursewareViewWidget.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    components: {
        CoursewareStructuralElement,
        CoursewareViewWidget,
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
