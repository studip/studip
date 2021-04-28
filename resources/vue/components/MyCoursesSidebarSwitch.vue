<template>
    <ul class="widget-list widget-links sidebar-views">
        <li :class="{ active: tableView }">
            <a href="#" @click.prevent="setTableView">
                <translate>Tabellarische Ansicht</translate>
            </a>
        </li>
        <li :class="{ active: tilesView }">
            <a href="#" @click.prevent="setTilesView">
                <translate>Kachelansicht</translate>
            </a>
        </li>
    </ul>
</template>

<script>
import MyCoursesMixin from '../mixins/MyCoursesMixin.js';

export default {
    name: 'my-courses-sidebar-switch',
    mixins: [MyCoursesMixin],
    computed: {
        tableView () {
            return this.getConfig('display_type') === 'tables';
        },
        tilesView () {
            return this.getConfig('display_type') === 'tiles';
        },
    },
    methods: {
        setTableView () {
            this.updateConfigValue({ key: 'display_type', value: 'tables' });
        },
        setTilesView () {
            this.updateConfigValue({ key: 'display_type', value: 'tiles' });
        },
    },
};
</script>
