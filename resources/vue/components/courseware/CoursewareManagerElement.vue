<template>
    <div class="cw-manager-element">
        <div v-if="currentElement">
            <div class="cw-manager-element-title">
                <div class="cw-manager-element-breadcrumb">
                    <span
                        v-for="element in breadcrumb"
                        :key="element.id"
                        class="cw-manager-element-breadcrumb-item"
                        @click="selectChapter(element.id)"
                    >
                        {{ element.attributes.title }}
                    </span>
                </div>
                <header>
                    <span v-if="elementInserterActive && moveSelfPossible && canEdit" @click="insertElement({element: currentElement, source: type})">
                        <studip-icon shape="arr_2left" size="24" role="sort" />
                    </span>
                    {{ elementTitle }}
                </header>
            </div>
            <courseware-collapsible-box
                v-if="canRead"
                :open="true" 
                :title="$gettext('Abschnitt')" 
                class="cw-manager-element-containers"
            >
                <div v-if="canSortContainers">
                    <button v-show="!sortContainersActive && isCurrent" class="button sort" @click="sortContainers">
                        <translate>Abschnitte sortieren</translate>
                    </button>
                    <button v-show="sortContainersActive && isCurrent" class="button accept" @click="storeContainersSort">
                        <translate>Sortieren beenden</translate>
                    </button>
                    <button v-show="sortContainersActive && isCurrent" class="button cancel" @click="resetContainersSort">
                        <translate>Sortieren abbrechen</translate>
                    </button>
                </div>
                <p v-if="!hasContainers">
                    <translate>Dieses Element enthält keine Abschnitte.</translate>
                </p>
                <transition-group name="cw-sort-ease" tag="div">
                    <courseware-manager-container
                        v-for="(container, index) in sortArrayContainers"
                        :key="container.id"
                        :container="container"
                        :isCurrent="isCurrent"
                        :sortContainers="sortContainersActive"
                        :inserter="containerInserterActive && moveSelfChildPossible"
                        :elementType="type" 
                        :blockInserter="blockInserterActive"
                        :canMoveUp="index !== 0"
                        :canMoveDown="index+1 !== sortArrayContainers.length"
                        @insertContainer="insertContainer"
                        @insertBlock="insertBlock"
                        @moveUp="moveUpContainer"
                        @moveDown="moveDownContainer"
                    />
                </transition-group>
                <courseware-manager-filing
                    v-if="isCurrent && !sortContainersActive && canEdit"
                    :parentId="currentElement.id"
                    :parentItem="currentElement"
                    itemType="container"
                />
            </courseware-collapsible-box>
            <courseware-collapsible-box :open="true" :title="$gettext('Seiten')" class="cw-manager-element-subchapters">
                <div v-if="canSortChildren">
                    <button v-show="!sortChildrenActive && isCurrent" class="button sort" @click="sortChildren">
                        <translate>Seiten sortieren</translate>
                    </button>
                    <button v-show="sortChildrenActive && isCurrent" class="button accept" @click="storeChildrenSort">
                        <translate>Sortieren beenden</translate>
                    </button>
                    <button v-show="sortChildrenActive && isCurrent" class="button cancel" @click="resetChildrenSort">
                        <translate>Sortieren abbrechen</translate>
                    </button>
                </div>
                <p v-if="!hasChildren">
                    <translate>Dieses Element enthält keine Seiten.</translate>
                </p>
                <transition-group name="cw-sort-ease" tag="div">
                    <courseware-manager-element-item
                        v-for="(child, index) in sortArrayChildren"
                        :key="child.id"
                        :element="child"
                        :sortChapters="sortChildrenActive"
                        :inserter="elementInserterActive && moveSelfChildPossible && filingData.parentItem.id !== child.id"
                        :type="type"
                        :canMoveUp="index !== 0"
                        :canMoveDown="index+1 !== sortArrayChildren.length"
                        @selectChapter="selectChapter"
                        @insertElement="insertElement"
                        @moveUp="moveUpChild"
                        @moveDown="moveDownChild"
                    />
                </transition-group>
                <courseware-manager-filing
                    v-if="isCurrent && !sortChildrenActive && canEdit"
                    :parentId="currentElement.id"
                    :parentItem="currentElement"
                    itemType="element"
                />
            </courseware-collapsible-box>
        </div>
    </div>
