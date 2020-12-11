const load = async function () {
    return await STUDIP.loadChunk('vue');
};

const on = async function (...args) {
    const { eventBus } = await load();
    eventBus.on(...args);
};

const emit = async function (...args) {
    const { eventBus } = await load();
    eventBus.emit(...args);
};

export default { load, on, emit };
