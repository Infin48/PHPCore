/**
 * This file is part of the PHPCore forum software
 * 
 * Made by InfinCZ 
 * @link https://github.com/Infin48
 *
 * @copyright (c) PHPCore Limited https://phpcore.cz
 * @license GNU General Public License, version 3 (GPL-3.0)
 */
$(document).ready(function () {
    'active' != $.cookie("cookie") && $('[js="cookie"]').show(),
        $('[js="cookie"] [js="cookie-button"]').on("click", function () {
            $.cookie('cookie', 'active', { expires: 365, path: '/' }), $('[js="cookie"]').fadeOut();
        });
});
