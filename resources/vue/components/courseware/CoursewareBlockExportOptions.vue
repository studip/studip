<template>
    <section class="cw-block-export-options">
        <header><translate>Export Options</translate></header>
        <div class="cw-block-features-content">
            <button class="button" @click="$emit('close')"><translate>Schließen</translate></button>
        </div>
    </section>
</template>

<script>
export default {
    name: 'courseware-block-export-options',
    components: {},
    props: {
        block: Object,
    },
};
</script>
