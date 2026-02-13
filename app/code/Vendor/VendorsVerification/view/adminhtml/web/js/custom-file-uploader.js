define([
    'jquery',    
    'Magento_Ui/js/form/element/file-uploader',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function ($, Element, alert) {
    'use strict';

    return Element.extend({
        defaults: {
            fileInputName: 'adimageupload'
        },
         /**
         * Initializes file uploader plugin on provided input element.
         *
         * @param {HTMLInputElement} fileInput
         * @returns {FileUploader} Chainable.
         */
        initUploader: function (fileInput) {
			var self =  this;
			if(this.savedFiles){
			var savedFiles =  this.savedFiles;
            if(savedFiles.length){
				$.each(savedFiles, function(index, file){					
					self.addFile(file);
				});
			}
		    }
			return this._super();
		},
        /**
         * Handler of the file upload complete event.
         *
         * @param {Event} e
         * @param {Object} data
         */
        onFileUploaded: function (e, data) {
            
            var response = data.result; // Here the response data are stored             
            console.log("Filoe upload Response");
            console.log(response);
            if(response.success!=true){
				alert(response.message);
			}else{
				this._super(e, data);            
			}
        },        
        /**
         * Handler which is invoked prior to the start of a file upload.
         *
         * @param {Event} event - Event object.
         * @param {Object} data - File data that will be uploaded.
         */
        onBeforeFileUpload: function (event, data) {
			var adspaceId = $("#adspace").val();			
			if(adspaceId<=0 || adspaceId==''){				
				 //alert($.mage.__("Please Select AD Space"));
				  alert({
					  title: $.mage.__('Error'),
					  content: $.mage.__('Please Select AD Space'),
					  actions: {
						  always: function(){}
						  }
				});
				return;
			}
			var file = data.files[0],
                allowed = this.isFileAllowed(file),
                target = $(event.target);
                
              target.on('fileuploadsend', function (eventBound, postData) {
                    postData.data.append('adspace_id', adspaceId );
                    
                }.bind(data));   
			
			this._super(event, data);            			
		},
         /**
         * Adds provided file to the files list.
         *
         * @param {Object} file
         * @returns {FileUploader} Chainable.
         */
        addFile: function (file) {
            file = this.processFile(file);
            this.isMultipleFiles ?
                this.value.push(file) :
                this.value([file]);

            return this;
        },        
    });
});
