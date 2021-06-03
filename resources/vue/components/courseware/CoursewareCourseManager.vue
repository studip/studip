<template>
    <div class="cw-course-manager">
        <courseware-tabs class="cw-course-manager-tabs">
            <courseware-tab :name="$gettext('Diese Courseware')" :selected="true">
                <courseware-manager-element
                    type="current"
                    :currentElement="currentElement"
                    @selectElement="setCurrentId"
                />
            </courseware-tab>
            <courseware-tab :name="$gettext('Export')">
                <button
                    class="button"
                    @click.prevent="doExportCourseware"
                    :class="{
                        disabled: exportRunning,
                    }"
                >
                    <translate>Alles exportieren</translate>
                </button>
                <br>
                <translate v-if="exportRunning">
                    Export läuft, bitte haben sie einen Moment Geduld...
                </translate>
            </courseware-tab>
        </courseware-tabs>

        <courseware-tabs class="cw-course-manager-tabs">
            <courseware-tab :name="$gettext('FAQ')">
                <courseware-collapsible-box :open="true" :title="$gettext('Wie finde ich die gewünschte Stelle?')">
                    <p><translate>
                        Wählen Sie auf der linken Seite "Diese Courseware" aus.
                        Beim laden der Seite ist dies immer gewählt. Die Überschrift
                        gibt an welche Seite Sie grade ausgewählt haben. Darunter befinden
                        sich die Abschnitte der Seite und innerhalb dieser dessen Blöcke.
                        Möchten Sie eine Seite die unterhalb der gewählten liegt bearbeiten,
                        können Sie diese über die Schaltflächen im Bereich "Seiten" wählen.
                        Über der Überschrift wird eine Navigation eingeblendet, mit dieser können
                        Sie beliebig weit hoch in der Hierarchie springen.
                    </translate></p>
                </courseware-collapsible-box>
                <courseware-collapsible-box :title="$gettext('Wie sortiere ich Objekte?')">
                    <p><translate>
                        Seiten, Abschnitte und Blöcke lassen sich in ihrer Reihenfolge sortieren.
                        Hierzu wählen Sie auf der linken Seite unter "Diese Courseware" die Schaltfläche "Seiten sortieren",
                        "Abschnitte sortieren" oder "Blöcke sortieren".
                        An den Objekten werden Pfeile angezeigt, mit diesen können die Objekte an die gewünschte
                        Position gebracht werden. Um die neue Sortierung zu speichern wählen Sie "Sortieren beenden".
                        Sie können die Änderungen auch rückgängig machen indem Sie "Sortieren abbrechen" wählen.
                    </translate></p>
                </courseware-collapsible-box>
                <courseware-collapsible-box :title="$gettext('Wie verschiebe ich Objekte?')">
                    <p><translate>
                        Seiten, Abschnitte und Blöcke lassen sich verschieben.
                        Hierzu wählen Sie auf der linken Seite unter "Diese Courseware" die Schaltfläche
                        "Seite an diese Stelle einfügen", "Abschnitt an diese Stelle einfügen" oder
                        "Block an diese Stelle einfügen". Wählen Sie dann auf der rechten Seite unter
                        "Verschieben" das Objekt aus das Sie verschieben möchten. Verschiebbare Objekte
                        erkennen Sie an den zwei nach links zeigenden gelben Pfeilen.
                    </translate></p>
                </courseware-collapsible-box>
                <courseware-collapsible-box :title="$gettext('Wie kopiere ich Objekte?')">
                    <p><translate>
                        Seiten, Abschnitte und Blöcke lassen sich aus einer anderen Veranstaltung und Ihren
                        eigenen Inhalten kopieren.
                        Hierzu wählen Sie auf der linken Seite unter "Diese Courseware" die Schaltfläche
                        "Seite an diese Stelle einfügen", "Abschnitt an diese Stelle einfügen" oder
                        "Block an diese Stelle einfügen". Wählen Sie dann auf der rechten Seite unter
                        "Kopieren" erst die Veranstaltung aus der Sie kopieren möchten oder Ihre eigenen
                        Inhalte. Wählen sie dann das Objekt aus das Sie kopieren möchten. Kopierbare Objekte
                        erkennen Sie an den zwei nach links zeigenden gelben Pfeilen.
                    </translate></p>
                </courseware-collapsible-box>
            </courseware-tab>
            <courseware-tab name="Verschieben" :selected="true">
                <courseware-manager-element
                type="self"
                :currentElement="selfElement"
                :moveSelfPossible="moveSelfPossible"
                :moveSelfChildPossible="moveSelfChildPossible"
                @selectElement="setSelfId"
                @reloadElement="reloadElements"
                />
            </courseware-tab>

            <courseware-tab :name="$gettext('Kopieren')">
                <courseware-manager-copy-selector @loadSelf="reloadElements"/>
            </courseware-tab>

            <courseware-tab :name="$gettext('Importieren')">
                <button
                    class="button"
                    @click.prevent="chooseFile"
                    :class="{
                        disabled: importRunning,
                    }"
                >
                    Importdatei auswählen
                </button>

                <div v-if="importZip">
                    <b>{{ importZip.name }}</b
                    ><br />
                    <translate>Größe</translate>: <span>{{ getFileSizeText(importZip.size) }}</span>
                </div>

                <br v-else />

                <div v-if="importState">
                    {{ importState }}
                </div>

                <button
                    class="button"
                    @click.prevent="doImportCourseware"
                    :class="{
                        disabled: importRunning || !importZip,
                    }"
                >
                    <translate>Alles importieren</translate>
                </button>

                <input ref="importFile" type="file" accept=".zip" @change="setImport" style="visibility: hidden" />
            </courseware-tab>
        </courseware-tabs>
    </div>
