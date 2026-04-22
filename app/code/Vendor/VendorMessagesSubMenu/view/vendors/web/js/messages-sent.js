define(
    [
        'uiComponent',
        'jquery',
        'mage/translate',
        'Magento_Ui/js/modal/alert'
    ],
    function (Component, $, $t, alert) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Vendor_VendorMessagesSubMenu/messages-sent',
                showSpinner: true,
                visible: true,
                disabled: false,
                messageInput: '',
                loader_image: '',
                message_id: ''
            },

            initialize: function () {
                this._super();
                this.scrollToBottomAsync();
                return this;
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'messageInput',
                        'messages'
                    ]);

                return this;
            },

            getMessages: function () {
                return this.messages() || [];
            },

            scrollToBottomAsync: function () {
                var self = this;

                window.setTimeout(function () {
                    self.scrollToBottom();
                }, 0);
            },

            scrollToBottom: function () {
                var box = document.querySelector('.vendor-message-sent__messages');
                if (box) {
                    box.scrollTop = box.scrollHeight;
                }
            },

            previewImage: function (message, event) {
                if (
                    $(event.target).is('.attachment-icon') ||
                    $(event.target).is('.vendor-message-sent__attachment-download')
                ) {
                    window.setLocation(message.download_url);
                    return true;
                }

                if (!message.is_image) {
                    return;
                }

                var id = 'vendor-message-attachment-' + message.id;
                if ($('#' + id).length) {
                    $('#' + id).modal('openModal');
                    return;
                }

                $('<div class="thumbnail-preview" id="' + id + '"></div>')
                    .html('<div class="thumbnail-preview-image-block"><img class="thumbnail-preview-image" src="' + message.url + '" /></div>')
                    .modal({
                        title: message.name,
                        type: 'popup',
                        modalClass: '_image-box vendor-message-attachment-modal',
                        autoOpen: true,
                        innerScroll: true,
                        buttons: {}
                    });
            },

            sendMessage: function () {
                if (!(this.messageInput() || '').trim()) {
                    alert({
                        title: $t('Error'),
                        content: $t('Please enter the message.')
                    });
                    return;
                }

                var self = this;
                $.ajax({
                    url: self.addMessageUrl,
                    method: 'POST',
                    data: {
                        message: self.messageInput()
                    },
                    showLoader: true,
                    dataType: 'json'
                }).done(function (response) {
                    if (response.error) {
                        alert({
                            title: $t('Error'),
                            content: response.message
                        });
                        return;
                    }

                    self.messageInput('');
                    self.addMessage(response.data);
                    self.scrollToBottomAsync();
                }).fail(function () {
                    alert({
                        title: $t('Error'),
                        content: $t('Something wrong. Please try to refresh the page.')
                    });
                });
            },

            addMessage: function (data) {
                var messages = this.messages();
                messages.push(data);
                this.messages(messages);
            }
        });
    }
);
