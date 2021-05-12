<template>
    <studip-date-time :timestamp="unixTimestamp" />
</template>

<script>
export default {
    name: 'courseware-iso-date',
    props: {
        date: String,
    },
    computed: {
        unixTimestamp() {
            return +new Date(this.date) / 1000;
        },
    },
};
</script>
