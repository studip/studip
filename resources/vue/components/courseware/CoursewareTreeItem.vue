<template>
    <li>
        <div :class="{'cw-tree-item-is-root': isRoot, 'cw-tree-item-first-level': isFirstLevel}">
            <router-link
                :to="'/structural_element/' + item.element_id"
                class="cw-tree-item-link"
                :class="{'cw-tree-item-link-current': item.current}"
            >
                {{ item.name }}
            </router-link>
        </div>
        <ul v-show="isOpen" v-if="hasChildren" :class="{'cw-tree-chapter-list': isRoot}">
            <courseware-tree-item
                v-for="(child, index) in item.children"
                :key="index"
                :item="child"
                class="cw-tree-item"
            />
        </ul>
    </li>
</template>

<script>
export default {
    name: 'courseware-tree-item',
    props: {
        item: Object,
    },
    data() {
        return {
            isOpen: true,
        };
    },
    computed: {
        hasChildren() {
            return this.item.children && this.item.children.length;
        },
        isRoot() {
            return this.item.depth === 0;
        },
        isFirstLevel() {
            return this.item.depth === 1;
        },
        showToggleButton() {
            return this.hasChildren && !this.isRoot && !this.isFirstLevel;
        },
        showDisabledToggleButton() {
            return !this.hasChildren && !this.isRoot && !this.isFirstLevel;
        }
    },
    methods: {
        toggle: function () {
            if (this.hasChildren) {
                this.isOpen = !this.isOpen;
            }
        },
    },
    mounted() {
        if (this.item.depth > 2) {
            this.isOpen = false;
        }
    }
};
</script>
