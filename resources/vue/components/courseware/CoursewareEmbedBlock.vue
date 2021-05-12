<template>
    <div class="cw-block cw-block-embed" ref="block">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="false"
            @storeEdit="storeBlock"
            @closeEdit="initCurrentData"
        >
            <template #content>
                <div v-if="currentTitle !== ''" class="cw-block-title">{{ currentTitle }}</div>
                <div v-if="oembedData !== null">
                    <div
                        v-if="oembedData.type === 'rich' || oembedData.type === 'video'"
                        v-html="oembedData.html"
                        class="cw-block-embed-iframe-wrapper"
                        :style="{ height: contentHeight + 'px' }"
                    ></div>

                    <div v-if="oembedData.type === 'photo'" :style="{ height: contentHeight + 'px' }">
                        <img :src="oembedData.url" />
                    </div>

                    <div v-if="oembedData.type === 'link' && oembedData.provider_name === 'DeviantArt'">
                        <img :src="oembedData.fullsize_url" />
                    </div>
                </div>
                <div class="cw-block-embed-info" v-if="oembedData !== null">
                    <span class="cw-block-embed-title">{{ oembedData.title }}</span>
                    <span class="cw-block-embed-author-name">
                        <translate>erstellt von</translate>
                        <a :href="oembedData.author_url" target="_blank">{{ oembedData.author_name }}</a></span
                    >
                    <span class="cw-block-embed-source">
                        <translate>veröffentlicht auf</translate>
                        <a :href="oembedData.provider_url" target="_blank">{{ oembedData.provider_name }}</a></span
                    >
                </div>
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        <translate>Überschrift</translate>
                        <input type="text" v-model="currentTitle" />
                    </label>
                    <label>
                        <translate>Quelle</translate>
                        <select v-model="currentSource">
                            <option v-for="(value, key) in endPoints" :key="key" :value="key">{{ key }}</option>
                        </select>
                    </label>
                    <label>
                        <translate>URL</translate>
                        <input type="text" v-model="currentUrl" />
                    </label>
                    <label v-if="currentSource === 'youtube'">
                        <translate>Startpunkt wählen</translate>
                        <input
                            type="time"
                            v-model="currentStartTime"
                            step="1"
                            min="00:00:00"
                            max="24:00:00"
                            @change="updateTime"
                        />
                    </label>
                    <label v-if="currentSource === 'youtube'">
                        <translate>Endpunkt wählen</translate>
                        <input
                            type="time"
                            v-model="currentEndTime"
                            step="1"
                            :min="currentStartTime"
                            max="24:00:00"
                            @change="updateTime"
                        />
                    </label>
                </form>
            </template>
            <template #info>
                <p><translate>Informationen zum Embed-Block</translate></p>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';

import { mapActions } from 'vuex';

export default {
    name: 'courseware-embed-block',
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
            currentSource: '',
            currentUrl: '',
            currentStartTime: '',
            currentEndTime: '',

            endPoints: {
                audiomack: 'https://www.audiomack.com/oembed',
                codepen: 'https://codepen.io/api/oembed',
                codesandbox: 'https://codesandbox.io/oembed',
                deviantart: 'https://backend.deviantart.com/oembed',
                ethfiddle: 'https://ethfiddle.com/services/oembed/',
                flickr: 'https://www.flickr.com/services/oembed/',
                giphy: 'https://giphy.com/services/oembed',
                kidoju: 'https://www.kidoju.com/api/oembed',
                learningapps: 'https://learningapps.org/oembed.php',
                sketchfab: 'https://sketchfab.com/oembed',
                slideshare: 'https://www.slideshare.net/api/oembed/2',
                soundcloud: 'https://soundcloud.com/oembed',
                speakerdeck: 'https://speakerdeck.com/oembed.json',
                sway: 'https://sway.com/api/v1.0/oembed',
                'sway.office': 'https://sway.office.com/api/v1.0/oembed',
                spotify: 'https://embed.spotify.com/oembed/',
                vimeo: 'https://vimeo.com/api/oembed.json',
                youtube: 'https://www.youtube.com/oembed',
            },
            oembedData: {},
            contentHeight: 300,
        };
    },
    computed: {
        url() {
            return this.block?.attributes?.payload?.url;
        },
        source() {
            return this.block?.attributes?.payload?.source;
        },
        title() {
            return this.block?.attributes?.payload?.title;
        },
        startTime() {
            return this.block?.attributes?.payload?.starttime;
        },
        endTime() {
            return this.block?.attributes?.payload?.endtime;
        },
        oembed() {
            return this.block?.attributes?.payload?.oembed;
        },
    },
    mounted() {
        this.initCurrentData();

        window.addEventListener('resize', this.calcContentHeight);
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
        }),
        initCurrentData() {
            this.currentTitle = this.title;
            this.currentSource = this.source;
            this.currentUrl = this.url;
            this.currentStartTime = this.startTime;
            this.currentEndTime = this.endTime;
            this.oembedData = this.oembed;
            if (this.oembedData !== null) {
                this.calcContentHeight();
                this.updateTime();
            }
        },
        addTimeData(data) {
            if (this.currentSource === 'youtube') {
                if (this.currentStartTime !== '') {
                    let start = this.currentStartTime.split(':');
                    let s = parseInt(start[0], 10) * 3600 + parseInt(start[1], 10) * 60 + parseInt(start[2], 10);
                    let query = '?feature=oembed&start=' + s;
                    if (this.currentEndTime !== '') {
                        let end = this.currentEndTime.split(':');
                        let e = parseInt(end[0], 10) * 3600 + parseInt(end[1], 10) * 60 + parseInt(end[2], 10);
                        query = query + '&end=' + e;
                    }
                    data.html = data.html.replace('?feature=oembed', query);
                }
            }
            return data;
        },
        updateTime() {
            this.oembedData = this.addTimeData(this.oembedData);
        },
        validateCurrentSource() {
            var validSource = false;
            let view = this;
            for (const key of Object.keys(this.endPoints)) {
                if (view.currentUrl.includes(key)) {
                    view.currentSource = key;
                    validSource = true;
                    break;
                }
            }

            return validSource;
        },
        calcContentHeight() {
            if (this.oembedData.height && this.oembedData.width) {
                this.contentHeight =
                    ((this.$refs.block.offsetWidth - 4) / this.oembedData.width) * this.oembedData.height;
            }
        },
        storeBlock() {
            let attributes = {};
            attributes.payload = {};
            attributes.payload.title = this.currentTitle;
            attributes.payload.url = this.currentUrl;
            attributes.payload.source = this.currentSource;
            attributes.payload.starttime = this.currentStartTime;
            attributes.payload.endtime = this.currentEndTime;

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
    },
};
</script>
