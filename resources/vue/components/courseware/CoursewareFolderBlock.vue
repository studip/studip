<template>
    <div class="cw-block cw-block-folder">
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
                <ul class="cw-block-folder-list">
                    <li v-for="file in files" :key="file.id" class="cw-block-folder-file-item">
                        <a target="_blank" :download="file.name" :href="file.download_url">
                            <span class="cw-block-file-info" :class="['cw-block-file-icon-' + file.icon]">
                                {{ file.name }}
                            </span>
                            <span class="cw-block-folder-download-icon"></span>
                        </a>
                    </li>
                    <li v-if="files.length === 0">
                        <span class="cw-block-file-info cw-block-file-icon-empty">
                            <translate>Dieser Ordner ist leer</translate>
                        </span>
                    </li>
                </ul>
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        <translate>Überschrift</translate>
                        <input type="text" v-model="currentTitle" />
                    </label>
                    <label>
                        <translate>Ordner</translate>
                        <courseware-folder-chooser v-model="currentFolderId" allowUserFolders />
                    </label>
                </form>
            </template>
            <template #info>
                <p><translate>Informationen zum Dateiordner-Block</translate></p>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import CoursewareFolderChooser from './CoursewareFolderChooser.vue';

import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-folder-block',
    components: {
        CoursewareDefaultBlock,
        CoursewareFolderChooser,
    },
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            currentTitle: '',
            currentFolderId: '',
            currentFileType: '',
            files: [],
        };
    },
    computed: {
        ...mapGetters({
            relatedFileRefs: 'file-refs/related',
            urlHelper: 'urlHelper',
            relatedTermOfUse: 'terms-of-use/related',
        }),
        folderType() {
            return this.block?.attributes?.payload?.type;
        },
        folderId() {
            return this.block?.attributes?.payload?.folder_id;
        },
        title() {
            return this.block?.attributes?.payload?.title;
        },
    },
    mounted() {
        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            loadRelatedFileRefs: 'file-refs/loadRelated',
            updateBlock: 'updateBlockInContainer',
        }),
        initCurrentData() {
            this.currentTitle = this.title;
            this.currentFolderId = this.folderId;
            this.currentFolderType = this.folderType;
        },
        async getFolderFiles() {
            const parent = { type: 'folders', id: `${this.currentFolderId}` };
            const relationship = 'file-refs';
            const options = { include: 'terms-of-use' };
            await this.loadRelatedFileRefs({ parent, relationship, options });
            const fileRefs = this.relatedFileRefs({ parent, relationship }) ?? [];
            this.processFiles(fileRefs);
        },
        processFiles(files) {
            this.files = files
            .filter((file) => {
                if (this.relatedTermOfUse({parent: file, relationship: 'terms-of-use'}).attributes['download-condition'] !== 0) {
                    return false;
                } else {
                    return true;
                }
            })
            .map(({ id, attributes }) => ({
                id,
                name: attributes.name,
                icon: this.getIcon(attributes['mime-type']),
                download_url: this.urlHelper.getURL(
                    `sendfile.php/`,
                    { type: 0, file_id: id, file_name: attributes.name },
                    true
                ),
            }));
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
            attributes.payload.folder_id = this.currentFolderId;
            attributes.payload.type = this.currentFileType;

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
    },
    watch: {
        currentFolderId() {
            this.getFolderFiles();
        },
    },
};
</script>
