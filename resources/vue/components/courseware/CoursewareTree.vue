<template>
    <div class="cw-tree">
        <ul class="cw-tree-root-list">
            <courseware-tree-item class="cw-tree-item" :item="treeData"></courseware-tree-item>
        </ul>
    </div>
</template>

<script>
import CoursewareTreeItem from './CoursewareTreeItem.vue';
export default {
    components: { CoursewareTreeItem },
    name: 'courseware-tree',
    props: {
        treeData: Object,
    },
};
</script>
