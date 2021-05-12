<template>
    <div class="cw-tab" :class="{ 'cw-tab-active': isActive }">
        <slot></slot>
    </div>
</template>

<script>
export default {
    name: 'courseware-tab',
    props: {
        name: {type: String, required: true },
        selected: { type: Boolean, default: false },
        index: {type: Number, default: 0 },
        icon: {type: String, default: ''},
    },
    data() {
        return {
            isActive: false,
        };
    },
    computed: {
        href() {
            return '#' +this.index + '-' +this.name.toLowerCase().replace(/ /g, '-');
        },
    },
    mounted() {
        this.isActive = this.selected;
    },
};
</script>
