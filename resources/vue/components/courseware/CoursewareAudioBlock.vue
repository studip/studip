<template>
    <div class="cw-block cw-block-audio">
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
                <audio
                    :src="currentURL"
                    class="cw-audio-player"
                    ref="audio"
                    @timeupdate="onTimeUpdateListener"
                    @loadeddata="setDuration"
                />
                <div class="cw-audio-container">
                    <div class="cw-audio-controls">
                        <input
                            class="cw-audio-range"
                            ref="range"
                            type="range"
                            :value="currentSeconds"
                            min="0"
                            :max="Math.round(durationSeconds)"
                            @input="rangeAction"
                        />
                        <span class="cw-audio-time">{{ currentTime }} / {{ durationTime }}</span>

                        <button v-if="hasPlaylist" class="cw-audio-button cw-audio-prevbutton" @click="prevAudio" />
                        <button v-if="!playing" class="cw-audio-button cw-audio-playbutton" @click="playAudio" />
                        <button v-if="playing" class="cw-audio-button cw-audio-pausebutton" @click="pauseAudio" />
                        <button v-if="hasPlaylist" class="cw-audio-button cw-audio-nextbutton" @click="nextAudio" />
                        <button class="cw-audio-button cw-audio-stopbutton" @click="stopAudio" />
                    </div>
                </div>
                <ul v-if="hasPlaylist" class="cw-audio-playlist">
                    <li
                        v-for="(file, index) in files"
                        :key="file.id"
                        :class="{
                            'is-playing': index === currentPlaylistItem && playing,
                            'current-item': index === currentPlaylistItem,
                        }"
                        class="cw-playlist-item"
                        @click="setCurrentPlaylistItem(index)"
                    >
                        {{ file.name }}
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
                        <translate>Quelle</translate>
                        <select v-model="currentSource">
                            <option value="studip_file"><translate>Dateibereich Datei</translate></option>
                            <option value="studip_folder"><translate>Dateibereich Ordner</translate></option>
                            <option value="web"><translate>Web-Adresse</translate></option>
                        </select>
                    </label>
                    <label v-if="currentSource === 'web'">
                        <translate>URL</translate>
                        <input type="text" v-model="currentWebUrl" />
                    </label>
                    <label v-if="currentSource === 'studip_file'">
                        <translate>Datei</translate>
                        <courseware-file-chooser
                            v-model="currentFileId"
                            :isAudio="true"
                            @selectFile="updateCurrentFile"
                        />
                    </label>
                    <label v-if="currentSource === 'studip_folder'">
                        <translate>Ordner</translate>
                        <courseware-folder-chooser v-model="currentFolderId" allowUserFolders />
                    </label>
                </form>
            </template>
            <template #info>
                <p><translate>Informationen zum Audio-Block</translate></p>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import CoursewareFileChooser from './CoursewareFileChooser.vue';
import CoursewareFolderChooser from './CoursewareFolderChooser.vue';

