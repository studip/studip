<template>
    <section class="cw-block-info">
        <header><translate>Informationen</translate></header>
        <div class="cw-block-features-content cw-block-info-content">
            <table class="cw-block-info-table">
                <tr>
                    <td><translate>Blockbeschreibung</translate></td>
                    <td><slot name="info" /></td>
                </tr>
                <tr>
                    <td><translate>Block wurde erstellt von</translate></td>
                    <td>{{ owner }}</td>
                </tr>
                <tr>
                    <td><translate>Block wurde erstellt am</translate>:</td>
                    <td><iso-date :date="block.attributes.mkdate" /></td>
                </tr>
                <tr>
                    <td><translate>Zuletzt bearbeitet von</translate>:</td>
                    <td>{{ editor }}</td>
                </tr>
                <tr>
                    <td><translate>Zuletzt bearbeitet am</translate>:</td>
                    <td><iso-date :date="block.attributes.chdate" /></td>
                </tr>
            </table>
            <button class="button" @click="$emit('close')"><translate>Schließen</translate></button>
        </div>
    </section>
</template>

<script>
import IsoDate from './IsoDate.vue';

export default {
    name: 'courseware-block-info',
    components: { IsoDate },
    props: {
        block: Object,
    },
    computed: {
        owner() {
            const owner = this.$store.getters['users/related']({
                parent: this.block,
                relationship: 'owner',
            });

            return owner?.attributes['formatted-name'] ?? '';
        },

        editor() {
            const editor = this.$store.getters['users/related']({
                parent: this.block,
                relationship: 'editor',
            });

            return editor?.attributes['formatted-name'] ?? '';
        },
    },
};
</script>
