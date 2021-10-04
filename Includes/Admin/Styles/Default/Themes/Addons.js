$('.navbar.navbar-default .container > ul').clone().appendTo('.navbar.navbar-mobile');

$('body').on('click', function(event) {

    if (!$(event.target).parents('[ajax-selector="window"]').length) {
        $('[ajax-selector="window"]').removeClass('window-active');
    }

    if ($(event.target).attr('ajax') == 'close') {
        $(event.target).closest('[ajax-selector="alert"]').remove();
        $(event.target).closest('[ajax-selector="window"]').remove();
    }

    if ($(event.target).parents('[ajax-selector^="navbar"]').length == 0) {
        $('.navbar-opened').removeClass('navbar-opened');
    }
});

$(document).ready(function() {
    $('html').removeClass('html-hidden');
});

window.FontAwesomeConfig = {
    searchPseudoElements: true
}

// TAB
$('.' + $('.tab-button.default').attr('id')).show();

$('.tab-button').on('click', function(event) {
    $('.tab-button').removeClass('active');

    $('.tabcontent').hide();
    $(event.target).addClass('active');
    $('.' + event.target.id + '.tabcontent').css('display', 'block');
});

// MOBILE DROPDOWN
$('[ajax="collapse"]').on('click', function() {

    var $navbarMobile = $('[ajax-selector="navbar navbar-side"]');
    $navbarMobile.toggleClass('navbar-opened', !$navbarMobile.hasClass('navbar-opened'));
});

$('input.script[type="radio"], input.script[type="checkbox"]').on('click', function() {
    var value = $(this).attr('value');

    if ($(this).attr('type') == 'checkbox') {
        if ($(this).is(':checked')) {
            value = 1;
        } else {
            value = 0;
        }
    }

    $('[show-on^="' + $(this).attr('name') + '"]').hide();
    $('[show-on="' + $(this).attr('name') + ':' + value + '"]').show();
    
    $('[hide-on^="' + $(this).attr('name') + '"]').show();
    $('[hide-on="' + $(this).attr('name') + ':' + value + '"]').hide();
});

$('input[type="radio"], input[type="checkbox"]').each(function() {
    if ($(this).is(':checked')) {
        $('[show-on="' + $(this).attr('name') + ':' + $(this).attr('value') + '"]').show();
    } else {
        $('[show-on="' + $(this).attr('name') + ':' + $(this).attr('value') + '"]').hide();
    }
});

$('[ajax-selector="dropdown"]').on('click', function() {

    if ($(this).hasClass('navbar-dropdown-opened')) {

        $(this).removeClass('navbar-dropdown-opened');
        $(this).find('[ajax-selector="arrow-left"]').show();
        $(this).find('[ajax-selector="arrow-down"]').hide();


    } else {
        $(this).addClass('navbar-dropdown-opened');
        $(this).find('[ajax-selector="arrow-left"]').hide();
        $(this).find('[ajax-selector="arrow-down"]').show();
    }
});

$('[ajax-selector="title"]').after($('[ajax-selector="title"]').clone().addClass('title-disabled'));
$('[ajax="title"]').on({
    mouseenter: function() {
        
        if ($('[ajax-selector="title"]:not(.title-disabled)').length) {

            var $title = $('[ajax-selector="title"]:not(.title-disabled)');

        } else {
            var $title = $('[ajax-selector="title"]:not(.title-disabled)').clone();
            $('[ajax-selector="title"]').after($title);
        }

        $title.addClass('title-active');
        $title.removeClass('title-disabled');
        $title.find('[ajax-selector="text"]').text($.trim($(this).data('title')));
        $title.css({'left': $(this).offset().left, 'top': $(this).offset().top - 40});

        if ($title.width() + $(this).offset().left > $('html').width()) {
            $title.addClass('title-edge');
        } else {
            $title.removeClass('title-edge');
        }

        $('[ajax-selector="title"]').removeClass('title-disabled');
    },
    mouseleave: function() {
        $('.title-active[ajax-selector="title"]').removeClass('title-active').addClass('title-disabled');
        setTimeout(function() {
            $('.title-disabled[ajax-selector="title"]').css({'left': 0, 'top': 0});
        }, 500);
    }
});

$.cAjax('up', {
    ajax: {
        url: '/admin/ajax/process/',
        method: 'get',
        context: {}
    },
    success: {
        default: function (settings, $element) {

            $listMedium = $element.children('[ajax-selector="list-row-inner"]').children('[ajax-selector="list-row-body"]').children('[ajax-selector="list-row-medium"]');
            $listPrevMedium = $element.prev('[ajax-selector="list-row"]').children('[ajax-selector="list-row-inner"]').children('[ajax-selector="list-row-body"]').children('[ajax-selector="list-row-medium"]');

            if (!$element.prev().prev(':not(.list-row-disabled)').length) {
                $listMedium.find('[ajax="up"]').remove();
                $listPrevMedium.find('[ajax="down"]').before('<a class="button button-icon button-up" ajax="up" ajax-process="/Up"><i class="fas fa-caret-up"></i></a>');
            }

            if (!$element.next().length) {
                if ($listMedium.find('[ajax="up"]').length) {
                    $listMedium.find('[ajax="up"]').after('<a class="button button-icon button-down" ajax="down" ajax-process="/Down"><i class="fas fa-caret-down"></i></a>');
                } else {
                    $listMedium.prepend('<a class="button button-icon button-down" ajax="down" ajax-process="/Down"><i class="fas fa-caret-down"></i></a>');
                }

                $listPrevMedium.find('[ajax="down"]').remove();
            }

            $element.prev().insertAfter($element);
        }
    }
});

$.cAjax('down', {
    ajax: {
        url: '/admin/ajax/process/',
        method: 'get',
        context: {}
    },
    success: {
        default: function (settings, $element) {

            $listMedium = $element.children('[ajax-selector="list-row-inner"]').children('[ajax-selector="list-row-body"]').children('[ajax-selector="list-row-medium"]');
            $listNextMedium = $element.next().children('[ajax-selector="list-row-inner"]').children('[ajax-selector="list-row-body"]').children('[ajax-selector="list-row-medium"]');

            if (!$element.next().next().length) {
                $listMedium.find('[ajax="down"]').remove();
                $listNextMedium.find('[ajax="up"]').after('<a class="button button-icon button-down" ajax="down" ajax-process="/Down"><i class="fas fa-caret-down"></i></a>');
            }
            
            if (!$element.prev(':not(.list-row-disabled)').length) {
                $listMedium.prepend('<a class="button button-icon button-up" ajax="up" ajax-process="/Up"><i class="fas fa-caret-up"></i></a>');
                $listNextMedium.find('[ajax="up"]').remove();
            }

            $element.next().insertBefore($element);
        }
    }
});
$.cAjax('process-window', {
    ajax: {
        url: '/admin/ajax/process/',
        method: 'get',
        context: {}
    },
    window: {
        url: '/admin/ajax/language/',
        context: {}
    }
});
$.cAjax('process', {
    ajax: {
        url: '/admin/ajax/process/',
        method: 'get',
        context: {}
    }
});
$.cAjax('execute', {
    ajax: {
        url: '/admin/ajax/execute/',
        method: 'get',
        context: {}
    }
});