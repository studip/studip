<template>
    <div class="cw-block cw-block-dialog-cards">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="true"
            @storeEdit="storeBlock"
            @closeEdit="initCurrentData"
        >
            <template #content>
                <div class="cw-block-dialog-cards-content">
                    <button class="cw-dialogcards-prev cw-dialogcards-navbutton" @click="prevCard"></button>
                    <div class="cw-dialogcards">
                        <div
                            class="scene scene--card"
                            :class="[card.active ? 'active' : '']"
                            v-for="card in currentCards"
                            :key="card.index"
                        >
                            <div class="card" @click="flipCard">
                                <div class="card__face card__face--front">
                                    <img v-if="card.front_file" :src="card.front_file.download_url" />
                                    <div v-if="!card.front_file" class="cw-dialogcards-front-no-image"></div>
                                    <p>{{ card.front_text }}</p>
                                </div>
                                <div class="card__face card__face--back">
                                    <img v-if="card.back_file" :src="card.back_file.download_url" />
                                    <div v-if="!card.back_file" class="cw-dialogcards-back-no-image"></div>
                                    <p>{{ card.back_text }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button class="cw-dialogcards-next cw-dialogcards-navbutton" @click="nextCard"></button>
                </div>
            </template>
            <template v-if="canEdit" #edit>
                <button class="button add" @click="addCard"><translate>Karte hinzufügen</translate></button>
                <courseware-tabs v-if="currentCards.length > 0" @selectTab="activateCard($event)">
                    <courseware-tab
                        v-for="(card, index) in currentCards"
                        :key="index"
                        :name="index.toString()"
                        :selected="index === 0"
                    > 
                    <form class="default" @submit.prevent="">
                            <label>
                                <translate>Bild Vorderseite</translate>:
                                <courseware-file-chooser
                                    v-model="card.front_file_id"
                                    :isImage="true"
                                    @selectFile="updateFile(index, 'front', $event)"
                                />
                            </label>
                            <label>
                                <translate>Text Vorderseite</translate>:
                                <input type="text" v-model="card.front_text" />
                            </label>
                            <label>
                                <translate>Bild Rückseite</translate>:
                                <courseware-file-chooser
                                    v-model="card.back_file_id"
                                    :isImage="true"
                                    @selectFile="updateFile(index, 'back', $event)"
                                />
                            </label>
                            <label>
                                <translate>Text Rückseite</translate>:
                                <input type="text" v-model="card.back_text" />
                            </label>
                        </form>
                    </courseware-tab>
                </courseware-tabs>
            </template>
            <template #info>
                <p><translate>Informationen zum DialogCards-Block</translate></p>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import CoursewareFileChooser from './CoursewareFileChooser.vue';
import CoursewareTabs from './CoursewareTabs.vue';
import CoursewareTab from './CoursewareTab.vue';

import { mapActions } from 'vuex';

export default {
    name: 'courseware-dialog-cards-block',
    components: {
        CoursewareDefaultBlock,
        CoursewareFileChooser,
        CoursewareTabs,
        CoursewareTab,
    },
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            currentCards: [],
        };
    },
    computed: {
        cards() {
            return this.block?.attributes?.payload?.cards;
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
            if (this.cards !== '') {
                this.currentCards = JSON.parse(JSON.stringify(this.cards));
            }
            this.activateCard(0);
        },
        storeBlock() {
            let cards = JSON.parse(JSON.stringify(this.currentCards));
            // don't store the file object
            cards.forEach((card) => {
                delete card.front_file;
                delete card.back_file;
            });
            let attributes = {};
            attributes.payload = {};
            attributes.payload.cards = cards;

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
        updateFile(cardIndex, side, file) {
            if (side === 'front') {
                this.currentCards[cardIndex].front_file_id = file.id;
                this.currentCards[cardIndex].front_file = file;
            }
            if (side === 'back') {
                this.currentCards[cardIndex].back_file_id = file.id;
                this.currentCards[cardIndex].back_file = file;
            }
        },
        addCard() {
            this.currentCards.push({
                index: this.currentCards.length,
                front_file_id: '',
                front_text: '',
                back_file_id: '',
                back_text: '',
            });
        },

        flipCard(event) {
            event.currentTarget.classList.toggle('is-flipped');
        },
        nextCard() {
            let view = this;
            this.currentCards.every((card, index) => {
                if (card.active) {
                    if (view.currentCards.length > index + 1) {
                        card.active = false;
                        view.currentCards[index + 1].active = true;
                    }
                    return false; // end every
                } else {
                    return true; // continue every
                }
            });
        },
        prevCard() {
            let view = this;
            this.currentCards.every((card, index) => {
                if (card.active) {
                    if (index > 0) {
                        card.active = false;
                        view.currentCards[index - 1].active = true;
                    }
                    return false; // end every
                } else {
                    return true; // continue every
                }
            });
        },
        activateCard(selectedIndex) {
            selectedIndex = parseInt(selectedIndex);
            if (selectedIndex > this.currentCards.length - 1) {
                console.log('can not select this card');
                return false;
            }
            this.currentCards.forEach((card, index) => {
                if (index === selectedIndex) {
                    card.active = true;
                } else {
                    card.active = false;
                }
            });
        },
    },
};
</script>
