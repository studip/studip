import { mapActions, mapGetters } from 'vuex';
import JSZip from 'jszip';
import FileSaver from 'file-saver';
import axios from 'axios';

export default {
    computed: {
        ...mapGetters({
            courseware: 'courseware',
            containerById: 'courseware-containers/byId',
            folderById: 'folders/byId',
            filesById: 'files/byId',
            structuralElementById: 'courseware-structural-elements/byId',
        }),
    },

    data() {
        return {
            exportFiles: {
                json: [],
                download: [],
            },
        };
    },

    methods: {
        async sendExportZip(root_id = null, options) {
            let zip = await this.createExportFile(root_id, options);
            await zip.generateAsync({ type: 'blob' }).then(function (content) {
                FileSaver.saveAs(content, 'courseware-export-' + new Date().toISOString().slice(0, 10) + '.zip');
            });
        },

        async createExportFile(root_id = null, options) {
            let completeExport = false;

            if (!root_id) {
                root_id = this.courseware.relationships.root.data.id;
                completeExport = true;
            }

            let exportData = await this.exportCourseware(root_id, options);

            let zip = new JSZip();
            zip.file('courseware.json', JSON.stringify(exportData.json));
            zip.file('files.json', JSON.stringify(exportData.files.json));

            if (completeExport) {
                zip.file('settings.json', JSON.stringify(exportData.settings));
            }

            // add all additional files from blocks
            for (let id in exportData.files.download) {
                zip.file(
                    id,
                    await fetch(exportData.files.download[id].url)
                        .then((response) => response.blob())
                        .then((textString) => {
                            return textString;
                        })
                );
            }

            return zip;
        },

        async exportCourseware(root_id, options) {
            let withChildren = false;

            if (options && options.withChildren === true) {
                withChildren = true;
            }

            let root_element = await this.structuralElementById({id: root_id});

            //prevent loss of data
            root_element = JSON.parse(JSON.stringify(root_element));

            // load whole courseware nonetheless, only export relevant elements
            let elements = await this.$store.getters['courseware-structural-elements/all'];

            root_element.containers = [];
            if (root_element.relationships.containers?.data?.length) {
                for (var j = 0; j < root_element.relationships.containers.data.length; j++) {
                    root_element.containers.push(
                        await this.exportContainer(
                            this.containerById({
                                id: root_element.relationships.containers.data[j].id,
                            })
                        )
                    );
                }
            }

            if (withChildren && elements !== []) {
                let children = await this.exportStructuralElement(root_id, elements);

                if (children.length) {
                    root_element.children = children;
                }
            }

            delete root_element.relationships;
            delete root_element.links;

            let settings = {
                'editing-permission-level': this.courseware.attributes['editing-permission-level'],
                'sequential-progression': this.courseware.attributes['sequential-progression']
            };

            return {
                json: root_element,
                files: this.exportFiles,
                settings: settings
            };
        },

        async exportToOER(element, options) {
            let formData = new FormData();

            let exportZip = await this.createExportFile(element.id, options);
            let zip = await exportZip.generateAsync({ type: 'blob' });

            let description = element.attributes.payload.description ? element.attributes.payload.description : '';
            let difficulty_start = element.attributes.payload.difficulty_start ? element.attributes.payload.difficulty_start : '1';
            let difficulty_end = element.attributes.payload.difficulty_end ? element.attributes.payload.difficulty_end : '12';

            if (element.relationships.image.data !== null) {
                let image = {};
                await axios.get(element.relationships.image.meta['download-url'] , {responseType: 'blob'}).then(response => { image = response.data });
                formData.append("image", image);
            }

            formData.append("data[name]", element.attributes.title);
            formData.append("tags[]", "Lernmaterial");
            formData.append("file", zip, (element.attributes.title).replace(/\s+/g, '_') + '.zip');
            formData.append("data[description]", description);
            formData.append("data[difficulty_start]", difficulty_start);
            formData.append("data[difficulty_end]", difficulty_end);
            formData.append("data[category]", 'elearning');

            axios({
                method: 'post',
                url: STUDIP.URLHelper.getURL('dispatch.php/oer/mymaterial/edit/'),
                data: formData,
                headers: { "Content-Type": "multipart/form-data"}
            }).then( () => {
                this.companionInfo({ info: this.$gettext('Seite wurde an OER Campus gesendet.') });
            });
        },

        async exportStructuralElement(parentId, data) {
            let children = [];

            for (var i = 0; i < data.length; i++) {
                if (data[i].relationships.parent.data?.id === parentId) {
                    let new_childs = await this.exportStructuralElement(data[i].id, data);
                    let content = { ...data[i] };
                    content.containers = [];

                    await this.loadStructuralElement(content.id);

                    let element = this.structuralElementById({ id: content.id });

                    // load containers, if there are any for this struct
                    if (element.relationships.containers?.data?.length) {
                        for (var j = 0; j < element.relationships.containers.data.length; j++) {
                            content.containers.push(
                                await this.exportContainer(
                                    this.containerById({
                                        id: element.relationships.containers.data[j].id,
                                    })
                                )
                            );
                        }
                    }

                    delete content.relationships;
                    content.children = new_childs;

                    children.push(content);
                }
            }

            return children;
        },

        async exportContainer(container_ref) {
            // make a local copy of the container
            let container = { ...container_ref };

            container.blocks = [];

            let blocks = this.$store.getters['courseware-blocks/all'];

            // now, load the blocks for this container, if there are any
            if (blocks.length) {
                for (var k = 0; k < blocks.length; k++) {
                    if (blocks[k].relationships.container?.data.id === container.id) {
                        container.blocks.push(await this.exportBlock(blocks[k]));
                    }
                }
            }

            delete container.relationships;

            return container;
        },

        async exportBlock(block_ref) {
            // make a local copy of the block
            let block = { ...block_ref };

            // export file data (if any)
            if (block_ref.relationships['file-refs']?.links?.related) {
                await this.exportFileRefs(block_ref.id);
            }

            delete block.relationships;

            return block;
        },

        async exportFileRefs(block_id) {
            // load file-ref data
            let refs = await this.loadFileRefs(block_id);

            // add infos to exportFiles JSON
            for (let ref_id in refs) {
                let fileref  = {};
                let folderId = refs[ref_id].relationships.parent.data.id;
                await this.loadFolder(folderId);
                let folder   = this.folderById({id: folderId});

                fileref.attributes = refs[ref_id].attributes;
                fileref.related_block_id = block_id;
                fileref.id = refs[ref_id].id;
                fileref.folder = {
                    id: folder.id,
                    name: folder.attributes.name,
                    type: folder.attributes['folder-type']
                }

                this.exportFiles.json.push(fileref);

                // prevent multiple downloads of the same file
                this.exportFiles.download[refs[ref_id].id] = {
                    folder: folderId,
                    url: refs[ref_id].meta['download-url']
                };
            }
        },

        ...mapActions([
            'loadStructuralElement',
            'loadFileRefs',
            'loadFolder',
            'companionInfo'
        ]),
    },
};
