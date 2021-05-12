import { mapActions, mapGetters } from 'vuex';

export default {
    data() {
        return {
            importFolder: null,
            file_mapping: {}
        };
    },

    computed: {
        ...mapGetters({
            context: 'context',
            courseware: 'courseware-instances/all'
        }),
    },

    methods: {
        animateImport() {},

        async importCourseware(element, parent_id, files)
        {
            // import all files
            await this.uploadAllFiles(files);

            this.animateImport();

            await this.importStructuralElement([element], parent_id, files);

        },

        async importStructuralElement(element, parent_id, files) {
            if (element.length) {
                for (var i = 0; i < element.length; i++) {
                    // TODO: create element on server and fetch new id
                    await this.createStructuralElement({
                        attributes: element[i].attributes,
                        parentId: parent_id,
                        currentId: parent_id,
                    });

                    this.animateImport();

                    let new_element = this.$store.getters['courseware-structural-elements/lastCreated'];
                    if (element[i].children?.length > 0) {
                        await this.importStructuralElement(element[i].children, new_element.id, files);
                    }

                    if (element[i].containers?.length > 0) {
                        for (var j = 0; j < element[i].containers.length; j++) {
                            let container = element[i].containers[j];
                            // TODO: create element on server and fetch new id
                            await this.createContainer({
                                attributes: container.attributes,
                                structuralElementId: new_element.id,
                            });

                            this.animateImport();

                            let new_container = this.$store.getters['courseware-containers/lastCreated'];

                            if (container.blocks?.length) {
                                for (var k = 0; k < container.blocks.length; k++) {
                                    await this.importBlock(container.blocks[k], new_container, files);
                                }
                            }
                        }
                    }
                }
            }
        },

        async importBlock(block, block_container, files) {
            // TODO: create element
            await this.createBlockInContainer({
                container: {type: block_container.type, id: block_container.id},
                blockType: block.attributes['block-type'],
            });

            this.animateImport();

            let new_block = this.$store.getters['courseware-blocks/lastCreated'];

            // update old id ids in payload part
            for (var i = 0; i < files.length; i++) {
                if (files[i].related_block_id === block.id) {
                    let old_file = this.file_mapping[files[i].id].old;
                    let new_file = this.file_mapping[files[i].id].new;
                    let payload = JSON.stringify(block.attributes.payload);

                    payload = payload.replaceAll(old_file.id, new_file.id);
                    payload = payload.replaceAll(old_file.folder.id, new_file.relationships.parent.data.id);

                    block.attributes.payload = JSON.parse(payload);
                }
            }

            await this.updateBlockInContainer({
                attributes: block.attributes,
                blockId: new_block.id,
                containerId: block_container.id,
            });

            this.animateImport();
        },


        async uploadAllFiles(files) {
            // create folder for importing the files into
            let now = new Date();
            let main_folder = await this.createRootFolder({
                context: this.context,
                folder: {
                    type: 'StandardFolder',
                    name: ' CoursewareImport '
                        + now.toLocaleString('de-DE', { timeZone: 'UTC' })
                        + ' ' + now.getMilliseconds(),
                }
            });

            this.animateImport();

            let folders = {};

            // upload all files to the newly created folder
            if (main_folder) {
                for (var i = 0; i < files.length; i++) {

                    // if the subfolder with the referenced id does not exist yet, create it
                    if (!folders[files[i].folder.id]) {
                        folders[files[i].folder.id] = await this.createFolder({
                            context: this.context,
                            parent: {
                                data: {
                                    id: main_folder.id,
                                    type: 'folders'
                                }
                            },
                            folder: {
                                type: files[i].folder.type,
                                name: files[i].folder.name
                            }
                        });
                    }

                    // only upload files with the same id once
                    if (this.file_mapping[files[i].id] === undefined) {
                        let zip_filedata = await this.zip.file(files[i].id).async('blob');

                        // create new blob with correct type
                        let filedata = zip_filedata.slice(0, zip_filedata.size, files[i].attributes['mime-type']);

                        let file = await this.createFile({
                            file: files[i],
                            filedata: filedata,
                            folder: folders[files[i].folder.id]
                        });

                        this.animateImport();

                        //file mapping
                        this.file_mapping[files[i].id] = {
                            old: files[i],
                            new: file
                        };
                    }
                }
            } else {
                return false;
            }

            return true;
        },

        ...mapActions([
            'createBlockInContainer',
            'createContainer',
            'createStructuralElement',
            'updateBlockInContainer',
            'createFolder',
            'createRootFolder',
            'createFile'
        ]),
    },
};
