<template>
    <div>
        <article v-for="(server, index) in serverConfig" :key="index" class="memcached-server">
            <header>
                <h3>
                    <translate>Memcached-Server</translate> {{ index + 1 }}
                    <studip-icon shape="trash" class="remove-server" @click.prevent="removeServer($event, index)"
                                 :size="24"></studip-icon>
                </h3>
            </header>
            <section class="col-4">
                <label :for="'hostname-' + index">
                    <translate>Hostname</translate>
                </label>
                <input type="text" :name="'servers[' + index + '][hostname]'" :id="'hostname-' + index"
                       placeholder="localhost" :value="server.hostname">
            </section>
            <section class="col-2">
                <label :for="'port-' + index">
                    <translate>Port</translate>
                </label>
                <input type="text" :name="'servers[' + index + '][port]'" :id="'port-' + index"
                       placeholder="11211" :value="server.port">
            </section>
        </article>
        <label class="add-server" @click="addServer">
            <studip-icon shape="add" :size="20"></studip-icon>
            <translate>Server hinzufügen</translate>
        </label>
    </div>
</template>

<script>
export default {
    name: 'MemcachedCacheConfig',
    props: {
        servers: {
            type: Array,
            default: () => []
        }
    },
    data () {
        return {
            serverConfig: this.servers
        }
    },
    methods: {
        addServer () {
            this.serverConfig.push({ server: '', port: null })
        },
        removeServer (event, index) {
            this.serverConfig.splice(index, 1)
        },
        isValid () {
            return this.serverConfig.length > 0;
        }
    },
    watch: {
        servers: {
            handler (current) {
                this.$emit('is-valid', this.isValid());
            },
            immediate: true
        }
    }
}
</script>

<style lang="scss" scoped>
.memcached-server {
    .remove-server {
        vertical-align: text-bottom;
    }
}

.add-server {
    &:not(:only-child) {
        margin-top: 25px;
    }

    img {
        vertical-align: top;
    }
}
</style>
