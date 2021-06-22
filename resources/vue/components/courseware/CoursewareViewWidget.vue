<template>
    <ul class="widget-list widget-links sidebar-views cw-view-widget">
        <li :class="{ active: readView }" @click="setReadView"><translate>Lesen</translate></li>
        <li :class="{ active: editView }" @click="setEditView"><translate>Bearbeiten</translate></li>
    </ul>
</template>

<script>
import { mapActions } from 'vuex';

export default {
    name: 'courseware-view-widget',
    computed: {
        readView() {
            return this.$store.getters.viewMode === 'read';
        },
        editView() {
            return this.$store.getters.viewMode === 'edit';
        },
    },
    methods: {
        ...mapActions(
            ['coursewareBlockAdder']
        ),
        setReadView() {
            this.$store.dispatch('coursewareViewMode', 'read');
            this.coursewareBlockAdder({});
        },
        setEditView() {
            this.$store.dispatch('coursewareViewMode', 'edit');
        },
    },
};
</script>
