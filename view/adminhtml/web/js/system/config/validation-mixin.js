define([
    'jquery'
], function($) {
    'use strict';

    return function() {
        $.validator.addMethod(
            'validate-html-template-id',
            function(value, element) {
                return value === "" || /^.*\.(html|htm|dhtml)$/.test(value);
            },
            $.mage.__('Please use one of the following extensions : html, htm, dhtml')
        )
    }
});
