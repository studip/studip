<template>
    <select v-model="currentValue" @change="changeSelection">
        <option v-if="unchoose" value=""><translate>kein Ordner ausgewählt</translate></option>
        <optgroup v-if="this.context.type === 'courses'" :label="textOptGroupCourse">
            <option v-for="folder in loadedCourseFolders" :key="folder.id" :value="folder.id">
                {{ folder.attributes.name }}
            </option>
        </optgroup>
        <optgroup v-if="allowUserFolders" :label="textOptGroupUser">
            <option v-for="folder in loadedUserFolders" :key="folder.id" :value="folder.id">
                {{ folder.attributes.name }}
            </option>
        </optgroup>
    </select>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-folder-chooser',
    props: {
        value: String,
        allowUserFolders: { type: Boolean, default: false },
        allowHomeworkFolders: { type: Boolean, default: false },
        unchoose: { type: Boolean, default: false },
    },
    data() {
        return {
            currentValue: Object,
            textOptGroupCourse: this.$gettext('Dateibereich dieser Veranstaltung'),
            textOptGroupUser: this.$gettext('Eigener Dateibereich'),
        };
    },
    computed: {
        ...mapGetters({
            context: 'context',
            relatedFolders: 'folders/related',
            userId: 'userId',
        }),
        courseObject() {
            return { type: 'courses', id: `${this.context.id}` };
        },
        userObject() {
            return { type: 'users', id: `${this.userId}` };
        },
        loadedCourseFolders() {
            let loadedCourseFolders = [];
            let CourseFolders = this.relatedFolders({ parent: this.courseObject, relationship: 'folders' }) ?? [];
            CourseFolders.forEach(folder => {
                switch (folder.attributes['folder-type']) {
                    case 'HiddenFolder':
                        if (folder.attributes['data-content']['download_allowed'] === 1) {
                            loadedCourseFolders.push(folder);
                        }
                        break;
                    case 'HomeworkFolder':
                        if(this.allowHomeworkFolders) {
                            loadedCourseFolders.push(folder);
                        }
                    default:
                        loadedCourseFolders.push(folder);
                }
            });

            return loadedCourseFolders;
        },
        loadedUserFolders() {
            let loadedUserFolders = [];
            let UserFolders = this.relatedFolders({ parent: this.userObject, relationship: 'folders' }) ?? [];
            UserFolders.forEach(folder => {
                if (folder.attributes['folder-type'] === 'PublicFolder') {
                    loadedUserFolders.push(folder);
                }
            });

            return loadedUserFolders;
        },
    },
    methods: {
        ...mapActions({ loadRelatedFolders: 'folders/loadRelated' }),
        changeSelection() {
            this.$emit('input', this.currentValue);
        },

        getCourseFolders() {
            return this.loadRelatedFolders({
                parent: this.courseObject,
                relationship: 'folders',
            });
        },
        getUserFolders() {
            return this.loadRelatedFolders({
                parent: this.userObject,
                relationship: 'folders',
            });
        },
    },
    mounted() {
        this.currentValue = this.value;
        if (this.context.type !== 'users') {
            this.getCourseFolders();
        }
        this.getUserFolders();
    },
};
</script>
