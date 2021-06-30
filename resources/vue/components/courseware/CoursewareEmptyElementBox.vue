<template>
    <div class="cw-wellcome-screen">
        <courseware-companion-box :msgCompanion="this.$gettext('Es wurden bisher noch keine Inhalte eingepflegt.')">
            <template v-slot:companionActions>
                <button v-if="canEdit && noContainers" class="button" @click="addContainer"><translate>Einen Abschnitt hinzufügen</translate></button>
                <button v-if="canEdit && !noContainers && !editMode" class="button" @click="switchToEditView"><translate>Seite bearbeiten</translate></button>
            </template>
        </courseware-companion-box>
    </div>
</template>

<script>
import { mapGetters } from 'vuex';
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';

export default {
    name: 'courseware-empty-element-box',
    components: {
        CoursewareCompanionBox,
    },
    props: {
        canEdit: Boolean,
        noContainers: Boolean
    },
    data() {
        return{}
    },
    computed: {
        ...mapGetters({
            viewMode: 'viewMode'
        }),
        editMode() {
            return this.viewMode === 'edit';
        }
    },
    methods: {
        addContainer() {
            this.$store.dispatch('coursewareViewMode', 'edit');
            this.$store.dispatch('coursewareConsumeMode', false);
            this.$store.dispatch('coursewareContainerAdder', true);
            this.$store.dispatch('coursewareShowToolbar', true);
        },
        switchToEditView() {
            this.$store.dispatch('coursewareViewMode', 'edit');
            this.$store.dispatch('coursewareConsumeMode', false);
        }
    }

}
</script>