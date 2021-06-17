import axios from 'axios';

const getDefaultState = () => {
    return {
        blockAdder: {},
        containerAdder: false,
        consumeMode: false,
        context: {},
        courseware: {},
        currentElement: {},
        oerTitle: null,
        licenses: null, // we need a route for License SORM
        httpClient: null,
        lastElement: null,
        msg: 'Dehydrated',
        msgCompanionOverlay:
            'Hallo! Ich bin Ihr persönlicher Companion. Wussten Sie schon, dass Courseware jetzt noch einfacher zu bedienen ist?',
        styleCompanionOverlay: 'default',
        pluginManager: null,
        showCompanionOverlay: false,
        showToolbar: false,
        urlHelper: null,
        userId: null,
        viewMode: 'read',
        filingData: {},
        userIsTeacher: false,

        showStructuralElementEditDialog: false,
        showStructuralElementAddDialog: false,
        showStructuralElementExportDialog: false,
        showStructuralElementInfoDialog: false,
        showStructuralElementDeleteDialog: false,
        showStructuralElementOerDialog: false,
    };
};

const initialState = getDefaultState();

const getters = {
    msg(state) {
        return state.msg;
    },
    lastElement(state) {
        return state.lastElement;
    },
    courseware(state) {
        return state.courseware;
    },
    currentElement(state) {
        return state.currentElement;
    },
    oerTitle(state) {
        return state.oerTitle;
    },
    licenses(state) {
        return state.licenses;
    },
    context(state) {
        return state.context;
    },
    blockTypes(state) {
        return state.courseware?.attributes?.['block-types'] ?? [];
    },
    containerTypes(state) {
        return state.courseware?.attributes?.['container-types'] ?? [];
    },
    favoriteBlockTypes(state) {
        const allBlockTypes = state.courseware?.attributes?.['block-types'] ?? [];
        const favorites = state.courseware?.attributes?.['favorite-block-types'] ?? [];

        return allBlockTypes.filter(({ type }) => favorites.includes(type));
    },
    viewMode(state) {
        return state.viewMode;
    },
    showToolbar(state) {
        return state.showToolbar;
    },
    blockAdder(state) {
        return state.blockAdder;
    },
    containerAdder(state) {
        return state.containerAdder;
    },
    showCompanionOverlay(state) {
        return state.showCompanionOverlay;
    },
    msgCompanionOverlay(state) {
        return state.msgCompanionOverlay;
    },
    styleCompanionOverlay(state) {
        return state.styleCompanionOverlay;
    },
    consumeMode(state) {
        return state.consumeMode;
    },
    httpClient(state) {
        return state.httpClient;
    },
    urlHelper(state) {
        return state.urlHelper;
    },
    userId(state) {
        return state.userId;
    },
    userIsTeacher(state) {
        return state.userIsTeacher;
    },
    pluginManager(state) {
        return state.pluginManager;
    },
    filingData(state) {
        return state.filingData;
    },
    showStructuralElementEditDialog(state) {
        return state.showStructuralElementEditDialog;
    },
    showStructuralElementAddDialog(state) {
        return state.showStructuralElementAddDialog;
    },
    showStructuralElementExportDialog(state) {
        return state.showStructuralElementExportDialog;
    },
    showStructuralElementInfoDialog(state) {
        return state.showStructuralElementInfoDialog;
    },
    showStructuralElementOerDialog(state) {
        return state.showStructuralElementOerDialog;
    },
    showStructuralElementDeleteDialog(state) {
        return state.showStructuralElementDeleteDialog;
    }
};

export const state = { ...initialState };

