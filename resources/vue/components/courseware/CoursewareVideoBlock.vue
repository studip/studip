<template>
    <div class="cw-block cw-block-video">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="true"
            @storeEdit="storeBlock"
            @closeEdit="initCurrentData"
        >
            <template #content>
                <div v-if="currentTitle !== '' && currentURL" class="cw-block-title">{{ currentTitle }}</div>
                <video
                    v-if="currentURL"
                    :src="currentURL"
                    :type="currentFile !== '' ? currentFile.mime_type : ''"
                    controls
                    :autoplay="currentAutoplay === 'enabled'"
                    @contextmenu="contextHandler"
                />
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        <translate>Überschrift</translate>
                        <input type="text" v-model="currentTitle" />
                    </label>
                    <label>
                        <translate>Quelle</translate>
                        <select v-model="currentSource">
                            <option value="studip"><translate>Dateibereich</translate></option>
                            <option value="web"><translate>Web-Adresse</translate></option>
                        </select>
                    </label>
                    <label v-if="currentSource === 'web'">
                        <translate>URL</translate>
                        <input type="text" v-model="currentWebUrl" />
                    </label>
                    <label v-if="currentSource === 'studip'">
                        <translate>Datei</translate>
                        <courseware-file-chooser
                            v-model="currentFileId"
                            :isVideo="true"
                            @selectFile="updateCurrentFile"
                        />
                    </label>
                    <label>
                        <translate>Seitenverhältnis</translate>
                        <select v-model="currentAspect">
                            <option value="169">16:9</option>
                            <option value="43">4:3</option>
                        </select>
                    </label>
                    <label>
                        <translate>Video startet automatisch</translate>
                        <select v-model="currentAutoplay">
                            <option value="disabled"><translate>Nein</translate></option>
                            <option value="enabled"><translate>Ja</translate></option>
                        </select>
                    </label>
                    <label>
                        <translate>Contextmenü</translate>
                        <select v-model="currentContextMenu">
                            <option value="enabled"><translate>Erlauben</translate></option>
                            <option value="disabled"><translate>Verhindern</translate></option>
                        </select>
                    </label>
                </form>
            </template>
            <template #info><translate>Informationen zum Video-Block</translate></template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import CoursewareFileChooser from './CoursewareFileChooser.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-video-block',
    components: {
        CoursewareDefaultBlock,
        CoursewareFileChooser,
    },
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            currentSource: '',
            currentTitle: '',
            currentFile: {},
            currentFileId: '',
            currentAspect: '',
            currentContextMenu: '',
            currentAutoplay: '',
            currentWebUrl: '',
        };
    },
    computed: {
        ...mapGetters({
            fileRefById: 'file-refs/byId',
            urlHelper: 'urlHelper',
        }),
        title() {
            return this.block?.attributes?.payload?.title;
        },
        source() {
            return this.block?.attributes?.payload?.source;
        },
        fileId() {
            return this.block?.attributes?.payload?.file_id;
        },
        webUrl() {
            return this.block?.attributes?.payload?.web_url;
        },
        aspect() {
            return this.block?.attributes?.payload?.aspect;
        },
        contextMenu() {
            return this.block?.attributes?.payload?.context_menu;
        },
        autoplay() {
            return this.block?.attributes?.payload?.autoplay;
        },
        currentURL() {
            if (this.currentSource === 'studip' && this.currentFile) {
                return this.currentFile.download_url;
            }
            if (this.currentSource === 'web') {
                return this.currentWebUrl;
            }
            return false;
        },

    },
    mounted() {
        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
            loadFileRef: 'file-refs/loadById',
            companionWarning: 'companionWarning',
        }),
        storeBlock() {
            let cmpInfo = false;
            let attributes = {};
            attributes.payload = {};
            attributes.payload.title = this.currentTitle;
            attributes.payload.source = this.currentSource;
            if (this.currentSource === 'studip' && this.currentFile !== undefined) {
                attributes.payload.file_id = this.currentFile.id;
                attributes.payload.web_url = '';
            } else if (this.currentSource === 'web' && this.currentWebUrl !== '') {
                attributes.payload.file_id = '';
                attributes.payload.web_url = this.currentWebUrl;
            } else {
                cmpInfo = this.$gettext('Bitte wählen Sie ein Video aus');
            }
            attributes.payload.aspect = this.currentAspect;
            attributes.payload.context_menu = this.currentContextMenu;
            attributes.payload.autoplay = this.currentAutoplay;

            if (cmpInfo) {
                this.companionWarning({ info: cmpInfo });
                return false;
            }

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
        initCurrentData() {
            this.currentSource = this.source;
            this.currentTitle = this.title;
            this.currentWebUrl = this.webUrl;
            this.currentFileId = this.fileId;
            this.currentAspect = this.aspect;
            this.currentContextMenu = this.contextMenu;
            this.currentAutoplay = this.autoplay;
            this.loadFile();

        },
        async loadFile() {
            const id = this.currentFileId;
            await this.loadFileRef({ id });
            const fileRef = this.fileRefById({ id });

            if (fileRef) {
                this.updateCurrentFile({
                    id: fileRef.id,
                    name: fileRef.attributes.name,
                    download_url: this.urlHelper.getURL(
                        'sendfile.php',
                        { type: 0, file_id: fileRef.id, file_name: fileRef.attributes.name },
                        true
                    ),
                });
            }
        },
        updateCurrentFile(file) {
            this.currentFile = file;
            this.currentFileId = file.id;
        },
        contextHandler(e) {
            if (this.currentContextMenu === '0') {
                e.preventDefault();
                console.log('context menu disabled');
            }
        },
    },
};
</script>
