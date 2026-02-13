define([
    'jquery',
    'Magento_Ui/js/form/element/file-uploader',
    'mage/translate'
], function ($, Element, $t) {
    'use strict';

    return Element.extend({
        defaults: {
            fileInputName: 'vendorverification-docs',
            maxFileAllow: 0,
            myUploadId: ""
        },

        /**
         * INITIALIZE COMPONENT
         * Yahan savedFiles ko observable value mein daalna zaroori hai
         */
        initialize: function () {
            this._super();
            var self = this;

            // Agar savedFiles mein data hai toh use component ki value mein load karein
            if (this.savedFiles && this.savedFiles.length > 0) {
                $.each(this.savedFiles, function (index, file) {
                    // Standard Magento way to show files on load
                    self.addFile(file);
                    
                    // Aapka custom logic
                    if (self.myUploadId === 'vendorcert') {
                        $('#is-certselected').val(file.name);
                    }
                });
            }
            return this;
        },

        addFile: function (file) {
            // Check if file is valid
            if (!file.url) return this;

            if (this.isMultipleFiles) {
                this.value.push(file);
            } else {
                this.value([file]);
            }
            return this;
        },

        onFileUploaded: function (e, data) {
            this._super(e, data);
            var response = data.result;
            if (this.myUploadId === 'vendorcert' && response.file) {
                $('#is-certselected').val(response.file);
            }
        },

        removeFile: function (file) {
            if (this.myUploadId === 'vendorcert') {
                $('#is-certselected').val('');
            }
            return this._super(file);
        }
    });
});