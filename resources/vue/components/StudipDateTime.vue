<template>
    <time :datetime="datetime" v-if="timestamp !== 0" :title="title">
        {{ formatted_date() }}
    </time>
</template>

<script>
    function pad(what, length = 2) {
        return `00000000${what}`.substr(-length);
    }

    export default {
        name: 'studip-date-time',
        props: {
            timestamp: Number,
            relative: {
                type: Boolean,
                required: false,
                default: false
            }
        },
        computed: {
            datetime () {
                if (!Number.isInteger(this.timestamp)) {
                    return '';
                }
                let date = new Date(this.timestamp * 1000);
                return date.toISOString();
            },
            title () {
                return this.display_relative() ? this.formatted_date(true) : false;
            }
        },
        methods: {
            display_relative: function () {
                return Date.now() - this.timestamp * 1000 < 12 * 60 * 60 * 1000;
            },
            formatted_date: function (force_absolute = false) {
                if (!Number.isInteger(this.timestamp)) {
                    return `Should be integer: ${this.timestamp}`;
                }
                let date = new Date(this.timestamp * 1000);
                let now = Date.now();
                if (!force_absolute && this.relative && this.display_relative()) {
                    if (now - date < 1 * 60 * 1000) {
                        return this.$gettext('Jetzt');
                    }
                    if (now - date < 2 * 60 * 60 * 1000) {
                        return this.$gettext('Vor %s Minuten').replace('%s', Math.floor((now - date) / (1000 * 60)));
                    }
                    return pad(date.getHours()) + ':' + pad(date.getMinutes());
                } else {
                    return pad(date.getDate()) + '.' + pad(date.getMonth() + 1) + '.' + date.getFullYear() + ' ' + pad(date.getHours()) + ':' + pad(date.getMinutes());
                }
            }
        },
        mounted: function () {
            window.setInterval(() => {
                this.$forceUpdate();
            }, 1000);
        }
    }
</script>
