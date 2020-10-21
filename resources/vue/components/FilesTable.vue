<template>
    <table class="default documents"
           :data-folder_id="topfolder.folder_id"
           data-shiftcheck>
        <caption>
            <div class="caption-container">
                <div v-if="breadcrumbs && !table_title">
                    <a v-if="breadcrumbs[0]" :href="breadcrumbs[0].url" :title="'Zum Hauptordner'.toLocaleString()">
                        <studip-icon shape="folder-home-full"
                                     role="clickable"
                                     class="text-bottom"
                                     size="30"></studip-icon>
                        <span v-if="breadcrumbs.length == 1">
                            {{ breadcrumbs[0].name }}
                        </span>
                    </a>
                    <span v-for="(breadcrumb, index) in breadcrumbs"
                              :key="breadcrumb.folder_id"
                              v-if="index > 0">
                        /<a :href="breadcrumb.url">
                            {{breadcrumb.name}}
                        </a>
                    </span>
                </div>
                <div v-if="table_title">{{table_title}}</div>
            </div>
            <div v-if="topfolder.description" style="font-size: small" v-html="topfolder.description"></div>
        </caption>

        <colgroup>
            <col v-if="show_bulk_actions" width="30px" data-filter-ignore>
            <col width="60px" data-filter-ignore>
            <col>
            <col width="100px" class="responsive-hidden" data-filter-ignore>
            <col v-if="showdownloads" width="100px" class="responsive-hidden" data-filter-ignore>
            <col width="150px" class="responsive-hidden">
            <col width="120px" class="responsive-hidden" data-filter-ignore>
            <col v-if="topfolder.additionalColumns"
                 v-for="(name, index) in topfolder.additionalColumns"
                 :key="index"
                 data-filter-ignore
                 class="responsive-hidden">
            <col width="80px" data-filter-ignore>
        </colgroup>
        <thead>
            <tr class="sortable">
                <th v-if="show_bulk_actions" data-sort="false">
                    <input type="checkbox"
                           class="studip-checkbox"
                           data-proxyfor="table.documents tbody :checkbox"
                           data-activates="table.documents tfoot .multibuttons .button"
                           id="all_files_checkbox">
                    <label for="all_files_checkbox"></label>
                </th>
                <th @click="sort('mime_type')">
                    {{ 'Typ'.toLocaleString() }}
                    <studip-icon v-if="sortedBy == 'mime_type' && sortDirection == 'asc'" shape="arr_1down" role="clickable" size="16" class="text-bottom"></studip-icon>
                    <studip-icon v-if="sortedBy == 'mime_type' && sortDirection == 'desc'" shape="arr_1up" role="clickable" size="16" class="text-bottom"></studip-icon>
                </th>
                <th @click="sort('name')">
                    {{ 'Name'.toLocaleString() }}
                    <studip-icon v-if="sortedBy == 'name' && sortDirection == 'asc'" shape="arr_1down" role="clickable" size="16" class="text-bottom"></studip-icon>
                    <studip-icon v-if="sortedBy == 'name' && sortDirection == 'desc'" shape="arr_1up" role="clickable" size="16" class="text-bottom"></studip-icon>
                </th>
                <th @click="sort('size')" class="responsive-hidden">
                    {{ 'Größe'.toLocaleString() }}
                    <studip-icon v-if="sortedBy == 'size' && sortDirection == 'asc'" shape="arr_1down" role="clickable" size="16" class="text-bottom"></studip-icon>
                    <studip-icon v-if="sortedBy == 'size' && sortDirection == 'desc'" shape="arr_1up" role="clickable" size="16" class="text-bottom"></studip-icon>
                </th>
                <th v-if="showdownloads" @click="sort('downloads')" class="responsive-hidden">
                    {{ 'Downloads'.toLocaleString() }}
                    <studip-icon v-if="sortedBy == 'downloads' && sortDirection == 'asc'" shape="arr_1down" role="clickable" size="16" class="text-bottom"></studip-icon>
                    <studip-icon v-if="sortedBy == 'downloads' && sortDirection == 'desc'" shape="arr_1up" role="clickable" size="16" class="text-bottom"></studip-icon>
                </th>
                <th class="responsive-hidden" @click="sort('author_name')">
                    {{ 'Autor/-in'.toLocaleString() }}
                    <studip-icon v-if="sortedBy == 'author_name' && sortDirection == 'asc'" shape="arr_1down" role="clickable" size="16" class="text-bottom"></studip-icon>
                    <studip-icon v-if="sortedBy == 'author_name' && sortDirection == 'desc'" shape="arr_1up" role="clickable" size="16" class="text-bottom"></studip-icon>
                </th>
                <th class="responsive-hidden" @click="sort('chdate')">
                    {{ 'Datum'.toLocaleString() }}
                    <studip-icon v-if="sortedBy == 'chdate' && sortDirection == 'asc'" shape="arr_1down" role="clickable" size="16" class="text-bottom"></studip-icon>
                    <studip-icon v-if="sortedBy == 'chdate' && sortDirection == 'desc'" shape="arr_1up" role="clickable" size="16" class="text-bottom"></studip-icon>
                </th>
                <th v-if="topfolder.additionalColumns"
                    v-for="(name, index) in topfolder.additionalColumns"
                    :key="index"
                    @click="sort(index)"
                    class="responsive-hidden">
                    {{name}}
                    <studip-icon v-if="sortedBy == index && sortDirection == 'asc'" shape="arr_1down" role="clickable" size="16" class="text-bottom"></studip-icon>
                    <studip-icon v-if="sortedBy == index && sortDirection == 'desc'" shape="arr_1up" role="clickable" size="16" class="text-bottom"></studip-icon>
                </th>
                <th data-sort="false">{{ 'Aktionen'.toLocaleString() }}</th>
            </tr>
        </thead>
        <tbody class="subfolders">
            <tr v-if="files.length + folders.length == 0" class="empty">
                <td :colspan="numberOfColumns">
                    {{ 'Dieser Ordner ist leer'.toLocaleString() }}
                </td>
            </tr>
            <tr v-for="folder in sortedFolders"
                :id="'row_folder_' + folder.id "
                :data-permissions="folder.permissions">
                <td v-if="show_bulk_actions">
                    <input type="checkbox"
                           name="ids[]"
                           class="studip-checkbox"
                           :id="'file_checkbox_' + folder.id "
                           :value="folder.id">
                    <label :for="'file_checkbox_' + folder.id"></label>
                </td>
                <td class="document-icon">
                    <a :href="folder.url">
                        <studip-icon :shape="folder.icon" role="clickable" size="26" class="text-bottom"></studip-icon>
                    </a>
                </td>
                <td>
                    <a :href="folder.url">{{folder.name}}</a>
                </td>
                <td class="responsive-hidden"></td>
                <td v-if="showdownloads"
                    class="responsive-hidden">
                </td>
                <td class="responsive-hidden">
                    {{folder.author_name}}
                </td>
                <td class="responsive-hidden">
                    <studip-date-time :timestamp="folder.chdate" :relative="true"></studip-date-time>
                </td>
                <template v-if="topfolder.additionalColumns"
                          v-for="(name, index)  in topfolder.additionalColumns">
                    <td v-if="folder.additionalColumns && folder.additionalColumns[index] && folder.additionalColumns[index].html"
                        class="responsive-hidden"
                        v-html="folder.additionalColumns[index].html"></td>
                    <td v-else class="responsive-hidden"></td>
                </template>
                <td class="actions" v-html="folder.actions">
                </td>
            </tr>
        </tbody>
        <tbody class="files">
            <tr v-for="file in sortedFiles"
                :class="file.new ? 'new' : ''"
                :id="'fileref_' + file.id"
                role="row"
                data-permissions="file.isEditable ? 'w' : (file.download_url ? 'dr' : '')">
                <td v-if="show_bulk_actions">
                    <template v-if="file.download_url">
                        <input type="checkbox"
                               class="studip-checkbox"
                               name="ids[]"
                               :id="'file_checkbox_' + file.id"
                               :value="file.id">
                        <label :for="'file_checkbox_' + file.id"></label>
                    </template>
                </td>
                <td class="document-icon">
                    <a v-if="file.download_url" :href="file.download_url" target="_blank" rel="noopener noreferrer">
                        <studip-icon :shape="file.icon" role="clickable" size="24" class="text-bottom"></studip-icon>
                    </a>
                    <studip-icon v-else :shape="file.icon" role="clickable" size="24"></studip-icon>

                    <a :href="file.download_url"
                       v-if="file.download_url && file.mime_type.indexOf('image/') === 0"
                       class="lightbox-image"
                       data-lightbox="gallery"></a>
                </td>
                <td>
                    <a :href="file.details_url" data-dialog>{{file.name}}</a>

                    <studip-icon v-if="file.restrictedTermsOfUse"
                                 shape="lock-locked"
                                 role="info"
                                 size="16"
                                 :title="'Das Herunterladen dieser Datei ist nur eingeschränkt möglich.'.toLocaleString()"></studip-icon>
                </td>
                <td :data-sort-value="file.size"
                    class="responsive-hidden">
                    <studip-file-size v-if="file.size !== null" :size="parseInt(file.size, 10)"></studip-file-size>
                </td>
                <td v-if="showdownloads"
                    class="responsive-hidden">
                    {{file.downloads}}
                </td>
                <td class="responsive-hidden">
                    {{file.author_name}}
                </td>
                <td data-sort-value="file.chdate" class="responsive-hidden">
                    <studip-date-time :timestamp="file.chdate" :relative="true"></studip-date-time>
                </td>
                <template v-if="topfolder.additionalColumns"
                          v-for="(name, index)  in topfolder.additionalColumns">
                    <td v-if="file.additionalColumns && file.additionalColumns[index] && file.additionalColumns[index].html"
                        class="responsive-hidden"
                        v-html="file.additionalColumns[index].html"></td>
                    <td v-else class="responsive-hidden"></td>
                </template>
                <td class="actions" v-html="file.actions">
                </td>
            </tr>
        </tbody>
        <tfoot v-if="(topfolder.buttons && show_bulk_actions) || tfoot_link">
            <tr>
                <td :colspan="numberOfColumns - (tfoot_link ? 1 : 0)">
                    <div class="footer-items">
                        <span v-if="topfolder.buttons && show_bulk_actions"
                              v-html="topfolder.buttons" class="bulk-buttons"></span>
                        <span v-if="tfoot_link" :colspan="(topfolder.buttons && show_bulk_actions ? 1 : numberOfColumns)">
                            <a :href="tfoot_link.href">{{tfoot_link.text}}</a>
                        </span>
                        <span v-if="pagination" v-html="pagination" class="pagination"></span>
                    </div>
                </td>
            </tr>
        </tfoot>
    </table>