export const actions = {
    async loadCoursewareStructure({ commit, dispatch, state, rootGetters }) {
        const parent = state.context;
        const relationship = 'courseware';
        const options = {
            include: 'bookmarks,root,root.descendants',
        };

        await dispatch(`courseware-instances/loadRelated`, { parent, relationship, options }, { root: true });

        return commit('coursewareSet', rootGetters['courseware-instances/all'][0]);
    },

    loadContainer({ dispatch }, containerId) {
        const options = {
            include: 'blocks',
        };

        return dispatch('courseware-containers/loadById', { id: containerId, options }, { root: true });
    },

    loadStructuralElement({ dispatch }, structuralElementId) {
        const options = {
            include:
                'ancestors,containers,containers.blocks,containers.blocks.editor,containers.blocks.owner,containers.blocks.user-data-field,containers.blocks.user-progress,descendants,editor,owner',
            'fields[users]': 'formatted-name',
        };

        return dispatch(
            'courseware-structural-elements/loadById',
            { id: structuralElementId, options },
            { root: true }
        );
    },

    loadFileRefs({ dispatch, rootGetters }, block_id) {
        const parent = {
            type: 'courseware-blocks',
            id: block_id,
        };

        const relationship = 'file-refs';

        return dispatch('courseware-blocks/loadRelated', { parent, relationship }, { root: true }).then(() => {
            const refs = rootGetters['courseware-blocks/related']({
                parent,
                relationship,
            });
            return refs;
        });
    },

    async createFile(context, { file, filedata, folder }) {
        const formData = new FormData();
        formData.append('file', filedata, file.attributes.name);

        const url = `folders/${folder.id}/file-refs`;
        let request = await state.httpClient.post(url, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        return state.httpClient.get(request.headers.location).then((response) => {
            return response.data.data;
        });
    },

    async createRootFolder({ dispatch, rootGetters }, { context, folder }) {
        // get root folder for this context
        await dispatch(
            'courses/loadRelated',
            {
                parent: context,
                relationship: 'folders',
            },
            { root: true }
        );

        let folders = await rootGetters['courses/related']({
            parent: context,
            relationship: 'folders',
        });

        let rootFolder = null;

        for (let i = 0; i < folders.length; i++) {
            if (folders[i].attributes['folder-type'] === 'RootFolder') {
                rootFolder = folders[i];
            }
        }

        const newFolder = {
            data: {
                type: 'folders',
                attributes: {
                    name: folder.name,
                    'folder-type': 'StandardFolder',
                },
                relationships: {
                    parent: {
                        data: {
                            type: 'folders',
                            id: rootFolder.id,
                        },
                    },
                },
            },
        };

        return state.httpClient.post(`courses/${context.id}/folders`, newFolder).then((response) => {
            return response.data.data;
        });
    },

    async createFolder(store, { context, parent, folder }) {
        const newFolder = {
            data: {
                type: 'folders',
                attributes: {
                    name: folder.name,
                    'folder-type': folder.type,
                },
                relationships: {
                    parent: parent,
                },
            },
        };

        return state.httpClient.post(`courses/${context.id}/folders`, newFolder).then((response) => {
            return response.data.data;
        });
    },

    loadFolder({ dispatch }, folderId) {
        const options = {};

        return dispatch('folders/loadById', { id: folderId, options }, { root: true });
    },

    copyBlock({ getters }, { parentId, block }) {
        const copy = {
            data: {
                block: block,
                parent_id: parentId,
            },
        };

        return state.httpClient.post(`courseware-blocks/${block.id}/copy`, copy).then((resp) => {
            // console.log(resp);
        });
    },
    copyContainer({ getters }, { parentId, container }) {
        const copy = {
            data: {
                container: container,
                parent_id: parentId,
            },
        };

        return state.httpClient.post(`courseware-containers/${container.id}/copy`, copy).then((resp) => {
            // console.log(resp);
        });
    },
    copyStructuralElement({ getters }, { parentId, element }) {
        const copy = {
            data: {
                element: element,
                parent_id: parentId,
            },
        };

        return state.httpClient.post(`courseware-structural-elements/${element.id}/copy`, copy).then((resp) => {
            // console.log(resp);
        });
    },

    lockObject({ dispatch, getters }, { id, type }) {
        return dispatch(`${type}/setRelated`, {
            parent: { id, type },
            relationship: 'edit-blocker',
            data: {
                type: 'users',
                id: getters.userId,
            },
        });
    },

    async createBlockInContainer({ dispatch }, { container, blockType }) {
        const block = {
            attributes: {
                'block-type': blockType,
                payload: null,
            },
            relationships: {
                container: {
                    data: { type: container.type, id: container.id },
                },
            },
        };
        await dispatch('courseware-blocks/create', block, { root: true });

        return dispatch('loadContainer', container.id);
    },

    async deleteBlockInContainer({ dispatch }, { containerId, blockId }) {
        const data = {
            id: blockId,
        };
        await dispatch('courseware-blocks/delete', data, { root: true });
        //TODO throws TypeError: block is undefined after delete
        return dispatch('loadContainer', containerId);
    },

    async updateBlockInContainer({ dispatch }, { attributes, blockId, containerId }) {
        const container = {
            type: 'courseware-containers',
            id: containerId,
        };
        const block = {
            type: 'courseware-blocks',
            attributes: attributes,
            id: blockId,
            relationships: {
                container: {
                    data: { type: container.type, id: container.id },
                },
            },
        };

        await dispatch('courseware-blocks/update', block, { root: true });
        await dispatch('unlockObject', { id: blockId, type: 'courseware-blocks' });

        return dispatch('loadContainer', containerId);
    },

    async updateBlock({ dispatch }, { block, containerId }) {
        const container = {
            type: 'courseware-containers',
            id: containerId,
        };
        const updateBlock = {
            type: 'courseware-blocks',
            attributes: block.attributes,
            id: block.id,
            relationships: {
                container: {
                    data: { type: container.type, id: container.id },
                },
            },
        };
        await dispatch('courseware-blocks/update', updateBlock, { root: true });

        return dispatch('loadContainer', containerId);
    },

    async deleteBlock({ dispatch }, { containerId, blockId }) {
        const data = {
            id: blockId,
        };
        await dispatch('courseware-blocks/delete', data, { root: true });
        //TODO throws TypeError: block is undefined after delete
        return dispatch('loadContainer', containerId);
    },

    async storeCoursewareSettings({ dispatch, getters }, { permission, progression }) {
        const courseware = getters.courseware;
        courseware.attributes['editing-permission-level'] = permission;
        courseware.attributes['sequential-progression'] = progression;

        return dispatch('courseware-instances/update', courseware, { root: true });
    },

    sortChildrenInStructualElements({ dispatch }, { parent, children }) {
        const childrenResourceIdentifiers = children.map(({ type, id }) => ({ type, id }));

        return dispatch(
            `courseware-structural-elements/setRelated`,
            {
                parent: { type: parent.type, id: parent.id },
                relationship: 'children',
                data: childrenResourceIdentifiers,
            },
            { root: true }
        );
    },

    async createStructuralElement({ dispatch }, { attributes, parentId, currentId }) {
        const data = {
            attributes,
            relationships: {
                parent: {
                    data: {
                        type: 'courseware-structural-elements',
                        id: parentId,
                    },
                },
            },
        };
        await dispatch('courseware-structural-elements/create', data, { root: true });

        return dispatch('loadStructuralElement', currentId);
    },

    async deleteStructuralElement({ dispatch }, { id, parentId }) {
        const data = {
            id: id,
        };
        await dispatch('courseware-structural-elements/delete', data, { root: true });
        return dispatch('loadStructuralElement', parentId);
    },

    async updateStructuralElement({ dispatch }, { element, id }) {
        await dispatch('courseware-structural-elements/update', element, { root: true });

        return dispatch('loadStructuralElement', id);
    },

    sortContainersInStructualElements({ dispatch }, { structuralElement, containers }) {
        const containerResourceIdentifiers = containers.map(({ type, id }) => ({ type, id }));

        return dispatch(
            `courseware-structural-elements/setRelated`,
            {
                parent: { type: structuralElement.type, id: structuralElement.id },
                relationship: 'containers',
                data: containerResourceIdentifiers,
            },
            { root: true }
        );
    },

    async createContainer({ dispatch }, { attributes, structuralElementId }) {
        const data = {
            attributes,
            relationships: {
                'structural-element': {
                    data: {
                        type: 'courseware-structural-elements',
                        id: structuralElementId,
                    },
                },
            },
        };
        await dispatch('courseware-containers/create', data, { root: true });

        return dispatch('loadStructuralElement', structuralElementId);
    },

    async deleteContainer({ dispatch }, { containerId, structuralElementId }) {
        const data = {
            id: containerId,
        };
        await dispatch('courseware-containers/delete', data, { root: true });
        //TODO throws TypeError: container is undefined after delete
        return dispatch('loadStructuralElement', structuralElementId);
    },

    async updateContainer({ dispatch }, { container, structuralElementId }) {
        await dispatch('courseware-containers/update', container, { root: true });

        return dispatch('loadStructuralElement', structuralElementId);
    },

    sortBlocksInContainer({ dispatch }, { container, sections }) {
        let blockResourceIdentifiers = [];

        sections.forEach((section) => {
            blockResourceIdentifiers.push(...section.blocks.map(({ type, id }) => ({ type, id })));
        });

        return dispatch(
            `courseware-containers/setRelated`,
            {
                parent: { type: container.type, id: container.id },
                relationship: 'blocks',
                data: blockResourceIdentifiers,
            },
            { root: true }
        );
    },

    lockObject({ dispatch, getters }, { id, type }) {
        return dispatch(`${type}/setRelated`, {
            parent: { id, type },
            relationship: 'edit-blocker',
            data: {
                type: 'users',
                id: getters.userId,
            },
        });
    },

    unlockObject({ dispatch }, { id, type }) {
        return dispatch(`${type}/setRelated`, {
            parent: { id, type },
            relationship: 'edit-blocker',
            data: null,
        });
    },

    async companionInfo({ dispatch }, { info }) {
        await dispatch('coursewareStyleCompanionOverlay', 'default');
        await dispatch('coursewareMsgCompanionOverlay', info);
        return dispatch('coursewareShowCompanionOverlay', true);
    },

    async companionSuccess({ dispatch }, { info }) {
        await dispatch('coursewareStyleCompanionOverlay', 'happy');
        await dispatch('coursewareMsgCompanionOverlay', info);
        return dispatch('coursewareShowCompanionOverlay', true);
    },

    async companionError({ dispatch }, { info }) {
        await dispatch('coursewareStyleCompanionOverlay', 'sad');
        await dispatch('coursewareMsgCompanionOverlay', info);
        return dispatch('coursewareShowCompanionOverlay', true);
    },

    async companionWarning({ dispatch }, { info }) {
        await dispatch('coursewareStyleCompanionOverlay', 'alert');
        await dispatch('coursewareMsgCompanionOverlay', info);
        return dispatch('coursewareShowCompanionOverlay', true);
    },

    async companionSpecial({ dispatch }, { info }) {
        await dispatch('coursewareStyleCompanionOverlay', 'special');
        await dispatch('coursewareMsgCompanionOverlay', info);
        return dispatch('coursewareShowCompanionOverlay', true);
    },

    // adds a favorite block type using the `type` of the BlockType
    async addFavoriteBlockType({ dispatch, getters }, blockType) {
        const blockTypes = new Set(getters.favoriteBlockTypes.map(({ type }) => type));
        blockTypes.add(blockType);

        return dispatch('storeFavoriteBlockTypes', [...blockTypes]);
    },

    // removes a favorite block type using the `type` of the BlockType
    async removeFavoriteBlockType({ dispatch, getters }, blockType) {
        const blockTypes = new Set(getters.favoriteBlockTypes.map(({ type }) => type));
        blockTypes.delete(blockType);

        return dispatch('storeFavoriteBlockTypes', [...blockTypes]);
    },

    // sets the favorite block types using an array of the `type`s of those BlockTypes
    async storeFavoriteBlockTypes({ dispatch, getters }, favoriteBlockTypes) {
        const courseware = getters.courseware;
        courseware.attributes['favorite-block-types'] = favoriteBlockTypes;

        return dispatch('courseware-instances/update', courseware, { root: true });
    },

    coursewareCurrentElement(context, id) {
        context.commit('coursewareCurrentElementSet', id);
    },

    coursewareContext(context, id) {
        context.commit('coursewareContextSet', id);
    },

    oerTitle(context, title) {
        context.commit('oerTitleSet', title);
    },

    licenses(context, licenses) {
        context.commit('licensesSet', licenses);
    },

    coursewareViewMode(context, view) {
        context.commit('coursewareViewModeSet', view);
    },

    coursewareShowToolbar(context, toolbar) {
        context.commit('coursewareShowToolbarSet', toolbar);
    },

    coursewareBlockAdder(context, adder) {
        context.commit('coursewareBlockAdderSet', adder);
    },

    coursewareContainerAdder(context, adder) {
        context.commit('coursewareContainerAdderSet', adder);
    },

    coursewareShowCompanionOverlay(context, companion_overlay) {
        context.commit('coursewareShowCompanionOverlaySet', companion_overlay);
    },

    coursewareMsgCompanionOverlay(context, companion_overlay_msg) {
        context.commit('coursewareMsgCompanionOverlaySet', companion_overlay_msg);
    },

    coursewareStyleCompanionOverlay(context, companion_overlay_style) {
        context.commit('coursewareStyleCompanionOverlaySet', companion_overlay_style);
    },

    coursewareConsumeMode(context, mode) {
        context.commit('coursewareConsumeModeSet', mode);
    },

    setHttpClient({ commit }, httpClient) {
        commit('setHttpClient', httpClient);
    },

    setUrlHelper({ commit }, urlHelper) {
        commit('setUrlHelper', urlHelper);
    },

    setUserId({ commit }, userId) {
        commit('setUserId', userId);
    },

    showElementEditDialog(context, bool) {
        context.commit('setShowStructuralElementEditDialog', bool)
    },

    showElementAddDialog(context, bool) {
        context.commit('setShowStructuralElementAddDialog', bool)
    },

    showElementExportDialog(context, bool) {
        context.commit('setShowStructuralElementExportDialog', bool)
    },

    showElementInfoDialog(context, bool) {
        context.commit('setShowStructuralElementInfoDialog', bool)
    },

    showElementOerDialog(context, bool) {
        context.commit('setShowStructuralElementOerDialog', bool)
    },

    showElementDeleteDialog(context, bool) {
        context.commit('setShowStructuralElementDeleteDialog', bool)
    },

    addBookmark({ dispatch, rootGetters }, structuralElement) {
        const cw = rootGetters['courseware'];

        // get existing bookmarks
        const bookmarks =
            rootGetters['courseware-structural-elements/related']({
                parent: cw,
                relationship: 'bookmarks',
            })?.map(({ type, id }) => ({ type, id })) ?? [];

        // add a new bookmark
        const data = [...bookmarks, { type: structuralElement.type, id: structuralElement.id }];

        // send them home
        return dispatch(
            `courseware-structural-elements/setRelated`,
            {
                parent: { type: cw.type, id: cw.id },
                relationship: 'bookmarks',
                data,
            },
            { root: true }
        );
    },

    removeBookmark({ dispatch, rootGetters }, structuralElement) {
        const cw = rootGetters['courseware'];

        // get existing bookmarks
        const bookmarks =
            rootGetters['courseware-structural-elements/related']({
                parent: cw,
                relationship: 'bookmarks',
            })?.map(({ type, id }) => ({ type, id })) ?? [];

        // filter bookmark that must be removed
        const data = bookmarks.filter(({ id }) => id !== structuralElement.id);

        // send them home
        return dispatch(
            `courseware-structural-elements/setRelated`,
            {
                parent: { type: cw.type, id: cw.id },
                relationship: 'bookmarks',
                data,
            },
            { root: true }
        );
    },

    setPluginManager({ commit }, pluginManager) {
        commit('setPluginManager', pluginManager);
    },

    uploadImageForStructuralElement({ dispatch, state }, { structuralElement, file }) {
        const formData = new FormData();
        formData.append('image', file);

        const url = `courseware-structural-elements/${structuralElement.id}/image`;
        return state.httpClient.post(url, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
    },

    async deleteImageForStructuralElement({ dispatch, state }, structuralElement) {
        const url = `courseware-structural-elements/${structuralElement.id}/image`;
        await state.httpClient.delete(url);

        return dispatch('loadStructuralElement', structuralElement.id);
    },

    cwManagerFilingData(context, msg) {
        context.commit('cwManagerFilingDataSet', msg);
    },

    loadUsersCourses({ dispatch, rootGetters, state }, userId) {
        const parent = {
            type: 'users',
            id: userId,
        };
        const relationship = 'course-memberships';

        const options = {
            include: 'course',
        };

        return dispatch('course-memberships/loadRelated', { parent, relationship, options }, { root: true }).then(
            () => {
                const memberships = rootGetters['course-memberships/related']({
                    parent,
                    relationship,
                });
                let courses = [];
                memberships.forEach((membership) => {
                    if (
                        membership.attributes.permission === 'dozent' &&
                        state.context.id !== membership.relationships.course.data.id
                    ) {
                        courses.push(rootGetters['courses/related']({ parent: membership, relationship: 'course' }));
                    }
                });
                return courses;
            }
        );
    },

    loadRemoteCoursewareStructure({ dispatch, rootGetters }, { rangeId, rangeType }) {
        const parent = {
            id: rangeId,
            type: rangeType,
        };

        const relationship = 'courseware';

        return dispatch(`courseware-instances/loadRelated`, { parent, relationship }, { root: true }).then(
            (response) => {
                const instance = rootGetters['courseware-instances/related']({
                    parent: parent,
                    relationship: relationship,
                });

                return instance;
            },
            (error) => {
                return null;
            }
        );
    },

    loadTeacherStatus({ dispatch, rootGetters, state, commit, getters }, userId) {
        const parent = {
            type: 'users',
            id: userId,
        };
        const relationship = 'course-memberships';

        const options = {
            include: 'course',
        };

        return dispatch('course-memberships/loadRelated', { parent, relationship, options }, { root: true }).then(
            () => {
                const memberships = rootGetters['course-memberships/related']({
                    parent,
                    relationship,
                });
                let isTeacher = false;
                memberships.forEach((membership) => {
                    if (getters.courseware.attributes['editing-permission-level'] === 'dozent') {
                        if (
                            membership.attributes.permission === 'dozent' &&
                            state.context.id === membership.relationships.course.data.id
                        ) {
                            isTeacher = true;
                        }
                    }
                    if (getters.courseware.attributes['editing-permission-level'] === 'tutor') {
                        if (
                            (membership.attributes.permission === 'dozent' ||
                                membership.attributes.permission === 'tutor') &&
                            state.context.id === membership.relationships.course.data.id
                        ) {
                            isTeacher = true;
                        }
                    }
                });
                return commit('setUserIsTeacher', isTeacher);
            }
        );
    },

    loadFeedback({ dispatch }, blockId) {
        const parent = { type: 'courseware-blocks', id: `${blockId}` };
        const relationship = 'feedback';
        const options = {
            include: 'user',
        };

        return dispatch('courseware-block-feedback/loadRelated', { parent, relationship, options }, { root: true });
    },

    async createFeedback({ dispatch }, { blockId, feedback }) {
        const data = {
            attributes: {
                feedback,
            },
            relationships: {
                block: {
                    data: {
                        type: 'courseware-blocks',
                        id: blockId,
                    },
                },
            },
        };
        await dispatch('courseware-block-feedback/create', data, { root: true });

        return dispatch('loadFeedback', blockId);
    },
};

/* eslint no-param-reassign: ["error", { "props": false }] */
export const mutations = {
    coursewareSet(state, data) {
        state.courseware = data;
    },

    coursewareCurrentElementSet(state, data) {
        state.lastElement = state.currentElement;
        state.currentElement = data;
    },

    coursewareContextSet(state, data) {
        state.context = data;
    },

    oerTitleSet(state, data) {
        state.oerTitle = data;
    },

    licensesSet(state, data) {
        state.licenses = data;
    },

    coursewareViewModeSet(state, data) {
        state.viewMode = data;
    },

    coursewareShowToolbarSet(state, data) {
        state.showToolbar = data;
    },

    coursewareBlockAdderSet(state, data) {
        state.blockAdder = data;
    },

    coursewareContainerAdderSet(state, data) {
        state.containerAdder = data;
    },

    coursewareShowCompanionOverlaySet(state, data) {
        state.showCompanionOverlay = data;
    },

    coursewareMsgCompanionOverlaySet(state, data) {
        state.msgCompanionOverlay = data;
    },

    coursewareStyleCompanionOverlaySet(state, data) {
        state.styleCompanionOverlay = data;
    },

    coursewareConsumeModeSet(state, data) {
        state.consumeMode = data;
    },

    setHttpClient(state, httpClient) {
        state.httpClient = httpClient;
    },

    setUrlHelper(state, urlHelper) {
        state.urlHelper = urlHelper;
    },

    setUserId(state, userId) {
        state.userId = userId;
    },

    setUserIsTeacher(state, isTeacher) {
        state.userIsTeacher = isTeacher;
    },

    setPluginManager(state, pluginManager) {
        state.pluginManager = pluginManager;
    },

    cwManagerFilingDataSet(state, data) {
        state.filingData = data;
    },

    setShowStructuralElementEditDialog(state, showEdit) {
        state.showStructuralElementEditDialog = showEdit;
    },

    setShowStructuralElementAddDialog(state, showAdd) {
        state.showStructuralElementAddDialog = showAdd;
    },

    setShowStructuralElementExportDialog(state, showExport) {
        state.showStructuralElementExportDialog = showExport;
    },

    setShowStructuralElementInfoDialog(state, showInfo) {
        state.showStructuralElementInfoDialog = showInfo;
    },

    setShowStructuralElementOerDialog(state, showOer) {
        state.showStructuralElementOerDialog = showOer;
    },

    setShowStructuralElementDeleteDialog(state, showDelete) {
        state.showStructuralElementDeleteDialog = showDelete;
    }
};

export default {
    state,
    actions,
    mutations,
    getters,
};
