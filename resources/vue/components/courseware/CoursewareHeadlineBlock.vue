<template>
    <div class="cw-block cw-block-headline">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="true"
            @storeEdit="storeText"
            @closeEdit="closeEdit"
        >
            <template #content>
                <div
                    class="cw-block-headline-content"
                    :class="[currentStyle, currentHeight === 'half' ? 'half' : 'full']"
                    :style="headlineStyle"
                >
                    <div
                        class="icon-layer"
                        :class="['icon-' + currentIconColor + '-' + currentIcon, currentHeight === 'half' ? 'half' : 'full']"
                    >
                        <div class="cw-block-headline-textbox">
                            <div class="cw-block-headline-title">
                                <h1 :style="textStyle">{{ currentTitle }}</h1>
                            </div>
                            <div class="cw-block-headline-subtitle">
                                <h2 :style="textStyle">{{ currentSubtitle }}</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        <translate>Layout</translate>
                        <select v-model="currentStyle">
                            <option value="heavy"><translate>Große Schrift</translate></option>
                            <option value="ribbon"><translate>Band</translate></option>
                            <option value="bigicon_top"><translate>Großes Icon oben</translate></option>
                            <option value="bigicon_before"><translate>Großes Icon davor</translate></option>
                        </select>
                    </label>
                    <label>
                        <translate>Höhe</translate>
                        <select v-model="currentHeight">
                            <option value="full"><translate>Voll</translate></option>
                            <option value="half"><translate>Halb</translate></option>
                        </select>
                    </label>
                    <label>
                        <translate>Haupttitel</translate>
                        <input type="text" v-model="currentTitle" />
                    </label>
                    <label>
                        <translate>Untertitel</translate>
                        <input type="text" v-model="currentSubtitle" />
                    </label>
                    <label>
                        <translate>Textfarbe</translate>
                        <v-select
                            :options="colors"
                            label="hex"
                            :reduce="color => color.hex"
                            :clearable="false"
                            v-model="currentTextColor"
                            class="cw-vs-select"
                        >
                            <template #open-indicator="selectAttributes">
                                <span v-bind="selectAttributes"><studip-icon shape="arr_1down" size="10"/></span>
                            </template>
                            <template #no-options="{ search, searching, loading }">
                                <translate>Es steht keine Auswahl zur Verfügung</translate>.
                            </template>
                            <template #selected-option="{name, hex}">
                                <span class="vs__option-color" :style="{'background-color': hex}"></span><span>{{name}}</span>
                            </template>
                            <template #option="{name, hex}">
                                <span class="vs__option-color" :style="{'background-color': hex}"></span><span>{{name}}</span>
                            </template>
                        </v-select>
                    </label>
                    <label>
                        <translate>Icon</translate>
                        <v-select :clearable="false" :options="icons" v-model="currentIcon" class="cw-vs-select">
                            <template #open-indicator="selectAttributes">
                                <span v-bind="selectAttributes"><studip-icon shape="arr_1down" size="10"/></span>
                            </template>
                            <template #no-options="{ search, searching, loading }">
                                <translate>Es steht keine Auswahl zur Verfügung</translate>.
                            </template>
                            <template #selected-option="option">
                                <studip-icon :shape="option.label"/> <span class="vs__option-with-icon">{{option.label}}</span>
                            </template>
                            <template #option="option">
                                <studip-icon :shape="option.label"/> <span class="vs__option-with-icon">{{option.label}}</span>
                            </template>
                        </v-select>
                    </label>
                    <label>
                        <translate>Icon-Farbe</translate>
                        <v-select
                            :options="iconColors"
                            label="value"
                            :reduce="iconColor => iconColor.class"
                            :clearable="false"
                            v-model="currentIconColor"
                            class="cw-vs-select"
                        >
                            <template #open-indicator="selectAttributes">
                                <span v-bind="selectAttributes"><studip-icon shape="arr_1down" size="10"/></span>
                            </template>
                            <template #no-options="{ search, searching, loading }">
                                <translate>Es steht keine Auswahl zur Verfügung</translate>.
                            </template>
                            <template #selected-option="{name, hex}">
                                <span class="vs__option-color" :style="{'background-color': hex}"></span><span>{{name}}</span>
                            </template>
                            <template #option="{name, hex}">
                                <span class="vs__option-color" :style="{'background-color': hex}"></span><span>{{name}}</span>
                            </template>
                        </v-select>
                    </label>
                    <label>
                        <translate>Hintergrundtyp</translate>
                        <select v-model="currentBackgroundType">
                            <option value="color"><translate>Farbe</translate></option>
                            <option value="image"><translate>Bild</translate></option>
                        </select>
                    </label>
                    <label  v-if="currentBackgroundType === 'color'">
                        <translate>Hintergrundfarbe</translate>
                        <v-select
                            :options="colors"
                            label="hex"
                            :reduce="color => color.hex"
                            v-model="currentBackgroundColor"
                            :clearable="false"
                            class="cw-vs-select"
                        >
                            <template #open-indicator="selectAttributes">
                                <span v-bind="selectAttributes"><studip-icon shape="arr_1down" size="10"/></span>
                            </template>
                            <template #no-options="{ search, searching, loading }">
                                <translate>Es steht keine Auswahl zur Verfügung</translate>.
                            </template>
                            <template #selected-option="{name, hex}">
                                <span class="vs__option-color" :style="{'background-color': hex}"></span><span>{{name}}</span>
                            </template>
                            <template #option="{name, hex}">
                                <span class="vs__option-color" :style="{'background-color': hex}"></span><span>{{name}}</span>
                            </template>
                        </v-select>
                    </label>
                    <label v-if="currentBackgroundType === 'image'">
                        <translate>Hintergrundbild</translate>
                        <courseware-file-chooser
                            v-model="currentBackgroundImageId"
                            :isImage="true"
                            @selectFile="updateCurrentBackgroundImage"
                        />
                    </label>
                </form>
            </template>
            <template #info><translate>Informationen zum Blickfang-Block</translate></template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import CoursewareFileChooser from './CoursewareFileChooser.vue';