</template>


<script>
    export default {
        name: 'files-table',
        props: {
            topfolder: Object,
            folders: {
                type: Array,
                required: false,
                default: function () {
                    return [];
                }
            },
            files: Array,
            breadcrumbs: {
                type: Array,
                required: false,
                default: function () {
                    return [];
                }
            },
            showdownloads: {
                type: Boolean,
                required: false,
                default: true
            },
            table_title: {
                type: String,
                required: false,
                default: ''
            },
            show_bulk_actions: {
                type: Boolean,
                required: false,
                default: true
            },
            tfoot_link: {
                type: Object,
                required: false,
                default: null
            },
            pagination: {
                type: String,
                required: false,
                default: ''
            }
        },
        data: function () {
            return {
                sortedBy: "name",
                sortDirection: "asc"
            };
        },
        methods: {
            sort (column) {
                let oldDirection = this.sortDirection;
                if (this.sortedBy === column) {
                    this.sortDirection = oldDirection === "asc" ? "desc" : "asc";
                }
                this.sortedBy = column;
            },
            removeFile (id) {
                this.files.forEach((file, i) => {
                    if (file.id === id) {
                        this.$delete(this.files, i);
                    }
                });
            },
            removeFolder (id) {
                this.folders.forEach((folder, i) => {
                    if (folder.id === id) {
                        this.$delete(this.folders, i);
                    }
                });
            }
        },
        computed: {
            numberOfColumns () {
                return 7
                    + (this.showdownloads ? 1 : 0)
                    + Object.keys(this.topfolder.additionalColumns).length;
            },
            sortedFiles () {
                if (this.files.length == 0) {
                    return this.files;
                }
                if (["mime_type", "name", "author_name"].indexOf(this.sortedBy) !== -1) {
                    if (this.sortDirection === "asc") {
                        return this.files.sort((a, b) => a[this.sortedBy].localeCompare(b[this.sortedBy]));
                    } else {
                        return this.files.sort((a, b) => b[this.sortedBy].localeCompare(a[this.sortedBy]));
                    }
                }
                if (["size", "downloads", "chdate"].indexOf(this.sortedBy) !== -1) {
                    if (this.sortDirection === "asc") {
                        return this.files.sort((a, b) => parseInt(b[this.sortedBy], 10) - parseInt(a[this.sortedBy], 10));
                    } else {
                        return this.files.sort((a, b) => parseInt(a[this.sortedBy], 10) - parseInt(b[this.sortedBy], 10));
                    }
                }
                if (typeof this.topfolder.additionalColumns[this.sortedBy] !== "undefined") {
                    let is_string = false;
                    for (let i in this.files) {
                        if (typeof this.files[i].additionalColumns[this.sortedBy].order === "string" && !isNaN(parseFloat(this.files[i].additionalColumns[this.sortedBy].order))) {
                            is_string = true;
                            break;
                        }
                    }
                    if (is_string) {
                        if (this.sortDirection === "asc") {
                            return this.files.sort((a, b) => a.additionalColumns[this.sortedBy].order.localeCompare(b.additionalColumns[this.sortedBy].order));
                        } else {
                            return this.files.sort((a, b) => b.additionalColumns[this.sortedBy].order.localeCompare(a.additionalColumns[this.sortedBy].order));
                        }
                    } else {
                        if (this.sortDirection === "asc") {
                            return this.files.sort((a, b) => b.additionalColumns[this.sortedBy].order - a.additionalColumns[this.sortedBy].order);
                        } else {
                            return this.files.sort((a, b) => a.additionalColumns[this.sortedBy].order - b.additionalColumns[this.sortedBy].order);
                        }
                    }
                }
            },
            sortedFolders () {
                if (this.folders.length == 0) {
                    return this.folders;
                }
                if (["mime_type", "name", "author_name"].indexOf(this.sortedBy) !== -1) {
                    if (this.sortDirection === "asc") {
                        return this.folders.sort((a, b) => a[this.sortedBy].localeCompare(b[this.sortedBy]));
                    } else {
                        return this.folders.sort((a, b) => b[this.sortedBy].localeCompare(a[this.sortedBy]));
                    }
                }
                if (["chdate"].indexOf(this.sortedBy) !== -1) {
                    if (this.sortDirection === "asc") {
                        return this.folders.sort((a, b) => b[this.sortedBy] - a[this.sortedBy]);
                    } else {
                        return this.folders.sort((a, b) => a[this.sortedBy] - b[this.sortedBy]);
                    }
                }
                if (typeof this.topfolder.additionalColumns[this.sortedBy] !== "undefined") {
                    let is_string = false;
                    for (let i in this.folders) {
                        if (typeof this.folders[i].additionalColumns[this.sortedBy].order === "string" && !isNaN(parseFloat(this.folders[i].additionalColumns[this.sortedBy].order))) {
                            is_string = true;
                            break;
                        }
                    }
                    if (is_string) {
                        if (this.sortDirection === "asc") {
                            return this.folders.sort((a, b) => a.additionalColumns[this.sortedBy].order.localeCompare(b.additionalColumns[this.sortedBy].order));
                        } else {
                            return this.folders.sort((a, b) => b.additionalColumns[this.sortedBy].order.localeCompare(a.additionalColumns[this.sortedBy].order));
                        }
                    } else {
                        if (this.sortDirection === "asc") {
                            return this.folders.sort((a, b) => parseInt(b.additionalColumns[this.sortedBy].order, 10) - parseInt(a.additionalColumns[this.sortedBy].order, 10));
                        } else {
                            return this.folders.sort((a, b) => parseInt(a.additionalColumns[this.sortedBy].order, 10) - parseInt(b.additionalColumns[this.sortedBy].order, 10));
                        }
                    }
                }
                return this.folders.sort((a, b) => a.name.localeCompare(b.name));
            }
        }
    }
</script>
