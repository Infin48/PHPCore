window.onload = function() {
    $('html').removeClass('html-hidden');
    $('[ajax-selector="dropdown"]').each(function () {
        if ($(this).find('[ajax-selector="dropdown-menu"]').length) {
            var elm = $(this).find('[ajax-selector="dropdown-menu"]');
            var off = elm.offset();
            var l = off.left;
            var w = elm.width();
            var docW = $('html').width();
    
            var isEntirelyVisible = (l + w <= docW);
            if (!isEntirelyVisible) {
                elm.addClass('dropdown-edge');
            } else {
                elm.removeClass('dropdown-edge');
            }
        }
    });
}