import { mapActions } from 'vuex';
import contentIcons from './content-icons.js';

export default {
    name: 'courseware-headline-block',
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
            currentSubtitle: '',
            currentStyle: '',
            currentHeight: '',
            currentBackgroundColor: '',
            currentTextColor: '',
            currentIcon: '',
            currentIconColor: '',
            currentBackgroundType: '',
            currentBackgroundImageId: '',
            currentBackgroundImage: {},
        };
    },
    computed: {
        title() {
            return this.block?.attributes?.payload?.title;
        },
        subtitle() {
            return this.block?.attributes?.payload?.subtitle;
        },
        style() {
            return this.block?.attributes?.payload?.style;
        },
        height() {
            return this.block?.attributes?.payload?.height;
        },
        backgroundColor() {
            return this.block?.attributes?.payload?.background_color;
        },
        textColor() {
            return this.block?.attributes?.payload?.text_color;
        },
        icon() {
            return this.block?.attributes?.payload?.icon;
        },
        iconColor() {
            return this.block?.attributes?.payload?.icon_color;
        },
        backgroundImageId() {
            return this.block?.attributes?.payload?.background_image_id;
        },
        backgroundImage() {
            return this.block?.attributes?.payload?.background_image;
        },
        backgroundType() {
            return this.block?.attributes?.payload?.background_type;
        },
        complementBackgroundColor() {
            return this.calcComplement(this.backgroundColor);
        },
        icons() {
            return contentIcons;
        },
        colors() {
            const colors = [
                {name: this.$gettext('Schwarz'), class: 'black', hex: '#000000', level: 100, icon: 'black', darkmode: true},
                {name: this.$gettext('Weiß'), class: 'white', hex: '#ffffff', level: 100, icon: 'white', darkmode: false},

                {name: this.$gettext('Blau'), class: 'studip-blue', hex: '#28497c', level: 100, icon: 'blue', darkmode: true},
                {name: this.$gettext('Hellblau'), class: 'studip-lightblue', hex: '#e7ebf1', level: 40, icon: 'lightblue', darkmode: false},
                {name: this.$gettext('Rot'), class: 'studip-red', hex: '#d60000', level: 100, icon: 'red', darkmode: false},
                {name: this.$gettext('Grün'), class: 'studip-green', hex: '#008512', level: 100, icon: 'green', darkmode: true},
                {name: this.$gettext('Gelb'), class: 'studip-yellow', hex: '#ffbd33', level: 100, icon: 'yellow', darkmode: false},
                {name: this.$gettext('Grau'), class: 'studip-gray', hex: '#636a71', level: 100, icon: 'grey', darkmode: true},

                {name: this.$gettext('Holzkohle'), class: 'charcoal', hex: '#3c454e', level: 100, icon: false, darkmode: true},
                {name: this.$gettext('Königliches Purpur'), class: 'royal-purple', hex: '#8656a2', level: 80, icon: false, darkmode: true},
                {name: this.$gettext('Leguangrün'), class: 'iguana-green', hex: '#66b570', level: 60, icon: false, darkmode: true},
                {name: this.$gettext('Königin blau'), class: 'queen-blue', hex: '#536d96', level: 80, icon: false, darkmode: true},
                {name: this.$gettext('Helles Seegrün'), class: 'verdigris', hex: '#41afaa', level: 80, icon: false, darkmode: true},
                {name: this.$gettext('Maulbeere'), class: 'mulberry', hex: '#bf5796', level: 80, icon: false, darkmode: true},
                {name: this.$gettext('Kürbis'), class: 'pumpkin', hex: '#f26e00', level: 100, icon: false, darkmode: true},
                {name: this.$gettext('Sonnenschein'), class: 'sunglow', hex: '#ffca5c', level: 80, icon: false, darkmode: false},
                {name: this.$gettext('Apfelgrün'), class: 'apple-green', hex: '#8bbd40', level: 80, icon: false, darkmode: true},
            ];

            return colors;
        },
        iconColors() {
            const iconColors = [
                {name: this.$gettext('Schwarz'), class: 'black', hex: '#000000'},
                {name: this.$gettext('Weiß'), class: 'white', hex: '#ffffff'},
                {name: this.$gettext('Blau'), class: 'studip-blue', hex: '#28497c'},
                {name: this.$gettext('Rot'), class: 'studip-red', hex: '#d60000'},
                {name: this.$gettext('Grün'), class: 'studip-green', hex: '#008512'},
                {name: this.$gettext('Gelb'), class: 'studip-yellow', hex: '#ffbd33'},
            ];

            return iconColors;
        },
        textStyle() {
            let style = {};
            style.color = this.currentTextColor;

            return style;
        },
        headlineStyle() {
            let style = {};
            if (this.currentBackgroundType === 'color') {
                style['background-color'] = this.currentBackgroundColor;
            }
            if (this.currentBackgroundType === 'image') {
                style['background-color'] = this.currentBackgroundColor;
                style['background-image'] = 'url(' + this.currentBackgroundURL + ')';
            }

            return style;
        },
        currentBackgroundURL() {
            return this.currentBackgroundImage.download_url;
        },
    },
    mounted() {
        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
        }),
        initCurrentData() {
            this.currentTitle = this.title;
            this.currentSubtitle = this.subtitle;
            this.currentStyle = this.style;
            this.currentHeight = this.height;
            this.currentBackgroundColor = this.backgroundColor;
            this.currentTextColor = this.textColor;
            this.currentIcon = this.icon;
            this.currentIconColor = this.iconColor;
            this.currentBackgroundType = this.backgroundType;
            this.currentBackgroundImageId = this.backgroundImageId;
            if (typeof this.backgroundImage === 'object' && !Array.isArray(this.backgroundImage)) {
                this.currentBackgroundImage = this.backgroundImage;
            }
        },
        updateCurrentBackgroundImage(file) {
            this.currentBackgroundImage = file;
            this.currentBackgroundImageId = file.id;
        },
        closeEdit() {
            this.initCurrentData();
        },
        storeText() {
            let attributes = {};
            attributes.payload = {};
            attributes.payload.title = this.currentTitle;
            attributes.payload.subtitle = this.currentSubtitle;
            attributes.payload.style = this.currentStyle;
            attributes.payload.height = this.currentHeight;
            attributes.payload.background_color = this.currentBackgroundColor;
            attributes.payload.text_color = this.currentTextColor;
            attributes.payload.icon = this.currentIcon;
            attributes.payload.icon_color = this.currentIconColor;
            attributes.payload.background_image_id = this.currentBackgroundImageId;
            attributes.payload.background_type = this.currentBackgroundType;

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
        calcComplement(color) {
            let RGB = this.calcRGB(color);

            return '#' + this.compToHex(255 - RGB.r) + this.compToHex(255 - RGB.g) + this.compToHex(255 - RGB.b);
        },
        calcIconColor(color) {
            let RGB = this.calcRGB(color);

            return (RGB.r + RGB.g + RGB.b) / 3 > 129 ? 'black' : 'white';
        },
        calcRGB(color) {
            color = color.slice(1); // remove #
            let val = parseInt(color, 16);
            let r = val >> 16;
            let g = (val >> 8) & 0x00ff;
            let b = val & 0x0000ff;

            if (g > 255) {
                g = 255;
            } else if (g < 0) {
                g = 0;
            }
            if (b > 255) {
                b = 255;
            } else if (b < 0) {
                b = 0;
            }

            return { r: r, g: g, b: b };
        },
        compToHex(comp) {
            let hex = comp.toString(16);
            return hex.length === 1 ? '0' + hex : hex;
        },
    },
};
</script>
