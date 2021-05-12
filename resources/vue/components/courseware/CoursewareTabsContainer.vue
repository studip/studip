<template>
    <courseware-default-container
        :container="container"
        :containerClass="'cw-container-tabs'"
        :canEdit="canEdit"
        :isTeacher="isTeacher"
        @storeContainer="storeContainer"
        @closeEdit="initCurrentData"
    >
        <template v-slot:containerContent>
            <courseware-tabs>
                <courseware-tab
                    v-for="(section, index) in container.attributes.payload.sections"
                    :key="index"
                    :index="index"
                    :name="section.name"
                    :icon="section.icon"
                    :selected="index === 0"
                >
                    <ul class="cw-container-tabs-block-list">
                        <li v-for="block in blocks" :key="block.id" class="cw-block-item">
                            <component
                                v-if="section.blocks.includes(block.id)"
                                :is="component(block)"
                                :block="block"
                                :canEdit="canEdit"
                                :isTeacher="isTeacher"
                            />
                        </li>
                        <li v-if="showEditMode">
                            <courseware-block-adder-area :container="container" :section="index" @updateContainerContent="updateContent"/>
                        </li>
                    </ul>
                </courseware-tab>
            </courseware-tabs>
        </template>
        <template v-slot:containerEditDialog>
            <form class="default cw-container-dialog-edit-form" @submit.prevent="">
                <fieldset v-for="(section, index) in currentContainer.attributes.payload.sections" :key="index">
                    <label>
                        <translate>Title</translate>
                        <input type="text" v-model="section.name" />
                    </label>
                    <label>
                        <translate>Icon</translate>
                        <v-select :options="icons" v-model="section.icon" class="cw-vs-select">
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
                    <label
                        class="cw-container-section-delete"
                        v-if="currentContainer.attributes.payload.sections.length > 1"
                    >
                    <button class="button trash" @click="deleteSection(index)"><translate>Tab löschen</translate></button>
                    </label>
                </fieldset>
            </form>
            <button class="button add" @click="addSection"><translate>Tab hinzufügen</translate></button>
        </template>
    </courseware-default-container>
</template>

<script>
import ContainerComponents from './container-components.js';
import containerMixin from '../../mixins/courseware/container.js';
import contentIcons from './content-icons.js';
import CoursewareTabs from './CoursewareTabs.vue';
import CoursewareTab from './CoursewareTab.vue';
import StudipIcon from './../StudipIcon.vue';

import { mapGetters, mapActions } from 'vuex';

export default {
    name: 'courseware-tabs-container',
    mixins: [containerMixin],
    components: Object.assign(ContainerComponents, {
        CoursewareTabs,
        CoursewareTab,
        StudipIcon,
    }),
    props: {
        container: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            currentContainer: {},
            textDeleteSection: this.$gettext('Sektion entfernen'),
            selectAttributes: {'ref': 'openIndicator', 'role': 'presentation', 'class': 'vs__open-indicator'}
        };
    },
    computed: {
        ...mapGetters({
            blockById: 'courseware-blocks/byId',
        }),
        blocks() {
            if (!this.container) {
                return [];
            }

            return this.container.relationships.blocks.data.map(({ id }) => this.blockById({ id }));
        },
        showEditMode() {
            return this.$store.getters.viewMode === 'edit';
        },
        icons() {
            return contentIcons;
        },
    },
    mounted() {
        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            updateContainer: 'updateContainer',
            unlockObject: 'unlockObject',
        }),
        initCurrentData() {
            // clone container to make edit reversible
            this.currentContainer = JSON.parse(JSON.stringify(this.container));
        },
        addSection() {
            this.currentContainer.attributes.payload.sections.push({ name: '', icon: '', blocks: [] });
        },
        deleteSection(index) {
            if (this.currentContainer.attributes.payload.sections.length === 1) {
                return;
            }
            if (this.currentContainer.attributes.payload.sections[index].blocks.length > 0) {
                if (index === 0) {
                    this.currentContainer.attributes.payload.sections[
                        index + 1
                    ].blocks = this.currentContainer.attributes.payload.sections[index + 1].blocks.concat(
                        this.currentContainer.attributes.payload.sections[index].blocks
                    );
                } else {
                    this.currentContainer.attributes.payload.sections[
                        index - 1
                    ].blocks = this.currentContainer.attributes.payload.sections[index - 1].blocks.concat(
                        this.currentContainer.attributes.payload.sections[index].blocks
                    );
                }
            }
            this.currentContainer.attributes.payload.sections.splice(index, 1);
        },
        async storeContainer() {
            await this.updateContainer({
                container: this.currentContainer,
                structuralElementId: this.currentContainer.relationships['structural-element'].data.id,
            });
            await this.unlockObject({ id: this.container.id, type: 'courseware-containers' });
            this.initCurrentData();
        },
        component(block) {
            return 'courseware-' + block.attributes["block-type"] + '-block';
        },
        updateContent(blockAdder) {
            if(blockAdder.container.id === this.container.id) {
                this.initCurrentData();
            }
        }
    },
};
</script>
