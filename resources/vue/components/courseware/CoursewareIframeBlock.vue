<template>
    <div class="cw-block cw-block-iframe">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="true"
            @storeEdit="storeBlock"
            @closeEdit="initCurrentData"
        >
            <template #content>
                <div v-if="currentTitle !== ''" class="cw-block-title">{{ currentTitle }}</div>
                <iframe
                    v-show="currentUrl.includes('http')"
                    :src="activeUrl"
                    :height="currentHeight"
                    width="100%"
                    allowfullscreen
                    sandbox="allow-forms allow-popups allow-pointer-lock allow-same-origin allow-scripts"
                />
                <div v-if="currentCcInfo" class="cw-block-iframe-cc-data">
                    <span class="cw-block-iframe-cc" :class="['cw-block-iframe-cc-' + currentCcInfo]"></span>
                    <div class="cw-block-iframe-cc-infos">
                        <p v-if="currentCcWork !== ''"><translate>Werk</translate> {{ currentCcWork }}</p>
                        <p v-if="currentCcAuthor !== ''"><translate>Autor</translate> {{ currentCcAuthor }}</p>
                        <p v-if="currentCcBase !== ''"><translate>Lizenz der Plattform</translate> {{ currentCcBase }}</p>
                    </div>
                </div>
                <div v-show="!currentUrl.includes('http')" :style="{ height: currentHeight + 'px' }"></div>
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        <translate>Titel</translate>
                        <input type="text" v-model="currentTitle" />
                    </label>
                    <label>
                        <translate>URL</translate>
                        <input type="text" v-model="currentUrl" @change="setProtocol" />
                    </label>
                    <label>
                        <translate>Höhe</translate>
                        <input type="number" v-model="currentHeight" min="0" />
                    </label>
                    <label>
                        <translate>Nutzerspezifische ID übergeben</translate>
                        <select v-model="currentSubmitUserId">
                            <option value="false"><translate>Nein</translate></option>
                            <option value="true"><translate>Ja</translate></option>
                        </select>
                    </label>

                    <label v-if="currentSubmitUserId === 'true'">
                        <translate>Name des Übergabeparameters</translate>
                        <input type="text" v-model="currentSubmitParam" />
                    </label>
                    <label v-if="currentSubmitUserId === 'true'">
                        <translate>Zufallszeichen für Verschlüsselung (Salt)</translate>
                        <input type="text" v-model="currentSalt" />
                    </label>
                    <label>
                        <translate>Creative Commons Angaben</translate>
                        <select v-model="currentCcInfo">
                            <option value="false"><translate>Keine</translate></option>
                            <option value="by">(by) <translate>Namensnennung</translate></option>
                            <option value="by-sa">
                                (by-sa) <translate>Namensnennung & Weitergabe unter gleichen Bedingungen</translate>
                            </option>
                            <option value="by-nc">
                                (by-nc) <translate>Namensnennung & Nicht kommerziell</translate>
                            </option>
                            <option value="by-nd">
                                (by-nd) <translate>Namensnennung & Keine Bearbeitung</translate>
                            </option>
                            <option value="by-nc-nd">
                                (by-nc-nd) <translate>Namensnennung & Nicht kommerziell & Keine Bearbeitung</translate>
                            </option>
                            <option value="by-nc-sa">
                                (by-nc-sa)
                                <translate>Namensnennung & Nicht kommerziell & Weitergabe unter gleichen Bedingungen</translate>
                            </option>
                        </select>
                    </label>
                    <label v-if="currentCcInfo !== 'false'">
                        CC <translate>Werk</translate>
                        <input type="text" v-model="currentCcWork" />
                    </label>
                    <label v-if="currentCcInfo !== 'false'">
                        CC <translate>Author</translate>
                        <input type="text" v-model="currentCcAuthor" />
                    </label>
                    <label v-if="currentCcInfo !== 'false'">
                        CC <translate>Lizenz der Plattform</translate>
                        <input type="text" v-model="currentCcBase" />
                    </label>
                </form>
            </template>
            <template #info>
                <p><translate>Informationen zum IFrame-Block</translate></p>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';

import { mapActions, mapGetters } from 'vuex';
import md5 from 'md5';

export default {
    name: 'courseware-iframe-block',
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
            currentTitle: '',
            currentUrl: '',
            currentHeight: '',
            currentSubmitUserId: '',
            currentSubmitParam: '',
            currentSalt: '',
            currentCcInfo: '',
            currentCcWork: '',
            currentCcAuthor: '',
            currentCcBase: '',
        };
    },
    computed: {
        ...mapGetters(['userId']),
        url() {
            return this.block?.attributes?.payload?.url;
        },
        title() {
            return this.block?.attributes?.payload?.title;
        },
        height() {
            return this.block?.attributes?.payload?.height;
        },
        submitUserId() {
            return this.block?.attributes?.payload?.submit_user_id;
        },
        submitParam() {
            return this.block?.attributes?.payload?.submit_param;
        },
        salt() {
            return this.block?.attributes?.payload?.salt;
        },
        ccInfo() {
            return this.block?.attributes?.payload?.cc_info;
        },
        ccWork() {
            return this.block?.attributes?.payload?.cc_work;
        },
        ccAuthor() {
            return this.block?.attributes?.payload?.cc_author;
        },
        ccBase() {
            return this.block?.attributes?.payload?.cc_base;
        },
        activeUrl() {
            if (this.currentSubmitUserId) {
                return this.currentUrl + '?' + this.currentSubmitParam + '=' + md5(this.userId + this.currentSalt);
            } else {
                return this.currentUrl;
            }
        },
    },
    mounted() {
        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
        }),
        initCurrentData() {
            this.currentTitle = this.title;
            this.currentUrl = this.url;
            this.currentHeight = this.height;
            this.currentSubmitUserId = this.submitUserId;
            this.currentSubmitParam = this.submitParam;
            this.currentSalt = this.salt;
            this.currentCcInfo = this.ccInfo;
            this.currentCcWork = this.ccWork;
            this.currentCcAuthor = this.ccAuthor;
            this.currentCcBase = this.ccBase;
            this.setProtocol();
        },
        setProtocol() {
            if (location.protocol === 'https:') {
                if (!this.currentUrl.includes('https:')) {
                    if (this.currentUrl.includes('http:')) {
                        this.currentUrl = this.currentUrl.replace('http', 'https');
                    } else {
                        this.currentUrl = 'https://' + this.currentUrl;
                    }
                }
            } else if (location.protocol === 'http:') {
                if (!this.currentUrl.includes('http:') && !this.currentUrl.includes('https:')) {
                    this.currentUrl = 'http://' + this.currentUrl;
                }
            }
        },

        storeBlock() {
            let attributes = {};
            attributes.payload = {};
            attributes.payload.title = this.currentTitle;
            attributes.payload.url = this.currentUrl;
            attributes.payload.height = this.currentHeight;
            attributes.payload.submit_user_id = this.currentSubmitUserId;
            attributes.payload.submit_param = this.currentSubmitParam;
            attributes.payload.salt = this.currentSalt;
            attributes.payload.cc_info = this.currentCcInfo;
            attributes.payload.cc_work = this.currentCcWork;
            attributes.payload.cc_author = this.currentCcAuthor;
            attributes.payload.cc_base = this.currentCcBase;

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
    },
};
</script>
