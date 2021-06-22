<template>
    <div class="cw-tools-element-adder">
        <ul class="cw-tools-element-adder-tabs">
            <li
                :class="{ 'active': showBlockadder }"
                class="cw-tools-element-adder-tab"
                @click="displayBlockAdder"
            >
                <translate>Blöcke</translate>
            </li>
            <li
                :class="{ 'active': showContaineradder }"
                class="cw-tools-element-adder-tab"
                @click="displayContainerAdder"
            >
                <translate>Abschnitte</translate>
            </li>
        </ul>

        <div v-show="showBlockadder" class="cw-tools cw-tools-blockadder">
            <courseware-collapsible-box :title="textBlockHelper">
                <courseware-block-helper :blockTypes="blockTypes" />
            </courseware-collapsible-box>

            <courseware-collapsible-box :title="textAdderFavs" :open="favoriteBlockTypes.length > 0">
                <div class="cw-element-adder-wrapper" v-if="!showEditFavs">
                    <courseware-companion-box 
                        v-if="favoriteBlockTypes.length === 0"
                        mood="sad"
                        :msgCompanion="textFavsEmpty"
                    />
                    <courseware-blockadder-item
                        v-for="(block, index) in favoriteBlockTypes"
                        :key="index"
                        :title="block.title"
                        :icon="block.icon"
                        :type="block.type"
                        :description="block.description"
                    />
                </div>

                <div class="cw-element-adder-favs-wrapper" v-if="showEditFavs">
                    <div class="cw-element-adder-all-blocks" :class="{ 'fav-edit-active': showEditFavs }">
                        <courseware-blockadder-item
                            v-for="(block, index) in blockTypes"
                            :key="index"
                            :title="block.title"
                            :type="block.type"
                            :description="block.description"
                        />
                    </div>
                    <div class="cw-element-adder-favs">
                        <div
                            v-for="(block, index) in blockTypes"
                            :key="'fav-item-' + index"
                            class="cw-block-fav-item"
                            :class="[isBlockFav(block) ? 'cw-block-fav-item-active' : '']"
                            @click="toggleFavItem(block)"
                        ></div>
                    </div>
                </div>

                <button v-show="!showEditFavs" class="button" @click="showEditFavs = true">
                    <translate>Favoriten bearbeiten</translate>
                </button>
                <button v-show="showEditFavs" class="button" @click="endEditFavs">
                    <translate>Favoriten bearbeiten schließen</translate>
                </button>
            </courseware-collapsible-box>

            <courseware-collapsible-box :title="textAdderAll">
                <div class="cw-element-adder-all-blocks" :class="{ 'fav-edit-active': showEditFavs }">
                    <courseware-blockadder-item
                        v-for="(block, index) in blockTypes"
                        :key="index"
                        :title="block.title"
                        :type="block.type"
                        :description="block.description"
                    />
                </div>
            </courseware-collapsible-box>

            <courseware-collapsible-box
                v-for="(category, index) in blockCategories"
                :key="index"
                :title="category.title"
                :open="category.type === 'basis' && favoriteBlockTypes.length === 0"
            >
                <div v-for="(block, index) in blockTypes" :key="index">
                    <courseware-blockadder-item
                        v-if="block.categories.includes(category.type)"
                        :title="block.title"
                        :icon="block.icon"
                        :type="block.type"
                        :description="block.description"
                    />
                </div>
            </courseware-collapsible-box>
        </div>

        <div v-show="showContaineradder" class="cw-tools cw-tools-containeradder">
            <courseware-collapsible-box
                v-for="(style, index) in containerStyles"
                :key="index"
                :title="style.title"
                :open="index === 0"
            >
                <courseware-container-adder-item
                    v-for="(container, index) in containerTypes"
                    :key="index"
                    :title="container.title"
                    :type="container.type"
                    :colspan="style.colspan"
                    :description="container.description"
                    :firstSection="$gettext('erstes Element')"
                    :secondSection="$gettext('zweites Element')"
                ></courseware-container-adder-item>
            </courseware-collapsible-box>
        </div>
    </div>
