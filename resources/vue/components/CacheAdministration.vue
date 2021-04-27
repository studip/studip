<template>
    <form class="default" :action="actionUrl" method="post" ref="configForm">
        <fieldset>
            <legend>
                <translate>Cachetyp</translate>
            </legend>
            <label>
                <translate>Cachetyp auswählen</translate>

                <select name="cachetype" v-model="selectedCacheType" @change="getCacheConfig">
                    <option v-for="(type) in cacheTypes" :key="type.cache_id" :value="type.class_name">
                        {{ type.display_name }}
                    </option>
                </select>
            </label>
        </fieldset>

        <fieldset>
            <legend>Konfiguration</legend>
            <template v-if="configComponent != null">
                <component :is="configComponent" v-bind="configProps" ref="cacheConfig" @is-valid="setValid"></component>
            </template>
            <template v-else>
                <translate>Für diesen Cachetyp ist keine Konfiguration erforderlich.</translate>
            </template>
        </fieldset>
        <footer data-dialog-button>
            <button class="button accept" @click.prevent="validateConfig" :disabled="!isValid">
                <translate>Speichern</translate>
            </button>
        </footer>
    </form>
</template>

<script>
import FileCacheConfig from './FileCacheConfig.vue'
import MemcachedCacheConfig from './MemcachedCacheConfig.vue'
import RedisCacheConfig from './RedisCacheConfig.vue'

export default {
    name: 'CacheAdministration',
    components: {
        FileCacheConfig,
        MemcachedCacheConfig,
        RedisCacheConfig
    },
    props: {
        cacheTypes: {
            type: Array,
            required: true
        },
        currentCache: {
            type: String,
            required: true
        },
        currentConfig: {
            type: Object,
            default: {
                component: null,
                props: []
            }
        }
    },
    data () {
        return {
            isValid: true,
            selectedCacheType: this.currentCache,
            configComponent: this.currentConfig.component,
            configProps: this.currentConfig.props
        }
    },
    computed: {
        actionUrl () {
            return STUDIP.URLHelper.getURL('dispatch.php/admin/cache/store_settings');
        }
    },
    methods: {
        /**
         * Fetches configuration template for selected cache
         * @param event
         */
        getCacheConfig (event) {
            fetch(STUDIP.URLHelper.getURL(`dispatch.php/admin/cache/get_config/${this.selectedCacheType}`))
                .then((response) => {
                    if (!response.ok) {
                        throw response
                    }

                    response.json()
                        .then((json) => {
                            this.configComponent = json.component
                            this.configProps = json.props
                        }).catch((error) => {
                            console.error(error)
                            console.error(error.status + ': ', error.statusText)
                        })
                }).catch((error) => {
                    console.error(error)
                    console.error(error.status + ': ', error.statusText)
                })
        },
        validateConfig () {
            if (this.configComponent == null || this.isValid) {
                this.$refs.configForm.submit()
            }
        },
        setValid (state) {
            this.isValid = state;
        }
    },
    watch: {
        configComponent () {
            this.isValid = true;
        }
    }
}
</script>
