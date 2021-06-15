<template>
    <ul class="widget-list widget-links cw-action-widget">
        <li v-show="canEdit" class="cw-action-widget-edit" @click="editElement"><translate>Seite bearbeiten</translate></li>
        <li v-show="canEdit" class="cw-action-widget-add" @click="addElement"><translate>Seite hinzufügen</translate></li>
        <li class="cw-action-widget-info" @click="showElementInfo"><translate>Informationen anzeigen</translate></li>
        <li class="cw-action-widget-star" @click="createBookmark"><translate>Lesezeichen setzen</translate></li>
        <li v-show="canEdit" @click="exportElement" class="cw-action-widget-export"><translate>Seite exportieren</translate></li>
        <li v-show="canEdit" @click="oerElement" class="cw-action-widget-oer"><translate>Seite auf %{oerTitle} veröffentlichen</translate></li>
        <li v-show="!isRoot && canEdit" class="cw-action-widget-trash" @click="deleteElement"><translate>Seite löschen</translate></li>
    </ul>
</template>

<script>
import StudipIcon from './../StudipIcon.vue';
import CoursewareExport from '@/vue/mixins/courseware/export.js';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-action-widget',
    components: {
        StudipIcon
    },
    mixins: [CoursewareExport],
    data() {
        return {
            currentId: null,
            currentElement: {},
        }
    },
    computed: {
         ...mapGetters({
            structuralElementById: 'courseware-structural-elements/byId',
            oerTitle: 'oerTitle',
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
            showElementExportDialog: 'showElementExportDialog',
            showElementOerDialog: 'showElementOerDialog',
            companionInfo: 'companionInfo',
            addBookmark: 'addBookmark',
            lockObject: 'lockObject'
        }),
        async setCurrentId(id) {
            this.currentId = id;
            await this.loadStructuralElement(this.currentId);
            this.initCurrent();
        },
        initCurrent() {
            this.currentElement = JSON.parse(JSON.stringify(this.structuralElement));
            if (!this.currentElement.attributes.payload.meta) {
                this.currentElement.attributes.payload.meta = {};
            }
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
        exportElement() {
            this.showElementExportDialog(true);
        },
        showElementInfo() {
            this.showElementInfoDialog(true);
        },
        createBookmark() {
            this.addBookmark(this.structuralElement);
            this.companionInfo({ info: this.$gettext('Das Lesezeichen wurde gesetzt') });
        },
        oerElement() {
            this.showElementOerDialog(true);
        }
    },
    watch: {
        $route(to) {
            this.setCurrentId(to.params.id);
        },
    },


}
</script>2