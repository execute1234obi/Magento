define([], function () {
    'use strict';

    function resolveBadge(value) {
        if (value === 2) {
            return {
                text: 'Approve',
                bg: '#e8f9ef',
                color: '#11b85a'
            };
        }

        if (value === 1 || value === 4) {
            return {
                text: 'On Process',
                bg: '#fff2e8',
                color: '#ff8a3d'
            };
        }

        if (value === 3) {
            return {
                text: 'Unapproved',
                bg: '#fdecec',
                color: '#d14343'
            };
        }

        return {
            text: 'Not Submited',
            bg: '#eef3f8',
            color: '#64748b'
        };
    }

    function buildStyle(bg, color) {
        return [
            'display:inline-flex',
            'align-items:center',
            'justify-content:center',
            'min-width:0',
            'padding:4px 12px',
            'border:0',
            'border-radius:6px',
            'font-size:13px',
            'font-weight:500',
            'line-height:1.2',
            'box-shadow:none',
            'white-space:nowrap',
            'background-color:' + bg + ' !important',
            'background-image:none !important',
            'color:' + color + ' !important'
        ].join(';');
    }

    return function (Target) {
        return Target.extend({
            getLabel: function (record) {
                var html = this._super(record);
                var value = parseInt(record[this.index], 10);
                var tone = resolveBadge(value);
                var style = buildStyle(tone.bg, tone.color);

                if (typeof html !== 'string' || !html) {
                    return html;
                }

                if (html.indexOf('style=') !== -1) {
                    html = html.replace(/style="[^"]*"/, 'style="' + style + '"');
                } else {
                    html = html.replace('<div ', '<div style="' + style + '" ');
                }

                return html.replace(/>[\s\S]*<\/div>$/, '>' + tone.text + '</div>');
            }
        });
    };
});
