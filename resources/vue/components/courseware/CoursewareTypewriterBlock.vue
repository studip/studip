<template>
    <div class="cw-block cw-block-typewriter">
        <courseware-default-block
        :block="block"
        :canEdit="canEdit"
        :isTeacher="isTeacher"
        :preview="true"
        @storeEdit="storeText"
        @closeEdit="closeEdit"
        >
            <template #content>
                <div class="cw-typewriter-content">
                    <transition-group tag="p" class="cw-typewriter" :class="[currentFont, currentSize]"  @enter="enter">
                        <span
                            v-show="startTyping"
                            v-for="(letter, index) in letters"
                            :key="index"
                            class="cw-typewriter-letter"
                            :class="[speedClasses[currentSpeed], { 'cw-typewriter-letter-fadein': typing }]"
                            :data-index="index"
                            >{{ letter }}</span
                        >
                    </transition-group>
                </div>
            </template>
            <template v-if="canEdit" #edit>
                <label class="cw-typewriter-content-label">
                    <translate>Text</translate>
                    <textarea v-model="currentText" name="cw-typewriter-content" class="cw-typewriter-content"></textarea>
                </label>

                <label class="cw-typewriter-speed-label">
                    <translate>Geschwindigkeit</translate>
                    <select v-model="currentSpeed" class="cw-typewriter-speed" name="cw-typewriter-speed">
                        <option value="0"><translate>Langsam</translate></option>
                        <option value="1"><translate>Normal</translate></option>
                        <option value="2"><translate>Schnell</translate></option>
                        <option value="3"><translate>Sehr schnell</translate></option>
                    </select>
                </label>

                <label class="cw-typewriter-font-label">
                    <translate>Schriftart</translate>
                    <select v-model="currentFont" class="cw-typewriter-font" name="cw-typewriter-font">
                        <option value="font-default"><translate>Standard</translate></option>
                        <option value="font-typewriter">Lucida Sans Typewriter</option>
                        <option value="font-trebuchet">Trebuchet MS</option>
                        <option value="font-tahoma">Tahoma</option>
                        <option value="font-georgia">Georgia</option>
                        <option value="font-narrow">Arial Narrow</option>
                    </select>
                </label>

                <label class="cw-typewriter-size-label">
                    <translate>Schriftgröße</translate>
                    <select v-model="currentSize" class="cw-typewriter-size" name="cw-typewriter-size">
                        <option value="size-default">100%</option>
                        <option value="size-tall">150%</option>
                        <option value="size-grande">200%</option>
                        <option value="size-huge">400%</option>
                    </select>
                </label>
            </template>
            <template #info>
                <p><translate>Informationen zum Schreibmaschinen-Block</translate></p>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import { mapActions } from 'vuex';

export default {
    name: 'courseware-typewriter-block',
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
            speeds: [200, 100, 50, 25],
            typing: false,
            startTyping: false,
            speedClasses: [
                'cw-typewriter-letter-fadein-slow',
                'cw-typewriter-letter-fadein-normal',
                'cw-typewriter-letter-fadein-fast',
                'cw-typewriter-letter-fadein-veryfast',
            ],
            currentText: '',
            currentSpeed: '',
            currentFont: '',
            currentSize: '',
        };
    },
    computed: {
        letters: function () {
            return this.currentText.split('');
        },
        text() {
            return this.block?.attributes?.payload?.text;
        },
        speed() {
            return this.block?.attributes?.payload?.speed;
        },
        font() {
            return this.block?.attributes?.payload?.font;
        },
        size() {
            return this.block?.attributes?.payload?.size;
        }
    },
    mounted() {
        this.initCurrentData();
        this.checkTypingStart();
        window.addEventListener('scroll', this.checkTypingStart);
    },
    beforeDestroy() {
        window.removeEventListener('scroll', this.checkTypingStart);
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
        }),
        initCurrentData() {
            this.currentText = this.text;
            this.currentSpeed = this.speed;
            this.currentFont = this.font;
            this.currentSize = this.size;
        },
        enter(el, done) {
            let view = this;
            let index = el.dataset.index;
            var delay = index * this.speeds[this.currentSpeed];
            setTimeout(function () {
                el.classList.add('cw-typewriter-letter-fadein');
                // set typing true when all letters are typed
                if (parseInt(index) === view.letters.length - 1) {
                    view.typing = true;
                }
                done();
            }, delay);
        },
        checkTypingStart() {
            if (this.isScrolledIntoView(this.$el) && !this.typing) {
                this.startTyping = true;
            }
        },
        isScrolledIntoView(element) {
            const rect = element.getBoundingClientRect();
            return rect.top >= 0 && rect.bottom <= (window.innerHeight || document.documentElement.clientHeight);
        },
        closeEdit() {
            this.initCurrentData();
        },
        storeText() {
            let attributes = {};
            attributes.payload = {};
            attributes.payload.text = this.currentText;
            attributes.payload.speed = this.currentSpeed;
            attributes.payload.font = this.currentFont;
            attributes.payload.size = this.currentSize;

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        }
    },
};
</script>
