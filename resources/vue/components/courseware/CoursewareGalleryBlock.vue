<template>
    <div class="cw-block cw-block-gallery">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="true"
            @storeEdit="storeBlock"
            @closeEdit="initCurrentData"
        >
            <template #content>
                <div v-if="files.length !== 0" class="cw-block-gallery-content" :style="{ 'max-height': currentHeight + 'px' }">
                    <div
                        v-for="(image, index) in files"
                        :key="image.id"
                        ref="images"
                        class="cw-block-gallery-slides cw-block-gallery-fade"
                    >
                        <div class="cw-block-gallery-number-text">{{ index + 1 }} / {{ files.length }}</div>
                        <img
                            :src="image.download_url"
                            :style="{ 'max-height': currentHeight + 'px' }"
                            @load="
                                if (files.length - 1 === index) {
                                    startGallery();
                                }
                            "
                        />
                        <div v-if="currentShowFileNames === 'true'" class="cw-block-gallery-file-name">
                            <span>{{ image.name }}</span>
                        </div>
                    </div>
                    <div v-if="currentNav === 'true'">
                        <a class="cw-block-gallery-prev" @click="plusSlides(-1)"></a>
                        <a class="cw-block-gallery-next" @click="plusSlides(1)"></a>
                    </div>
                </div>
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        <translate>Ordner</translate>
                        <courseware-folder-chooser v-model="currentFolderId" allowUserFolders />
                    </label>
                    <label>
                        <translate>Maximale Höhe</translate>
                        <input type="number" min="0" max="800" v-model="currentHeight" />
                    </label>
                    <label>
                        <translate>Autoplay</translate>
                        <select v-model="currentAutoplay">
                            <option value="true"><translate>Ja</translate></option>
                            <option value="false"><translate>Nein</translate></option>
                        </select>
                    </label>
                    <label v-if="currentAutoplay === 'true'">
                        <translate>Autoplay Timer in Sekunden</translate>
                        <input type="number" min="1" max="60" v-model="currentAutoplayTimer" />
                    </label>
                    <label v-if="currentAutoplay === 'true'">
                        <translate>Navigation</translate>
                        <select v-model="currentNav">
                            <option value="true"><translate>Ja</translate></option>
                            <option value="false"><translate>Nein</translate></option>
                        </select>
                    </label>
                    <label>
                        <translate>Dateinamen anzeigen</translate>
                        <select v-model="currentShowFileNames">
                            <option value="true"><translate>Ja</translate></option>
                            <option value="false"><translate>Nein</translate></option>
                        </select>
                    </label>
                </form>
            </template>
            <template #info>
                <p><translate>Informationen zum Galerie-Block</translate></p>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import CoursewareFolderChooser from './CoursewareFolderChooser.vue';

import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-gallery-block',
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
            currentFolderId: '',
            currentAutoplay: '',
            currentNav: '',
            currentHeight: '',
            currentShowFileNames: '',
            currentAutoplayTimer: '',
            files: [],
            slideIndex: 0,
        };
    },
    computed: {
        ...mapGetters({
            urlHelper: 'urlHelper',
            relatedFileRefs: 'file-refs/related',
            relatedTermOfUse: 'terms-of-use/related',
        }),
        folderId() {
            return this.block?.attributes?.payload?.folder_id;
        },
        autoplay() {
            return this.block?.attributes?.payload?.autoplay;
        },
        autoplayTimer() {
            return this.block?.attributes?.payload?.autoplay_timer;
        },
        nav() {
            return this.block?.attributes?.payload?.nav;
        },
        height() {
            return this.block?.attributes?.payload?.height;
        },
        showFileNames() {
            return this.block?.attributes?.payload?.show_filenames;
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
            this.currentFolderId = this.folderId;
            this.currentAutoplay = this.autoplay;
            this.currentAutoplayTimer = this.autoplayTimer;
            this.currentNav = this.nav;
            this.currentHeight = this.height;
            this.currentShowFileNames = this.showFileNames;
        },
        startGallery() {
            this.slideIndex = 0;
            this.showSlides(0);
            if (this.currentAutoplay === 'true') {
                this.playSlides();
            }
        },
        async getFolderFiles() {
            const parent = { type: 'folders', id: `${this.currentFolderId}` };
            const relationship = 'file-refs';
            const options = { include: 'terms-of-use'}
            await this.loadRelatedFileRefs({ parent, relationship, options });

            const files = this.relatedFileRefs({ parent, relationship });
            this.processFiles(files);
        },
        processFiles(files) {
            this.files = files
                .filter((file) => {
                    if (this.relatedTermOfUse({parent: file, relationship: 'terms-of-use'}).attributes['download-condition'] !== 0) {
                        return false;
                    }
                    if (! file.attributes['mime-type'].includes('image')) {
                        return false;
                    }

                    return true;
                })
                .map((file) => ({
                    id: file.id,
                    name: file.attributes.name,
                    download_url: this.urlHelper.getURL(
                        'sendfile.php',
                        { type: 0, file_id: file.id, file_name: file.attributes.name },
                        true
                    ),
                }));
        },
        storeBlock() {
            let attributes = {};
            attributes.payload = {};
            attributes.payload.folder_id = this.currentFolderId;
            attributes.payload.autoplay = this.currentAutoplay;
            attributes.payload.autoplay_timer = this.currentAutoplayTimer;
            attributes.payload.nav = this.currentNav;
            attributes.payload.height = this.currentHeight;
            attributes.payload.show_filenames = this.currentShowFileNames;

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
        plusSlides: function (n) {
            this.showSlides((this.slideIndex += n));
        },
        showSlides: function (n) {
            let slides = this.$refs.images;
            if (slides === undefined) {
                return false;
            }
            if (n > slides.length - 1) {
                this.slideIndex = 0;
            }
            if (n < 0) {
                this.slideIndex = slides.length - 1;
            }
            slides.forEach((slide) => {
                slide.style.display = 'none';
            });
            slides[this.slideIndex].style.display = 'block';
        },
        playSlides: function () {
            let slides = this.$refs.images;
            slides.forEach((slide) => {
                slide.style.display = 'none';
            });
            if (this.slideIndex > slides.length - 1) {
                this.slideIndex = 0;
            }
            if (slides[this.slideIndex]) {
                slides[this.slideIndex].style.display = 'block';
            }
            this.slideIndex++;
            if (this.currentAutoplay === 'true') {
                setTimeout(this.playSlides, this.currentAutoplayTimer * 1000);
            }
        },
    },
    watch: {
        currentFolderId() {
            this.getFolderFiles();
        },
        currentAutoplay(value) {
            if (value === 'false') {
                this.currentNav = 'true';
            }
        },
        currentAutoplayTimer(value) {
            if (value > 60 || value < 1) {
                this.currentAutoplayTimer = '2';
            }
        },
    },
};
</script>