</template>
<script>
import CoursewareTabs from './CoursewareTabs.vue';
import CoursewareTab from './CoursewareTab.vue';
import CoursewareCollapsibleBox from './CoursewareCollapsibleBox.vue';
import CoursewareManagerElement from './CoursewareManagerElement.vue';
import CoursewareManagerCopySelector from './CoursewareManagerCopySelector.vue';
import CoursewareImport from '@/vue/mixins/courseware/import.js';
import CoursewareExport from '@/vue/mixins/courseware/export.js';
import { mapActions, mapGetters } from 'vuex';

import JSZip from 'jszip';
import FileSaver from 'file-saver';

export default {
    name: 'courseware-course-manager',
    components: {
        CoursewareTabs,
        CoursewareTab,
        CoursewareCollapsibleBox,
        CoursewareManagerElement,
        CoursewareManagerCopySelector,
    },

    mixins: [CoursewareImport, CoursewareExport],

    data() {
        return {
            exportRunning: false,
            importRunning: false,
            importZip: null,
            importState: '',
            importPos: 0,
            currentElement: {},
            currentId: null,
            selfElement: {},
            selfId: null,
            zip: null
        };
    },

    computed: {
        ...mapGetters({
            courseware: 'courseware',
            structuralElementById: 'courseware-structural-elements/byId',
        }),
        moveSelfPossible() {
            if (this.selfElement.relationships === undefined) {
                return false
            } else if (this.selfElement.relationships.parent.data === null) {
                return false;
            } else if (this.currentElement.id === this.selfElement.relationships.parent.data.id) {
                return false;
            } else if (this.currentId === this.selfId) {
                return false;
            } else {
                return true;
            }
        },
        moveSelfChildPossible() {
            return this.currentId !== this.selfId;
        },
    },

    methods: {
        ...mapActions({
            loadCoursewareStructure: 'loadCoursewareStructure',
            createStructuralElement: 'createStructuralElement',
            updateStructuralElement: 'updateStructuralElement',
            deleteStructuralElement: 'deleteStructuralElement',
            loadStructuralElement: 'loadStructuralElement',
            lockObject: 'lockObject',
            unlockObject: 'unlockObject',
            addBookmark: 'addBookmark',
            companionInfo: 'companionInfo',
        }),
        async reloadElements() {
            await this.setCurrentId(this.currentId);
            await this.setSelfId(this.selfId);
        },
        async setCurrentId(target) {
            this.currentId = target;
            await this.loadStructuralElement(this.currentId);
            this.initCurrent();
        },
        async initCurrent() {
            this.currentElement = await this.structuralElementById({ id: this.currentId });
        },
        async setSelfId(target) {
            this.selfId = target;
            await this.loadStructuralElement(this.selfId);
            this.initSelf();
        },
        initSelf() {
            this.selfElement = this.structuralElementById({ id: this.selfId });
        },
        animateImport() {
            // get number of dots
            this.importPos++;

            if (this.importPos > 3) {
                this.importPos = 0;
            }

            this.importState = this.$gettext('Import läuft') + '.'.repeat(this.importPos);
        },

        async doExportCourseware() {
            if (this.exportRunning) {
                return false;
            }

            this.exportRunning = true;

            await this.loadCoursewareStructure();
            await this.sendExportZip();

            this.exportRunning = false;
        },

        setImport() {
            this.importZip = event.target.files[0];
        },

        async doImportCourseware() {
            if (this.importZip === null) {
                return false;
            }

            this.importRunning = true;
            this.animateImport();

            let view = this;

            view.zip = new JSZip();

            await view.zip.loadAsync(this.importZip).then(async function () {
                let data = await view.zip.file('courseware.json').async('string');
                let courseware = JSON.parse(data);

                let data_files = await view.zip.file('files.json').async('string');
                let files = JSON.parse(data_files);

                await view.loadCoursewareStructure();
                let parent_id = view.courseware.relationships.root.data.id;

                await view.importCourseware(courseware, parent_id, files);
            });

            this.importState = this.$gettext('Import erfolgreich!');
            this.importZip = null;
            this.importRunning = false;
        },

        chooseFile() {
            this.$refs.importFile.click();
        },
        getFileSizeText(size) {
            if (size / 1024 < 1000) {
                return (size / 1024).toFixed(2) + ' kB';
            } else {
                return (size / 1048576).toFixed(2) + ' MB';
            }
        }
    },
    watch: {
        courseware(newValue, oldValue) {
            let currentId = newValue.relationships.root.data.id;
            this.setCurrentId(currentId);
            this.setSelfId(currentId);
        },
    },
};
</script>
