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
import Sidebar from "../../assets/javascripts/lib/sidebar.js";
import MyCoursesMixin from '../mixins/MyCoursesMixin.js';

export default {
    name: 'my-courses-sidebar-switch',
    mixins: [MyCoursesMixin],
    computed: {
        tableView () {
            return this.getConfig(this.viewConfig) === 'tables';
        },
        tilesView () {
            return this.getConfig(this.viewConfig) === 'tiles';
        },
    },
    methods: {
        setTableView () {
            this.setView('tables');
        },
        setTilesView () {
            this.setView('tiles');
        },
        setView (view) {
            this.updateConfigValue({
                key: this.viewConfig,
                value: view
            }).then(() => {
                Sidebar.close();
            });
        }
    },
};
</script>
