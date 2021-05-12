<template>
    <div
        class="cw-manager-element-item"
        :class="{ 'cw-manager-element-item-sorting': sortChapters }"
        @click="clickItem"
    >
        <span v-if="inserter" @click="clickItem">
            <studip-icon shape="arr_2left" size="16" role="sort" />
        </span>
        {{ element.attributes.title }}
        <div v-if="sortChapters" class="cw-manager-element-item-buttons">
            <studip-icon :class="{'cw-manager-icon-disabled' : !canMoveUp}" shape="arr_2up" size="16" role="sort" @click="moveUp" />
            <studip-icon :class="{'cw-manager-icon-disabled' : !canMoveDown}" shape="arr_2down" size="16" role="sort" @click="moveDown" />
        </div>
    </div>
</template>

<script>
export default {
    name: 'courseware-manager-element-item',
    props: {
        element: Object,
        inserter: Boolean,
        sortChapters: Boolean,
        type: String,
        canMoveUp: Boolean,
        canMoveDown: Boolean
    },
    methods: {
        clickItem() {
            if (this.sortChapters) {
                return false;
            }
            if (this.inserter) {
                this.$emit('insertElement', {element: this.element, source: this.type});
            } else {
                this.$emit('selectChapter', this.element.id);
            }
        },
        moveUp() {
            if (this.sortChapters) {
                this.$emit('moveUp', this.element.id);
            }
        },
        moveDown() {
            if (this.sortChapters) {
                this.$emit('moveDown', this.element.id);
            }
        },
    },
};
</script>
