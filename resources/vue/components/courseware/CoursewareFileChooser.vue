<template>
    <div class="cw-file-chooser">
        <span v-translate>Ordner-Filter</span>
        <courseware-folder-chooser allowUserFolders unchoose v-model="selectedFolderId" />
        <span v-translate>Datei</span>
        <select v-model="currentValue" @change="selectFile">
            <option v-show="canBeEmpty" value="">
                <translate>Keine Auswahl</translate>
            </option>
            <optgroup v-if="this.context.type === 'courses' && courseFiles.length !== 0" :label="textOptGroupCourse">
                <option v-for="(file, index) in courseFiles" :key="index" :value="file.id">
                    {{ file.name }}
                </option>
            </optgroup>
            <optgroup v-if="userFiles.length !== 0" :label="textOptGroupUser">
                <option v-for="(file, index) in userFiles" :key="index" :value="file.id">
                    {{ file.name }}
                </option>
            </optgroup>
            <option v-show="userFiles.length === 0 && courseFiles.length === 0" disabled>
                <translate>Keine Dateien vorhanden</translate>
            </option>
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
        canBeEmpty: { type: Boolean, default: false },
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
            relatedTermOfUse: 'terms-of-use/related'
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
                if (this.relatedTermOfUse({parent: file, relationship: 'terms-of-use'}).attributes['download-condition'] !== 0) {
                    return false;
                }
                if (this.selectedFolderId !== '' && this.selectedFolderId !== file.relationships.parent.data.id) {
                    return false;
                }
                if (this.mimeType !== '' && this.mimeType !== file.attributes['mime-type']) {
                    return false;
                }
                if (this.isImage && !file.attributes['mime-type'].includes('image')) {
                    return false;
                }
                const videoConditions = ['video/mp4', 'video/ogg', 'video/webm'];
                if (this.isVideo && !videoConditions.some(condition => file.attributes['mime-type'].includes(condition))) {
                    return false;
                }
                const audioConditions = ['audio/wav', 'audio/ogg', 'audio/webm','audio/flac', 'audio/mpeg'];
                if (this.isAudio && !audioConditions.some(condition => file.attributes['mime-type'].includes(condition)) ) {
                    return false;
                }
                const officeConditions = ['application/pdf']; //TODO enable more mime types
                if (this.isDocument && !officeConditions.some(condition => file.attributes['mime-type'].includes(condition)) ) {
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
            const options = { include: 'terms-of-use' };
            await this.loadRelatedFileRefs({ parent, relationship, options });

            this.loadedCourseFiles = this.relatedFileRefs({ parent, relationship });
            this.updateFiles();
        },
        async getUserFiles() {
            const parent = { type: 'users', id: `${this.userId}` };
            const relationship = 'file-refs';
            const options = { include: 'terms-of-use' };
            await this.loadRelatedFileRefs({ parent, relationship, options });

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
