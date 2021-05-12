<template>
    <div class="cw-default-block-elements">
        <courseware-block-actions
            :block="block"
            :canEdit="canEdit"
            @editBlock="editBlock"
            @showFeedback="showFeedback"
            @showComments="showComments"
            @showExportOptions="showExportOptions"
        />
        <courseware-block-feedback v-if="canEdit" :block="block" :canEdit="canEdit" :isTeacher="isTeacher" />
        <courseware-block-comments :block="block" />
        <courseware-block-export-options v-if="canEdit" :block="block" />
    </div>
</template>

<script>
import CoursewareBlockActions from './CoursewareBlockActions.vue';
import CoursewareBlockComments from './CoursewareBlockComments.vue';
import CoursewareBlockExportOptions from './CoursewareBlockExportOptions.vue';
import CoursewareBlockFeedback from './CoursewareBlockFeedback.vue';

export default {
    name: 'courseware-default-block-elements',
    components: {
        CoursewareBlockActions,
        CoursewareBlockComments,
        CoursewareBlockExportOptions,
        CoursewareBlockFeedback,
    },
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
        showFeatures: Boolean,
    },
    data() {
        return {
            displayFeatures: false,
        };
    },
    methods: {
        editBlock() {
            this.$emit('editBlock');
        },
        showFeedback() {},
        showComments() {},
        showExportOptions() {},
    },
};
</script>
