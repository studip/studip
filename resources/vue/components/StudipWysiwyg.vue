<template>
    <textarea 
        :value="value"
        ref="studip_wysiwyg"
        class="studip-wysiwyg"
        @input="updateValue($event.target.value)"/>
</template>

<script>
// need v-model to provide and get content -> <studip-wysiwyg v-model="content" />
export default {
    name: 'studip-wysiwyg',
    props: {
        value: String
    },
    data() {
        return {
            fallbackActive: false
        }
    },
    mounted() {
        let ckeInit = this.initCKE();
        if (!ckeInit) {
            this.fallbackActive = true;
        }
    },
    methods: {
        initCKE() {
            if (!STUDIP.wysiwyg_enabled) {
                return false;
            }
            let view = this;
            STUDIP.wysiwyg.replace(view.$refs.studip_wysiwyg);
            let wysiwyg_editor = CKEDITOR.instances[view.$refs.studip_wysiwyg.id];
            wysiwyg_editor.on('blur', function() {
                //console.log('cke blur');
            });
            wysiwyg_editor.on('change', function() {
                view.$emit('input', wysiwyg_editor.getData());
            });
            return true;
        },
        updateValue(value) {
            if (this.fallbackActive) {
                this.$emit('input', value);
            }
        }
    },

}
</script>

<style>

</style>