</template>

<script>
import StudipIcon from '../StudipIcon.vue';
import CoursewareCollapsibleBox from './CoursewareCollapsibleBox.vue';
import CoursewareManagerContainer from './CoursewareManagerContainer.vue';
import CoursewareManagerElementItem from './CoursewareManagerElementItem.vue';
import CoursewareManagerFiling from './CoursewareManagerFiling.vue';
import { mapActions, mapGetters } from 'vuex';
import { forEach } from 'jszip';

export default {
    name: 'courseware-manager-element',
    components: {
        CoursewareCollapsibleBox,
        CoursewareManagerContainer,
        CoursewareManagerElementItem,
        CoursewareManagerFiling,
        StudipIcon,
    },
    props: {
        type: {
            validator(value) {
                return ['current', 'self', 'remote', 'own','import'].includes(value);
            },
        },
        remoteCoursewareRangeId: String,
        currentElement: Object,
        moveSelfPossible: {
            default: true
        },
        moveSelfChildPossible: {
            default: true
        }
    },
    data() {
        return {
            elementInserterActive: false,
            containerInserterActive: false,
            blockInserterActive: false,
            sortChildrenActive: false,
            sortContainersActive: false,
            sortArrayChildren: [],
            discardStateArrayChildren: [],
            sortArrayContainers: [],
            discardStateArrayContainers: [],
        };
    },
    computed: {
        ...mapGetters({
            structuralElementById: 'courseware-structural-elements/byId',
            containerById: 'courseware-containers/byId',
        }),
        isCurrent() {
            return this.type === 'current';
        },
        isSelf() {
            return this.type === 'self';
        },
        isRemote() {
            return this.type === 'remote';
        },
        isImport() {
            return this.type === 'import';
        },
        isOwn() {
            return this.type === 'own';
        },
        isSorting() {
            return this.sortChildrenActive || this.sortContainersActive || this.sortBlocksActive;
        },
        canEdit() {
            if (this.currentElement.attributes) {
                return this.currentElement.attributes['can-edit'];
            } else {
                return false;
            }
        },
        canRead() {
            if (this.currentElement.attributes) {
                return this.currentElement.attributes['can-read'];
            } else {
                return false;
            }
        },
        breadcrumb() {
            if(this.currentElement.relationships) {
                let view = this;
                let ancestors = this.currentElement.relationships.ancestors.data;
                let ancestorElements = [];
                if(ancestors) {
                    ancestors.forEach((element) => {
                        ancestorElements.push(view.structuralElementById({ id: element.id }));
                    });
                }
                return ancestorElements;
            } else {
                return [];
            }
        },
        elementTitle() {
            if (this.currentElement.attributes) {
                return this.currentElement.attributes.title
            } else {
                return '';
            }
        },
        hasChildren() {
            if (this.children === null) {
                return false;
            } else {
                return this.children.length >= 1;
            }
        },
        canSortChildren() {
            if (this.children === null) {
                return false;
            } else {
                return this.children.length > 1 && this.canEdit;
            }
        },
        hasContainers() {
            if (this.containers === null) {
                return false;
            } else {
                return this.containers.length >= 1;
            }
        },
        canSortContainers() {
            if (this.containers === null) {
                return false;
            } else {
                return this.containers.length > 1 && this.canEdit;
            }
        },
        emptyContainers() {
            if (this.containers === null) {
                return true;
            } else {
                return this.containers.length === 0;
            }
        },
        containers() {
            if (!this.currentElement) {
                return [];
            }

            const containers = this.$store.getters['courseware-containers/related']({
                parent: this.currentElement,
                relationship: 'containers',
            });

            return containers;
        },
        children() {
            if (!this.currentElement) {
                return [];
            }

            if(this.currentElement.relationships) {
                let view = this;
                let children = this.currentElement.relationships.children.data;
                let childElements = [];
                children.forEach((element) => {
                    childElements.push(view.structuralElementById({ id: element.id }));
                });

                return childElements;
            } else {
                return [];
            }

        },
        filingData() {
            return this.$store.getters.filingData;
        }
    },
    methods: {
        ...mapActions({
            createStructuralElement: 'createStructuralElement',
            updateStructuralElement: 'updateStructuralElement',
            deleteStructuralElement: 'deleteStructuralElement',
            copyStructuralElement: 'copyStructuralElement',
            loadStructuralElement: 'loadStructuralElement',
            loadContainer: 'loadContainer',
            updateContainer: 'updateContainer',
            deleteContainer: 'deleteContainer',
            copyContainer: 'copyContainer',
            updateBlock: 'updateBlock',
            deleteBlock: 'deleteBlock',
            copyBlock: 'copyBlock',
            lockObject: 'lockObject',
            unlockObject: 'unlockObject',
            sortContainersInStructualElements: 'sortContainersInStructualElements',
            sortChildrenInStructualElements: 'sortChildrenInStructualElements'
        }),

        selectChapter(target) {
            this.$emit('selectElement', target);
        },
        async insertElement(data) {
            let source = data.source;
            let element = data.element;
            if (source === 'self') {
                element.relationships.parent.data.id = this.filingData.parentItem.id;
                element.attributes.position = this.filingData.parentItem.relationships.children.data.length;
                await this.lockObject({ id: element.id, type: 'courseware-structural-elements' });
                await this.updateStructuralElement({
                    element: element,
                    id: element.id,
                });
                await this.unlockObject({ id: element.id, type: 'courseware-structural-elements' });
                this.loadStructuralElement(this.currentElement.id);
                this.$store.dispatch('cwManagerFilingData', {});
            } else if(source === 'remote' || source === 'own') {
                //create Element
                let parentId = this.filingData.parentItem.id;
                await this.copyStructuralElement({
                    parentId: parentId,
                    element: element,
                });
                this.$emit('loadSelf', parentId);
                this.$store.dispatch('cwManagerFilingData', {});
            } else {
                console.log('unreliable source:');
                console.log(source);
                console.log(element);
            }

        },
        async insertContainer(data) {
            let source = data.source;
            let container = data.container;
            if (source === 'self') {
                container.relationships['structural-element'].data.id = this.filingData.parentItem.id;
                container.attributes.position = this.filingData.parentItem.relationships.containers.data.length;
                await this.lockObject({id: container.id, type: 'courseware-containers'});
                await this.updateContainer({
                    container: container,
                    structuralElementId: this.currentElement.id
                });
                await this.unlockObject({id: container.id, type: 'courseware-containers'});
                this.$store.dispatch('cwManagerFilingData', {});
            } else if (source === 'remote' || source === 'own') {
                let parentId = this.filingData.parentItem.id;
                await this.copyContainer({
                    parentId: parentId,
                    container: container,
                });
                this.$emit('loadSelf', parentId);
                this.$store.dispatch('cwManagerFilingData', {});
            } else {
                console.log('unreliable source:');
                console.log(source);
                console.log(container);
            }

        },
        async insertBlock(data) {
            let source = data.source;
            let block = data.block;
            if (source === 'self') {
                let sourceContainer = await this.containerById({id: block.relationships.container.data.id});
                sourceContainer.attributes.payload.sections.forEach(section => {
                    let index = section.blocks.indexOf(block.id);
                    if(index !== -1) {
                        section.blocks.splice(index, 1);
                    }
                });
                await this.lockObject({id: sourceContainer.id, type: 'courseware-containers'});
                await this.updateContainer({
                    container: sourceContainer,
                    structuralElementId: sourceContainer.relationships['structural-element'].data.id
                });
                await this.unlockObject({id: sourceContainer.id, type: 'courseware-containers'});

                let destinationContainer = await this.containerById({id: this.filingData.parentItem.id});
                destinationContainer.attributes.payload.sections[destinationContainer.attributes.payload.sections.length-1].blocks.push(block.id);
                await this.lockObject({id: destinationContainer.id, type: 'courseware-containers'});
                await this.updateContainer({
                    container: destinationContainer,
                    structuralElementId: destinationContainer.relationships['structural-element'].data.id
                });
                await this.unlockObject({id: destinationContainer.id, type: 'courseware-containers'});

                block.relationships.container.data.id = this.filingData.parentItem.id;
                block.attributes.position = this.filingData.parentItem.relationships.blocks.data.length;
                await this.lockObject({id: block.id, type: 'courseware-blocks'});
                await this.updateBlock({
                    block: block,
                    containerId: this.filingData.parentItem.id
                });
                await this.unlockObject({id: block.id, type: 'courseware-blocks'});
                await this.loadContainer(sourceContainer.id);
                await this.loadContainer(destinationContainer.id);
                this.$emit('reloadElement');
                this.$store.dispatch('cwManagerFilingData', {});
            } else if (source === 'remote' || source === 'own') {
                let parentId = this.filingData.parentItem.id;
                await this.copyBlock({
                    parentId: parentId,
                    block: block,
                });
                await this.loadContainer(parentId);
                this.$emit('loadSelf',this.filingData.parentItem.relationships['structural-element'].data.id);
                this.$store.dispatch('cwManagerFilingData', {});
            } else {
                console.debug('unreliable source:', source, block);
            }
        },

        sortChildren() {
            this.discardStateArrayChildren = [...this.children]; //copy array because of watcher?
            this.sortChildrenActive = true;
        },
        sortContainers() {
            this.discardStateArrayContainers = [...this.containers];
            this.sortContainersActive = true;
        },

        storeChildrenSort() {
            this.sortChildrenInStructualElements({parent: this.currentElement, children: this.sortArrayChildren});

            this.discardStateArrayChildren = [];
            this.sortChildrenActive = false;
        },
        resetChildrenSort() {
            this.sortArrayChildren = this.discardStateArrayChildren;
            this.sortChildrenActive = false;
        },

        storeContainersSort() {
            this.sortContainersInStructualElements({structuralElement: this.currentElement, containers: this.sortArrayContainers});

            this.discardStateArrayContainers = [];
            this.sortContainersActive = false;
        },
        resetContainersSort() {
            this.sortArrayContainers = this.discardStateArrayContainers;
            this.sortContainersActive = false;
        },

        moveUpChild(childId) {
            this.moveUp(childId, this.sortArrayChildren);
        },
        moveDownChild(childId) {
            this.moveDown(childId, this.sortArrayChildren);
        },
        moveUpContainer(containerId) {
            this.moveUp(containerId, this.sortArrayContainers);
        },
        moveDownContainer(containerId) {
            this.moveDown(containerId, this.sortArrayContainers);
        },

        moveUp(itemId, sortArray) {
            sortArray.every((item, index) => {
                if (item.id === itemId) {
                    if (index === 0) {
                        return false;
                    }
                    sortArray.splice(index - 1, 0, sortArray.splice(index, 1)[0]);
                    return false;
                } else {
                    return true;
                }
            });
        },
        moveDown(itemId, sortArray) {
            sortArray.every((item, index) => {
                if (item.id === itemId) {
                    if (index === sortArray.length - 1) {
                        return false;
                    }
                    sortArray.splice(index + 1, 0, sortArray.splice(index, 1)[0]);
                    return false;
                } else {
                    return true;
                }
            });
        },
        updateFilingData(data) {
            if (Object.keys(data).length !== 0) {
                switch (data.itemType) {
                    case 'element':
                        this.elementInserterActive = true;
                        break;
                    case 'container':
                        this.containerInserterActive = true;
                        break;
                    case 'block':
                        this.blockInserterActive = true;
                        break;
                }
            } else {
                this.elementInserterActive = false;
                this.containerInserterActive = false;
                this.blockInserterActive = false;
            }
        }
    },
    mounted() {
        this.updateFilingData(this.filingData);
    },
    watch: {
        filingData(newValue) {
            if (!['self', 'remote', 'own', 'import'].includes(this.type)) {
                return false;
            }
            this.updateFilingData(newValue);
        },
        containers(newContainers) {
            this.sortArrayContainers = newContainers;
        },
        children(newChildren) {
            this.sortArrayChildren = newChildren;
        }
    },
};
</script>
