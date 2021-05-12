class PluginManager {
    constructor() {
        this.blocks = [];
    }

    addBlock (name, block) {
        this.blocks[name] = block;
    }

    registerComponentsLocally(component) {
        for (const [name, block] of Object.entries(this.blocks)) {
            component.$options.components[name] = block;
        }
    }
}

export default PluginManager;
