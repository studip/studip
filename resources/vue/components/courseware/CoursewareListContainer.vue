<template>
    <courseware-default-container
        :container="container"
        :containerClass="'cw-container-list'"
        :canEdit="canEdit"
        :isTeacher="isTeacher"
        @storeContainer="storeContainer"
    >
        <template v-slot:containerContent>
            <ul class="cw-container-list-block-list">
                <li v-for="block in blocks" :key="block.id" class="cw-block-item">
                    <component :is="component(block)" :block="block" :canEdit="canEdit" :isTeacher="isTeacher" />
                </li>
                <li v-if="showEditMode && canEdit"><courseware-block-adder-area :container="container" :section="0" /></li>
            </ul>
        </template>
    </courseware-default-container>
</template>

<script>
import ContainerComponents from './container-components.js';
import containerMixin from '../../mixins/courseware/container.js';
import { mapGetters } from 'vuex';

export default {
    name: 'courseware-list-container',
    mixins: [containerMixin],
    components: ContainerComponents,
    props: {
        container: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {};
    },
    computed: {
        ...mapGetters({
            blockById: 'courseware-blocks/byId',
        }),
        blocks() {
            if (!this.container) {
                return [];
            }

            return this.container.relationships.blocks.data.map(({ id }) => this.blockById({ id }));
        },
        showEditMode() {
            return this.$store.getters.viewMode === 'edit';
        },
    },
    methods: {
        storeContainer(data) {
            console.log(data);
        },
        component(block) {
            if (block.attributes["block-type"] !== undefined) {
                return 'courseware-' + block.attributes["block-type"] + '-block';
            }
        },
    },
    mounted() {},
};
</script>
