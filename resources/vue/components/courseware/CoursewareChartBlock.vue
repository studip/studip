<template>
    <div class="cw-block cw-block-chart">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="true"
            @storeEdit="storeBlock"
            @closeEdit="initCurrentData"
        >
            <template #content>
                <canvas class="cw-chartblock-canvas" ref="chartCanvas" />
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        <translate>Label</translate>
                        <input type="text" v-model="currentLabel" @focusout="buildChart" />
                    </label>
                    <label>
                        <translate>Typ</translate>
                        <select v-model="currentType">
                            <option value="bar"><translate>Säulendiagramm</translate></option>
                            <option value="horizontalBar"><translate>Balkendiagramm</translate></option>
                            <option value="pie"><translate>Kreisdiagramm</translate></option>
                            <option value="doughnut"><translate>Ringdiagramm</translate></option>
                            <option value="polarArea"><translate>Polardiagramm</translate></option>
                            <option value="line"><translate>Liniendiagramm</translate></option>
                        </select>
                    </label>
                    <fieldset v-for="(item, index) in currentContent" :key="index">
                        <legend><translate>Datensatz</translate> {{ index + 1 }}</legend>
                        <label>
                            <translate>Wert</translate>
                            <input type="number" v-model="item.value" @change="buildChart" />
                        </label>
                        <label>
                            <translate>Bezeichnung</translate>
                            <input type="text" v-model="item.label" @focusout="buildChart" />
                        </label>
                        <label>
                            <translate>Farbe</translate>
                            <select v-model="item.color" @change="buildChart">
                                <option value="red"><translate>rot</translate></option>
                                <option value="blue"><translate>blau</translate></option>
                                <option value="yellow"><translate>gelb</translate></option>
                                <option value="green"><translate>grün</translate></option>
                                <option value="purple"><translate>lila</translate></option>
                                <option value="orange"><translate>orange</translate></option>
                                <option value="turquoise"><translate>türkis</translate></option>
                                <option value="grey"><translate>grau</translate></option>
                                <option value="lightgrey"><translate>hellgrau</translate></option>
                                <option value="black"><translate>schwarz</translate></option>
                            </select>
                        </label>
                    </fieldset>
                </form>
                <button class="button add" @click="addItem"><translate>Datensatz hinzufügen</translate></button>
            </template>
            <template #info>
                <p><translate>Informationen zum Chart-Block</translate></p>
            </template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import Chart from 'chart.js';
import { mapActions } from 'vuex';

export default {
    name: 'courseware-chart-block',
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
            currentContent: [],
            currentLabel: '',
            currentType: '',
            colors: {
                red: '192, 57, 43',
                blue: '52, 152, 219',
                yellow: '241, 196, 15',
                green: '46, 204, 113',
                purple: '155, 89, 182',
                orange: '230, 126, 34',
                turquoise: '26, 188, 156',
                grey: '52, 73, 94',
                lightgrey: '149, 165, 166',
                black: '0, 0, 0',
            },
        };
    },
    computed: {
        content() {
            return this.block?.attributes?.payload?.content;
        },
        label() {
            return this.block?.attributes?.payload?.label;
        },
        type() {
            return this.block?.attributes?.payload?.type;
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
            this.currentContent = this.content;
            this.currentLabel = this.label;
            this.currentType = this.type;
        },
        storeBlock() {
            let attributes = {};
            attributes.payload = {};
            attributes.payload.content = this.currentContent;
            attributes.payload.label = this.currentLabel;
            attributes.payload.type = this.currentType;

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },

        addItem() {
            this.currentContent.push({ value: '0', label: '', color: 'blue' });
        },

        buildChart() {
            let ctx = this.$refs.chartCanvas.getContext('2d');
            let type = this.currentType;
            let label = this.currentLabel;
            let labels = [];
            let data = [];
            let backgroundColor = [];
            let borderColor = [];

            this.currentContent.forEach((item) => {
                labels.push(item.label);
                data.push(item.value);
                backgroundColor.push('rgba(' + this.colors[item.color] + ', 0.6)');
                borderColor.push('rgba(' + this.colors[item.color] + ', 1.0)');
            });

            switch (type) {
                case 'bar':
                case 'horizontalBar':
                    new Chart(ctx, {
                        type: type,
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: label,
                                    data: data,
                                    backgroundColor: backgroundColor,
                                    borderColor: borderColor,
                                    borderWidth: 1,
                                },
                            ],
                        },
                        options: {
                            scales: {
                                yAxes: [
                                    {
                                        ticks: {
                                            beginAtZero: true,
                                        },
                                    },
                                ],
                                xAxes: [
                                    {
                                        ticks: {
                                            beginAtZero: true,
                                        },
                                    },
                                ],
                            },
                            legend: {
                                display: false,
                            },
                            title: {
                                display: true,
                                text: label,
                            },
                        },
                    });
                    break;
                case 'pie':
                case 'doughnut':
                case 'polarArea':
                    new Chart(ctx, {
                        type: type,
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    data: data,
                                    backgroundColor: backgroundColor,
                                    borderWidth: 1,
                                },
                            ],
                        },
                        options: {
                            title: {
                                display: true,
                                text: label,
                            },
                        },
                    });
                    break;
                case 'line':
                    new Chart(ctx, {
                        type: type,
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: label,
                                    data: data,
                                    fill: false,
                                    borderWidth: 2,
                                    pointBackgroundColor: borderColor,
                                },
                            ],
                        },
                        options: {
                            title: {
                                display: true,
                                text: label,
                            },
                        },
                    });
                    break;
            }
        },
    },
    watch: {
        currentType() {
            this.buildChart();
        },
    },
};
</script>
