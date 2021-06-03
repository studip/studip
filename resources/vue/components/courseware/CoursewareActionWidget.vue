<template>
    <ul class="widget-list widget-links cw-action-widget">
        <li v-show="canEdit" class="cw-action-widget-edit" @click="editElement"><translate>Seite bearbeiten</translate></li>
        <li v-show="canEdit" class="cw-action-widget-add" @click="addElement"><translate>Seite hinzufügen</translate></li>
        <li class="cw-action-widget-info" @click="showElementInfo"><translate>Informationen anzeigen</translate></li>
        <li class="cw-action-widget-star" @click="createBookmark"><translate>Lesezeichen setzen</translate></li>
        <li v-show="!isRoot && canEdit" class="cw-action-widget-trash" @click="deleteElement"><translate>Seite löschen</translate></li>
    </ul>
</template>

<script>
import StudipIcon from './../StudipIcon.vue';
import { mapActions, mapGetters } from 'vuex';
export default {
    name: 'courseware-action-widget',
    components: {
        StudipIcon
    },
    data() {
        return {
            currentId: null,
        }
    },
    computed: {
         ...mapGetters({
            structuralElementById: 'courseware-structural-elements/byId',
        }),
        structuralElement() {
            if (!this.currentId) {
                return null;
            }

            return this.structuralElementById({ id: this.currentId });
        },
        isRoot() {
            if (!this.structuralElement) {
                return true;
            }

            return this.structuralElement.relationships.parent.data === null;
        },
        canEdit() {
            if (!this.structuralElement) {
                return false;
            }
            return this.structuralElement.attributes['can-edit'];
        },
    },
    async mounted() {
        if (!this.currentId) {
            this.setCurrentId(this.$route.params.id);
        }
    },
    methods: {
        ...mapActions({
            loadStructuralElement: 'loadStructuralElement',
            showElementEditDialog: 'showElementEditDialog',
            showElementAddDialog: 'showElementAddDialog',
            showElementDeleteDialog: 'showElementDeleteDialog',
            showElementInfoDialog: 'showElementInfoDialog',
            companionInfo: 'companionInfo',
            addBookmark: 'addBookmark',
            lockObject: 'lockObject'
        }),
        async setCurrentId(id) {
            this.currentId = id;
            await this.loadStructuralElement(this.currentId);
        },
        async editElement() {
            await this.lockObject({ id: this.currentId, type: 'courseware-structural-elements' });
            this.showElementEditDialog(true);
        },
        async deleteElement() {
            await this.lockObject({ id: this.currentId, type: 'courseware-structural-elements' });
            this.showElementDeleteDialog(true);
        },
        addElement() {
            this.showElementAddDialog(true);
        },
        showElementInfo() {
            this.showElementInfoDialog(true);
        },
        createBookmark() {
            this.addBookmark(this.structuralElement);
            this.companionInfo({ info: this.$gettext('Das Lesezeichen wurde gesetzt') });
        }
    },
    watch: {
        $route(to) {
            this.setCurrentId(to.params.id);
        },
    },


}
</script>