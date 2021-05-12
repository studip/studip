<template>
    <div class="cw-block cw-block-text">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="false"
            ref="defaultBlock"
            @storeEdit="storeText"
            @closeEdit="closeEdit"
        >
            <template #content>
                <section class="cw-block-content" v-html="currentText"></section>
            </template>
            <template v-if="canEdit" #edit>
                <studip-wysiwyg v-model="currentText"></studip-wysiwyg>
            </template>
            <template #info><translate>Informationen zum Text-Block</translate></template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import StudipWysiwyg from '../StudipWysiwyg.vue';
import { mapActions } from 'vuex';

export default {
    name: 'courseware-text-block',
    components: {
        CoursewareDefaultBlock,
        StudipWysiwyg,
    },
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            currentText: '',
        };
    },
    computed: {
        text() {
            return this.block?.attributes?.payload?.text;
        },
    },
    mounted() {
        this.currentText = this.text;
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
        }),
        closeEdit() {
            this.currentText = this.text;
        },
        async storeText() {
            let attributes = this.block.attributes;
            attributes.payload.text = this.currentText;
            await this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
            this.$refs.defaultBlock.displayFeature(false);
        },
    },
};
</script>
