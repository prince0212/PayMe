define([
    "Deloitte_PayMe/js/paycode-generator/paycode",
    "Deloitte_PayMe/js/paycode-generator/qrcode"
], function (paycode, qrcode) {
    'use strict';

    return function (config, element) {
        var qrCodeLink = config.qrCodeLink;
        var typenumber = config.typenumber;
        var errorcorrection = config.errorcorrection;
        var qrCodeSize = config.size;

        var create_qrcode = function (text, typeNumber, errorCorrectionLevel, mode, mb) {
            qrcode.stringToBytes = qrcode.stringToBytesFuncs[mb];
            if (typeNumber == 0) {
                typeNumber = suggestTypeNumber(text);
            }

            var qr = qrcode(typeNumber || 4, errorCorrectionLevel || 'M');
            qr.addData(text, mode);
            qr.make();

            return qr
        };

        var suggestTypeNumber = function (text) {
            var length = text.length;
            if (length <= 32) {
                return 3;
            } else if (length <= 46) {
                return 4;
            } else if (length <= 60) {
                return 5;
            } else if (length <= 74) {
                return 6;
            } else if (length <= 86) {
                return 7;
            } else if (length <= 108) {
                return 8;
            } else if (length <= 130) {
                return 9;
            } else if (length <= 151) {
                return 10;
            } else if (length <= 177) {
                return 11;
            } else if (length <= 203) {
                return 12;
            } else if (length <= 241) {
                return 13;
            } else if (length <= 258) {
                return 14;
            } else if (length <= 292) {
                return 15;
            } else {
                return 40;
            }
        }

        var encodeImageFileAsURL = function (element) {
            var file = element.files[0];
            var reader = new FileReader();
            reader.onloadend = function () {
                var logo = document.getElementById('logo');
                logo.src = reader.result;
            }
            reader.readAsDataURL(file);
        }

        var update_qrcode = function (qrCodeLink, typenumber, errorcorrection, qrCodeSize) {
            var text = qrCodeLink;
            var t = typenumber;
            var e = errorcorrection;
            var m = 'Byte';
            var mb = 'UTF-8';
            var qr = create_qrcode(text, t, e, m, mb);
            var size = qrCodeSize;
            var consumer = false;

            var canvas = document.getElementById('payCodeCanvas');
            var ctx = canvas.getContext('2d');
            var logo = document.getElementById('logo');

            canvas.width = size;
            canvas.height = size;
            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            paycode.drawPayCode(qr, canvas, 7, logo, consumer);
            //drawPayCode(qr, canvas, 7, logo, consumer);
        };
        var update_qrcode = update_qrcode(qrCodeLink, typenumber, errorcorrection, qrCodeSize);
    }
});