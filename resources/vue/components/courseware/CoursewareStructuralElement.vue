<template>
    <div>
    <div :class="{ 'cw-structural-element-consumemode': consumeMode }" class="cw-structural-element" v-if="validContext">
        <div class="cw-structural-element-content" v-if="structuralElement">
            <courseware-ribbon :canEdit="canEdit">
                <template #buttons>
                    <router-link v-if="prevElement" :to="'/structural_element/' + prevElement.id">
                        <button class="cw-ribbon-button cw-ribbon-button-prev" :title="textRibbon.perv" />
                    </router-link>
                    <button v-else class="cw-ribbon-button cw-ribbon-button-prev-disabled" />
                    <router-link v-if="nextElement" :to="'/structural_element/' + nextElement.id">
                        <button class="cw-ribbon-button cw-ribbon-button-next" :title="textRibbon.next" />
                    </router-link>
                    <button v-else class="cw-ribbon-button cw-ribbon-button-next-disabled" />
                </template>
                <template #breadcrumbList>
                    <li
                        v-for="ancestor in ancestors"
                        :key="ancestor.id"
                        :title="ancestor.attributes.title"
                        class="cw-ribbon-breadcrumb-item"
                    >
                    <span>
                        <router-link :to="'/structural_element/' + ancestor.id">
                            {{ ancestor.attributes.title }}
                        </router-link>
                    </span>
                    </li><li class="cw-ribbon-breadcrumb-item cw-ribbon-breadcrumb-item-current" :title="structuralElement.attributes.title">
                        <span>{{ structuralElement.attributes.title }}</span>
                    </li>
                </template>
                <template #breadcrumbFallback>
                    <li class="cw-ribbon-breadcrumb-item cw-ribbon-breadcrumb-item-current" :title="structuralElement.attributes.title">
                        <span>{{ structuralElement.attributes.title }}</span>
                    </li>
                </template>
                <template #menu>
                    <studip-action-menu 
                        v-if="!consumeMode"
                        :items="menuItems" 
                        class="cw-ribbon-action-menu"
                        @editCurrentElement="menuAction('editCurrentElement')" 
                        @addElement="menuAction('addElement')" 
                        @deleteCurrentElement="menuAction('deleteCurrentElement')" 
                        @showInfo="menuAction('showInfo')" 
                        @showExportOptions="menuAction('showExportOptions')" 
                        @oerCurrentElement="menuAction('oerCurrentElement')" 
                        @setBookmark="menuAction('setBookmark')" 
                    />
                </template>
            </courseware-ribbon>

            <div v-if="canRead" class="cw-container-wrapper" :class="{ 'cw-container-wrapper-consume': consumeMode }">
                <div v-if="structuralElementLoaded" class="cw-companion-box-wrapper">
                    <courseware-empty-element-box
                        v-if="(empty && !isRoot && canEdit) || (empty && !canEdit) || (!noContainers && empty && isRoot && canEdit)"
                        :canEdit="canEdit"
                        :noContainers="noContainers"
                    />
                    <courseware-wellcome-screen v-if="noContainers && isRoot && canEdit"/>
                </div>
                <component
                    v-for="container in containers"
                    :key="container.id"
                    :is="containerComponent(container)"
                    :container="container"
                    :canEdit="canEdit"
                    :isTeacher="isTeacher"
                    class="cw-container-item"
                />
            </div>
            <div v-else class="cw-container-wrapper" :class="{ 'cw-container-wrapper-consume': consumeMode }">
                <div v-if="structuralElementLoaded" class="cw-companion-box-wrapper">
                    <courseware-companion-box mood="sad" :msgCompanion="$gettext('Diese Seite steht Ihnen leider nicht zur Verfügung')" />
                </div>
            </div>
        </div>

        <courseware-companion-overlay />

        <studip-dialog
            v-if="showEditDialog"
            :title="textEdit.title"
            :confirmText="textEdit.confirm"
            :confirmClass="'accept'"
            :closeText="textEdit.close"
            :closeClass="'cancel'"
            height="500"
            width="500"
            class="studip-dialog-with-tab"
            @close="closeEditDialog"
            @confirm="storeCurrentElement"
        >
            <template v-slot:dialogContent>
                <courseware-tabs class="cw-tab-in-dialog">
                    <courseware-tab :name="textEdit.basic" :selected="true">
                        <form class="default" @submit.prevent="">
                            <label>
                                <translate>Titel</translate>
                                <input type="text" v-model="currentElement.attributes.title" />
                            </label>
                            <label>
                                <translate>Beschreibung</translate>
                                <textarea
                                    v-model="currentElement.attributes.payload.description"
                                    class="cw-structural-element-description"
                                />
                            </label>
                        </form>
                    </courseware-tab>
                    <courseware-tab :name="textEdit.meta">
                        <form class="default" @submit.prevent="">
                            <label>
                                <translate>Farbe</translate>
                                <v-select
                                    v-model="currentElement.attributes.payload.color"
                                    :options="colors"
                                    :reduce="color => color.class"
                                    label="class"
                                    class="cw-vs-select"
                                >
                                    <template #open-indicator="selectAttributes">
                                        <span v-bind="selectAttributes"><studip-icon shape="arr_1down" size="10"/></span>
                                    </template>
                                    <template #no-options="{ search, searching, loading }">
                                        <translate>Es steht keine Auswahl zur Verfügung</translate>.
                                    </template>
                                    <template #selected-option="{name, hex}">
                                        <span class="vs__option-color" :style="{'background-color': hex}"></span><span>{{name}}</span>
                                    </template>
                                    <template #option="{name, hex}">
                                        <span class="vs__option-color" :style="{'background-color': hex}"></span><span>{{name}}</span>
                                    </template>
                                </v-select>
                            </label>
                            <label>
                                <translate>Zweck</translate>
                                <select v-model="currentElement.attributes.purpose">
                                    <option value="content"><translate>Inhalt</translate></option>
                                    <option value="template"><translate>Vorlage</translate></option>
                                    <option value="oer"><translate>OER-Material</translate></option>
                                    <option value="portfolio"><translate>ePortfolio</translate></option>
                                    <option value="draft"><translate>Entwurf</translate></option>
                                    <option value="other"><translate>Sonstiges</translate></option>
                                </select>
                            </label>
                            <label>
                                <translate>Lizenztyp</translate>
                                <select v-model="currentElement.attributes.payload.license_type">
                                    <option v-for="license in licenses" :key="license.id" :value="license.id">{{license.name}}</option>
                                </select>
                            </label>
                            <label>
                                <translate>Geschätzter zeitlicher Aufwand</translate>
                                <input type="text" v-model="currentElement.attributes.payload.required_time" />
                            </label>
                            <label>
                                <translate>Niveau</translate><br>
                                <translate>von</translate>
                                <select v-model="currentElement.attributes.payload.difficulty_start">
                                    <option v-for="difficulty_start in 12" :key="difficulty_start" :value="difficulty_start">{{difficulty_start}}</option>
                                </select>
                                <translate>bis</translate>
                                <select v-model="currentElement.attributes.payload.difficulty_end">
                                    <option v-for="difficulty_end in 12" :key="difficulty_end" :value="difficulty_end">{{difficulty_end}}</option>
                                </select>
                            </label>
                        </form>
                    </courseware-tab>
                    <courseware-tab :name="textEdit.image">
                        <form class="default" @submit.prevent="">
                            <img
                                v-if="image"
                                :src="image"
                                class="cw-structural-element-image-preview"
                                :alt="$gettext('Vorschaubild')"
                            />
                            <label v-if="image">
                                <button class="button" @click="deleteImage" v-translate>Bild löschen</button>
                            </label>
                            <div v-if="uploadFileError" class="messagebox messagebox_error">
                                {{ uploadFileError }}
                            </div>
                            <label v-if="!image">
                                <translate>Bild hochladen</translate>
                                <input ref="upload_image" type="file" accept="image/*" @change="checkUploadFile" />
                            </label>
                        </form>
                    </courseware-tab>
                    <courseware-tab :name="textEdit.approval">
                        <courseware-structural-element-permissions
                            v-if="inCourse"
                            :element="currentElement"
                            @updateReadApproval="updateReadApproval"
                            @updateWriteApproval="updateWriteApproval"
                        />
                        <!-- <h1>
                            <translate>Lehrende in Stud.IP</translate>
                        </h1>
                        <label>
                            <input
                                type="checkbox"
                                class="default"
                                value="copy_approval"
                                v-model="currentElement.attributes['copy-approval']"
                            />
                            <translate>Seite zum kopieren für Lehrende freigeben</translate>
                        </label> -->
                    </courseware-tab>
                    <courseware-tab v-if="inCourse" :name="textEdit.visible">
                        <form class="default" @submit.prevent="">
                            <label>
                                <translate>Sichtbar ab</translate>
                                <input type="date" v-model="currentElement.attributes['release-date']" />
                            </label>
                            <label>
                                <translate>Unsichtbar ab</translate>
                                <input type="date" v-model="currentElement.attributes['withdraw-date']" />
                            </label>
                        </form>
                    </courseware-tab>
                </courseware-tabs>
            </template>
        </studip-dialog>

        <studip-dialog
            v-if="showAddDialog"
            :title="$gettext('Seite hinzufügen')"
            :confirmText="'Erstellen'"
            :confirmClass="'accept'"
            :closeText="$gettext('Schließen')"
            :closeClass="'cancel'"
            class="cw-structural-element-dialog"
            @close="closeAddDialog"
            @confirm="createElement"
        >
            <template v-slot:dialogContent>
                <form class="default" @submit.prevent="">
                    <label>
                        <translate>Position der neuen Seite</translate>
                        <select v-model="newChapterParent">
                            <option v-if="!isRoot" value="sibling">
                                <translate>Neben der aktuellen Seite</translate>
                            </option>
                            <option value="descendant"><translate>Unterhalb der aktuellen Seite</translate></option>
                        </select>
                    </label>
                    <label>
                        <translate>Name der neuen Seite</translate><br />
                        <input v-model="newChapterName" type="text" />
                    </label>
                </form>
            </template>
        </studip-dialog>

        <studip-dialog
            v-if="showInfoDialog"
            :title="textInfo.title"
            :closeText="textInfo.close"
            :closeClass="'cancel'"
            @close="showElementInfoDialog(false)"
        >
            <template v-slot:dialogContent>
                <table class="cw-structural-element-info">
                    <tr>
                        <td><translate>Titel</translate>:</td>
                        <td>{{ structuralElement.attributes.title }}</td>
                    </tr>
                    <tr>
                        <td><translate>Beschreibung</translate>:</td>
                        <td>{{ structuralElement.attributes.payload.description }}</td>
                    </tr>
                    <tr>
                        <td><translate>Seite wurde erstellt von</translate>:</td>
                        <td>{{ owner }}</td>
                    </tr>
                    <tr>
                        <td><translate>Seite wurde erstellt am</translate>:</td>
                        <td><iso-date :date="structuralElement.attributes.mkdate" /></td>
                    </tr>
                    <tr>
                        <td><translate>Zuletzt bearbeitet von</translate>:</td>
                        <td>{{ editor }}</td>
                    </tr>
                    <tr>
                        <td><translate>Zuletzt bearbeitet am</translate>:</td>
                        <td><iso-date :date="structuralElement.attributes.chdate" /></td>
                    </tr>
                </table>
            </template>
        </studip-dialog>

        <studip-dialog
            v-if="showExportDialog"
            :title="textExport.title"
            :confirmText="textExport.confirm"
            :confirmClass="'accept'"
            :closeText="textExport.close"
            :closeClass="'cancel'"
            @close="showElementExportDialog(false)"
            @confirm="exportCurrentElement"
        >
            <template v-slot:dialogContent>
                <translate>Hiermit exportieren Sie die Seite "{{ currentElement.attributes.title }}" als ZIP-Datei.</translate>

                <div class="cw-element-export">
                    <label>
                        <input type="checkbox" v-model="exportChildren">
                        <translate>Unterseiten exportieren</translate>
                    </label>
                </div>

                <translate v-if="exportRunning">
                    Export läuft...
                </translate>
            </template>

        </studip-dialog>

        <studip-dialog
            v-if="showOerDialog"
            height="600"
            width="600"
            :title="textOer.title"
            :confirmText="textOer.confirm"
            :confirmClass="'accept'"
            :closeText="textOer.close"
            :closeClass="'cancel'"
            @close="showElementOerDialog(false)"
            @confirm="publishCurrentElement"
        >
        
            <template v-slot:dialogContent>
                <form class="default" @submit.prevent="">
                    <fieldset>
                        <legend><translate>Grunddaten</translate></legend>
                        <label>
                            <p><translate>Vorschaubild</translate>:</p>
                            <img
                                v-if="currentElement.relationships.image.data"
                                :src="currentElement.relationships.image.meta['download-url']"
                                width="400"
                            />
                        </label>
                        <label>
                            <p><translate>Beschreibung</translate>:</p>
                            <p> {{ currentElement.attributes.payload.description }}</p>
                        </label>
                        <label>
                            <translate>Niveau</translate>:
                            <p> {{ currentElement.attributes.payload.difficulty_start }} - {{ currentElement.attributes.payload.difficulty_end }}</p>
                        </label>
                        <label>
                            <translate>Lizenztyp</translate>:
                            <p>{{currentLicenseName}}</p>
                        </label>
                        <label>
                            <translate>Sie können diese Daten unter "Seite bearbeiten" verändern</translate>.
                        </label>

                    </fieldset>
                    <fieldset>
                        <legend><translate>Einstellungen</translate></legend>
                        <label>
                            <translate>Unterseiten veröffentlichen</translate>
                            <input type="checkbox" v-model="oerChildren">
                        </label>
                    </fieldset>
                </form>
            </template>
            
        </studip-dialog>

        <studip-dialog
            v-if="showDeleteDialog"
            :title="textDelete.title"
            :question="textDelete.alert"
            height="180"
            @confirm="deleteCurrentElement"
            @close="closeDeleteDialog"
        ></studip-dialog>
    </div>
    <div v-else>
        <courseware-companion-box v-if="currentElement !== ''" :msgCompanion="textCompanionWrongContext" mood="sad"/>
    </div>

    </div>
