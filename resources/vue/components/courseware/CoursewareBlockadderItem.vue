<template>
    <div class="cw-blockadder-item" :class="['cw-blockadder-item-' + type]" @click="addBlock">
        <header class="cw-blockadder-item-title">
            {{ title }}
        </header>
        <p class="cw-blockadder-item-description">
            {{ description }}
        </p>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-blockadder-item',
    components: {},
    props: {
        title: String,
        description: String,
        type: String,
    },
    data() {
        return {
            showInfo: false,
        };
    },
    computed: {
        ...mapGetters({
            blockAdder: 'blockAdder',
        }),
    },
    methods: {
        ...mapActions({
            createBlock: 'createBlockInContainer',
            companionInfo: 'companionInfo',
            companionWarning: 'companionWarning',
            companionSuccess: 'companionSuccess',
            updateContainer: 'updateContainer',
            lockObject: 'lockObject',
            unlockObject: 'unlockObject',
        }),
        async addBlock() {
            if (Object.keys(this.blockAdder).length !== 0) {
                // lock parent container
                await this.lockObject({ id: this.blockAdder.container.id, type: 'courseware-containers' });
                // create new block
                await this.createBlock({
                    container: this.blockAdder.container,
                    section: this.blockAdder.section,
                    blockType: this.type,
                });
                //get new Block
                const newBlock = this.$store.getters['courseware-blocks/lastCreated'];
                // update container information -> new block id in sections
                let container = this.blockAdder.container;
                container.attributes.payload.sections[this.blockAdder.section].blocks.push(newBlock.id);
                const structuralElementId = container.relationships['structural-element'].data.id;
                // update container
                await this.updateContainer({ container, structuralElementId });
                // unlock container
                await this.unlockObject({ id: this.blockAdder.container.id, type: 'courseware-containers' });
                this.companionSuccess({
                    info: this.$gettext('Block wurde erfolgreich eingefügt.'),
                });
                this.$emit('blockAdded');
            } else {
                // companion action
                this.companionWarning({
                    info: this.$gettext('Bitte wählen Sie einen Ort aus, an dem der Block eingefügt werden soll.'),
                });
            }
        },
    },
};
</script>

<style></style>
