/*
 * Copyright © 2017 Vnecoms. All rights reserved.
 */

define(
    [
        'Magento_Ui/js/lib/validation/validator',
        'Magento_Ui/js/form/element/file-uploader',
        'ko'
    ],
    function (
        validator,
        UpLoader,
        ko
    ) {
        return UpLoader.extend({
            defaults: {
                template: 'Vnecoms_VendorsMessage/uploader/uploader',
                previewTmpl: 'Vnecoms_VendorsMessage/uploader/preview',
                isMultipleFiles : true,
                inputName: 'image',
                uploaderConfig: {
                    dataType: 'json',
                    sequentialUploads: true,
                    formData: {
                        'form_key': window.FORM_KEY
                    }
                },
                links: {
                    value: '${ $.parentName }:uploadValue'
                }
            },

            /**
             * Invokes initialize method of parent class,
             * contains initialization logic
             */
            initialize: function () {
                _.bindAll(this, 'reset');
                this._super()
                    .setInitialValue()
                    ._setClasses()
                    .initSwitcher();

                return this;
            },

            /**
             *  Retrieve files type allowed
             */
            getFileTypesAllowed: function() {
                return this.uploaderConfig.acceptFileTypes;
            },

            /**
             *  Retrieve max number file allow to upload
             */
            getMaxFileNumber: function() {
                return this.uploaderConfig.maxFileNumber;
            },

            /**
             *  check upload limit
             *
             * @returns {*}
             */
            onFilesChoosed: function () {
                var files = this.value();
                if (files.length >= this.getMaxFileNumber()) {
                    alert("maximum number of files exceeded, You can't upload more!");
                    return false;
                }
                return this;
            },

            /**
             * Is Image File
             */
            isImage: function(filename){
            	var extension = filename.split('.').pop().toLowerCase();
            	return ['png','jpg','jpeg','gif'].indexOf(extension) != -1;
            },
            
            /**
             * Get file class name
             */
            getClassIcon:function (filename){
            	var extension = filename.split('.').pop().toLowerCase();
            	switch(extension){
            		case 'rar':
            		case 'tgz':
            		case 'bz':
	                case 'zip':
	                    return 'message-icon-file-zip';
	                case 'pdf':
	                    return 'message-icon-file-pdf';
	                case 'doc':
	                case 'docx':
	                    return 'message-icon-file-word';
	                case 'xls':
	                case 'xlsx':
	                    return 'message-icon-file-excel';
	                case 'png':
	                case 'jpeg':
	                case 'jpg':
	                case 'gif':
	                    return 'message-icon-file-image';
	                default: return 'message-icon-file-empty';
	            }
            }

        });
    }
);