import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-audio-block',
    components: {
        CoursewareDefaultBlock,
        CoursewareFileChooser,
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
            currentSource: '',
            currentFileId: '',
            currentFolderId: '',
            currentWebUrl: '',
            currentFile: {},
            currentSeconds: 0,
            durationSeconds: 0,
            playing: false,
            currentPlaylistItem: 0,
        };
    },
    computed: {
        ...mapGetters({
            fileRefById: 'file-refs/byId',
            relatedFileRefs: 'file-refs/related',
            urlHelper: 'urlHelper',
        }),
        files() {
            const files =
                this.relatedFileRefs({
                    parent: { type: 'folders', id: this.currentFolderId },
                    relationship: 'file-refs',
                }) ?? [];

            return files
                .filter((file) => file.attributes['mime-type'].includes('audio'))
                .map(({ id, attributes }) => {
                    return {
                        id,
                        name: attributes.name,
                        download_url: this.urlHelper.getURL(
                            'sendfile.php/',
                            { type: 0, file_id: id, file_name: attributes.name },
                            true
                        ),
                    };
                });
        },
        currentTime() {
            return this.seconds2time(this.currentSeconds);
        },
        durationTime() {
            return this.seconds2time(this.durationSeconds);
        },
        title() {
            return this.block?.attributes?.payload?.title;
        },
        source() {
            return this.block?.attributes?.payload?.source;
        },
        fileId() {
            return this.block?.attributes?.payload?.file_id;
        },
        folderId() {
            return this.block?.attributes?.payload?.folder_id;
        },
        webUrl() {
            return this.block?.attributes?.payload?.web_url;
        },
        hasPlaylist() {
            return this.files.length > 0 && this.currentSource === 'studip_folder';
        },
        currentURL() {
            if (this.currentSource === 'studip_file') {
                return this.currentFile.download_url;
            }
            if (this.currentSource === 'studip_folder') {
                if (this.files.length > 0) {
                    return this.files[this.currentPlaylistItem].download_url;
                } else {
                    return '';
                }
            }
            if (this.currentSource === 'web') {
                return this.currentWebUrl;
            }

            return '';
        },
    },
    mounted() {
        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            loadFileRef: 'file-refs/loadById',
            loadRelatedFileRefs: 'file-refs/loadRelated',
            updateBlock: 'updateBlockInContainer',
        }),
        initCurrentData() {
            this.currentTitle = this.title;
            this.currentSource = this.source;
            this.currentFileId = this.fileId;
            this.currentWebUrl = this.webUrl;
            if (this.currentFileId !== '') {
                this.loadFile();
            }
            this.currentFolderId = this.folderId;
        },
        updateCurrentFile(file) {
            this.currentFile = file;
            this.currentFileId = file.id;
        },
        getFolderFiles() {
            return this.loadRelatedFileRefs({
                parent: { type: 'folders', id: this.currentFolderId },
                relationship: 'file-refs',
            });
        },
        storeBlock() {
            let attributes = {};
            attributes.payload = {};
            attributes.payload.title = this.currentTitle;
            attributes.payload.source = this.currentSource;
            attributes.payload.file_id = '';
            attributes.payload.web_url = '';
            attributes.payload.folder_id = '';
            if (this.currentSource === 'studip_file') {
                attributes.payload.file_id = this.currentFileId;
            } else if (this.currentSource === 'web') {
                attributes.payload.web_url = this.currentWebUrl;
            } else if (this.currentSource === 'studip_folder') {
                attributes.payload.folder_id = this.currentFolderId;
            } else {
                return false;
            }

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
        rangeAction() {
            if (this.$refs.range.value !== this.currentSeconds) {
                this.$refs.audio.currentTime = this.$refs.range.value;
            }
        },
        setDuration() {
            this.durationSeconds = this.$refs.audio.duration;
        },
        playAudio() {
            this.$refs.audio.play();
            this.playing = true;
        },
        pauseAudio() {
            this.$refs.audio.pause();
            this.playing = false;
        },
        stopAudio() {
            this.pauseAudio();
            this.$refs.audio.currentTime = 0;
        },
        onTimeUpdateListener() {
            this.currentSeconds = this.$refs.audio.currentTime;
        },
        seconds2time(seconds) {
            seconds = Math.round(seconds);
            let hours = Math.floor(seconds / 3600);
            let minutes = Math.floor((seconds - hours * 3600) / 60);
            let time = '';
            seconds = seconds - hours * 3600 - minutes * 60;
            if (hours !== 0) {
                time = hours + ':';
            }
            if (minutes !== 0 || time !== '') {
                minutes = minutes < 10 && time !== '' ? '0' + minutes : String(minutes);
                time += minutes + ':';
            }
            if (time === '') {
                time = seconds < 10 ? '0:0' + seconds : '0:' + seconds;
            } else {
                time += seconds < 10 ? '0' + seconds : String(seconds);
            }
            return time;
        },
        setCurrentPlaylistItem(index) {
            if (this.currentPlaylistItem === index) {
                this.pauseAudio();
            } else {
                this.currentPlaylistItem = index;
                this.playAudio();
            }
        },
        prevAudio() {
            if (this.currentPlaylistItem !== 0) {
                this.currentPlaylistItem = this.currentPlaylistItem - 1;
            } else {
                this.currentPlaylistItem = this.files.length - 1;
            }
        },
        nextAudio() {
            if (this.currentPlaylistItem < this.files.length - 1) {
                this.currentPlaylistItem = this.currentPlaylistItem + 1;
            } else {
                this.currentPlaylistItem = 0;
            }
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
        }
    },
    watch: {
        currentFolderId() {
            this.getFolderFiles();
        },
    },
};
</script>
