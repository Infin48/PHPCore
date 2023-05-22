$('body').on('click', function(event)
{
    if (!$(event.target).parents('[js="window"]').length)
    {
        $('[js="window"]').removeClass('window-active');
    }

    if ($(event.target).attr('ajax') == 'close' || $(event.target).parent().attr('ajax') == 'close')
    {
        $(event.target).closest('[js="alert"]').remove();
        $(event.target).closest('[js="window"]').remove();
    }

    if ($('[js="navbar navbar-side"]').hasClass('navbar-opened'))
    {
        if ($(event.target).parents('[js^="navbar"]').length == 0)
        {
            $('[js="navbar navbar-side"]').removeClass('navbar-opened');
            $('[js="opacity"]').addClass('opacity-hide').removeClass('opacity-show');
        }
    }
});

$(document).ready(function()
{
    $('html').removeClass('html-hidden');
});

window.FontAwesomeConfig = {
    searchPseudoElements: true
}

// Tab
$('.' + $('.tab-button.default').attr('id')).show();

$('.tab-button').on('click', function(event)
{
    $('.tab-button').removeClass('active');

    $('.tabcontent').hide();
    $(event.target).addClass('active');
    $('.' + event.target.id + '.tabcontent').css('display', 'block');
});

// Mobile dropdown
$('[ajax="collapse-navbar"]').on('click', function()
{
    var $navbarMobile = $('[js="navbar navbar-side"]');
    $navbarMobile.toggleClass('navbar-opened', !$navbarMobile.hasClass('navbar-opened'));
    $('[js="opacity"]').addClass('opacity-show').removeClass('opacity-hide');
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

$('input[type="radio"], input[type="checkbox"]').each(function()
{
    if ($(this).is(':checked') == false)
    {
        if ($(this).attr('type') == 'radio')
        {
            return;
        }
    }
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

$('[js="dropdown"]:not(.navbar-button-disabled) [js="dropdown-content"]').on('click', function() {

    if ($(this).closest('[js="dropdown"]').hasClass('navbar-dropdown-opened')) {

        $(this).closest('[js="dropdown"]').removeClass('navbar-dropdown-opened');
        $(this).find('[js="arrow-left"]').show();
        $(this).find('[js="arrow-down"]').hide();


    } else {
        $(this).closest('[js="dropdown"]').addClass('navbar-dropdown-opened');
        $(this).find('[js="arrow-left"]').hide();
        $(this).find('[js="arrow-down"]').show();
    }
});

$('[js="title"]').after($('[js="title"]').clone().addClass('title-disabled'));
$('[ajax="title"]').on({
    mouseenter: function() {
        
        if ($('[js="title"]:not(.title-disabled)').length) {

            var $title = $('[js="title"]:not(.title-disabled)');

        } else {
            var $title = $('[js="title"]:not(.title-disabled)').clone();
            $('[js="title"]').after($title);
        }

        $title.addClass('title-active');
        $title.removeClass('title-disabled');
        $title.find('[js="text"]').text($.trim($(this).attr('ajax-title')));
        $title.css({'left': $(this).offset().left, 'top': $(this).offset().top - 40});

        if ($title.width() + $(this).offset().left > $('html').width()) {
            $title.addClass('title-edge');
        } else {
            $title.removeClass('title-edge');
        }

        $('[js="title"]').removeClass('title-disabled');
    },
    mouseleave: function() {
        $('.title-active[js="title"]').removeClass('title-active').addClass('title-disabled');
        setTimeout(function() {
            $('.title-disabled[js="title"]').css({'left': 0, 'top': 0});
        }, 500);
    }
});

$.cAjax({
    success: {
        'run/delete-attachment': function ()
        {
            $(this).closest('.attachment').remove();
        },

        'run/?/up': function (settings, $element) {


            $listMedium = $element.children('[js="list-row-inner"]').children('[js="list-row-body"]').children('[js="list-row-medium"]');
            $listPrevMedium = $element.prev('[js="list-row"]').children('[js="list-row-inner"]').children('[js="list-row-body"]').children('[js="list-row-medium"]');

            if (!$element.prev().prev(':not(.list-row-disabled)').length) {
                $listMedium.find('[ajax-action="up"]').addClass('button-disabled');
                $listPrevMedium.find('[ajax-action="up"]').removeClass('button-disabled');
            }

            if (!$element.next().length) {
                if ($listMedium.find('[ajax-action="up"]').length) {
                    $listMedium.find('[ajax-action="down"]').removeClass('button-disabled');
                } else {
                    $listMedium.find('[ajax-action="down"]').removeClass('button-disabled');
                }

                $listPrevMedium.find('[ajax-action="down"]').addClass('button-disabled');
            }

            $element.prev().insertAfter($element);
        },

        'run/?/down': function (settings, $element) {

            $listMedium = $element.children('[js="list-row-inner"]').children('[js="list-row-body"]').children('[js="list-row-medium"]');
            $listNextMedium = $element.next().children('[js="list-row-inner"]').children('[js="list-row-body"]').children('[js="list-row-medium"]');

            if (!$element.next().next().length)
            {
                $listMedium.find('[ajax-action="down"]').addClass('button-disabled');
                $listNextMedium.find('[ajax-action="down"]').removeClass('button-disabled');
            }
            
            if (!$element.prev(':not(.list-row-disabled)').length) {
                $listMedium.find('[ajax-action="up"]').removeClass('button-disabled');
                $listNextMedium.find('[ajax-action="up"]').addClass('button-disabled');
            }

            $element.next().insertBefore($element);
        }
    },
});
