<template>
    <div class="cw-block cw-block-code">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="true"
            @storeEdit="storeBlock"
            @closeEdit="initCurrentData"
        >
            <template #content>
                <pre v-show="currentContent !== ''"  v-highlightjs="currentContent"><code ref="code" :class="[currentLang]"></code></pre>
                <div v-show="currentLang !== ''" class="code-lang">
                    <span>{{ currentLang }}</span>
                </div>
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        <translate>Sprache</translate>
                        <input type="text" v-model="currentLang" />
                    </label>
                    <label>
                        <translate>Quelltext</translate>
                        <textarea v-model="currentContent"></textarea>
                    </label>
                </form>
            </template>
            <template #info>
                <p><translate>Informationen zum Quelltext-Block</translate></p>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import hljs from 'highlight.js';

import { mapActions } from 'vuex';

export default {
    name: 'courseware-code-block',
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
            currentLang: '',
            currentContent: '',
        };
    },
    computed: {
        content() {
            return this.block?.attributes?.payload?.content;
        },
        lang() {
            return this.block?.attributes?.payload?.lang;
        },
    },
    directives: {
        highlightjs: {
            deep: true,
            bind(el, binding) {
                let targets = el.querySelectorAll('code');
                targets.forEach((target) => {
                    if (binding.value) {
                        target.innerHTML = binding.value;
                    }
                    hljs.highlightBlock(target);
                });
            },
            componentUpdated(el, binding) {
                let targets = el.querySelectorAll('code');
                targets.forEach((target) => {
                    if (binding.value) {
                        target.innerHTML = binding.value;
                        hljs.highlightBlock(target);
                    }
                });
            },
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
            this.currentLang = this.lang;
            this.currentContent = this.content;
        },
        storeBlock() {
            let attributes = {};
            attributes.payload = {};
            attributes.payload.lang = this.currentLang;
            attributes.payload.content = this.currentContent;

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
    },
};
</script>
