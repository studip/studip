CKEDITOR.plugins.add('studip-upload', {
    icons: 'upload',
    hidpi: true,
    lang: 'de,en',
    init: function(editor){
        var lang = editor.lang['studip-upload'];
        // utilities
        var isString = function(object) {
                return (typeof object) === 'string';
            },
            isImage = function(mime_type){
                return isString(mime_type) && mime_type.match('^image');
            },
            isSVG = function(mime_type){
                return isString(mime_type) && mime_type === 'image/svg+xml';
            },
            insertNode = function($node){
                editor.insertHtml($('<div>').append($node).html() + ' ');
            },
            insertImage = function(file){
                insertNode($('<img />').attr({
                    src: file.url,
                    alt: file.name,
                    title: file.name
                }));
            },
            insertLink = function(file){
                insertNode($('<a>').attr({
                    href: file.url,
                    type: file.type,
                    target: '_blank',
                    rel: 'nofollow'
                }).append(file.name));
            },
            insertFile = function(file){
                // NOTE StudIP sends SVGs as application/octet-stream
                if (isImage(file.type) && !isSVG(file.type)) {
                    insertImage(file);
                } else {
                    insertLink(file);
                }
            },
            //Ajax-call for img-upload via copy paste
            uploadFromBase64 = function(studipUpload_url, formDataToUpload, node) {
                $.ajax({
                    url: studipUpload_url,
                    data: formDataToUpload,
                    type:"POST",
                    contentType:false,
                    async: false,
                    processData:false,
                    cache:false,
                    dataType:"json",
                    error:function(err){
                        console.error(err);
                    },
                    success: function(data) {
                        if (data.files.length > 0) {
                            jQuery(node).attr('alt', data.files[0].name);
                            jQuery(node).attr('src', data.files[0].url);
                        }
                    }
                });
            },
            convertSrcDataToBlob = function (src_data) {
                //Split the data by the first comma, then check if the
                //string base64 is in the first part. If so, get the mime type
                //and decode the base64 data of the second part.
                var data_parts = src_data.split(',', 2);
                if (data_parts[0].indexOf('base64') === -1) {
                    //We cannot continue.
                    return false;
                }
                var mime_type = data_parts[0].match(/data\:([^;]*)\;/)[1];
                if (!mime_type) {
                    //We cannot continue.
                    return false;
                }
                if ((mime_type != 'image/png') && (mime_type != 'image/jpeg')) {
                    //We do not serve such mime types here!
                    return false;
                }
                var blob = atob(data_parts[1]);
                if (!blob) {
                    //There are no data in blob.
                    return false;
                }
                var uint8_blob = new Uint8Array(blob.length);
                if (!uint8_blob) {
                    //No uint8 data can be created from the blob.
                    return false;
                }
                for (var i = 0; i < blob.length; i++) {
                    uint8_blob[i] = blob.charCodeAt(i);
                }
                //Now the binary data is in the correct format.
                return new Blob([uint8_blob], {type: mime_type});
            },
            extractAndConvertSrcAttribute = function (node) {
                var src_attr = jQuery(node).attr('src');
                if (src_attr) {
                    var blob = convertSrcDataToBlob(src_attr);
                    if (blob === false) {
                        //Leave the element as it is.
                        return;
                    } else {
                        var form_data_to_upload = new FormData();
                        form_data_to_upload.append("files[]", blob);
                        uploadFromBase64(editor.config.studipUpload_url, form_data_to_upload, node);
                    }
                }
            },
            handleUploads = function(fileList){
                var errors = [];
                $.each(fileList, function(index, file){
                    if (file.url) {
                        insertFile(file);
                    } else {
                        errors.push(file.name + ': ' + file.error);
                    }
                });
                if (errors.length) {
                    alert(lang.uploadError + '\n\n' + errors.join('\n'));
                }
            };

        editor.on('instanceReady', function(event){
            var $container = $(event.editor.container.$);
            // install upload handler
            $('<input>')
                .attr({
                    class: 'fileupload',
                    type: 'file',
                    name: 'files[]',
                    multiple: true
                })
                .css('display', 'none')
                .appendTo($container)
                .fileupload({
                    url: editor.config.studipUpload_url,
                    singleFileUploads: false,
                    dataType: 'json',
                    done: function(e, data){
                        if (data.result.files) {
                            handleUploads(data.result.files);
                        } else {
                            alert(lang.uploadFailed + '\n\n' + data.result);
                        }
                    },
                    fail: function (e, data) {
                        alert(
                            lang.uploadFailed + '\n\n'
                            + lang.error + ' '
                            + data.errorThrown.message
                        );
                    }
                });
        });

        // avoid multiple uploads of the same file via drag and drop
        editor.on('beforeDestroy', function(event){
            $(event.editor.container.$).find('.fileupload').fileupload('destroy');
        });

        // ckeditor
        editor.addCommand('upload', {    // command handler
            exec: function(editor){
                $(editor.container.$).find('.fileupload').click();
            }
        });
        editor.ui.addButton('upload', {  // toolbar button
            label: lang.buttonLabel,
            command: 'upload',
            toolbar: 'insert,80'
        });

        //Handle the CKEditor paste event to be able to treat the image
        //uploading different than uploading "usual" HTML.
        editor.on('paste', function(event) {
            //There are two cases, because different browsers behave different
            //when copying data from the clipboard.
            if (event.data.dataValue) {
                var nodes = null;
                try {
                    nodes = jQuery(event.data.dataValue);
                } catch (error) {
                    //If jQuery cannot build a node from the data that is
                    //supposed to be HTML, do nothing.
                    return;
                }
                if (nodes.length < 1) {
                    //jQuery could not build a node out of the HTML snippet.
                    //Do nothing either.
                    return;
                }
                jQuery(nodes).each(function(index) {
                    if (this.tagName == 'IMG') {
                        extractAndConvertSrcAttribute(this);
                    } else {
                        //Traverse the child nodes and look for img nodes.
                        jQuery(this).find('img').each(function (index) {
                            extractAndConvertSrcAttribute(this);
                        });
                    }
                });
                event.data.dataValue = jQuery('<div></div>').append(nodes).html();
            } else {
                if (event.data.dataTransfer._.files.length > 0) {
                    for (i = 0; i < event.data.dataTransfer._.files.length; i++) {
                        var reader = new FileReader();
                        reader.readAsDataURL(event.data.dataTransfer._.files[i]);
                        reader.onload = function () {
                            var input = reader.result;
                            var blob = convertSrcDataToBlob(input);
                            if (blob === false) {
                                //TODO: Prevent the pasted element from appearing.
                            } else {
                                var formDataToUpload = new FormData();
                                formDataToUpload.append("files[]", blob);
                                var node = jQuery('<img>');
                                uploadFromBase64(editor.config.studipUpload_url, formDataToUpload, node);
                                event.editor.insertHtml(
                                    jQuery('<div></div>').append(node).html()
                                );
                            }
                        }
                    }
                }
            }
        });
    }
});
