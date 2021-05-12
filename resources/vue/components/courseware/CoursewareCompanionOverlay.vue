<template>
    <div
        class="cw-companion-overlay"
        :class="[showCompanion ? 'cw-companion-overlay-in' : '', showCompanion ? '' : 'cw-companion-overlay-out', styleCompanion]"
    >
        <div class="cw-companion-overlay-content" v-html="msgCompanion"></div>
        <button class="cw-compantion-overlay-close" @click="hideCompanion"></button>
    </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    name: 'courseware-companion-overlay',
    computed: {
        ...mapGetters({
            showCompanion: 'showCompanionOverlay',
            msgCompanion: 'msgCompanionOverlay',
            styleCompanion: 'styleCompanionOverlay',
            showToolbar: 'showToolbar',
        }),
    },
    methods: {
        hideCompanion() {
            this.$store.dispatch('coursewareShowCompanionOverlay', false);
        },
    },
    watch: {
        showCompanion(newValue, oldValue) {
            let view = this;
            if (newValue === true && oldValue === false) {
                setTimeout(() => {
                    view.hideCompanion();
                }, 4000);
            }
        },
        showToolbar(newValue, oldValue) {
            // hide companion when toolbar is closed 
            if (oldValue === true && newValue === false) {
                this.hideCompanion();
            }
        }
    },
};
</script>
