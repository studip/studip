<template>
    <div class="cw-block cw-block-image-map">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="true"
            @storeEdit="storeBlock"
            @closeEdit="initCurrentData"
        >
            <template #content>
                <img :src="currentUrl" class="cw-image-map-original-img" ref="original_img" @load="buildCanvas" />
                <canvas class="cw-image-map-canvas" ref="canvas"></canvas>
                <img
                    class="cw-image-from-canvas"
                    :src="image_from_canvas"
                    ref="image_from_canvas"
                    :usemap="'#' + map_name"
                />
                <map ref="map" :name="map_name">
                    <area
                        v-for="area in areas"
                        :key="area.id"
                        :id="area.id"
                        :shape="area.shape"
                        :coords="area.coords"
                        :title="area.title"
                        :href="area.external_target"
                        :target="area.link_target"
                        @click="
                            if (area.target_type === 'internal') {
                                areaLink(area.internal_target);
                            }
                        "
                    />
                </map>
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        <translate>Bilddatei</translate>
                        <courseware-file-chooser
                            v-model="currentFileId"
                            :isImage="true"
                            @selectFile="updateCurrentFile"
                        />
                    </label>
                    <label>
                        <a class="button add" @click="addShape('arc')"><translate>Kreis hinzufügen</translate></a>
                        <a class="button add" @click="addShape('ellipse')"><translate>Oval hinzufügen</translate></a>
                        <a class="button add" @click="addShape('rect')"><translate>Rechteck hinzufügen</translate></a>
                    </label>
                    <courseware-tabs v-if="currentShapes.length > 0">
                        <courseware-tab
                            v-for="(shape, index) in currentShapes"
                            :key="index"
                            :name="shape.title"
                            :icon="shape.title === '' ? 'link-extern' : ''"
                            :selected="index === 0"
                        >
                            <label>
                                <translate>Farbe</translate>
                                <v-select
                                    :options="colors"
                                    label="name"
                                    :reduce="color => color.class"
                                    :clearable="false"
                                    v-model="shape.data.color"
                                    class="cw-vs-select"
                                >
                                    <template #open-indicator="selectAttributes">
                                        <span v-bind="selectAttributes"><studip-icon shape="arr_1down" size="10"/></span>
                                    </template>
                                    <template #no-options="{ search, searching, loading }">
                                        <translate>Es steht keine Auswahl zur Verfügung</translate>.
                                    </template>
                                    <template #selected-option="{name, rgba}">
                                        <span class="vs__option-color" :style="{'background-color': rgba}"></span><span>{{name}}</span>
                                    </template>
                                    <template #option="{name, rgba}">
                                        <span class="vs__option-color" :style="{'background-color': rgba}"></span><span>{{name}}</span>
                                    </template>
                                </v-select>
                            </label>
                            <label v-if="shape.type === 'arc'" class="cw-block-image-map-dimensions">
                                X: <input type="number" v-model="shape.data.centerX" @change="drawScreen" /> Y:
                                <input type="number" v-model="shape.data.centerY" @change="drawScreen" />
                                <translate>Radius</translate>
                                <input type="number" v-model="shape.data.radius" @change="drawScreen" />
                            </label>
                            <label v-if="shape.type === 'rect'" class="cw-block-image-map-dimensions">
                                X: <input type="number" v-model="shape.data.X" @change="drawScreen" /> Y:
                                <input type="number" v-model="shape.data.Y" @change="drawScreen" />
                                <translate>Höhe</translate>
                                <input type="number" v-model="shape.data.height" @change="drawScreen" />
                                <translate>Breite</translate>
                                <input type="number" v-model="shape.data.width" @change="drawScreen" />
                            </label>
                            <label v-if="shape.type === 'ellipse'" class="cw-block-image-map-dimensions">
                                X: <input type="number" v-model="shape.data.X" @change="drawScreen" /> Y:
                                <input type="number" v-model="shape.data.Y" @change="drawScreen" />
                                <translate>Radius</translate> X:
                                <input type="number" v-model="shape.data.radiusX" @change="drawScreen" />
                                <translate>Radius</translate> Y:
                                <input type="number" v-model="shape.data.radiusY" @change="drawScreen" />
                            </label>
                            <label>
                                <translate>Bezeichnung</translate>
                                <input type="text" v-model="shape.title" />
                            </label>
                            <label>
                                <translate>Beschriftung</translate>
                                <input type="text" v-model="shape.data.text" @change="drawScreen" />
                            </label>
                            <label>
                                <translate>Art des Links</translate>
                                <select v-model="shape.link_type">
                                    <option value="internal"><translate>Interner Link</translate></option>
                                    <option value="external"><translate>Externer Link</translate></option>
                                </select>
                            </label>
                            <label v-if="shape.link_type === 'internal'">
                                <translate>Ziel des Links</translate>
                                <select v-model="shape.target_internal" @change="drawScreen">
                                    <option v-for="(el, index) in courseware" :key="index" :value="el.id">
                                        {{ el.attributes.title }}
                                    </option>
                                </select>
                            </label>
                            <label v-if="shape.link_type === 'external'">
                                <translate>Ziel des Links</translate>
                                <input
                                    type="text"
                                    placeholder="https://www.studip.de"
                                    v-model="shape.target_external"
                                    @change="
                                        drawScreen();
                                        fixUrl(index);
                                    "
                                />
                            </label>
                            <label>
                                <a class="button cancel" @click="removeShape(index)"
                                    ><translate>Form entfernen</translate></a
                                >
                            </label>
                        </courseware-tab>
                    </courseware-tabs>
                </form>
            </template>
            <template #info>
                <p><translate>Informationen zum Verweissensitive-Grafik-Block</translate></p>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import CoursewareFileChooser from './CoursewareFileChooser.vue';
