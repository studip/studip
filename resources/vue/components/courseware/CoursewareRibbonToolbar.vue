<template>
    <div
        class="cw-ribbon-tools"
        :class="{ unfold: toolsActive, 'cw-ribbon-tools-consume': consumeMode }"
    >
        <div class="cw-ribbon-tool-content" ref="ribbonContent">
            <div class="cw-ribbon-tool-content-nav">
                <ul>
                    <li 
                        tabindex="0"
                        ref="focusPoint"
                        :class="{ active: showContents }"
                        @click="displayTool('contents')"
                    >
                        <translate>Inhaltsverzeichnis</translate>
                    </li>
                    <li
                        v-if="!consumeMode && showEditMode && canEdit"
                        tabindex="0"
                        :class="{ active: showBlockAdder }"
                        @click="displayTool('blockadder')"
                    >
                        <translate>Elemente hinzufügen</translate>
                    </li>
                    <li 
                        v-if="!consumeMode && displaySettings" 
                        tabindex="0" 
                        :class="{ active: showAdmin }"
                        @click="displayTool('admin')"
                    >
                        <translate>Einstellungen</translate>
                    </li>
                </ul>
                <button :title="textClose" class="cw-tools-hide-button" @click="$emit('deactivate')"></button>
            </div>
            <div class="cw-ribbon-tool">
                <courseware-tools-contents v-if="showContents" />
                <courseware-tools-blockadder v-if="showBlockAdder" @scrollTop="scrollTop"/>
                <courseware-tools-admin v-if="showAdmin" />
            </div>
        </div>
    </div>
</template>
<script>
import CoursewareToolsAdmin from './CoursewareToolsAdmin.vue';
import CoursewareToolsBlockadder from './CoursewareToolsBlockadder.vue';
import CoursewareToolsContents from './CoursewareToolsContents.vue';
import { mapGetters } from 'vuex';

export default {
    name: 'courseware-ribbon-toolbar',
    components: {
        CoursewareToolsAdmin,
        CoursewareToolsBlockadder,
        CoursewareToolsContents,
    },
    props: {
        toolsActive: Boolean,
        canEdit: Boolean,
    },
    data() {
        return {
            showContents: true,
            showAdmin: false,
            showBlockAdder: false,
            textClose: this.$gettext('schließen')
        };
    },
    computed: {
        ...mapGetters({
            userIsTeacher: 'userIsTeacher',
            consumeMode: 'consumeMode',
            containerAdder: 'containerAdder',
            adderStorage: 'blockAdder',
            viewMode: 'viewMode',
            context: 'context'
        }),
        showEditMode() {
            return this.viewMode === 'edit';
        },
        displaySettings() {
            return this.context.type === 'courses' && this.isTeacher;
        },
        isTeacher() {
            return this.userIsTeacher;
        },
    },
    methods: {
        displayTool(tool) {
            this.showContents = false;
            this.showAdmin = false;
            this.showBlockAdder = false;

            switch (tool) {
                case 'contents':
                    this.showContents = true;
                    this.disableContainerAdder();
                    break;
                case 'admin':
                    this.showAdmin = true;
                    this.disableContainerAdder();
                    break;
                case 'blockadder':
                    this.showBlockAdder = true;
                    break;
            }
        },
        disableContainerAdder() {
            if (this.containerAdder !== false) {
                this.$store.dispatch('coursewareContainerAdder', false);
            }
        },
        scrollTop() {
            this.$refs.ribbonContent.scrollTop = 0;
        }
    },
    watch: {
        adderStorage(newValue) {
            if (Object.keys(newValue).length !== 0) {
                this.displayTool('blockadder');
            }
        },
        consumeMode(newValue) {
            if (newValue) {
                this.displayTool('contents');
            }
        },
        containerAdder(newValue) {
            if (newValue === true) {
                this.displayTool('blockadder');
            }
        },
        showEditMode(newValue) {
            if (!newValue) {
                this.displayTool('contents');
            }
        }
    },
};
</script>
