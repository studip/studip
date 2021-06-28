<template>
    <div class="cw-block cw-block-key-point">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="true"
            @storeEdit="storeBlock"
            @closeEdit="closeEdit"
        >
            <template #content>
                <div class="cw-keypoint-content" :class="['cw-keypoint-' + currentColor]">
                    <studip-icon v-if="currentIcon" size="48" :shape="currentIcon" :role="currentRole"/>
                    <p class="cw-keypoint-sentence">{{currentText}}</p>
                </div>
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label for="cw-keypoint-content">
                        <translate>Merksatz</translate>
                        <input
                            type="text"
                            name="cw-keypoint-content"
                            class="cw-keypoint-set-content"
                            v-model="currentText"
                            spellcheck="true"
                        />
                    </label>

                    <label for="cw-keypoint-color">
                        <translate>Farbe</translate>
                        <v-select
                            :options="colors"
                            label="icon"
                            :clearable="false"
                            :reduce="option => option.icon"
                            v-model="currentColor"
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

                    <label for="cw-keypoint-icons">
                        <translate>Icon</translate>
                        <v-select :options="icons" :clearable="false" v-model="currentIcon" class="cw-vs-select">
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
                </form>
            </template>
            <template #info>
                <p><translate>Informationen zum Merksatz-Block</translate></p>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import { mapActions } from 'vuex';
import contentIcons from './content-icons.js';

export default {
    name: 'courseware-key-point-block',
    components: {
        CoursewareDefaultBlock,
    },
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            currentText: '',
            currentColor: '',
            currentIcon: '',
        };
    },
    computed: {
        file() {
            return `icons/${this.color}/${this.icon}.svg`;
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
            let iconColors = [];

            colors.forEach(color => {
                if(color.icon && color.class !== 'white' && color.class !== 'studip-lightblue') {
                    iconColors.push(color);
                }
            });

            return iconColors;
        },
        text() {
            return this.block?.attributes?.payload?.text;
        },
        color() {
            return this.block?.attributes?.payload?.color;
        },
        icon() {
            return this.block?.attributes?.payload?.icon;
        },
        currentRole() {
            switch (this.currentColor) {
                case 'black':
                    return 'info';

                case 'grey':
                    return 'inactive';

                case 'green':
                    return 'status-green';

                case 'red':
                    return 'status-red';

                case 'white':
                    return 'info_alt';

                case 'yellow':
                    return 'status-yellow';

                case 'blue':
                    return 'clickable';
            }
        }
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
        }),
        initCurrentData() {
            this.currentText = this.text;
            this.currentColor = this.color;
            this.currentIcon = this.icon;
        },
        storeBlock() {
            let attributes = {};
            attributes.payload = {};
            attributes.payload.text = this.currentText;
            attributes.payload.color = this.currentColor;
            attributes.payload.icon = this.currentIcon;

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
        closeEdit() {
            this.initCurrentData();
        },
    },
    mounted() {
        this.initCurrentData();
    },
};
</script>
