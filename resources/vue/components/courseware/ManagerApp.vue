<template>
    <courseware-course-manager></courseware-course-manager>
</template>

<script>
import CoursewareCourseManager from './CoursewareCourseManager.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    components: { CoursewareCourseManager },
    computed: {
        ...mapGetters(['courseware']),
    },
    methods: {
        ...mapActions(['loadCoursewareStructure']),
    },
    async mounted() {
        await this.loadCoursewareStructure();
    },
};
</script>
