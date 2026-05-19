define(['jquery'], function ($) {
    'use strict';

    var STYLE_ID = 'gcc-feedback-styles';
    var TOAST_ID = 'gcc-feedback-toast';
    var CONFIRM_ID = 'gcc-feedback-confirm';
    var PENDING_TOAST_KEY = 'gccFeedbackToast';
    var toastTimer = null;
    var confirmCallback = null;
    var eventsBound = false;

    function injectStyles() {
        if (document.getElementById(STYLE_ID)) {
            return;
        }

        var css = [
            'body.gcc-feedback-lock{overflow:hidden;}',
            '.gcc-feedback-toast{position:fixed;top:16px;right:16px;z-index:100001;display:flex;flex-direction:column;gap:12px;width:min(92vw,360px);pointer-events:none;}',
            '.gcc-feedback-toast__item{pointer-events:auto;display:none;align-items:flex-start;gap:12px;padding:14px 16px;border-radius:14px;border:1px solid transparent;background:#fff;box-shadow:0 18px 40px rgba(15,23,42,.16);font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;}',
            '.gcc-feedback-toast__item.is-visible{display:flex;animation:gccFadeIn .18s ease-out;}',
            '.gcc-feedback-toast__icon{width:34px;height:34px;border-radius:12px;display:grid;place-items:center;flex:0 0 auto;font-weight:800;color:#fff;background:linear-gradient(135deg,var(--gcc-feedback-accent),var(--gcc-feedback-accent-dark));}',
            '.gcc-feedback-toast__content{min-width:0;flex:1;}',
            '.gcc-feedback-toast__title{margin:0 0 2px;font-size:14px;font-weight:700;color:#0f172a;}',
            '.gcc-feedback-toast__message{display:block;font-size:13px;line-height:1.45;color:#374151;word-break:break-word;}',
            '.gcc-feedback-toast__close{border:none;background:transparent;color:#94a3b8;cursor:pointer;font-size:20px;line-height:1;padding:2px 4px;align-self:flex-start;}',
            '.gcc-feedback-toast__item.is-success{border-color:rgba(26,55,100,.18);--gcc-feedback-accent:#1A3764;--gcc-feedback-accent-dark:#0f2f5f;}',
            '.gcc-feedback-toast__item.is-warning{border-color:rgba(196,69,105,.28);--gcc-feedback-accent:#C44569;--gcc-feedback-accent-dark:#8f2a4a;}',
            '.gcc-feedback-toast__item.is-error{border-color:rgba(185,28,28,.2);--gcc-feedback-accent:#b91c1c;--gcc-feedback-accent-dark:#7f1d1d;}',
            '.gcc-feedback-modal{position:fixed;inset:0;z-index:100000;display:none;align-items:center;justify-content:center;padding:16px;}',
            '.gcc-feedback-modal.is-open{display:flex;}',
            '.gcc-feedback-modal__backdrop{position:absolute;inset:0;background:rgba(15,23,42,.56);backdrop-filter:blur(4px);}',
            '.gcc-feedback-modal__dialog{position:relative;width:min(92vw,440px);background:linear-gradient(180deg,#ffffff 0%,#f8fbff 100%);border:1px solid rgba(26,55,100,.14);border-radius:20px;box-shadow:0 30px 80px rgba(15,23,42,.28);padding:22px 22px 20px;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#111827;}',
            '.gcc-feedback-modal__badge{width:48px;height:48px;border-radius:16px;background:linear-gradient(135deg,#1A3764,#2e5a9b);display:grid;place-items:center;color:#fff;font-size:24px;font-weight:800;margin-bottom:14px;box-shadow:0 10px 24px rgba(26,55,100,.28);}',
            '.gcc-feedback-modal__title{margin:0 0 8px;font-size:20px;font-weight:800;line-height:1.2;color:#0f172a;}',
            '.gcc-feedback-modal__message{margin:0 0 18px;font-size:14px;line-height:1.55;color:#475569;}',
            '.gcc-feedback-modal__actions{display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap;}',
            '.gcc-feedback-modal__button{border:none;border-radius:12px;min-height:44px;padding:0 18px;font-size:14px;font-weight:700;cursor:pointer;transition:transform .15s ease,filter .15s ease;}',
            '.gcc-feedback-modal__button:hover{transform:translateY(-1px);}',
            '.gcc-feedback-modal__button--secondary{background:#fff;color:#1A3764;border:1px solid rgba(26,55,100,.2);}',
            '.gcc-feedback-modal__button--primary{background:linear-gradient(135deg,#1A3764,#0f2f5f);color:#fff;box-shadow:0 10px 20px rgba(15,47,95,.22);}',
            '.gcc-feedback-modal__button--danger{background:linear-gradient(135deg,#C44569,#8f2a4a);color:#fff;box-shadow:0 10px 20px rgba(196,69,105,.22);}',
            '@keyframes gccFadeIn{from{opacity:0;transform:translateY(-8px) scale(.98);}to{opacity:1;transform:translateY(0) scale(1);}}',
            '@media (max-width:575px){.gcc-feedback-toast{left:12px;right:12px;top:12px;width:auto;}.gcc-feedback-toast__item{padding:12px 14px;border-radius:12px;}.gcc-feedback-modal__dialog{padding:18px;border-radius:18px;}.gcc-feedback-modal__title{font-size:18px;}.gcc-feedback-modal__actions{justify-content:stretch;}.gcc-feedback-modal__button{flex:1 1 0;min-width:0;}}'
        ].join('');

        var style = document.createElement('style');
        style.id = STYLE_ID;
        style.type = 'text/css';

        if (style.styleSheet) {
            style.styleSheet.cssText = css;
        } else {
            style.appendChild(document.createTextNode(css));
        }

        document.head.appendChild(style);
    }

    function ensureToast() {
        if (document.getElementById(TOAST_ID)) {
            return;
        }

        $('body').append(
            '<div class="gcc-feedback-toast" id="' + TOAST_ID + '" aria-live="polite" aria-atomic="true">' +
                '<div class="gcc-feedback-toast__item" data-gcc-toast aria-hidden="true">' +
                    '<div class="gcc-feedback-toast__icon" aria-hidden="true">!</div>' +
                    '<div class="gcc-feedback-toast__content">' +
                        '<p class="gcc-feedback-toast__title"></p>' +
                        '<span class="gcc-feedback-toast__message"></span>' +
                    '</div>' +
                    '<button type="button" class="gcc-feedback-toast__close" aria-label="Close">&times;</button>' +
                '</div>' +
            '</div>'
        );
    }

    function ensureConfirm() {
        if (document.getElementById(CONFIRM_ID)) {
            return;
        }

        $('body').append(
            '<div class="gcc-feedback-modal" id="' + CONFIRM_ID + '" aria-hidden="true">' +
                '<div class="gcc-feedback-modal__backdrop" data-gcc-confirm-cancel></div>' +
                '<div class="gcc-feedback-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="gcc-feedback-confirm-title">' +
                    '<div class="gcc-feedback-modal__badge" aria-hidden="true">!</div>' +
                    '<h3 class="gcc-feedback-modal__title" id="gcc-feedback-confirm-title"></h3>' +
                    '<p class="gcc-feedback-modal__message"></p>' +
                    '<div class="gcc-feedback-modal__actions">' +
                        '<button type="button" class="gcc-feedback-modal__button gcc-feedback-modal__button--secondary" data-gcc-confirm-cancel>Cancel</button>' +
                        '<button type="button" class="gcc-feedback-modal__button gcc-feedback-modal__button--danger" data-gcc-confirm-ok>Remove</button>' +
                    '</div>' +
                '</div>' +
            '</div>'
        );
    }

    function bindEvents() {
        if (eventsBound) {
            return;
        }

        eventsBound = true;

        $(document).on('click', '#' + TOAST_ID + ' .gcc-feedback-toast__close', function () {
            hideToast();
        });

        $(document).on('click', '#' + CONFIRM_ID + ' [data-gcc-confirm-cancel]', function (e) {
            e.preventDefault();
            closeConfirm();
        });

        $(document).on('click', '#' + CONFIRM_ID + ' [data-gcc-confirm-ok]', function (e) {
            var callback;

            e.preventDefault();
            callback = confirmCallback;
            closeConfirm();

            if (typeof callback === 'function') {
                callback();
            }
        });

        $(document).on('keydown.gccFeedback', function (e) {
            if (e.keyCode === 27) {
                closeConfirm();
            }
        });
    }

    function getToastMeta(type) {
        var normalized = type === 'success' || type === 'error' ? type : 'warning';

        if (normalized === 'success') {
            return {
                className: 'is-success',
                icon: 'OK',
                title: 'Success'
            };
        }

        if (normalized === 'error') {
            return {
                className: 'is-error',
                icon: 'X',
                title: 'Error'
            };
        }

        return {
            className: 'is-warning',
            icon: '!',
            title: 'Notice'
        };
    }

    function hideToast() {
        var $toast = $('#' + TOAST_ID);
        var $item = $toast.find('[data-gcc-toast]');

        window.clearTimeout(toastTimer);
        $item.removeClass('is-visible');
        $item.attr('aria-hidden', 'true');

        window.setTimeout(function () {
            if (!$item.hasClass('is-visible')) {
                $toast.hide();
            }
        }, 180);
    }

    function showToast(message, type, title, duration) {
        var meta = getToastMeta(type);
        var $toast;
        var $item;

        if (!message) {
            return;
        }

        injectStyles();
        ensureToast();
        bindEvents();

        $toast = $('#' + TOAST_ID);
        $item = $toast.find('[data-gcc-toast]');

        window.clearTimeout(toastTimer);
        $item.removeClass('is-success is-warning is-error is-visible');
        $item.addClass(meta.className);
        $item.find('.gcc-feedback-toast__icon').text(meta.icon);
        $item.find('.gcc-feedback-toast__title').text(title || meta.title);
        $item.find('.gcc-feedback-toast__message').text(message);
        $item.attr('aria-hidden', 'false');
        $toast.stop(true, true).show();
        $item.addClass('is-visible');

        toastTimer = window.setTimeout(function () {
            hideToast();
        }, typeof duration === 'number' ? duration : 3500);
    }

    function closeConfirm() {
        var $modal = $('#' + CONFIRM_ID);

        confirmCallback = null;
        $modal.removeClass('is-open');
        $modal.attr('aria-hidden', 'true');
        $('body').removeClass('gcc-feedback-lock');
    }

    function showConfirm(message, callback, options) {
        var $modal;
        var $okButton;
        var confirmTone;
        var confirmText;
        var cancelText;

        injectStyles();
        ensureConfirm();
        bindEvents();

        options = options || {};
        confirmCallback = typeof callback === 'function' ? callback : null;
        $modal = $('#' + CONFIRM_ID);
        $okButton = $modal.find('[data-gcc-confirm-ok]');
        confirmTone = options.tone || 'danger';
        confirmText = options.confirmText || 'Remove';
        cancelText = options.cancelText || 'Cancel';

        $modal.find('.gcc-feedback-modal__title').text(options.title || 'Confirm action');
        $modal.find('.gcc-feedback-modal__message').text(message || '');
        $modal.find('[data-gcc-confirm-cancel]').text(cancelText);
        $okButton.text(confirmText);
        $okButton.removeClass('gcc-feedback-modal__button--primary gcc-feedback-modal__button--danger');

        if (confirmTone === 'primary') {
            $okButton.addClass('gcc-feedback-modal__button--primary');
        } else {
            $okButton.addClass('gcc-feedback-modal__button--danger');
        }

        $modal.addClass('is-open');
        $modal.attr('aria-hidden', 'false');
        $('body').addClass('gcc-feedback-lock');

        window.setTimeout(function () {
            $modal.find('[data-gcc-confirm-cancel]').first().trigger('focus');
        }, 0);
    }

    function setPendingToast(message, type, title) {
        var payload;

        if (!message) {
            return;
        }

        payload = {
            message: message,
            type: type || 'success',
            title: title || ''
        };

        try {
            window.sessionStorage.setItem(PENDING_TOAST_KEY, JSON.stringify(payload));
        } catch (e) {
            // Ignore storage failures and keep the redirect flow moving.
        }
    }

    function showPendingToast() {
        var raw;
        var payload;

        try {
            raw = window.sessionStorage.getItem(PENDING_TOAST_KEY);

            if (!raw) {
                return;
            }

            window.sessionStorage.removeItem(PENDING_TOAST_KEY);
            payload = JSON.parse(raw);

            if (payload && payload.message) {
                showToast(payload.message, payload.type, payload.title);
            }
        } catch (e) {
            // Storage may be unavailable in some privacy modes.
        }
    }

    return {
        toast: showToast,
        confirm: showConfirm,
        closeConfirm: closeConfirm,
        setPendingToast: setPendingToast,
        showPendingToast: showPendingToast
    };
});
