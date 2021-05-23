function parseNewLines(html) {
    var array = [];
    var result = [];
    var lastTextPosition = null;
    var firstTextPosition = null;
    html = $(html);
    $.each(html, function (i, el) {
        array[i] = '<'+html[i].tagName.toLowerCase()+'>' + $(el).html() + '</'+html[i].tagName.toLowerCase()+'>';
        if ($(el).html().replace(/(<([^>]+)>)/ig, '') != '') {
            if (firstTextPosition == null) {
                firstTextPosition = i;
            }
            lastTextPosition = i;
        }
    });

    for (var i = firstTextPosition; i <= lastTextPosition; i++) {
        result[i] = array[i];
    }

    return result.join('');
}

$('[ajax-selector="navbar navbar-default"] [ajax-selector="navbar-content"]').first().clone().appendTo('[ajax-selector="navbar navbar-mobile"]');

$('[ajax="close"]').on('click', function() {
    $(this).closest('[ajax-selector="alert"]').remove();

    if ($(this).parents('.window')) {
        $(this).closest('.window').removeClass('window-active');
    }
});


// FONT AWESOME IN PSEUDO ELEMENTS
window.FontAwesomeConfig = {
    searchPseudoElements: true
}


$('[ajax-selector="tab '+$('.active[ajax="tab"]').data('name')+'"]').show();

$('[ajax="tab"]').on('click', function () {
 
    $('[ajax="tab"]').removeClass('active');
    $(this).addClass('active');

    $('[ajax-selector^="tab"]').hide();
    $('[ajax-selector="tab '+$(this).data('name')+'"]').show();
});

$('.dropdown-name-collapse a').on('click', function() {

    var $navbarMobile = $('[ajax-selector="navbar navbar-mobile"]');

    if ($navbarMobile.hasClass('navbar-opened')) {
        $('html').removeClass('html-navbar');
        $navbarMobile.removeClass('navbar-opened');
    } else {
        $('html').addClass('html-navbar');
        $navbarMobile.addClass('navbar-opened');
    }
});

$('body').on('click', function (event) {
    if ($(event.target).parents('[ajax-selector^="navbar"]').length == 0) {
        $('.navbar-opened').removeClass('navbar-opened');
        $('html').removeClass('html-navbar');
    }
});

$('[ajax-selector="dropdown"] > a').on('click', function() {

    var $dropdown = $(this).closest('[ajax-selector="dropdown"]');

    if ($dropdown.hasClass('dropdown-opened')) {
        $dropdown.removeClass('dropdown-opened');
    } else {
        $dropdown.addClass('dropdown-opened');
    }
});

// SUBMIT HTML
$('input[type="submit"]').on('click', function() {
    $('.trumbowyg-textarea').val(parseNewLines($('.trumbowyg .trumbowyg-editor').html()));
});

$('[ajax-selector="navbar navbar-default"] [ajax-selector="dropdown"] > a, [ajax-selector="navbar navbar-default"] [ajax-selector="dropdown-menu"], [ajax-selector="panel"] [ajax-selector="dropdown"] > a, [ajax-selector="panel"] [ajax-selector="dropdown-menu"]').hover(function() {
    $(this).closest('[ajax-selector="dropdown"]').addClass('dropdown-opened');
}, function () {
    $(this).closest('[ajax-selector="dropdown"]').removeClass('dropdown-opened');
});

$('body').on('click', function (e) {
    if (!$(e.target).parents('[ajax-selector="window"]').length) {
        $('[ajax-selector="window"]').removeClass('window-active');
    }
});

