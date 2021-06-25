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
                    @ended="onEndedListener"
                />
                <div v-if="!emptyAudio" class="cw-audio-container">
                    <div class="cw-audio-current-track">
                        <p>{{ activeTrackName }}</p>
                    </div>
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
                <div v-if="hasPlaylist" class="cw-audio-playlist-wrapper">
                    <ul class="cw-audio-playlist">
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
                    <div v-if="showRecorder && canGetMediaDevices" class="cw-audio-playlist-recorder">
                        <button 
                            v-show="!userRecorderEnabled"
                            class="button"
                            @click="enableRecorder"
                        >
                            <translate>Aufnahme aktivieren</translate>
                        </button>
                        <button
                            v-show="userRecorderEnabled && !isRecording && !newRecording"
                            class="button"
                            @click="startRecording"
                        >
                            <translate>Aufnahme starten</translate>
                        </button>
                        <button
                            v-show="newRecording && !isRecording"
                            class="button"
                            @click="startRecording"
                        >
                            <translate>Aufnahme wiederholen</translate>
                        </button>
                        <button 
                            v-show="isRecording"
                            class="button"
                            @click="stopRecording"
                        >
                            <translate>Aufnahme beenden</translate>
                        </button>
                        <button 
                            v-show="newRecording && !isRecording"
                            class="button"
                            @click="resetRecorder"
                        >
                            <translate>Aufnahme löschen</translate>
                        </button>
                        <button 
                            v-show="newRecording && !isRecording"
                            class="button"
                            @click="storeRecording"
                        >
                            <translate>Aufnahme speichern</translate>
                        </button>
                        <span v-show="isRecording">
                            <translate>Aufnahme läuft</translate>: {{seconds2time(timer)}}
                        </span>
                    </div>
                </div>
                <div v-if="emptyAudio" class="cw-audio-empty">
                    <p><translate>Es ist keine Audio-Datei verfügbar</translate></p>
                </div>
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
                    <label v-show="currentSource === 'web'">
                        <translate>URL</translate>
                        <input type="text" v-model="currentWebUrl" />
                    </label>
                    <label v-show="currentSource === 'studip_file'">
                        <translate>Datei</translate>
                        <courseware-file-chooser
                            v-model="currentFileId"
                            :isAudio="true"
                            @selectFile="updateCurrentFile"
                        />
                    </label>
                    <label v-show="currentSource === 'studip_folder'">
                        <translate>Ordner</translate>
                        <courseware-folder-chooser v-model="currentFolderId" allowUserFolders />
                    </label>
                    <label v-show="currentSource === 'studip_folder'">
                        <translate>Audio Aufnahmen zulassen</translate>
                        <select v-model="currentRecorderEnabled">
                            <option value="true"><translate>Ja</translate></option>
                            <option value="false"><translate>Nein</translate></option>
                        </select>
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
            currentRecorderEnabled: false,
            userRecorderEnabled: false,
            recorder: null,
            chunks: [],
            blob: null,
            timer: 0,
            isRecording: false,
            newRecording: false,
        };
    },
    computed: {
        ...mapGetters({
            fileRefById: 'file-refs/byId',
            relatedFileRefs: 'file-refs/related',
            urlHelper: 'urlHelper',
            userId: 'userId',
            usersById: 'users/byId',
            relatedTermOfUse: 'terms-of-use/related'
        }),
        files() {
            const files =
                this.relatedFileRefs({
                    parent: { type: 'folders', id: this.currentFolderId },
                    relationship: 'file-refs'
                }) ?? [];

            return files
                .filter((file) => {
                    if (this.relatedTermOfUse({parent: file, relationship: 'terms-of-use'}).attributes['download-condition'] !== 0) {
                        return false;
                    } 
                    if (! file.attributes['mime-type'].includes('audio')) {
                        return false;
                    }

                    return true;
                })
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
        recorderEnabled() {
            return this.block?.attributes?.payload?.recorder_enabled;
        },
        showRecorder() {
            return this.currentRecorderEnabled === 'true';
        },
        hasPlaylist() {
            return this.files.length > 0 && this.currentSource === 'studip_folder';
        },
        canGetMediaDevices() {
            return navigator.mediaDevices !== undefined;
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
        activeTrackName() {
            if (this.currentSource === 'studip_file') {
                return this.currentFile.name;
            }
            if (this.currentSource === 'studip_folder') {
                if (this.files.length > 0) {
                    return this.files[this.currentPlaylistItem].name;
                } else {
                    return '';
                }
            }
            if (this.currentSource === 'web') {
                return this.currentWebUrl;
            }

            return '';
        },
        emptyAudio() {
            if (this.currentSource === 'studip_folder' && this.currentFolderId !== '') {
                return false;
            }
            if (this.currentSource === 'studip_file' && this.currentFileId !== '') {
                return false;
            }
            if (this.currentSource === 'web' && this.currentWebUrl !== '') {
                return false;
            }
            return true;
        }
    },
    mounted() {
        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            loadFileRef: 'file-refs/loadById',
            loadRelatedFileRefs: 'file-refs/loadRelated',
            updateBlock: 'updateBlockInContainer',
            companionWarning: 'companionWarning',
            companionSuccess: 'companionSuccess',
            createFile: 'createFile',
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
            this.currentRecorderEnabled = this.recorderEnabled;
        },
        updateCurrentFile(file) {
            this.currentFile = file;
            this.currentFileId = file.id;
        },
        getFolderFiles() {
            return this.loadRelatedFileRefs({
                parent: { type: 'folders', id: this.currentFolderId },
                relationship: 'file-refs',
                options: { include: 'terms-of-use' }
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
            attributes.payload.recorder_enabled = 'false';
            if (this.currentSource === 'studip_file') {
                attributes.payload.file_id = this.currentFileId;
            } else if (this.currentSource === 'web') {
                attributes.payload.web_url = this.currentWebUrl;
            } else if (this.currentSource === 'studip_folder') {
                attributes.payload.folder_id = this.currentFolderId;
                attributes.payload.recorder_enabled = this.currentRecorderEnabled;
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
        onEndedListener() {
            this.stopAudio();
            if(this.hasPlaylist) {
                this.nextAudio();
            }
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
                if (this.playing) {
                    this.pauseAudio();
                } else {
                    this.playAudio();
                }
            } else {
                this.currentPlaylistItem = index;
                this.$nextTick(()=> {
                    this.playAudio();
                });
            }
        },
        prevAudio() {
            this.stopAudio();
            if (this.currentPlaylistItem !== 0) {
                this.currentPlaylistItem = this.currentPlaylistItem - 1;
            } else {
                this.currentPlaylistItem = this.files.length - 1;
            }
            this.$nextTick(()=> {
                this.playAudio();
            });
        },
        nextAudio() {
            this.stopAudio();
            if (this.currentPlaylistItem < this.files.length - 1) {
                this.currentPlaylistItem = this.currentPlaylistItem + 1;
            } else {
                this.currentPlaylistItem = 0;
            }
            this.$nextTick(()=> {
                this.playAudio();
            });
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
        enableRecorder() {
            let view = this;
            navigator.mediaDevices.getUserMedia({audio: true}).then(_stream => {
                let stream = _stream;
                view.recorder = new MediaRecorder(stream);
                view.userRecorderEnabled = true;

                view.recorder.ondataavailable = e => {
                    view.chunks.push(e.data);
                    if(view.recorder.state == 'inactive') {
                        this.blob = new Blob(view.chunks, {type: 'audio/mpeg' });
                    }
                };
                view.recorder.start();
                view.recorder.stop();
                view.chunks = [];
                view.blob = null;

            }).catch(error => {
                view.companionWarning({
                    info: view.$gettext('Sie müssen ein Mikrofon freigeben, um eine Aufnahme starten zu können.')
                });
                console.debug(error);
            });
        },
        startRecording() {
            let view = this;
            this.chunks = [];
            this.timer = 0;
            this.recorder.start();
            this.isRecording = true;
            setTimeout(function(){ view.setTimer(); }, 1000);
        },
        stopRecording() {
            this.isRecording = false;
            this.newRecording = true;
            this.recorder.stop();
        },
        setTimer() {
            let view = this;
            if (this.recorder.state === 'recording') {
                this.timer++;
                setTimeout(function(){ view.setTimer(); }, 1000);
            }
        },
        async storeRecording() {
            let view = this;
            let user = this.usersById({id: this.userId});
            let file = {};
            file.attributes = {};
            file.attributes.name = (user.attributes["formatted-name"]).replace(/\s+/g, '_') + '.mp3';
            let fileObj = false;
            try {
                 fileObj = await this.createFile({
                    file: file,
                    filedata: view.blob,
                    folder: {id: this.currentFolderId}
                });
            }
            catch(e) {
                this.companionError({
                    info: this.$gettext('Es ist ein Fehler aufgetretten! Die Aufnahme konnte nicht gespeichert werden.')
                });
                console.debug(e);
            }
            if(fileObj && fileObj.type === 'file-refs') {
                this.companionSuccess({
                    info: this.$gettext('Aufnahme wurde erfolgreich im Dateibereich abgelegt.')
                });
            }
            this.newRecording = false;
            this.getFolderFiles();
        },
        resetRecorder() {
            this.newRecording = false;
            this.chunks = [];
            this.timer = 0;
            this.blob = null;
        },
    },
    watch: {
        currentFolderId() {
            this.getFolderFiles();
        },
    },
};
</script>
