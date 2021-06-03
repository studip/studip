<template>
    <div v-if="canEdit" class="cw-container-actions">
        <studip-action-menu 
            :items="menuItems" 
            @editContainer="editContainer"
            @deleteContainer="deleteContainer"
        />
    </div>
</template>

<script>
export default {
    name: 'courseware-container-actions',
    props: {
        canEdit: Boolean,
        container: Object,
    },
    computed: {
        menuItems() {
            if (this.container.attributes["container-type"] === 'list') {
                return [{ id: 1, label: this.$gettext('Abschnitt löschen'), icon: 'trash', emit: 'deleteContainer' }];
            } else {
                return [
                    { id: 1, label: this.$gettext('Abschnitt bearbeiten'), icon: 'edit', emit: 'editContainer' },
                    { id: 2, label: this.$gettext('Abschnitt löschen'), icon: 'trash', emit: 'deleteContainer' },
                ];
            }
        },
    },
    methods: {
        menuAction(action) {
            this[action]();
        },
        editContainer() {
            this.$emit('editContainer');
        },
        deleteContainer() {
            this.$emit('deleteContainer');
        },
    },
};
</script>