</template>

<script>
import CoursewareCollapsibleBox from './CoursewareCollapsibleBox.vue';
import CoursewareBlockadderItem from './CoursewareBlockadderItem.vue';
import CoursewareContainerAdderItem from './CoursewareContainerAdderItem.vue';
import CoursewareBlockHelper from './CoursewareBlockHelper.vue';
import { mapGetters } from 'vuex';
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';

export default {
    name: 'cw-tools-blockadder',
    components: {
        CoursewareCollapsibleBox,
        CoursewareBlockadderItem,
        CoursewareContainerAdderItem,
        CoursewareBlockHelper,
        CoursewareCompanionBox,
    },
    data() {
        return {
            showBlockadder: true,
            showContaineradder: false,
            showEditFavs: false,
            textAdderFavs: this.$gettext('Favoriten'),
            textAdderAll: this.$gettext('Alle Blöcke'),
            textBlockHelper: this.$gettext('Blockassistent'),
            textFavsEmpty: this.$gettext('Sie haben noch keine Lieblingsblöcke ausgewählt.'),
        };
    },
    computed: {
        ...mapGetters({
            adderStorage: 'blockAdder',
            containerAdder: 'containerAdder',
            unorderedBlockTypes: 'blockTypes',
            containerTypes: 'containerTypes',
            favoriteBlockTypes: 'favoriteBlockTypes',
            showToolbar: 'showToolbar',
        }),
        blockTypes() {
            let blockTypes = JSON.parse(JSON.stringify(this.unorderedBlockTypes));
            blockTypes.sort((a, b) => {
                return a.title > b.title ? 1 : b.title > a.title ? -1 : 0;
            });
            return blockTypes;
        },
        containerStyles() {
            return [
                { title: this.$gettext('Standard'), colspan: 'full'},
                { title: this.$gettext('Halbe Breite'), colspan: 'half' },
                { title: this.$gettext('Halbe Breite (zentriert)'), colspan: 'half-center' },
            ];
        },
        blockCategories() {
            return [
                { title: this.$gettext('Standard'), type: 'basis' },
                { title: this.$gettext('Texte'), type: 'text' },
                { title: this.$gettext('Multimedia'), type: 'multimedia' },
                { title: this.$gettext('Aufgaben & Interaktion'), type: 'interaction' },
                { title: this.$gettext('Gestaltung'), type: 'layout' },
                { title: this.$gettext('Dateien'), type: 'files' },
                { title: this.$gettext('Externe Inhalte'), type: 'external' },
            ];
        }
    },
    methods: {
        displayContainerAdder() {
            this.showContaineradder = true;
            this.showBlockadder = false;
        },
        displayBlockAdder() {
            this.showContaineradder = false;
            this.showBlockadder = true;
            this.disableContainerAdder();
        },
        toggleFavItem(block) {
            if (this.isBlockFav(block)) {
                this.$store.dispatch('removeFavoriteBlockType', block.type);
            } else {
                this.$store.dispatch('addFavoriteBlockType', block.type);
            }
        },
        isBlockFav(block) {
            let isFav = false;
            this.favoriteBlockTypes.forEach((type) => {
                if (type.type === block.type) {
                    isFav = true;
                }
            });

            return isFav;
        },
        disableContainerAdder() {
            this.$store.dispatch('coursewareContainerAdder', false);
        },
        endEditFavs() {
            this.showEditFavs = false;
            this.$emit('scrollTop');
        }
    },
    mounted() {
        if (this.containerAdder === true) {
            this.displayContainerAdder();
        }
    },
    watch: {
        adderStorage(newValue) {
            if (Object.keys(newValue).length !== 0) {
                this.displayBlockAdder();
            }
        },
        containerAdder(newValue) {
            if (newValue === true) {
                this.displayContainerAdder();
            }
        },
        showToolbar(newValue, oldValue) {
            if (oldValue === true && newValue === false) {
                this.disableContainerAdder();
            }
        }
    }
};
</script>