</template>

<script>
import ContainerComponents from './container-components.js';
import CoursewareStructuralElementPermissions from './CoursewareStructuralElementPermissions.vue';
import CoursewareAccordionContainer from './CoursewareAccordionContainer.vue';
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';
import CoursewareWellcomeScreen from './CoursewareWellcomeScreen.vue';
import CoursewareEmptyElementBox from './CoursewareEmptyElementBox.vue';
import CoursewareCompanionOverlay from './CoursewareCompanionOverlay.vue';
import CoursewareListContainer from './CoursewareListContainer.vue';
import CoursewareTabsContainer from './CoursewareTabsContainer.vue';
import CoursewareRibbon from './CoursewareRibbon.vue';
import CoursewareTabs from './CoursewareTabs.vue';
import CoursewareTab from './CoursewareTab.vue';
import CoursewareExport from '@/vue/mixins/courseware/export.js';
import IsoDate from './IsoDate.vue';
import StudipDialog from '../StudipDialog.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-structural-element',
    components: {
        CoursewareStructuralElementPermissions,
        CoursewareRibbon,
        CoursewareListContainer,
        CoursewareAccordionContainer,
        CoursewareTabsContainer,
        CoursewareCompanionBox,
        CoursewareCompanionOverlay,
        CoursewareWellcomeScreen,
        CoursewareEmptyElementBox,
        CoursewareTabs,
        CoursewareTab,
        IsoDate,
        StudipDialog,
    },
    props: {},

    mixins: [CoursewareExport],

    data() {
        return {
            currentId: null,
            newChapterName: '',
            newChapterParent: 'descendant',
            currentElement: '',
            uploadFileError: '',
            textCompanionWrongContext: this.$gettext('Die angeforderte Seite ist nicht Teil dieser Courseware.'),
            textEdit: {
                title: this.$gettext('Seite bearbeiten'),
                confirm: this.$gettext('Speichern'),
                close: this.$gettext('Schließen'),
                basic: this.$gettext('Grunddaten'),
                image: this.$gettext('Bild'),
                meta: this.$gettext('Metadaten'),
                approval: this.$gettext('Rechte'),
                visible: this.$gettext('Sichtbarkeit'),
            },
            textInfo: {
                title: this.$gettext('Informationen zur Seite'),
                close: this.$gettext('Schließen'),
            },
            textExport: {
                title: this.$gettext('Seite exportieren'),
                confirm: this.$gettext('Exportieren'),
                close: this.$gettext('Schließen'),
            },
            textAdd: {
                title: this.$gettext('Seite hinzufügen'),
                confirm: this.$gettext('Erstellen'),
                close: this.$gettext('Schließen'),
            },
            textRibbon: {
                perv: this.$gettext('zurück'),
                next: this.$gettext('weiter'),
            },
            exportRunning: false,
            exportChildren: false,
            oerChildren: true,
        };
    },

    computed: {
        ...mapGetters({
            courseware: 'courseware',
            consumeMode: 'consumeMode',
            containerById: 'courseware-containers/byId',
            structuralElementById: 'courseware-structural-elements/byId',
            userIsTeacher: 'userIsTeacher',
            pluginManager: 'pluginManager',
            showEditDialog: 'showStructuralElementEditDialog',
            showAddDialog: 'showStructuralElementAddDialog',
            showExportDialog: 'showStructuralElementExportDialog',
            showInfoDialog: 'showStructuralElementInfoDialog',
            showDeleteDialog : 'showStructuralElementDeleteDialog',
            showOerDialog : 'showStructuralElementOerDialog',
            oerTitle: 'oerTitle',
            licenses: 'licenses'
        }),

        textOer() {
            return {
                title: this.$gettext('Seite auf') + ' ' + this.oerTitle + ' ' + this.$gettext('veröffentlichen'),
                confirm: this.$gettext('Veröffentlichen'),
                close: this.$gettext('Schließen'),
            }
        },

        inCourse() {
            return this.$store.getters.context.type === 'courses';
        },

        textDelete() {
            let textDelete = {};
            textDelete.title = this.$gettext('Seite unwiderruflich löschen');
            textDelete.alert = this.$gettext('Möchten Sie die Seite wirklich löschen?');
            if (this.structuralElementLoaded) {
                textDelete.alert = this.$gettext('Möchten Sie die Seite') +' "'+ this.structuralElement.attributes.title + '" '+ this.$gettext('wirklich löschen?');
            }

            return textDelete;
        },

        validContext() {
            let valid = false;
            let context = this.$store.getters.context;
            if (context.type === 'courses' && this.currentElement.relationships) {
                if (this.currentElement.relationships.course && context.id === this.currentElement.relationships.course.data.id) {
                    valid = true;
                }
            }

            if (context.type === 'users' && this.currentElement.relationships) {
                if (this.currentElement.relationships.user && context.id === this.currentElement.relationships.user.data.id) {
                    valid = true;
                }
            }

            return valid;
        },

        image() {
            return this.structuralElement.relationships?.image?.meta?.['download-url'] ?? null;
        },

        structuralElement() {
            if (!this.currentId) {
                return null;
            }

            return this.structuralElementById({ id: this.currentId });
        },

        structuralElementLoaded() {
            return this.structuralElement !== null && this.structuralElement !== {};
        },

        ancestors() {
            if (!this.currentElement) {
                return [];
            }
            if (this.currentElement.relationships.ancestors.data) {
                return this.currentElement.relationships.ancestors.data.map(({ id }) =>
                    this.structuralElementById({ id })
                );
            }
            return [];
        },
        parent() {
            if (!this.structuralElement) {
                return [];
            }
            if (this.structuralElement.relationships.parent.data) {
                let id = this.structuralElement.relationships.parent.data.id;
                return this.structuralElementById({ id });
            }
            return [];
        },
        hasSiblings() {
            if (this.parent.length !== 0) {
                return this.parent.relationships.children.data.length > 1;
            } else {
                return false;
            }
        },
        prevElement() {
            if (this.hasSiblings) {
                let view = this;
                let siblings = this.parent.relationships.children.data;
                let id = '';
                siblings.forEach((el, index) => {
                    if (el.id === view.currentId && index !== 0) {
                        id = siblings[index - 1].id;
                    }
                });
                if (id === '') {
                    return this.parent;
                } else {
                    return this.structuralElementById({ id });
                }
            } else if (this.parent.length !== 0) {
                return this.parent;
            } else {
                return null;
            }
        },
        nextElement() {
            let view = this;
            if (this.structuralElement.relationships.children.data.length > 0) {
                let id = this.structuralElement.relationships.children.data[0].id;
                return this.structuralElementById({ id });
            } else if (this.hasSiblings) {
                let siblings = this.parent.relationships.children.data;
                let id = '';
                siblings.forEach((el, index) => {
                    if (el.id === view.currentId && siblings.length > index + 1) {
                        id = siblings[index + 1].id;
                    }
                });
                if (id === '') {
                    return this.getNextParentSibling(this.currentId);
                } else {
                    return this.structuralElementById({ id });
                }
            } else {
                return this.getNextParentSibling(this.currentId);
            }
        },
        empty() {
            if (this.containers === null) {
                return true;
            } else {
                let noBlockFound = true;
                this.containers.forEach((container) => {
                    if (container.relationships.blocks.data.length > 0) {
                        noBlockFound = false;
                    }
                });
                return noBlockFound;
            }
        },
        containers() {
            if (!this.structuralElement) {
                return [];
            }

            const containers = this.$store.getters['courseware-containers/related']({
                parent: this.structuralElement,
                relationship: 'containers',
            });

            return containers;
        },
        noContainers() {
            if (this.containers === null) {
                return true;
            } else {
                return this.containers.length === 0;
            }
        },

        canEdit() {
            if (!this.structuralElement) {
                return false;
            }
            return this.structuralElement.attributes['can-edit'];
        },
        canRead() {
            if (!this.structuralElement) {
                return false;
            }
            return this.structuralElement.attributes['can-read'];
        },
        isTeacher() {
            return this.userIsTeacher;
        },

        isRoot() {
            return this.structuralElement.relationships.parent.data === null;
        },

        owner() {
            const owner = this.$store.getters['users/related']({
                parent: this.structuralElement,
                relationship: 'owner',
            });

            return owner?.attributes['formatted-name'] ?? '';
        },

        editor() {
            const editor = this.$store.getters['users/related']({
                parent: this.structuralElement,
                relationship: 'editor',
            });

            return editor?.attributes['formatted-name'] ?? '';
        },
        menuItems() {
            let menu = [
                { id: 3, label: this.$gettext('Informationen anzeigen'), icon: 'info', emit: 'showInfo' },
                { id: 4, label: this.$gettext('Lesezeichen setzen'), icon: 'star', emit: 'setBookmark' },
            ];
            if (this.canEdit) {
                menu.push({ id: 1, label: this.$gettext('Seite bearbeiten'), icon: 'edit', emit: 'editCurrentElement' });
                menu.push({ id: 2, label: this.$gettext('Seite hinzufügen'), icon: 'add', emit: 'addElement' });
                menu.push({ id: 5, label: this.$gettext('Seite exportieren'), icon: 'export', emit: 'showExportOptions' });
                menu.push({ id: 6, label: this.textOer.title, icon: 'service', emit: 'oerCurrentElement' });
            }
            if(!this.isRoot && this.canEdit) {
                menu.push({ id: 7, label: this.$gettext('Seite löschen'), icon: 'trash', emit: 'deleteCurrentElement' });
            }
            menu.sort((a, b) => a.id - b.id);

            return menu;
        },
        colors() {
            const colors = [
                {name: this.$gettext('Schwarz'), class: 'black', hex: '#000000', level: 100, icon: 'black', darkmode: true},
                {name: this.$gettext('Weiß'), class: 'white', hex: '#ffffff', level: 100, icon: 'white', darkmode: false},

                {name: this.$gettext('Blau'), class: 'studip-blue', hex: '#28497c', level: 100, icon: 'blue', darkmode: true},
                {name: this.$gettext('Hellblau'), class: 'studip-lightblue', hex: '#e7ebf1', level: 40, icon: 'lightblue', darkmode: false},
                {name: this.$gettext('Rot'), class: 'studip-red', hex: '#d60000', level: 100, icon: 'red', darkmode: false},
                {name: this.$gettext('Grün'), class: 'studip-green', hex: '#008512', level: 100, icon: 'green', darkmode: true},
                {name: this.$gettext('Gelb'), class: 'studip-yellow', hex: '#ffbd33', level: 100, icon: 'yellow', darkmode: false},
                {name: this.$gettext('Grau'), class: 'studip-gray', hex: '#636a71', level: 100, icon: 'grey', darkmode: true},

                {name: this.$gettext('Holzkohle'), class: 'charcoal', hex: '#3c454e', level: 100, icon: false, darkmode: true},
                {name: this.$gettext('Königliches Purpur'), class: 'royal-purple', hex: '#8656a2', level: 80, icon: false, darkmode: true},
                {name: this.$gettext('Leguangrün'), class: 'iguana-green', hex: '#66b570', level: 60, icon: false, darkmode: true},
                {name: this.$gettext('Königin blau'), class: 'queen-blue', hex: '#536d96', level: 80, icon: false, darkmode: true},
                {name: this.$gettext('Helles Seegrün'), class: 'verdigris', hex: '#41afaa', level: 80, icon: false, darkmode: true},
                {name: this.$gettext('Maulbeere'), class: 'mulberry', hex: '#bf5796', level: 80, icon: false, darkmode: true},
                {name: this.$gettext('Kürbis'), class: 'pumpkin', hex: '#f26e00', level: 100, icon: false, darkmode: true},
                {name: this.$gettext('Sonnenschein'), class: 'sunglow', hex: '#ffca5c', level: 80, icon: false, darkmode: false},
                {name: this.$gettext('Apfelgrün'), class: 'apple-green', hex: '#8bbd40', level: 80, icon: false, darkmode: true},
            ];
            let elementColors = [];
            colors.forEach( color => {
                if(color.darkmode) {
                    elementColors.push(color);
                }
            });

            return elementColors;
        },
        currentLicenseName() {
            for(let i = 0; i < this.licenses.length; i++) {
                if (this.licenses[i]['id'] == this.currentElement.attributes.payload.license_type) {
                    return this.licenses[i]['name'];
                }
            }

            return '';
        }
    },

    watch: {
        $route(to) {
            this.setCurrentId(to.params.id);
        },
    },

    async mounted() {
        if (!this.currentId) {
            this.setCurrentId(this.$route.params.id);
        }
    },

    methods: {
        ...mapActions({
            createStructuralElement: 'createStructuralElement',
            updateStructuralElement: 'updateStructuralElement',
            deleteStructuralElement: 'deleteStructuralElement',
            loadStructuralElement: 'loadStructuralElement',
            lockObject: 'lockObject',
            unlockObject: 'unlockObject',
            addBookmark: 'addBookmark',
            companionInfo: 'companionInfo',
            uploadImageForStructuralElement: 'uploadImageForStructuralElement',
            deleteImageForStructuralElement: 'deleteImageForStructuralElement',
            companionSuccess: 'companionSuccess',
            showElementEditDialog: 'showElementEditDialog',
            showElementAddDialog: 'showElementAddDialog',
            showElementExportDialog: 'showElementExportDialog',
            showElementInfoDialog: 'showElementInfoDialog',
            showElementDeleteDialog: 'showElementDeleteDialog',
            showElementOerDialog: 'showElementOerDialog',
        }),

        async setCurrentId(id) {
            this.currentId = id;
            await this.loadStructuralElement(this.currentId);
            this.initCurrent();
        },
        initCurrent() {
            this.currentElement = JSON.parse(JSON.stringify(this.structuralElement));
            this.uploadFileError = '';
        },
        async menuAction(action) {
            switch (action) {
                case 'editCurrentElement':
                    await this.lockObject({ id: this.currentId, type: 'courseware-structural-elements' });
                    this.showElementEditDialog(true);
                    break;
                case 'addElement':
                    this.newChapterName = '';
                    this.newChapterParent = 'descendant';
                    this.showElementAddDialog(true);
                    break;
                case 'deleteCurrentElement':
                    await this.lockObject({ id: this.currentId, type: 'courseware-structural-elements' });
                    this.showElementDeleteDialog(true);
                    break;
                case 'showInfo':
                    this.showElementInfoDialog(true);
                    break;
                case 'showExportOptions':
                    this.showElementExportDialog(true);
                    break;
                case 'oerCurrentElement':
                    this.showElementOerDialog(true);
                    break;
                case 'setBookmark':
                    this.setBookmark();
                    break;
            }
        },
        async closeEditDialog() {
            await this.unlockObject({ id: this.currentId, type: 'courseware-structural-elements' });
            this.showElementEditDialog(false)
            this.initCurrent();
        },
        closeAddDialog() {
            this.showElementAddDialog(false);
        },
        checkUploadFile() {
            const file = this.$refs?.upload_image?.files[0];
            if (file.size > 2097152) {
                this.uploadFileError = this.$gettext('Diese Datei ist zu groß. Bitte wählen Sie eine kleinere Datei.');
            } else if (!file.type.includes('image')) {
                this.uploadFileError = this.$gettext('Diese Datei ist kein Bild. Bitte wählen Sie ein Bild aus.');
            } else {
                this.uploadFileError = '';
            }
        },
        deleteImage() {
            this.deleteImageForStructuralElement(this.currentElement);
            this.initCurrent();
        },
        async storeCurrentElement() {
            const file = this.$refs?.upload_image?.files[0];
            if (file) {
                if (file.size > 2097152) {
                    return false;
                }

                this.uploadFileError = '';
                this.uploadImageForStructuralElement({
                    structuralElement: this.currentElement,
                    file,
                }).catch((error) => {
                    console.error(error);
                    this.uploadFileError = this.$gettext('Fehler beim Hochladen der Datei.');
                });
            }

            if (this.currentElement.attributes['release-date'] !== '') {
                this.currentElement.attributes['release-date'] =
                    new Date(this.currentElement.attributes['release-date']).getTime() / 1000;
            }

            if (this.currentElement.attributes['withdraw-date'] !== '') {
                this.currentElement.attributes['withdraw-date'] =
                    new Date(this.currentElement.attributes['withdraw-date']).getTime() / 1000;
            }

            await this.updateStructuralElement({
                element: this.currentElement,
                id: this.currentId,
            });
            await this.unlockObject({ id: this.currentId, type: 'courseware-structural-elements' });
            this.setCurrentId(this.$route.params.id);
            this.showElementEditDialog(false);
        },

        async exportCurrentElement(data) {
            if (this.exportRunning) {
                return;
            }

            this.exportRunning = true;

            await this.sendExportZip(this.currentElement.id, {
                withChildren: this.exportChildren
            });

            this.exportRunning = false;
            this.showElementExportDialog(false);
        },

        async publishCurrentElement() {
            this.exportToOER(this.currentElement, {withChildren: this.oerChildren});
        },

        async closeDeleteDialog() {
            await this.unlockObject({ id: this.currentId, type: 'courseware-structural-elements' });
            this.showElementDeleteDialog(false);
        },
        async deleteCurrentElement() {
            let parent_id = this.structuralElement.relationships.parent.data.id;
            await this.deleteStructuralElement({
                id: this.currentId,
                parentId: this.structuralElement.relationships.parent.data.id,
            });
            this.showElementDeleteDialog(false);
            this.$router.push(parent_id);
        },
        async createElement() {
            let title = this.newChapterName; // this is the title of the new element
            let parent_id = this.currentId; // new page is descandant as default
            if (this.newChapterParent === 'sibling') {
                parent_id = this.structuralElement.relationships.parent.data.id;
            }
            this.showElementAddDialog(false);
            await this.createStructuralElement({
                attributes: {
                    title,
                },
                parentId: parent_id,
                currentId: this.currentId,
            });
            let newElement = this.$store.getters['courseware-structural-elements/lastCreated'];
            this.companionSuccess({
                info: this.$gettext('Seite') +' "' + newElement.attributes.title  + '" ' + this.$gettext('wurde erfolgreich angelegt.'),
            });
        },
        containerComponent(container) {
            return 'courseware-' + container.attributes['container-type'] + '-container';
        },
        getNextParentSibling(element_id) {
            let current = this.structuralElementById({ id: element_id });
            if (current.relationships.parent.data === null) {
                return null;
            }
            let parent = this.structuralElementById({ id: current.relationships.parent.data.id });
            if (parent.relationships.parent.data === null) {
                return null;
            }
            let grandParent = this.structuralElementById({ id: parent.relationships.parent.data.id });
            let parentSiblings = grandParent.relationships.children.data;
            let id = '';
            parentSiblings.forEach((el, index) => {
                if (parseInt(el.id, 10) === parseInt(parent.id, 10) && parentSiblings.length > index + 1) {
                    id = parentSiblings[index + 1].id;
                }
            });
            if (id === '') {
                this.getNextParentSibling(parent.id);
            } else {
                return this.structuralElementById({ id });
            }
        },
        setBookmark() {
            this.addBookmark(this.structuralElement);
            this.companionInfo({ info: this.$gettext('Das Lesezeichen wurde gesetzt') });
        },
        updateReadApproval(approval) {
            this.currentElement.attributes['read-approval'] = approval;
        },
        updateWriteApproval(approval) {
            this.currentElement.attributes['write-approval'] = approval;
        },
    },
    created() {
        this.pluginManager.registerComponentsLocally(this);
    },
    // this line provides all the components to courseware plugins
    provide: () => ({ containerComponents: ContainerComponents }),
};
</script>
