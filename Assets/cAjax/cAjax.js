/**
 * This file is part of the PHPCore forum software
 * 
 * Made by InfinCZ 
 * @link https://github.com/Infin48
 *
 * @copyright (c) PHPCore Limited https://phpcore.cz
 * @license GNU General Public License, version 3 (GPL-3.0)
 */
(function($) {

    // Enabled
    var enabled = true;

    // Window
    var $window = $('[js="window"]');

    // Opacity
    var $opacity = $('[js="opacity"]');

    // Loading icon
    var $loading = $('[js="loading"]');

    // Window alert
    var $alert = $('[js="window-alert"]');

    // Settings
    var settings = {};

    var popup = {

        show: function()
        {
            $window.addClass('window-show').removeClass('window-hide');
            $opacity.addClass('opacity-show').removeClass('opacity-hide');
        },

        hide: function()
        {
            if ($window.hasClass('window-show'))
            {
                $window.addClass('window-hide').removeClass('window-show');
            }
            if ($opacity.hasClass('opacity-show'))
            {
                $opacity.addClass('opacity-hide').removeClass('opacity-show');
            }
        }
    }

    function end( alertText, alertType )
    {
        if ($alert.length && alertText && alertType)
        {
            t = 'error';
            if (alertType == 'error')
            {
                t = 'success';
            }

            $alert.addClass('window-show').removeClass('window-hide');
            $alert.find('[js^="window-alert-icon"]').hide();
            $alert.find('[js="window-alert-icon-' + alertType + '"]').show();
            $alert.removeClass('window-alert-' + t).addClass('window-alert-' + alertType);
            $alert.find('[js="window-alert-body"]').text(alertText);
            setTimeout(function()
            {
                $alert.addClass('window-hide').removeClass('window-show');
                setTimeout(function()
                {
                    enabled = true;
                }, 250);
            }, 1750);
        } else enabled = true;

        $loading.removeClass('loading-show');

        return false;
    }

    function run()
    {
        settings = self.settings;
        explode = self.settings.context.ajax.split('/');
        explode[0] = 'run';
        settings.context.ajax = explode.join('/');

        self.settings = settings;

        if (settings.submit)
        {
            $.each(settings.submit, function (name, method)
            {
                if (name === settings.context.ajax)
                {
                    settings.submit[name].call($button, settings, $element);
                    return false;
                }
                
                explode = settings.context.ajax.split('/');
                explode[1] = '?';
                
                if (explode.length != 3)
                {
                    return;
                }

                if (name === explode.join('/'))
                {
                    settings.submit[name].call($button, settings, $element);
                    return false;
                }
            });
        }

        $.post(window.location.origin + window.location.pathname + 'action-ajax/', self.settings.context, function (data)
        {
            $loading.addClass('loading-show');

            if (!data)
            {
                return end();
            }



            try {
                settings.data = $.parseJSON(data);
            } catch (error) {

                return end(data, 'error');
            }

            if (!settings.data.data)
            {
                settings.data.data = {};
            }

            if (settings.data.status != 'ok')
            {
                if (settings.failure)
                {
                    settings.failure.call($button, settings, $element);
                }

                return end(settings.data.message, 'error');
            }

            if (settings.data.redirect)
            {
                window.location.href = settings.data.redirect;
                return true;
            }

            if (settings.data.refresh)
            {
                location.reload();
                return true;
            }

            if (settings.success)
            {
                $.each(settings.success, function (name, method)
                {
                    if (name === settings.context.ajax)
                    {
                        settings.success[name].call($button, settings, $element);
                        return false;
                    }
                    
                    explode = settings.context.ajax.split('/');
                    explode[1] = '?';
                    
                    if (explode.length != 3)
                    {
                        return;
                    }

                    if (name === explode.join('/'))
                    {
                        settings.success[name].call($button, settings, $element);
                        return false;
                    }
                });
            }

            popup.hide();
            return end(settings.data.message ? settings.data.message : '', 'success');
        });
    }

    jQuery.cAjax = function (settings)
    {
        $(document).on('click', '[ajax-action]', function (event)
        {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();

            if (enabled == false)
            {
                return;
            }
            enabled = false;
            
            $loading.addClass('loading-show');

            var $button = $element = $(this);

            if (!$element.attr('js-id'))
            {
                $.each(['block block-form', 'block', 'form', 'list-row', 'panel'], function (key, selector)
                {
                    if ($button.closest('[js="'+selector+'"]').length)
                    {
                        $element = $button.closest('[js="'+selector+'"]').first();
                        return false;
                    }
                });
            }

            settings.context = {};

            settings.context.ajax = 'window/';
            if (typeof $(this).attr('ajax-window') == 'undefined')
            {
                settings.context.ajax = 'run/';
            }

            settings.context.id = $element.attr('ajax-id') ? $element.attr('ajax-id') : $(this).attr('ajax-id');
            settings.context.ajax += $element.attr('ajax-item') ? $element.attr('ajax-item') + '/' + $(this).attr('ajax-action') : $(this).attr('ajax-action');
            settings.context.selected = 0;
            if ($element.is('[ajax-selected]'))
            {
                settings.context.selected = 1;
            }
            self.settings = settings;

            self.$button = $(this);

            if (settings.context.ajax.startsWith('window'))
            {
                $.post(window.location.origin + window.location.pathname + 'action-ajax/', settings.context, function (data)
                {
                    if (!data)
                    {
                        return end();
                    }

                    try {
                        self.settings.data = $.parseJSON(data);
                    } catch (error) {

                        return end(data, 'error');
                    }
                    
                    if (settings.data.status != 'ok') {
                        return end(settings.data.message, 'error');
                    }

                    popup.show();
                    $window.find('[js="window-title"]').text(settings.data.data.title);
                    $window.find('[js="window-body"]').html(settings.data.data.content);

                    if (settings.data.data.submit) {
                        $window.find('[js="window-bottom"]').show();
                        $window.find('[js="window-submit"]').text(settings.data.data.submit);
                        $window.find('[js="window-cancel"]').text(settings.data.data.close);
                    } else {
                        $window.find('[js="window-bottom"]').hide();
                    }

                    enabled = true;
                    $loading.removeClass('loading-show');
                });

                $(document).on('click', '[ajax="confirm"]', function (event)
                {
                    event.preventDefault();
                    event.stopPropagation();
                    event.stopImmediatePropagation();

                    if (enabled == false)
                    {
                        return;
                    }

                    enabled = false;

                    $loading.addClass('loading-show');

                    run();

                    return;
                });

                $(document).on('click', function(event)
                {

                    if (!$(event.target).parents('[js="window"]').length)
                    {
                        $('[ajax="confirm"]').off('click');
                        popup.hide();
                    }
                
                    if ($(event.target).attr('ajax') == 'window-close')
                    {
                        $('[ajax="confirm"]').off('click');
                        popup.hide();
                    }
                });


            } else {
                
                if (settings.submit)
                {
                    $.each(settings.submit, function (name, method)
                    {
                        if (name === settings.context.ajax)
                        {
                            settings.submit[name].call($button, settings, $element);
                            return false;
                        }
                        
                        explode = settings.context.ajax.split('/');
                        explode[1] = '?';
                        
                        if (explode.length != 3)
                        {
                            return;
                        }

                        if (name === explode.join('/'))
                        {
                            settings.submit[name].call($button, settings, $element);
                            return false;
                        }
                    });
                }
                
                run();
            }
        });
    };
})(jQuery);