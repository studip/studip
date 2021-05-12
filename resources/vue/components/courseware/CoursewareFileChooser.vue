<template>
    <div class="cw-file-chooser">
        <span v-translate>Ordner-Filter</span>
        <courseware-folder-chooser allowUserFolders v-model="selectedFolderId" />
        <span v-translate>Datei</span>
        <select v-model="currentValue" @change="selectFile">
            <optgroup v-if="this.context.type === 'courses'" :label="textOptGroupCourse">
                <option v-for="(file, index) in courseFiles" :key="index" :value="file.id">
                    {{ file.name }}
                </option>
            </optgroup>
            <optgroup :label="textOptGroupUser">
                <option v-for="(file, index) in userFiles" :key="index" :value="file.id">
                    {{ file.name }}
                </option>
            </optgroup>
        </select>
    </div>
</template>

<script>
import CoursewareFolderChooser from './CoursewareFolderChooser.vue';

import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-file-chooser',
    components: { CoursewareFolderChooser },
    props: {
        value: String,
        mimeType: { type: String, default: '' },
        isImage: { type: Boolean, default: false },
        isVideo: { type: Boolean, default: false },
        isAudio: { type: Boolean, default: false },
        isDocument: { type: Boolean, default: false },
    },
    data() {
        return {
            currentValue: '',
            selectedFolderId: '',
            loadedCourseFiles: [],
            courseFiles: [],
            loadedUserFiles: [],
            userFiles: [],
            textOptGroupCourse: this.$gettext('Dateibereich der Veranstaltung'),
            textOptGroupUser: this.$gettext('eigener Dateibereich'),
        };
    },
    computed: {
        ...mapGetters({
            context: 'context',
            relatedFileRefs: 'file-refs/related',
            urlHelper: 'urlHelper',
            userId: 'userId',
        }),
    },
    methods: {
        ...mapActions({
            loadRelatedFileRefs: 'file-refs/loadRelated',
        }),
        selectFile() {
            this.$emit(
                'selectFile',
                this.userFiles.concat(this.courseFiles).find((file) => file.id === this.currentValue)
            );
        },
        filterFiles(loadArray) {
            const filterFile = (file) => {
                if (this.selectedFolderId !== '' && this.selectedFolderId !== file.relationships.parent.data.id) {
                    return false;
                }
                if (this.mimeType !== '' && this.mimeType !== file.attributes['mime-type']) {
                    return false;
                }
                if (this.isImage && !file.attributes['mime-type'].includes('image')) {
                    return false;
                }
                if (this.isVideo && !file.attributes['mime-type'].includes('video')) {
                    return false;
                }
                if (this.isAudio && !file.attributes['mime-type'].includes('audio')) {
                    return false;
                }
                const office = ['application/pdf']; //TODO enable more mime types
                if (this.isDocument && !office.includes(file.attributes['mime-type'])) {
                    return false;
                }

                return true;
            };

            return loadArray.filter(filterFile).map((file) => ({
                id: file.id,
                name: file.attributes.name,
                mime_type: file.attributes['mime-type'],
                download_url: this.urlHelper.getURL(
                    'sendfile.php',
                    { type: 0, file_id: file.id, file_name: file.attributes.name },
                    true
                ),
            }));
        },
        updateFiles() {
            this.courseFiles = this.filterFiles(this.loadedCourseFiles);
            this.userFiles = this.filterFiles(this.loadedUserFiles);
        },
        async getCourseFiles() {
            const parent = { type: 'courses', id: `${this.context.id}` };
            const relationship = 'file-refs';
            await this.loadRelatedFileRefs({ parent, relationship });

            this.loadedCourseFiles = this.relatedFileRefs({ parent, relationship });
            this.updateFiles();
        },
        async getUserFiles() {
            const parent = { type: 'users', id: `${this.userId}` };
            const relationship = 'file-refs';
            await this.loadRelatedFileRefs({ parent, relationship });

            this.loadedUserFiles = this.relatedFileRefs({ parent, relationship });
            this.updateFiles();
        },
    },
    mounted() {
        if (this.context.type !== 'users') {
            this.getCourseFiles();
        }
        this.getUserFiles();

        this.currentValue = this.value;
    },
    watch: {
        selectedFolderId() {
            this.updateFiles();
        },
    },
};
</script>
