<template>
    <div class="cw-block cw-block-before-after">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="true"
            @storeEdit="storeBlock"
            @closeEdit="initCurrentData"
        >
            <template #content>
                <TwentyTwenty :before="currentBeforeUrl" :after="currentAfterUrl" />
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        <translate>Quelle vorher</translate>
                        <select v-model="currentBeforeSource">
                            <option value="studip"><translate>Dateibereich</translate></option>
                            <option value="web"><translate>Web-Adresse</translate></option>
                        </select>
                    </label>
                    <label v-if="currentBeforeSource === 'web'">
                        <translate>URL</translate>:
                        <input type="text" v-model="currentBeforeWebUrl" />
                    </label>
                    <label v-if="currentBeforeSource === 'studip'">
                        <translate>Datei</translate>
                        <courseware-file-chooser
                            v-model="currentBeforeFileId"
                            :isImage="true"
                            @selectFile="updateCurrentBeforeFile"
                        />
                    </label>
                    <label>
                        <translate>Quelle nachher</translate>
                        <select v-model="currentAfterSource">
                            <option value="studip"><translate>Dateibereich</translate></option>
                            <option value="web"><translate>Web-Adresse</translate></option>
                        </select>
                    </label>
                    <label v-if="currentAfterSource === 'web'">
                        <translate>URL</translate>
                        <input type="text" v-model="currentAfterWebUrl" />
                    </label>
                    <label v-if="currentAfterSource === 'studip'">
                        <translate>Datei</translate>
                        <courseware-file-chooser
                            v-model="currentAfterFileId"
                            :isImage="true"
                            @selectFile="updateCurrentAfterFile"
                        />
                    </label>
                </form>
            </template>
            <template #info><translate>Informationen zum Bildvergleich-Block</translate></template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import CoursewareFileChooser from './CoursewareFileChooser.vue';
import TwentyTwenty from 'vue-twentytwenty';
import 'vue-twentytwenty/dist/vue-twentytwenty.css';
import { mapActions } from 'vuex';

export default {
    name: 'courseware-before-after-block',
    components: {
        CoursewareDefaultBlock,
        CoursewareFileChooser,
        TwentyTwenty,
    },
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            currentBeforeSource: '',
            currentBeforeFileId: '',
            currentBeforeFile: {},
            currentBeforeWebUrl: '',
            currentAfterSource: '',
            currentAfterFileId: '',
            currentAfterFile: {},
            currentAfterWebUrl: '',
            afterFile: null,
            beforeFile: null
        };
    },
    computed: {
        beforeSource() {
            return this.block?.attributes?.payload?.before_source;
        },
        beforeFileId() {
            return this.block?.attributes?.payload?.before_file_id;
        },
        beforeWebUrl() {
            return this.block?.attributes?.payload?.before_web_url;
        },
        afterSource() {
            return this.block?.attributes?.payload?.after_source;
        },
        afterFileId() {
            return this.block?.attributes?.payload?.after_file_id;
        },
        afterWebUrl() {
            return this.block?.attributes?.payload?.after_web_url;
        },
        currentBeforeUrl() {
            if (this.currentBeforeSource === 'studip'&& this.currentBeforeFile?.meta) {
                return this.currentBeforeFile.meta['download-url'];
            } else if (this.currentBeforeSource === 'web') {
                return this.currentBeforeWebUrl;
            } else {
                return '';
            }
        },
        currentAfterUrl() {
            if (this.currentAfterSource === 'studip'&& this.currentAfterFile?.meta) {
                return this.currentAfterFile.meta['download-url'];
            } else if (this.currentAfterSource === 'web') {
                return this.currentAfterWebUrl;
            } else {
                return '';
            }
        },
    },
    mounted() {
        this.loadFileRefs(this.block.id).then((response) => {
            for (let i = 0; i < response.length; i++) {
                if (response[i].id === this.beforeFileId) {
                    this.beforeFile = response[i];
                }

                if (response[i].id === this.afterFileId) {
                    this.afterFile = response[i];
                }
            }

            this.currentBeforeFile = this.beforeFile;
            this.currentAfterFile  = this.afterFile;
        });

        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
            loadFileRefs: 'loadFileRefs',
            companionWarning: 'companionWarning',
        }),
        initCurrentData() {
            this.currentBeforeSource = this.beforeSource;
            this.currentBeforeFileId = this.beforeFileId;
            this.currentBeforeWebUrl = this.beforeWebUrl;
            this.currentAfterSource = this.afterSource;
            this.currentAfterFileId = this.afterFileId;
            this.currentAfterWebUrl = this.afterWebUrl;
        },
        updateCurrentBeforeFile(file) {
            this.currentBeforeFile = file;
            this.currentBeforeFileId = file.id;
        },
        updateCurrentAfterFile(file) {
            this.currentAfterFile = file;
            this.currentAfterFileId = file.id;
        },
        storeBlock() {
            let cmpInfo = false;
            let cmpInfoBefore = this.$gettext('Bitte wählen Sie ein Vorherbilder aus.');
            let cmpInfoAfter = this.$gettext('Bitte wählen Sie ein Nachherbilder aus.');
            let attributes = {};
            attributes.payload = {};
            attributes.payload.before_source = this.currentBeforeSource;
            attributes.payload.after_source = this.currentAfterSource;
            if (this.currentAfterSource === 'studip') {
                if (this.currentAfterFile === null) {
                    cmpInfo = cmpInfoAfter;
                } else {
                    attributes.payload.after_file_id = this.currentAfterFile.id;
                    attributes.payload.after_web_url = '';
                }
            } else if (this.currentAfterSource === 'web') {
                if (this.currentAfterWebUrl === '') {
                    cmpInfo = cmpInfoAfter;
                } else {
                    attributes.payload.after_file_id = '';
                    attributes.payload.after_web_url = this.currentAfterWebUrl;
                }

            } else {
                cmpInfo = cmpInfoAfter;
            }
            if (this.currentBeforeSource === 'studip') {
                if (this.currentBeforeFile === null) {
                    cmpInfo = cmpInfoBefore;
                } else {
                    attributes.payload.before_file_id = this.currentBeforeFile.id;
                    attributes.payload.before_web_url = '';
                }
            } else if (this.currentBeforeSource === 'web') {
                if (this.currentBeforeWebUrl === '') {
                    cmpInfo = cmpInfoBefore;
                } else {
                    attributes.payload.before_file_id = '';
                    attributes.payload.before_web_url = this.currentBeforeWebUrl;
                }
            } else {
                cmpInfo = cmpInfoBefore;
            }

            if (cmpInfo) {
                this.companionWarning({
                    info: cmpInfo
                });
                return false;
            } else {
                this.updateBlock({
                    attributes: attributes,
                    blockId: this.block.id,
                    containerId: this.block.relationships.container.data.id,
                });
            }
        },
    },
};
</script>
