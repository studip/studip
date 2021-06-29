class PluginManager {
    constructor() {
        this.blocks = [];
        this.containers = [];
    }

    addBlock(name, block) {
        this.blocks[name] = block;
    }
    addContainer(name, container) {
        this.containers[name] = container;
    }

    registerComponentsLocally(component) {
        for (const [name, block] of Object.entries(this.blocks)) {
            component.$options.components[name] = block;
        }
        for (const [name, container] of Object.entries(this.containers)) {
            component.$options.components[name] = container;
        }
    }
}

export default PluginManager;
