<template>
    <div class="cw-block cw-block-document">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="false"
            @storeEdit="storeBlock"
            @closeEdit="initCurrentData"
        >
            <template #content>
                <div v-if="hasFile" class="cw-pdf-header cw-block-title">
                    <button class="cw-pdf-button-prev" :class="{ inactive: pageNum - 1 === 0 }" @click="prevPage" />
                    <span class="cw-pdf-title">{{ currentTitle }}</span>
                    <a :href="currentUrl" class="cw-pdf-download" download></a>
                    <span>
                        <translate :translate-params="{pageNum, pageCount}">
                            (Seite %{pageNum} von %{pageCount})
                        </translate>
                    </span>
                    <button class="cw-pdf-button-next" :class="{ inactive: pageNum === pageCount }" @click="nextPage" />
                </div>
                <canvas
                    v-if="hasFile"
                    ref="pdfcanvas"
                    class="cw-pdf-canvas"
                    @mousedown="browse = true"
                    @mouseup="browse = false"
                    @mouseleave="browse = false"
                    @mousemove="browsePdf"
                />
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        <translate>Überschrift</translate>
                        <input type="text" v-model="currentTitle" />
                    </label>
                    <label>
                        <translate>Datei</translate>
                        <courseware-file-chooser
                            v-model="currentFileId"
                            :isDocument="true"
                            @selectFile="updateCurrentFile"
                        />
                    </label>
                    <label>
                        <translate>Download-Icon anzeigen</translate>
                        <select v-model="currentDownloadable">
                            <option value="true"><translate>Ja</translate></option>
                            <option value="false"><translate>Nein</translate></option>
                        </select>
                    </label>
                    <label>
                        <translate>Dateityp</translate>
                        <select v-model="currentDocType">
                            <option value="pdf">PDF</option>
                        </select>
                    </label>
                </form>
            </template>
            <template #info>
                <p><translate>Informationen zum Document-Block</translate></p>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import CoursewareFileChooser from './CoursewareFileChooser.vue';
import * as pdfjsLib from 'pdfjs-dist';
import pdfjsWorker from 'pdfjs-dist/build/pdf.worker.entry';

import { mapActions } from 'vuex';

export default {
    name: 'courseware-document-block',
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
            currentFileId: '',
            currentFile: {},
            currentDownloadable: '',
            currentDocType: '',

            PdfViewer: true,
            pdfDoc: null,
            pageNum: 1,
            pageRendering: false,
            pageNumPending: null,
            pageCount: 0,
            scale: 2,
            canvas: {},
            context: {},
            browse: false,
            browseDirection: [],
            file: null
        };
    },
    computed: {
        title() {
            return this.block?.attributes?.payload?.title;
        },
        downloadable() {
            return this.block?.attributes?.payload?.downloadable;
        },
        fileId() {
            return this.block?.attributes?.payload?.file_id;
        },
        docType() {
            return this.block?.attributes?.payload?.doc_type;
        },
        currentUrl() {
            if (this.currentFile?.meta) {
                return this.currentFile.meta['download-url'];
            } else {
                return '';
            }
        },
        hasFile() {
            return this.currentFileId !== '';
        }
    },
    watch: {
        browseDirection: function (val) {
            if (val.length > 6) {
                this.evaluateBrowseAction();
            }
        },
    },
    mounted() {
        this.loadFileRefs(this.block.id).then((response) => {
            this.file = response[0];
            this.currentFile = this.file;
            this.loadPdfViewer();
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
            this.currentTitle = this.title;
            this.currentDownloadable = this.downloadable;
            this.currentFileId = this.fileId;
            this.currentDocType = this.docType;
        },
        updateCurrentFile(file) {
            this.currentFile = file;
            this.currentFileId = file.id;
        },
        loadPdfViewer() {
            if (this.PdfViewer && this.currentUrl) {
                let view = this;
                this.canvas = this.$refs.pdfcanvas;
                this.context = this.canvas.getContext('2d');
                pdfjsLib.GlobalWorkerOptions.workerSrc = pdfjsWorker;
                pdfjsLib.getDocument(this.currentUrl).promise.then(function (pdf) {
                    view.pdfDoc = pdf;
                    view.pageCount = view.pdfDoc.numPages;
                    view.renderPage(view.pageNum);
                });
            }
        },
        renderPage(num) {
            let view = this;
            this.pageRendering = true;
            this.pdfDoc.getPage(num).then(function (page) {
                let viewport = page.getViewport({ scale: view.scale });
                view.canvas.height = viewport.height;
                view.canvas.width = viewport.width;

                let renderContext = {
                    canvasContext: view.context,
                    viewport: viewport,
                };
                let renderTask = page.render(renderContext);

                renderTask.promise.then(function () {
                    view.pageRendering = false;
                    if (view.pageNumPending !== null) {
                        view.renderPage(view.pageNumPending);
                        view.pageNumPending = null;
                    }
                });
            });
        },
        queueRenderPage(num) {
            if (this.pageRendering) {
                this.pageNumPending = num;
            } else {
                this.renderPage(num);
            }
        },
        prevPage() {
            if (this.pageNum <= 1) {
                return;
            }
            this.pageNum--;
            this.queueRenderPage(this.pageNum);
        },
        nextPage() {
            if (this.pageNum >= this.pdfDoc.numPages) {
                return;
            }
            this.pageNum++;
            this.queueRenderPage(this.pageNum);
        },
        browsePdf(e) {
            if (this.browse) {
                this.browseDirection.push(e.clientX);
            }
        },
        evaluateBrowseAction() {
            this.browse = false;
            let first = this.browseDirection[0];
            let last = this.browseDirection.pop();
            this.browseDirection = [];
            if (first < last) {
                this.prevPage();
            } else {
                this.nextPage();
            }
        },

        storeBlock() {
            if (this.currentFile === undefined) {
                this.companionWarning({
                    info: this.$gettext('Bitte wählen Sie eine Datei aus')
                });
                return false;
            } else {
                let attributes = {};
                attributes.payload = {};
                attributes.payload.title = this.currentTitle;
                attributes.payload.file_id = this.currentFile.id;
                attributes.payload.downloadable = this.currentDownloadable;
                attributes.payload.doc_type = this.currentDocType;

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
