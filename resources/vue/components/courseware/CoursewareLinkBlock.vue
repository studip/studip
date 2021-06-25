<template>
    <div class="cw-block cw-block-link">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="true"
            @storeEdit="storeBlock"
            @closeEdit="initCurrentData"
        >
            <template #content>
                <div v-if="currentType === 'external'">
                    <a :href="currentUrl" target="_blank">
                        <div class="cw-link external">
                            <span class="cw-link-title">{{ currentTitle }}</span>
                        </div>
                    </a>
                </div>
                <div v-if="currentType === 'internal'">
                    <router-link :to="'/structural_element/' + currentTarget">
                        <div class="cw-link internal">
                            <span class="cw-link-title">
                                {{ currentTitle }}
                            </span>
                        </div>
                    </router-link>
                </div>
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        <translate>Titel</translate>
                        <input type="text" v-model="currentTitle" />
                    </label>
                    <label>
                        <translate>Art des Links</translate>
                        <select v-model="currentType">
                            <option value="external"><translate>Extern</translate></option>
                            <option value="internal"><translate>Intern</translate></option>
                        </select>
                    </label>
                    <label v-show="currentType === 'external'">
                        <translate>URL</translate>
                        <input type="text" v-model="currentUrl" @change="fixUrl" />
                    </label>
                    <label v-show="currentType === 'internal'">
                        <translate>Seite</translate>
                        <select v-model="currentTarget">
                            <option v-for="(el, index) in courseware" :key="index" :value="el.id">
                                {{ el.attributes.title }}
                            </option>
                        </select>
                    </label>
                </form>
            </template>
            <template #info>
                <p><translate>Informationen zum Link-Block</translate></p>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-link-block',
    components: {
        CoursewareDefaultBlock,
    },
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            currentType: '',
            currentTarget: '',
            currentUrl: '',
            currentTitle: '',
        };
    },
    computed: {
        ...mapGetters({
            courseware: 'courseware-structural-elements/all',
        }),
        type() {
            return this.block?.attributes?.payload?.type;
        },
        target() {
            return this.block?.attributes?.payload?.target;
        },
        url() {
            return this.block?.attributes?.payload?.url;
        },
        title() {
            return this.block?.attributes?.payload?.title;
        },
    },
    mounted() {
        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
            companionWarning: 'companionWarning',
        }),
        initCurrentData() {
            this.currentType = this.type;
            this.currentTarget = this.target;
            this.currentUrl = this.url;
            this.currentTitle = this.title;
            this.fixUrl();
        },
        fixUrl() {
            if (
                this.currentUrl.indexOf('http://') !== 0 &&
                this.currentUrl.indexOf('https://') !== 0 &&
                this.currentUrl !== ''
            ) {
                this.currentUrl = 'https://' + this.currentUrl;
            }
        },
        storeBlock() {
            let attributes = {};
            attributes.payload = {};
            attributes.payload.type = this.currentType;
            attributes.payload.target = this.currentTarget;
            attributes.payload.url = this.currentUrl;
            attributes.payload.title = this.currentTitle;
            if (this.currentType === 'internal' && this.currentTarget === '') {
                this.companionWarning({
                    info: this.$gettext('Bitte wählen Sie eine Seite als Ziel aus')
                });
                return false;
            } else {
                this.updateBlock({
                    attributes: attributes,
                    blockId: this.block.id,
                    containerId: this.block.relationships.container.data.id,
                });
            }

        },
    },
};
</script>
