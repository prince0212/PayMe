define(
    [
        'jquery',
        'mage/url'
    ],
    function ($, Url) {
        'use strict';
        return {
            getUrl: function () {
                return Url.build('payme/index/index/');
            }
        }
    }
);