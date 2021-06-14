<template>
    <div class="cw-block-actions">
        <studip-action-menu 
            :items="menuItems"
            @editBlock="editBlock"
            @setVisibility="setVisibility"
            @showComments="showComments"
            @showFeedback="showFeedback"
            @showInfo="showInfo"
            @deleteBlock="deleteBlock"
        />
    </div>
</template>

<script>
import StudipActionMenu from './../StudipActionMenu.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-block-actions',
    components: {
        StudipActionMenu,
    },
    props: {
        canEdit: Boolean,
        block: Object,
    },
    data() {
        return {
            menuItems: [
                { id: 6, label: this.$gettext('Kommentare anzeigen'), icon: 'comment2', emit: 'showComments' },
            ],
        };
    },
    computed: {
        ...mapGetters({
            userId: 'userId',
        }),
        blocked() {
            return this.block?.relationships['edit-blocker'].data !== null;
        },
        blockerId() {
            return this.blocked ? this.block?.relationships['edit-blocker'].data?.id : null;
        },
    },
    mounted() {
        if (this.canEdit) {
            this.menuItems.push({ id: 1, label: this.$gettext('Block bearbeiten'), icon: 'edit', emit: 'editBlock' });
            this.menuItems.push({
                id: 2,
                label: this.block.attributes.visible
                    ? this.$gettext('unsichtbar setzen')
                    : this.$gettext('sichtbar setzen'),
                icon: this.block.attributes.visible ? 'visibility-visible' : 'visibility-invisible', // do we change the icons ?
                emit: 'setVisibility',
            });
            this.menuItems.push({
                id: 5,
                label: this.$gettext('Feedback anzeigen'),
                icon: 'comment',
                emit: 'showFeedback',
            });
            this.menuItems.push({
                id: 7,
                label: this.$gettext('Informationen zum Block'),
                icon: 'info',
                emit: 'showInfo',
            });
            this.menuItems.push({ 
                id: 9,
                label: this.$gettext('Block löschen'), 
                icon: 'trash',
                emit: 'deleteBlock' 
            });
        }

        this.menuItems.sort((a, b) => {
            return a.id > b.id ? 1 : b.id > a.id ? -1 : 0;
        });
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
            lockObject: 'lockObject',
            unlockObject: 'unlockObject',
        }),
        menuAction(action) {
            this[action]();
        },
        editBlock() {
            this.$emit('editBlock');
        },
        showFeedback() {
            this.$emit('showFeedback');
        },
        showComments() {
            this.$emit('showComments');
        },
        showInfo() {
            this.$emit('showInfo');
        },
        showExportOptions() {
            this.$emit('showExportOptions');
        },
        async setVisibility() {
            if (!this.blocked) {
                await this.lockObject({ id: this.block.id, type: 'courseware-blocks' });
            } else {
                if (this.blockerId !== this.userId) {
                    return false;
                }
            }
            let attributes = {};
            attributes.visible = !this.block.attributes.visible;

            await this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });

            await this.unlockObject({ id: this.block.id, type: 'courseware-blocks' });
        },
        copyToClipboard() {
            // use JSONAPI to copy to clipboard
        },
        deleteBlock() {
            this.$emit('deleteBlock');
        },
    },
};
</script>
