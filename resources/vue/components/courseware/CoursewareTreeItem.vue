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
        <ul v-if="hasChildren" :class="{'cw-tree-chapter-list': isRoot}">
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
    },
};
</script>
