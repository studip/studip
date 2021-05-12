<template>
    <ul class="cw-dashboard-activities">
        <courseware-activity-item v-for="(item, index) in activitiesList" :key="index" :item="item" />
    </ul>
</template>

<script>
import CoursewareActivityItem from './CoursewareActivityItem.vue';

export default {
    name: 'courseware-dashboard-activities',
    components: {
        CoursewareActivityItem,
    },
    props: {
        activitiesList: Array,
    },
};
</script>
