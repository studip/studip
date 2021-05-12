<template>
    <div :class="{ 'cw-manager-block-clickable': inserter }" class="cw-manager-block" @click="clickItem">
        <span v-if="inserter">
            <studip-icon shape="arr_2left" size="16" role="sort" />
        </span>
        {{ block.attributes.title }}
        <div v-if="sortBlocks" class="cw-manager-block-buttons">
            <studip-icon :class="{'cw-manager-icon-disabled' : !canMoveUp}" shape="arr_2up" size="16" role="sort" @click="moveUp" />
            <studip-icon :class="{'cw-manager-icon-disabled' : !canMoveDown}" shape="arr_2down" size="16" role="sort" @click="moveDown" />
        </div>
    </div>
</template>

<script>
export default {
    name: 'courseware-manager-block',
    props: {
        block: Object,
        inserter: Boolean,
        sortBlocks: Boolean,
        elementType: String,
        canMoveUp: Boolean,
        canMoveDown: Boolean,
        sectionId: Number
    },
    methods: {
        clickItem() {
            if (this.inserter) {
                this.$emit('insertBlock', {block: this.block, source: this.elementType});
            }
        },
        moveUp() {
            if (this.sortBlocks) {
                this.$emit('moveUp', this.block.id, this.sectionId);
            }
        },
        moveDown() {
            if (this.sortBlocks) {
                this.$emit('moveDown', this.block.id, this.sectionId);
            }
        },
    },
};
</script>
