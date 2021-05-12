<template>
    <div class="block-helper">
        <courseware-companion-box :msgCompanion="currentQuestion.text" :mood="companionMood"/>
        <div v-if="showBlocks" class="cw-block-helper-results">
            <courseware-blockadder-item
                v-for="(block, index) in selectedBlockTypes"
                :key="index"
                :title="block.title"
                :type="block.type"
                :description="block.description"
                @blockAdded="resetQuestions"
            />
        </div>
        <div class="cw-block-helper-buttons">
            <button
                v-for="(response, index) in currentQuestion.responses"
                class="button"
                :key="index"
                @click="setQuestion(response.answer)"
            >
                {{ response.text }}
            </button>

            <button v-if="currentQuestionId !== 'a'" class="button cw-block-helper-reset" @click="resetQuestions">
                zurücksetzen
            </button>
        </div>
    </div>
</template>

<script>
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';
import CoursewareBlockadderItem from './CoursewareBlockadderItem.vue';
import { mapGetters } from 'vuex';

export default {
    name: 'courseware-block-helper',
    components: {
        CoursewareCompanionBox,
        CoursewareBlockadderItem,
    },

    data() {
        return {
            companionMood: 'pointing',
            questions: [
                {
                    id: 'a',
                    text: this.$gettext(
                        'Ich helfe Ihnen bei der Auswahl des richtigen Blocks. Beantworten Sie mir einfach ein paar Fragen. Meine Vorschläge werden dann hier anzeigen.'
                    ),
                    responses: [
                        {
                            text: this.$gettext('Ok'),
                            answer: 'b',
                        },
                    ],
                    blockChooser: false,
                },
                {
                    id: 'b',
                    text: this.$gettext('Kommt der Inhalt von einer anderen Plattform, z.B. Youtube?'),
                    responses: [
                        {
                            text: this.$gettext('Ja'),
                            answer: 'c',
                        },
                        {
                            text: this.$gettext('Nein'),
                            answer: 'd',
                        },
                    ],
                    blockChooser: false,
                },
                {
                    id: 'c',
                    text: this.$gettext('Prima! Hier sind meine Vorschläge.'),
                    responses: [],
                    blockChooser: { category: 'external', fileType: false },
                },
                {
                    id: 'd',
                    text: this.$gettext('Enthält der Inhalt eine oder mehrere Dateien?'),
                    responses: [
                        {
                            text: this.$gettext('Ja'),
                            answer: 'e',
                        },
                        {
                            text: this.$gettext('Nein'),
                            answer: 'f',
                        },
                    ],
                    blockChooser: false,
                },
                {
                    id: 'f',
                    text: this.$gettext('Handelt es sich bei dem Inhalt hauptsächlich um Text?'),
                    responses: [
                        {
                            text: this.$gettext('Ja'),
                            answer: 'g',
                        },
                        {
                            text: this.$gettext('Nein'),
                            answer: 'h',
                        },
                    ],
                    blockChooser: false,
                },
                {
                    id: 'g',
                    text: this.$gettext('Prima! Hier sind meine Vorschläge.'),
                    responses: [],
                    blockChooser: { category: 'text', fileType: false },
                },
                {
                    id: 'h',
                    text: this.$gettext('Prima! Hier sind meine Vorschläge.'),
                    responses: [],
                    blockChooser: { category: 'layout', fileType: false },
                },
                {
                    id: 'e',
                    text: this.$gettext('Um weleche Art von Datei(en) handelt es sich?'),
                    responses: [
                        {
                            text: this.$gettext('Audio'),
                            answer: 'i',
                        },
                        {
                            text: this.$gettext('Bild'),
                            answer: 'j',
                        },
                        {
                            text: this.$gettext('Dokument'),
                            answer: 'k',
                        },
                        {
                            text: this.$gettext('Video'),
                            answer: 'l',
                        },
                        {
                            text: this.$gettext('beliebig'),
                            answer: 'm',
                        },
                    ],
                    blockChooser: false,
                },
                {
                    id: 'i',
                    text: this.$gettext('Prima! Hier sind meine Vorschläge.'),
                    responses: [],
                    blockChooser: { category: false, fileType: 'audio' },
                },
                {
                    id: 'j',
                    text: this.$gettext('Prima! Hier sind meine Vorschläge.'),
                    responses: [],
                    blockChooser: { category: false, fileType: 'image' },
                },
                {
                    id: 'k',
                    text: this.$gettext('Prima! Hier sind meine Vorschläge.'),
                    responses: [],
                    blockChooser: { category: false, fileType: 'document' },
                },
                {
                    id: 'l',
                    text: this.$gettext('Prima! Hier sind meine Vorschläge.'),
                    responses: [],
                    blockChooser: { category: false, fileType: 'video' },
                },
                {
                    id: 'm',
                    text: this.$gettext('Prima! Hier sind meine Vorschläge.'),
                    responses: [],
                    blockChooser: { category: false, fileType: 'all' },
                },
            ],
            currentQuestionId: 'a',
            showBlocks: false,
            selectedBlockTypes: [],
        };
    },
    computed: {
        ...mapGetters({ blockTypes: 'blockTypes' }),
        currentQuestion() {
            let question = {};
            let view = this;
            this.questions.forEach((q) => {
                if (q.id === view.currentQuestionId) {
                    question = q;
                }
            });
            return question;
        },
    },
    methods: {
        blockChooser(choice) {
            if (choice.category) {
                this.setSelectedBlockTypesByCategory(choice.category);
                this.showBlocks = true;
            } else if (choice.fileType) {
                this.setSelectedBlockTypesByFileTypes(choice.fileType);
                this.showBlocks = true;
            }
        },
        setQuestion(q) {
            this.currentQuestionId = q;
            if(this.currentQuestion.responses.length === 0) {
                this.companionMood= 'special';
            } else {
                this.companionMood= 'unsure';
            }
        },
        setSelectedBlockTypesByCategory(cat) {
            this.selectedBlockTypes = [];

            this.blockTypes.forEach((block) => {
                if (block.categories.includes(cat)) {
                    this.selectedBlockTypes.push(block);
                }
            });
            this.selectedBlockTypes.sort((a, b) => {
                return a.title > b.title ? 1 : b.title > a.title ? -1 : 0;
            });
        },
        setSelectedBlockTypesByFileTypes(type) {
            this.selectedBlockTypes = [];

            this.blockTypes.forEach((block) => {
                if (type === 'all' && block.file_types.length > 0) {
                    this.selectedBlockTypes.push(block);
                } else if (block.file_types.includes(type)) {
                    this.selectedBlockTypes.push(block);
                }
            });
            this.selectedBlockTypes.sort((a, b) => {
                return a.title > b.title ? 1 : b.title > a.title ? -1 : 0;
            });
        },
        resetQuestions() {
            this.currentQuestionId = 'a';
            this.showBlocks = false;
            this.selectedBlockTypes = [];
            this.companionMood= 'pointing';
        },
    },
    watch: {
        currentQuestionId() {
            this.blockChooser(this.currentQuestion.blockChooser);
        },
    },
};
</script>
