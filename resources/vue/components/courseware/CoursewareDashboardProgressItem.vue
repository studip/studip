<template>
    <div class="cw-dashboard-progress-item" @click="$emit('selectChapter', chapterId)">
        <div class="cw-dashboard-progress-item-value">
            <courseware-progress-circle :value="parseInt(value)" />
        </div>
        <div class="cw-dashboard-progress-item-description">
            {{ name }}
        </div>
    </div>
</template>

<script>
import CoursewareProgressCircle from './CoursewareProgressCircle.vue';

export default {
    name: 'courseware-dashboard-progress-item',
    components: {
        CoursewareProgressCircle,
    },
    props: {
        name: String,
        value: Number,
        chapterId: String,
    },
};
</script>
