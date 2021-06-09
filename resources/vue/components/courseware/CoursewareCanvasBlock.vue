<template>
    <div class="cw-block cw-block-canvas" ref="block">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="true"
            @storeEdit="storeBlock"
            @closeEdit="initCurrentData"
        >
            <template #content>
                <div v-if="currentTitle" class="cw-block-title">
                    {{ currentTitle }}
                </div>
                <div class="cw-canvasblock-toolbar">
                    <div class="cw-canvasblock-buttonset">
                        <button class="cw-canvasblock-reset" :title="$gettext('Zurücksetzen')" @click="reset"></button>
                        <button class="cw-canvasblock-undo" :title="$gettext('Rückgängig')" @click="undo"></button>
                        <button v-if="hasUploadFolder" class="cw-canvasblock-store" :title="$gettext('Bild im Dateibereich speichern')" @click="store"></button>
                    </div>
                    <div class="cw-canvasblock-buttonset">
                        <button
                            v-for="(rgba, color) in colors"
                            :key="color"
                            class="cw-canvasblock-color"
                            :class="[currentColor === color ? 'selected-color' : '', color]"
                            @click="setColor(color)"
                        />
                    </div>
                    <div class="cw-canvasblock-buttonset">
                        <button
                            class="cw-canvasblock-size cw-canvasblock-size-small"
                            :class="{ 'selected-size': currentSize === 2 }"
                            :title="$gettext('klein')"
                            @click="setSize('small')"
                        />
                        <button
                            class="cw-canvasblock-size cw-canvasblock-size-normal"
                            :class="{ 'selected-size': currentSize === 5 }"
                            :title="$gettext('normal')"
                            @click="setSize('normal')"
                        />
                        <button
                            class="cw-canvasblock-size cw-canvasblock-size-large"
                            :class="{ 'selected-size': currentSize === 8 }"
                            :title="$gettext('groß')"
                            @click="setSize('large')"
                        />
                        <button
                            class="cw-canvasblock-size cw-canvasblock-size-huge"
                            :class="{ 'selected-size': currentSize === 12 }"
                            :title="$gettext('riesig')"
                            @click="setSize('huge')"
                        />
                    </div>
                    <div class="cw-canvasblock-buttonset">
                        <button
                            class="cw-canvasblock-tool cw-canvasblock-tool-pen"
                            :class="{ 'selected-tool': currentTool === 'pen' }"
                            :title="$gettext('Zeichenwerkzeug')"
                            @click="setTool('pen')"
                        />
                        <button
                            class="cw-canvasblock-tool cw-canvasblock-tool-text"
                            :class="{ 'selected-tool': currentTool === 'text' }"
                            :title="$gettext('Textwerkzeug')"
                            @click="setTool('text')"
                        >
                            T
                        </button>
                    </div>
                </div>
                <img :src="currentUrl" class="cw-canvasblock-original-img" ref="image" @load="buildCanvas" />
                <input
                    v-show="textInput"
                    class="cw-canvasblock-text-input"
                    ref="textInputField"
                    @keyup="textInputKeyUp"
                />
                <canvas
                    class="cw-canvasblock-canvas"
                    :class="{
                        'cw-canvasblock-tool-selected-pen': currentTool === 'pen',
                        'cw-canvasblock-tool-selected-text': currentTool === 'text',
                    }"
                    ref="canvas"
                    @mousedown="mouseDown"
                    @mousemove="mouseMove"
                    @mouseup="mouseUp"
                    @mouseout="mouseUp"
                    @mouseleave="mouseUp"
                />
                <div class="cw-canvasblock-hints">
                    <div v-show="write" class="messagebox messagebox_info cw-canvasblock-text-info">
                        <translate>Texteingabe mit Enter-Taste bestätigen</translate>
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
                        <translate>Hintergrundbild</translate>
                        <select v-model="currentImage">
                            <option value="true"><translate>Ja</translate></option>
                            <option value="false"><translate>Nein</translate></option>
                        </select>
                    </label>
                    <label v-if="currentImage === 'true'">
                        <translate>Bilddatei</translate>
                        <courseware-file-chooser
                            v-model="currentFileId"
                            :isImage="true"
                            @selectFile="updateCurrentFile"
                        />
                    </label>
                    <label>
                        <translate>Speicherort</translate>
                        <courseware-folder-chooser v-model="currentUploadFolderId" :unchoose="true"/>
                    </label>
                    <label>
                        <translate>Werte anderer Nutzer anzeigen</translate>
                        <select v-model="currentShowUserData">
                            <option value="off"><translate>deaktiviert</translate></option>
                            <option value="teacher"><translate>nur für Lehrede</translate></option>
                            <option value="all"><translate>für alle</translate></option>
                        </select>
                    </label>
                </form>
            </template>
            <template #info>
                <p><translate>Informationen zum Leinwand-Block</translate></p>
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
    name: 'courseware-canvas-block',
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
            currentImage: '',
            currentFileId: '',
            currentUploadFolderId: '',
            currentShowUserData: '',
            currentFile: {},

            context: {},
            paint: false,
            write: false,
            clickX: [],
            clickY: [],
            clickDrag: [],
            clickColor: [],
            colors: {
                white: 'rgba(255,255,255,1)',
                blue: 'rgba(52,152,219,1)',
                green: 'rgba(46,204,113,1)',
                purple: 'rgba(155,89,182,1)',
                red: 'rgba(231,76,60,1)',
                yellow: 'rgba(254,211,48,1)',
                orange: 'rgba(243,156,18,1)',
                grey: 'rgba(149,165,166,1)',
                darkgrey: 'rgba(52,73,94,1)',
                black: 'rgba(0,0,0,1)',
            },
            currentColor: '',
            currentColorRGBA: '',
            sizes: { small: 2, normal: 5, large: 8, huge: 12 },
            clickSize: [],
            currentSize: '',
            tools: { pen: 'pen', text: 'text' },
            currentTool: '',
            clickTool: [],
            Text: [],
            textInput: false,
            file: null
        };
    },
    computed: {
        ...mapGetters({
            userId: 'userId',
            getUserDataById: 'courseware-user-data-fields/byId',
            usersById: 'users/byId',
        }),
        userData() {
            return this.getUserDataById({ id: this.block.relationships['user-data-field'].data.id });
        },
        canvasDraw() {
            if (this.userData !== undefined && this.userData.attributes.payload.canvas_draw) {
                return this.userData.attributes.payload.canvas_draw;
            } else {
                return false;
            }
        },
        title() {
            return this.block?.attributes?.payload?.title;
        },
        image() {
            return this.block?.attributes?.payload?.image;
        },
        fileId() {
            return this.block?.attributes?.payload?.file_id;
        },
        uploadFolderId() {
            return this.block?.attributes?.payload?.upload_folder_id;
        },
        showUsersData() {
            return this.block?.attributes?.payload?.show_usersdata;
        },
        currentUrl() {
            if (this.currentFile?.meta) {
                return this.currentFile.meta['download-url'];
            } else if(this.currentFile?.download_url) {
                    return this.currentFile.download_url;
            } else {
                return '';
            }
        },
        currentFileName() {
            if (this.currentFile?.attributes?.name) {
                return this.currentFile.attributes.name;
            } else {
                return this.currentTitle + '.jpg';
            }
        },
        hasUploadFolder() {
            return this.currentUploadFolderId !== "";
        },
    },
    mounted() {
        this.loadFileRefs(this.block.id).then((response) => {
            this.file = response[0];
            this.currentFile = this.file;
            this.initCurrentData();
            this.buildCanvas();
        });
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
            loadFileRefs: 'loadFileRefs',
            createFile: 'createFile',
            companionSuccess: 'companionSuccess',
            companionError: 'companionError',
        }),
        initCurrentData() {
            this.currentTitle = this.title;
            this.currentImage = this.image;
            this.currentFileId = this.fileId;
            this.currentUploadFolderId = this.uploadFolderId;
            this.currentShowUserData = this.showUsersData;
            if (this.canvasDraw) {
                this.clickX = JSON.parse(this.canvasDraw.clickX);
                this.clickY = JSON.parse(this.canvasDraw.clickY);
                this.clickDrag = JSON.parse(this.canvasDraw.clickDrag);
                this.clickColor = JSON.parse(this.canvasDraw.clickColor);
                this.clickSize = JSON.parse(this.canvasDraw.clickSize);
                this.clickTool = JSON.parse(this.canvasDraw.clickTool);
                this.Text = JSON.parse(this.canvasDraw.Text);
            }
        },
        updateCurrentFile(file) {
            this.currentFile = file;
            this.currentFileId = file.id;
            this.buildCanvas();
        },
        setColor(color) {
            if (this.write) {
                return;
            }
            this.currentColor = color;
            this.currentColorRGBA = this.colors[color];
        },
        setSize(size) {
            if (this.textInput) {
                return;
            }
            this.currentSize = this.sizes[size];
        },
        setTool(tool) {
            if (this.write) {
                this.clickX.pop();
                this.clickY.pop();
                this.clickDrag.pop();
                this.clickColor.pop();
                this.clickSize.pop();
                this.clickTool.pop();
                this.write = false;
                this.textInput = false;
            }
            this.currentTool = this.tools[tool];
        },
        reset() {
            this.clickX.length = 0;
            this.clickY.length = 0;
            this.clickDrag.length = 0;
            this.clickColor.length = 0;
            this.clickSize.length = 0;
            this.clickTool.length = 0;
            this.Text.length = 0;
            this.paint = false;
            this.write = false;
            this.textInput = false;
            this.redraw();
        },
        buildCanvas() {
            let blockElem = this.$refs.block;
            let image = this.$refs.image;
            let canvas = this.$refs.canvas;
            canvas.width = blockElem.offsetWidth - 2;
            if (this.currentImage === 'true' && image.height > 0) {
                canvas.height = Math.round((canvas.width / image.width) * image.height);
            } else {
                canvas.height = 500;
            }
            this.context = canvas.getContext('2d');
            this.currentColor = 'blue';
            this.currentColorRGBA = this.colors['blue'];
            this.currentSize = this.sizes['normal'];
            this.currentTool = this.tools['pen'];
            this.redraw();
        },
        redraw() {
            let view = this;
            let context = view.context;
            let clickX = view.clickX;
            let clickY = view.clickY;
            context.clearRect(0, 0, context.canvas.width, context.canvas.height); // Clears the canvas
            context.fillStyle = '#ffffff';
            context.fillRect(0, 0, context.canvas.width, context.canvas.height); // set background
            if (view.currentImage === 'true') {
                let outlineImage = new Image();
                outlineImage.src = this.currentUrl;
                context.drawImage(outlineImage, 0, 0, context.canvas.width, context.canvas.height);
            }

            context.lineJoin = 'round';
            for (var i = 0; i < clickX.length; i++) {
                if (view.clickTool[i] === 'pen') {
                    context.beginPath();
                    if (view.clickDrag[i] && i) {
                        context.moveTo(clickX[i - 1], clickY[i - 1]);
                    } else {
                        context.moveTo(clickX[i] - 1, clickY[i]);
                    }
                    context.lineTo(clickX[i], clickY[i]);
                    context.closePath();
                    context.strokeStyle = view.clickColor[i];
                    context.lineWidth = view.clickSize[i];
                    context.stroke();
                }
                if (view.clickTool[i] === 'text') {
                    let fontsize = view.clickSize[i] * 6;
                    context.font = fontsize + 'px Arial ';
                    context.fillStyle = view.clickColor[i];
                    context.fillText(view.Text[i], clickX[i], clickY[i] + fontsize);
                }
            }
        },
        mouseDown(e) {
            if (this.write) {
                let view = this;
                this.$refs.textInputField.focus();
                window.setTimeout(function () {
                    view.$refs.textInputField.focus();
                }, 0);
                return;
            }
            if (this.currentTool === 'pen') {
                this.paint = true;
                this.addClick(e.offsetX, e.offsetY, false);
                this.redraw();
            }
            if (this.currentTool === 'text') {
                this.write = true;
                this.addClick(e.offsetX, e.offsetY, false);
            }
        },
        mouseMove(e) {
            if (this.paint) {
                this.addClick(e.offsetX, e.offsetY, true);
                this.redraw();
            }
        },
        mouseUp(e) {
            this.storeDraw();
            this.paint = false;
        },
        addClick(x, y, dragging) {
            this.clickX.push(x);
            this.clickY.push(y);
            this.clickDrag.push(dragging);
            this.clickColor.push(this.currentColorRGBA);
            this.clickSize.push(this.currentSize);
            this.clickTool.push(this.currentTool);
            if (this.currentTool === 'text') {
                this.enableTextInput(x, y);
            } else {
                this.Text.push('');
            }
        },
        undo() {
            let dragging = this.clickDrag[this.clickDrag.length - 1];
            this.clickX.pop();
            this.clickY.pop();
            this.clickDrag.pop();
            this.clickColor.pop();
            this.clickSize.pop();
            this.clickTool.pop();
            if (this.write) {
                this.textInput = false;
                this.write = false;
            } else {
                this.Text.pop('');
            }
            if (dragging) {
                this.undo();
            }
            this.redraw();
        },
        enableTextInput(x, y) {
            let view = this;
            let fontsize = this.currentSize * 6;
            this.textInput = true;
            let input = this.$refs.textInputField;
            input.value = '';
            input.style.position = 'absolute';
            input.style.top = this.$refs.canvas.offsetTop + y + 'px';
            input.style.left = 320 + x + 'px';
            input.style.lineHeight = fontsize + 'px';
            input.style.fontSize = fontsize + 'px';
            input.style.width = '300px';
            window.setTimeout(function () {
                view.$refs.textInputField.focus();
            }, 0);
        },
        textInputKeyUp(e) {
            if (e.defaultPrevented) {
                return;
            }
            let key = e.key || e.keyCode;
            if (key === 'Enter' || key === 13) {
                this.Text.push(this.$refs.textInputField.value);
                this.textInput = false;
                this.write = false;
                this.redraw();
            }
            if (key === 'Escape' || key === 'Esc' || key === 27) {
                this.clickX.pop();
                this.clickY.pop();
                this.clickDrag.pop();
                this.clickColor.pop();
                this.clickSize.pop();
                this.clickTool.pop();
                this.textInput = false;
                this.write = false;
            }
        },
        async storeDraw() {
            let data = {};
            data.type = 'courseware-user-data-fields';
            data.id = this.block.relationships['user-data-field'].data.id;
            data.relationships = {};
            data.relationships.block = {};
            data.relationships.block.data = {};
            data.relationships.block.data.id = this.block.id;
            data.relationships.block.data.type = this.block.type;
            data.attributes = {};
            data.attributes.payload = {};
            data.attributes.payload.canvas_draw = {};
            data.attributes.payload.canvas_draw.clickX = JSON.stringify(this.clickX);
            data.attributes.payload.canvas_draw.clickY = JSON.stringify(this.clickY);
            data.attributes.payload.canvas_draw.clickDrag = JSON.stringify(this.clickDrag);
            data.attributes.payload.canvas_draw.clickColor = JSON.stringify(this.clickColor);
            data.attributes.payload.canvas_draw.clickSize = JSON.stringify(this.clickSize);
            data.attributes.payload.canvas_draw.clickTool = JSON.stringify(this.clickTool);
            data.attributes.payload.canvas_draw.Text = JSON.stringify(this.Text);

            await this.$store.dispatch('courseware-user-data-fields/update', data);
        },
        storeBlock() {
            let attributes = {};
            attributes.payload = {};
            attributes.payload.title = this.currentTitle;
            attributes.payload.image = this.currentImage;
            if (this.currentImage === 'true') {
                attributes.payload.file_id = this.currentFileId;
            } else {
                attributes.payload.file_id = '';
            }
            attributes.payload.upload_folder_id = this.currentUploadFolderId;
            attributes.payload.show_usersdata = this.currentShowUserData;

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
        async store() {
            let user = this.usersById({id: this.userId});
            let imageBase64 = this.context.canvas.toDataURL("image/jpeg", 1.0);
            let image = await fetch(imageBase64);
            let imageBlob = await image.blob();
            let file = {};
            file.attributes = {};
            if(this.currentImage === 'true') {
                file.attributes.name = (user.attributes["formatted-name"]).replace(/\s+/g, '_') + '_' + this.currentFile.attributes.name;
            } else {
                file.attributes.name = (user.attributes["formatted-name"]).replace(/\s+/g, '_') + '_' + this.block.attributes.title + '_' + this.block.id;
            }

            let img = false;
            try {
                 img = await this.createFile({
                    file: file,
                    filedata: imageBlob,
                    folder: {id: this.currentUploadFolderId}
                });
            }
            catch(e) {
                this.companionError({
                    info: this.$gettext('Es ist ein Fehler aufgetretten! Das Bild konnte nicht gespeichert werden.')
                });
                console.log(e);
            }
            if(img && img.type === 'file-refs') {
                this.companionSuccess({
                    info: this.$gettext('Bild wurde erfolgreich im Dateibereich abgelegt.')
                });
            }
        },
    },
};
</script>
