<template>
    <div :class="{ 'cw-ribbon-wrapper-consume': consumeMode }">
        <div v-if="stickyRibbon" class="cw-ribbon-sticky-top"></div>
        <header class="cw-ribbon" :class="{ 'cw-ribbon-sticky': stickyRibbon, 'cw-ribbon-consume': consumeMode }">
            <div class="cw-ribbon-wrapper-left">
                <nav class="cw-ribbon-nav">
                    <slot name="buttons" />
                </nav>
                <nav class="cw-ribbon-breadcrumb">
                    <ul>
                        <slot name="breadcrumbList" />
                    </ul>
                </nav>
            </div>
            <div class="cw-ribbon-wrapper-right">
                <button class="cw-ribbon-button cw-ribbon-button-menu" @click="activeToolbar"></button>
                <button class="cw-ribbon-button" :class="[consumeMode ? 'cw-ribbon-button-zoom-out' : 'cw-ribbon-button-zoom']" @click="toggleConsumeMode"></button>
                <slot name="menu" />
            </div>
            <div v-if="consumeMode" class="cw-ribbon-consume-bottom"></div>
            <courseware-ribbon-toolbar
                :toolsActive="toolsActive"
                :class="{ 'cw-ribbon-tools-sticky': stickyRibbon }"
                :canEdit="canEdit"
                @deactivate="deactivateToolbar"
            />
        </header>
        <div v-if="stickyRibbon" class="cw-ribbon-sticky-bottom"></div>
        <div v-if="stickyRibbon" class="cw-ribbon-sticky-spacer"></div>
    </div>
</template>

<script>
import CoursewareRibbonToolbar from './CoursewareRibbonToolbar.vue';

export default {
    name: 'courseware-ribbon',
    components: {
        CoursewareRibbonToolbar,
    },
    props: {
        canEdit: Boolean,
    },
    data() {
        return {
            readModeActive: false,
            stickyRibbon: false,
        };
    },
    computed: {
        consumeMode() {
            return this.$store.getters.consumeMode;
        },
        toolsActive() {
            return this.$store.getters.showToolbar;
        },
    },
    methods: {
        toggleConsumeMode() {
            if (!this.consumeMode) {
                this.$store.dispatch('coursewareConsumeMode', true);
                this.$store.dispatch('coursewareViewMode', 'read');
            } else {
                this.$store.dispatch('coursewareConsumeMode', false);
            }
        },
        activeToolbar() {
            this.$store.dispatch('coursewareShowToolbar', true);
        },
        deactivateToolbar() {
            this.$store.dispatch('coursewareShowToolbar', false);
        },
        handleScroll() {
            this.stickyRibbon = window.scrollY > 130;
        },
    },
    mounted() {
        window.addEventListener('scroll', this.handleScroll);
    },
};
</script>
