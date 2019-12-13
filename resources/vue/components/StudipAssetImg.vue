<template>
    <img :src="url"
         :width="width">
</template>

<script>
    export default {
        name: 'studip-asset-img',
        props: ['width', 'file'],
        computed: {
            url: function () {
                return `${STUDIP.ASSETS_URL}images/${this.file}`;
            }
        }
    }
</script>