import CoursewareTabs from './CoursewareTabs.vue';
import CoursewareTab from './CoursewareTab.vue';

import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-image-map-block',
    components: {
        CoursewareDefaultBlock,
        CoursewareFileChooser,
        CoursewareTabs,
        CoursewareTab,
    },
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            currentFileId: '',
            currentFile: {},
            currentShapes: {},
            context: {},
            image_from_canvas: '',
            map_name: '',
            areas: [],
            darkColors: ['black', 'darkgrey', 'purple'],
            colors: [
                { name: this.$gettext('Transparent'), class: 'transparent', rgba: 'rgba(0,0,0,0)' },
                { name: this.$gettext('Weiß'), class: 'white', rgba: 'rgba(255,255,255,1)' },
                { name: this.$gettext('Blau'), class: 'blue', rgba: 'rgba(52,152,219,1)' },
                { name: this.$gettext('Grün'), class: 'green', rgba: 'rgba(46,204,113,1)' },
                { name: this.$gettext('Lila'), class: 'purple', rgba: 'rgba(155,89,182,1)' },
                { name: this.$gettext('Rot'), class: 'red', rgba: 'rgba(231,76,60,1)' },
                { name: this.$gettext('Gelb'), class: 'yellow', rgba: 'rgba(254,211,48,1)' },
                { name: this.$gettext('Orange'), class: 'orange', rgba: 'rgba(243,156,18,1)' },
                { name: this.$gettext('Grau'), class: 'grey', rgba: 'rgba(236, 240, 241,1)' },
                { name: this.$gettext('Dunkelgrau'), class: 'darkgrey', rgba: 'rgba(52,73,94,1)' },
                { name: this.$gettext('Schwarz'), class: 'black', rgba: 'rgba(0,0,0,1)' }
            ],
            file: null
        };
    },
    computed: {
        ...mapGetters({
            courseware: 'courseware-structural-elements/all',
            fileRefById: 'file-refs/byId',
            urlHelper: 'urlHelper',
        }),
        fileId() {
            return this.block?.attributes?.payload?.file_id;
        },
        shapes() {
            return this.block?.attributes?.payload?.shapes;
        },
        currentUrl() {
            if (this.currentFile.download_url !== 'undefined') {
                return this.currentFile.download_url;
            } else {
                return '';
            }
        },
    },
    mounted() {
        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
            loadFileRef: 'file-refs/loadById',
        }),
        async initCurrentData() {
            this.currentFileId = this.fileId;
            this.currentShapes = JSON.parse(JSON.stringify(this.shapes));
            await this.loadFile();
            this.buildCanvas();
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
        updateCurrentFile(file) {
            this.currentFile = file;
            this.currentFileId = file.id;
        },
        storeBlock() {
            let attributes = {};
            attributes.payload = {};
            attributes.payload.file_id = this.currentFileId;
            attributes.payload.shapes = this.currentShapes;

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },

        buildCanvas() {
            let canvas = this.$refs.canvas;
            let original_img = this.$refs.original_img;
            canvas.width = 1085;
            if (original_img.height > 0) {
                canvas.height = Math.round((canvas.width / original_img.width) * original_img.height);
            } else {
                canvas.height = 484;
            }
            this.context = canvas.getContext('2d');
            this.drawScreen();
        },
        drawScreen() {
            let context = this.context;
            let view = this;
            let outlineImage = new Image();
            outlineImage.src = this.currentUrl;
            outlineImage.onload = function () {
                context.clearRect(0, 0, context.canvas.width, context.canvas.height); // Clears the canvas
                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, context.canvas.width, context.canvas.height); // set background
                if (outlineImage.src !== '') {
                    context.drawImage(outlineImage, 0, 0, context.canvas.width, context.canvas.height);
                }
                view.drawShapes();

                if (!(view.$refs.canvas.length > 0)) {
                    view.image_from_canvas = view.context.canvas.toDataURL('image/jpeg', 1.0);
                    view.mapImage();
                }
            };
        },
        drawShapes() {
            let context = this.context;
            let view = this;
            this.currentShapes.forEach((value) => {
                let shape = value;
                let text = shape.data.text;
                let shape_width = 0;
                let shape_height = 0;
                let text_X = 0;
                let text_Y = 0;

                context.beginPath();
                switch (shape.type) {
                    case 'arc':
                        shape_width = Math.round((2 * shape.data.radius) / Math.sqrt(2)) * 0.85;
                        shape_height = shape_width / 0.85;
                        text_X = shape.data.centerX;
                        text_Y = shape.data.centerY - shape.data.radius * 0.75;
                        context.arc(shape.data.centerX, shape.data.centerY, shape.data.radius, 0, 2 * Math.PI); // x, y, r, startAngle, endAngle ... Angle in radians!
                        context.fillStyle = view.colors.filter((color) => {return color.class === shape.data.color})[0].rgba;
                        context.fill();
                        break;
                    case 'ellipse':
                        shape_width = shape.data.radiusX;
                        shape_height = shape.data.radiusY * 1.75;
                        text_X = shape.data.X;
                        text_Y = shape.data.Y - shape.data.radiusY * 0.8;
                        context.ellipse(
                            shape.data.X,
                            shape.data.Y,
                            shape.data.radiusX,
                            shape.data.radiusY,
                            0,
                            0,
                            2 * Math.PI
                        );
                        context.fillStyle = view.colors.filter((color) => {return color.class === shape.data.color})[0].rgba;
                        context.fill();
                        break;
                    case 'rect':
                        shape_width = shape.data.width;
                        shape_height = shape.data.height;
                        text_X = shape.data.X + shape.data.width / 2;
                        text_Y = shape.data.Y;
                        context.rect(shape.data.X, shape.data.Y, shape.data.width, shape.data.height);
                        context.fillStyle = view.colors.filter((color) => {return color.class === shape.data.color})[0].rgba;
                        context.fill();
                        break;
                    default:
                        return;
                }

                if (text && shape.data.color !== 'transparent') {
                    text = view.fitTextToShape(context, text, shape_width);
                    context.textAlign = 'center';
                    context.font = '14px Arial';
                    if (view.darkColors.indexOf(shape.data.color) > -1) {
                        context.fillStyle = '#ffffff';
                    } else {
                        context.fillStyle = '#000000';
                    }
                    let lineHeight = shape_height / (text.length + 1);
                    text.forEach((value, key) => {
                        context.fillText(value, text_X, text_Y + lineHeight * (key + 1));
                    });
                }

                context.closePath();
            });
        },
        fitTextToShape(context, text, shape_width) {
            let text_width = context.measureText(text).width;
            if (text_width > shape_width) {
                text = text.split(' ');
                let line = '';
                let word = ' ';
                let new_text = [];
                do {
                    word = text.shift();
                    if (context.measureText(word).width >= shape_width) {
                        return [''];
                    }
                    line = line + word + ' ';
                    if (context.measureText(line).width > shape_width) {
                        text.unshift(word);
                        line = line.substring(0, line.lastIndexOf(word));
                        new_text.push(line.trim());
                        line = '';
                    }
                } while (text.length > 0);
                new_text.push(line.trim());
                return new_text;
            } else {
                return [text];
            }
        },
        mapImage() {
            let view = this;
            // generate map name
            let map_name = 'cw-image-map-' + Math.round(Math.random() * 100);
            this.map_name = map_name;

            // insert areas
            this.areas = [];
            this.currentShapes.forEach((value, key) => {
                let shape = value;
                let area = {};
                area.id = 'shape-' + key;

                switch (shape.type) {
                    case 'arc':
                        area.shape = 'circle';
                        area.coords = shape.data.centerX + ', ' + shape.data.centerY + ', ' + shape.data.radius;
                        break;
                    case 'ellipse':
                        let coords = '';
                        let x = 0;
                        let y = 0;
                        for (let theta = 0; theta < 2 * Math.PI; theta += (2 * Math.PI) / 20) {
                            x = parseInt(shape.data.X) + Math.round(parseInt(shape.data.radiusX) * Math.cos(theta));
                            y = parseInt(shape.data.Y) + Math.round(parseInt(shape.data.radiusY) * Math.sin(theta));
                            coords = coords + x + ',' + y + ',';
                        }
                        area.shape = 'poly';
                        area.coords = coords;
                        break;
                    case 'rect':
                    case 'text':
                        let x2 = parseInt(shape.data.X) + parseInt(shape.data.width);
                        let y2 = parseInt(shape.data.Y) + parseInt(shape.data.height);
                        area.shape = 'rect';
                        area.coords = shape.data.X + ', ' + shape.data.Y + ', ' + x2 + ', ' + y2;
                        break;
                }
                area.title = shape.title;
                shape.link_type === 'external'
                    ? (area.external_target = shape.target_external)
                    : (area.external_target = '#');
                if (shape.link_type === 'internal') {
                    area.internal_target = shape.target_internal;
                } else {
                    area.internal_target = '';
                }
                shape.link_type === 'external' ? (area.link_target = '_blank') : (area.link_target = '_self');
                area.link_type = shape.link_type;
                area.target_type = shape.link_type;
                view.areas.push(area);
            });
        },
        areaLink(target) {
            this.$router.push(target);
        },

        //edit methods
        addShape(addtype) {
            let data = {};
            switch (addtype) {
                case 'arc':
                    data = {
                        centerX: 50,
                        centerY: 50,
                        radius: 50,
                        color: 'blue',
                        border: false,
                        text: '',
                    };
                    break;
                case 'rect':
                    data = {
                        X: 50,
                        Y: 50,
                        height: 100,
                        width: 50,
                        color: 'blue',
                        border: false,
                        text: '',
                    };
                    break;
                case 'ellipse':
                    data = {
                        X: 50,
                        Y: 50,
                        radiusX: 50,
                        radiusY: 20,
                        color: 'blue',
                        border: false,
                        text: '',
                    };
                    break;
            }
            this.currentShapes.push({
                type: addtype,
                data: data,
                title: '',
                link_type: 'external',
                target_internal: '',
                target_external: '',
            });
            this.buildCanvas();
        },
        removeShape(index) {
            this.currentShapes.splice(index, 1);
        },
        fixUrl(index) {
            let url = this.currentShapes[index].target_external;
            if (url !== '' && url.indexOf('http://') !== 0 && url.indexOf('https://') !== 0) {
                url = 'https://' + url;
            }
            this.currentShapes[index].target_external = url;
        },
    },
};
</script>
