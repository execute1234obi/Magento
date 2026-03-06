define(
    [
        'ko',
        'uiCollection',
        'mageUtils',
        'uiLayout',
        'underscore',
        'jquery',
        'mage/translate',
        'moment',
        'Magento_Ui/js/modal/alert',
        'Vnecoms_VendorsMessage/js/uploader',
        'mage/adminhtml/wysiwyg/tiny_mce/setup',
        'Magento_Ui/js/modal/modal',
        'domReady!'
    ],
    function(ko, Component, utils, layout, _, $, $t, moment, alert, Uploader) {
        'use strict';

        return Component.extend({
            default: {
                template: 'Vnecoms_VendorsMessage/messages',
                showSpinner: true,
                visible: true,
                disabled: false,
                messageInput: '',
                loader_image: '',
                message_id: ''
            },

            initialize: function () {
                this._super();
                /*this.initBindClicks.bind(this);*/
                return this;
            },

            /**
             *  Initialize wysiwyg
             */
            initEditor: function() {
                var self = this;
                var wysiwygpage_reply_msg_box = new wysiwygSetup(
                    "reply_msg_box",
                    {
                        "width": "100%",
                        "height": "20em",
                        "plugins":"",
                        "tinymce4": {
                            "toolbar": "formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table charmap",
                            "plugins": "",
                            "content_css": self.get('content_css')
                        },
                        "tinymce": {
                            "toolbar": "formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table charmap",
                            "plugins": "",
                            "content_css": self.get('content_css')
                        }
                    }
                );
                wysiwygpage_reply_msg_box.setup('exact');
            },

            /**
             * Calls 'initObservable' of parent
             *
             * @returns {Object} Chainable.
             */
            initObservable: function () {
                var self = this;
                this._super()
                    .observe([
                        'messageInput',
                        'messages',
                    ]);
                return this;
            },

            /**
             * Get All Messages.
             */
            getMessages: function(){
            	return this.messages();
            },

            /**
             * Show preview image modal.
             */
            previewImage: function(message, event) {
            	if(
        			$(event.target).is('.action-icon') ||
        			$(event.target).is('.download-action')
    			){
            		window.setLocation(message.download_url);
            		return true;
        		};

        		if(!message.is_image) return;

            	var id = 'vendor-message-attachment-'+message.id;
           	 	if($('#'+id).length){
	           		 $('#'+id).modal('openModal');
           	 	}else{
   	 			$('<div class="thumbnail-preview" id="'+id+'"></div>').html('<div class="thumbnail-preview-image-block"><img class="thumbnail-preview-image" src="'+message.url+'" /></div>')
					.modal({
					    title: message.name,
					    type: 'popup',
					    modalClass: '_image-box vendor-message-attachment-modal',
					    autoOpen: true,
					    innerScroll: true,
					    buttons:{}
					});
           	 	}
            },

            /**
             * Get uploaded values
             */
            getUploadedValues: function() {
            	var uploadedValues = [];
            	if(this.regions['uploader']){
            		var uploader = this.regions['uploader']()[0];
            		uploader.value.each(function(file){
            			uploadedValues.push(file.file);
            		});
            	}

            	return uploadedValues.join('||');
            },

            toggleMessageView: function(data, event) {
                if (!$(event.currentTarget).parent().hasClass('last')) {
                    $(event.currentTarget).toggleClass("read");
                    $(event.currentTarget).siblings(".mailbox-read-message").toggleClass("hide");
                }
            },

            /**
             * Send message via ajax
             */
            sendMessage: function () {
        		/*Validate*/
            	if(!this.messageInput()){
            		alert({
            			title: $t('Error'),
            			content: $t('Please enter the message.'),
            		});
            		return;
            	}

                var self = this;
                var txtMessage = self.messageInput();
                var uploadedValues = self.getUploadedValues();
                $.ajax({
                    url: self.addMessageUrl,
                    method: "POST",
                    data: {
                        message: txtMessage,
                        attachments: uploadedValues
                    },
                    showLoader: true,
                    dataType: "json"
                }).done(function (response) {
                	if(response.error){
                		alert({
                			title: $t('Error'),
                			content: response.message,
                		});
                	}else if(!response.error){
                        /*Clear text box*/
                        self.messageInput('');
                        self.resetUploader();
                        tinyMCE.get("reply_msg_box").setContent('');
                        /*Add message*/
                        self.addMessage(response.data);
                    }
                }).fail(function () {
                	alert({
	            		title: $t('Error'),
	                    content: $t('Something wrong. Please try to refresh the page.')
	                });
                });
            },

            /**
             * Add message
             */
            addMessage: function(data) {
            	var messages = this.messages();
            	messages.push(data);
            	this.messages(messages);
            },

            /**
             * Reset uploader
             */
            resetUploader: function() {
            	if(this.regions['uploader']){
            		var uploader = this.regions['uploader']()[0];
            		uploader.value([]);
            	}
            }
        });
    }
);
