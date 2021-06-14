<template>
    <div class="cw-tools cw-tools-contents">
        <courseware-tree :treeData="treeData" v-if="courseware.length" />
    </div>
</template>

<script>
import CoursewareTree from './CoursewareTree.vue';
import { mapGetters } from 'vuex';

export default {
    name: 'courseware-tools-contents',
    components: {
        CoursewareTree,
    },

    computed: {
        ...mapGetters({
            courseware: 'courseware-structural-elements/all',
        }),

        currentElementId() {
            return this.$route.params.id;
        },

        treeData() {
            let treeData = {
                name: 'Courseware',
            };
            if (this.courseware !== []) {
                let children = this.loadChildren(null, this.courseware, 0);

                if (children.length) {
                    treeData.children = children;
                }
            }

            if (treeData.children !== undefined && treeData.children.length) {
                return treeData.children[0];
            }

            return treeData;
        },
    },

    methods: {
        loadChildren(parentId, data, depth) {
            let children = [];

            for (var i = 0; i < data.length; i++) {
                if (data[i].relationships.parent.data?.id == parentId) {
                    let new_childs = this.loadChildren(data[i].id, data, depth + 1);
                    children.push({
                        name: data[i].attributes.title,
                        position: data[i].attributes.position,
                        element_id: data[i].id,
                        children: new_childs,
                        depth: depth,
                        current: this.currentElementId === data[i].id
                    });
                }
            }

            children.sort((a, b) => {
                return a.position > b.position ? 1 : b.position > a.position ? -1 : 0;
            });

            return children;
        },
    },
};
</script>
