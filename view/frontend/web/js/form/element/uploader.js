/**
 * @description Uploader JS
 * @author      C. M. de Picciotto <d3p1@d3p1.dev> (https://d3p1.dev/)
 */
define([
    'Magento_Ui/js/form/element/file-uploader'
], function (Element) {
    'use strict';

    return Element.extend({
        /**
         *
         * @type {Object}
         *
         */
        defaults: {
            template      : 'D3p1_CustomerFile/form/element/uploader',
            previewTmpl   : 'D3p1_CustomerFile/form/element/uploader/preview',
            fieldScope    : 'customer',
            fieldAttribute: ''
        },

        /**
         * Initialize
         *
         * @returns {void}
         * @public
         */
        initialize: function () {
            this._super();
            this.fieldName = this.fieldScope + '[' + this.fieldAttribute + ']';
        }
    });
});