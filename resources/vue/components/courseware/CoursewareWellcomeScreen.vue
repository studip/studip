<template>
    <div class="cw-wellcome-screen">
        <div class="cw-wellcome-screen-keyvisual"></div>
        <header>
            <translate>Willkommen bei Courseware</translate>
        </header>
        <div class="cw-wellcome-screen-actions">
            <a href="https://hilfe.studip.de/help/5.0/de/Basis.Courseware" target="_blank">
                <button class="button"><translate>Mehr über Courseware erfahren</translate></button>
            </a>
            <button class="button" :title="$gettext('Fügt einen Standard-Abschnitt mit einem Text-Block hinzu')" @click="addDefault"><translate>Ersten Inhalt erstellen</translate></button>
            <button class="button" @click="addContainer"><translate>Einen Abschnitt auswählen</translate></button>

        </div>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-wellcome-screen',
    components: {
    },
    props: {},
    data() {
        return{}
    },
    computed: {
        ...mapGetters({
            consumeMode: 'consumeMode'
        }),
    },
    methods: {
        ...mapActions({
            createContainer: 'createContainer',
            createBlock: 'createBlockInContainer',
            coursewareBlockAdder: 'coursewareBlockAdder',
            companionSuccess: 'companionSuccess',
            updateContainer: 'updateContainer',
            lockObject: 'lockObject',
            unlockObject: 'unlockObject',
        }),
        addContainer() {
            this.$store.dispatch('coursewareConsumeMode', false);
            this.$store.dispatch('coursewareViewMode', 'edit');
            this.$store.dispatch('coursewareContainerAdder', true);
            this.$store.dispatch('coursewareShowToolbar', true);
        },
        async addDefault() {
            let attributes = {};
            attributes["container-type"] = 'list';
            attributes.payload = {
                colspan: 'full',
                sections: [{ name: 'Liste', icon: '', blocks: [] }],
            };
            await this.createContainer({ structuralElementId: this.$route.params.id, attributes: attributes });
            let newContainer = this.$store.getters['courseware-containers/lastCreated'];
            await this.lockObject({ id: newContainer.id, type: 'courseware-containers' });
            await this.createBlock({
                container: newContainer,
                section: 0,
                blockType: 'text',
            });
            this.$store.dispatch('coursewareViewMode', 'edit');
            this.$store.dispatch('coursewareConsumeMode', false);
            this.companionSuccess({
                info: this.$gettext('Elemente für Ihren ersten Inhalt wurden angelegt'),
            });
            const newBlock = this.$store.getters['courseware-blocks/lastCreated'];
            newContainer.attributes.payload.sections[0].blocks.push(newBlock.id);
            const structuralElementId = this.$route.params.id
            await this.updateContainer({ container: newContainer, structuralElementId: structuralElementId });
            await this.unlockObject({ id: newContainer.id, type: 'courseware-containers' });
            

        }
    }

}
</script>