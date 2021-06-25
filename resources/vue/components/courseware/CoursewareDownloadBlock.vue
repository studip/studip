<template>
    <div class="cw-block cw-block-download">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="true"
            @storeEdit="storeBlock"
            @closeEdit="initCurrentData"
        >
            <template #content>
                <div v-if="currentTitle !== ''" class="cw-block-title">{{ currentTitle }}</div>
                <div v-if="currentFile !== null" class="cw-block-download-content">
                    <div v-if="currentInfo !== '' && !userHasDownloaded" class="messagebox messagebox_info">
                        {{ currentInfo }}
                    </div>
                    <div v-if="currentSuccess !== '' && userHasDownloaded" class="messagebox messagebox_info">
                        {{ currentSuccess }}
                    </div>
                    <div class="cw-block-download-file-item">
                        <a target="_blank" :download="currentFile.name" :href="currentFile.download_url">
                            <span class="cw-block-file-info" :class="['cw-block-file-icon-' + currentFile.icon]">
                                {{ currentFile.name }}
                            </span>
                            <span class="cw-block-download-download-icon"></span>
                        </a>
                    </div>
                </div>
                <div v-else class="cw-block-download-content">
                    <div class="cw-block-download-file-item-not-available">
                            <span class="cw-block-file-info cw-block-file-icon-none">
                                <translate>Datei ist nicht verfügbar</translate>
                            </span>
                    </div>
                </div>
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        <translate>Überschrift</translate>
                        <input type="text" v-model="currentTitle" />
                    </label>
                    <label>
                        <translate>Datei</translate>
                        <courseware-file-chooser v-model="currentFileId" @selectFile="updateCurrentFile" />
                    </label>
                    <label>
                        <translate>Infobox vor Download</translate>
                        <input type="text" v-model="currentInfo" />
                    </label>
                    <label>
                        <translate>Infobox nach Download</translate>
                        <input type="text" v-model="currentSuccess" />
                    </label>
                    <label>
                        <translate>Fortschritt erst beim Herunterladen</translate>
                        <select v-model="currentGrade">
                            <option value="false"><translate>Nein</translate></option>
                            <option value="true"><translate>Ja</translate></option>
                        </select>
                    </label>
                </form>
            </template>
            <template #info>
                <p><translate>Informationen zum Download-Block</translate></p>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import CoursewareFileChooser from './CoursewareFileChooser.vue';

import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-download-block',
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
            currentTitle: '',
            currentInfo: '',
            currentSuccess: '',
            currentGrade: '',
            currentFileId: '',
            currentFile: null,
            userHasDownloaded: false, // Todo set and get user_data
        };
    },
    computed: {
        ...mapGetters({
            fileRefById: 'file-refs/byId',
            urlHelper: 'urlHelper',
            relatedTermOfUse: 'terms-of-use/related',
        }),
        title() {
            return this.block?.attributes?.payload?.title;
        },
        info() {
            return this.block?.attributes?.payload?.info;
        },
        success() {
            return this.block?.attributes?.payload?.success;
        },
        grade() {
            return this.block?.attributes?.payload?.grade;
        },
        fileId() {
            return this.block?.attributes?.payload?.file_id;
        },
    },
    mounted() {
        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            loadFileRef: 'file-refs/loadById',
            updateBlock: 'updateBlockInContainer',
        }),
        initCurrentData() {
            this.currentTitle = this.title;
            this.currentInfo = this.info;
            this.currentFileId = this.fileId;
            this.currentSuccess = this.success;
            this.currentGrade = this.grade;
            if (this.currentFileId !== '') {
                this.loadFile();
            }
        },
        async loadFile() {
            const id = `${this.currentFileId}`;
            const options = { include: 'terms-of-use' };
            await this.loadFileRef({ id: id, options });
            const fileRef = this.fileRefById({ id: id });
            if (fileRef && this.relatedTermOfUse({parent: fileRef, relationship: 'terms-of-use'}).attributes['download-condition'] === 0) {
                this.updateCurrentFile({
                    id: fileRef.id,
                    name: fileRef.attributes.name,
                    icon: this.getIcon(fileRef.attributes['mime-type']),
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
            if (!this.currentFile.icon) {
                this.currentFile.icon = this.getIcon(file.mime_type);
            }
        },
        getIcon(mimeType) {
            let icon = 'file';
            if (mimeType.includes('audio')) {
                icon = 'audio';
            }
            if (mimeType.includes('image')) {
                icon = 'pic';
            }
            if (mimeType.includes('video')) {
                icon = 'video';
            }
            if (mimeType.includes('text')) {
                icon = 'text';
            }
            if (mimeType.includes('pdf')) {
                icon = 'pdf';
            }
            if (mimeType.includes('msword')) {
                icon = 'word';
            }
            if (mimeType.includes('opendocument.text')) {
                icon = 'word';
            }
            if (mimeType.includes('openxmlformats-officedocument. wordprocessingml.document')) {
                icon = 'word';
            }
            if (mimeType.includes('msexcel')) {
                icon = 'spreadsheet';
            }
            if (mimeType.includes('opendocument.spreadsheet')) {
                icon = 'spreadsheet';
            }
            if (mimeType.includes('openxmlformats-officedocument. spreadsheetml.sheet')) {
                icon = 'spreadsheet';
            }
            if (mimeType.includes('mspowerpoint')) {
                icon = 'ppt';
            }
            if (mimeType.includes('zip')) {
                icon = 'archive';
            }

            return icon;
        },
        storeBlock() {
            let attributes = {};
            attributes.payload = {};
            attributes.payload.title = this.currentTitle;
            attributes.payload.info = this.currentInfo;
            attributes.payload.success = this.currentSuccess;
            attributes.payload.grade = this.currentGrade;
            attributes.payload.file_id = this.currentFileId;

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
    },
};
</script>
