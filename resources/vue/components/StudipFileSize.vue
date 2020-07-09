<template>
    <span>
        {{ formatted_size }}
    </span>
</template>

<script>
    export default {
        name: 'studip-file-size',
        props: {
            size: Number
        },
        computed: {
            formatted_size () {
                if (this.size < 1024) {
                    return this.size + " Byte";
                }
                if (this.size < 1024 * 1024) {
                    let rel = (this.size / 1024).toFixed(1);
                    return rel + " KB";
                }
                if (this.size < 1024 * 1024 * 1024) {
                    let rel = (this.size / 1024 / 1024).toFixed(1);
                    return rel + " MB";
                }
                if (this.size < 1024 * 1024 * 1024 * 1024) {
                    let rel = (this.size / 1024 / 1024 / 1024).toFixed(1);
                    return rel + " GB";
                }
                if (this.size < 1024 * 1024 * 1024 * 1024 * 1024) {
                    let rel = (this.size / 1024 / 1024 / 1024 / 1024).toFixed(1);
                    return rel + " TB";
                }
            }
        }
    }
</script>
