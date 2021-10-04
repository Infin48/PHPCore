$('body').on('click', '[ajax="quote"]', function() {
    var $block = $(this).closest('[ajax-selector="block"]');
    $('[ajax-selector="block block-form"] .trumbowyg-editor').html('<blockquote><span data-user="' + $.trim($block.find('[ajax-selector="user_name"]').text())+ '"></span>'+$block.find('[ajax-selector="block-content"').html()+'</blockquote><p><br></p>');
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

$('body').on('click', '[ajax="show"]', function() {
    $(this).closest('[ajax-selector="block"]').first().removeClass('block-closed');
    $(this).closest('[ajax-selector="block"]').first().find('[ajax="hide"]').first().show();
    $(this).hide();
});

$('body').on('click', '[ajax="hide"]', function() {
    $(this).closest('[ajax-selector="block"]').first().addClass('block-closed');
    $(this).closest('[ajax-selector="block"]').first().find('[ajax="show"]').first().show();
    $(this).hide();
});

$.cAjax('likes', {
    window: {
        url: '/ajax/likes/',
        context: {}
    }
});


$.cAjax('get', {
    ajax: {
        url: '/ajax/get/',
        method: 'get',
        context: {}
    },
    onload: {
        '/User': function (settings) {
            settings.ajax.context.user = $(this).closest('[ajax-selector="field-row"]').find('input[type="text"]').first().val();
        }
    },
    success: {
        '/ProfilePostComment/Previous': function(settings) {
            $(this).closest('[ajax-selector="block"]').after(settings.data.content);
            $(this).closest('[ajax-selector="block"]').first().remove();
        },

        '/User': function(settings) {

            $row = $(this).closest('[ajax-selector="field-row"]');
            $field = $(this).closest('[ajax-selector="field"]');
            $select = $row.find('[ajax-selector="select"]');
            $list = $field.find('[ajax-selector="recipient-list"]');

            if ($select.find('[value="'+settings.data.user_id+'"]').length == 0 && $select.children().length < 9 ) {
                $list.find('[ajax-selector="no-recipients"]').hide();
                $list.prepend('<div class="recipient" ajax-selector="recipient" data-id="'+settings.data.user_id+'">'+settings.data.user+'<a ajax="remove-recipient"><i class="fas fa-times"></i></a></div>');
                $select.prepend('<option selected value="'+settings.data.user_id+'">'+settings.data.user_id+'</option>');
                $list.removeClass('d-none-i');
            }

            $row.find('input[type="text"]').val('');
        }
    }
});

$.cAjax('report', {

    ajax: {
        url: '/ajax/process/',
        method: 'post',
        context: {}
    },

    window: {
        url: '/ajax/language/',
        context: {},
        submit: function (settings) {
            settings.ajax.context.report_reason_text = $(this).find('textarea').val();
        }
    },
    success: {
        default: function(settings, $element) {
            if (settings.data.notice) {
                $notice = $(settings.data.notice);
                $notice.find('[ajax-selector="details"]').attr('href', settings.data.url);
                $element.find('> [ajax-place="notice-reported"], > * > [ajax-place="notice-reported"]').first().replaceWith($notice);
                $element.addClass('block-disabled');
            }
        }
    }
});

$.cAjax('like', {
    ajax: {
        url: '/ajax/process/',
        method: 'get',
        context: {}
    },
    success: {
        default: function (settings, $element) {

            $(this).after(settings.data.button);

            var $inner = $element.find('[ajax-selector="likes"]');
            if ($inner.length) {
                $inner.find('[ajax-selector="list"]').prepend('<span>'+settings.data.you+'</span>');
                $inner.find('[ajax-selector="count"]').html(parseInt($inner.find('[ajax-selector="count"]').text()) + 1);
            } else {
                var $likes = $(settings.data.block);
                $likes.find('[ajax-selector="count"]').text('1');
                $likes.find('[ajax-selector="list"]').html('<span>' + settings.data.you + '</span>');
                $element.find('[ajax-place="likes"]').after($likes);
            }

            $(this).remove();
        }
    }
});

$.cAjax('unlike', {
    ajax: {
        url: '/ajax/process/',
        method: 'get',
        context: {}
    },
    success: {
        default: function (settings, $element) {

            $(this).after(settings.data.button);

            var $inner = $element.find('[ajax-selector="likes"]');
            if ($inner.find('[ajax-selector="list"]').children().length <= 1) {
                $inner.remove(); 
            } else {
                $inner.find('[ajax-selector="list"]').children().first().remove();
                $inner.find('[ajax-selector="count"]').html(parseInt($inner.find('[ajax-selector="count"]').text()) - 1);
            }
            
            $(this).remove();
        }
    }
});

$.cAjax('delete', {
    ajax: {
        url: '/ajax/process/',
        method: 'get',
        context: {}
    },
    window: {
        url: '/ajax/language/',
        context: {}
    },
    success: {
        default: function (settings, $element) {

            $trumbowyg = $element.find('[ajax-selector="trumbowyg-box"]').first();
            
            if ($trumbowyg.length) {

                $content = $trumbowyg.find('[ajax-selector="block-content"]').removeClass('trumbowyg-editor');

                if (settings.process == '/ProfilePostComment/Delete') {

                    $trumbowyg.replaceWith('<div class="' + $content.attr('class') + '" ajax-selector="block-content">'+parseNewLines($content.html())+'</div>');

                } else {

                    if (settings.process == '/ProfilePost/Delete') {

                        $($element.find('[ajax-selector="block"]')).each(function () {

                            $_content = $(this).find('[ajax-selector="block-content"]').removeClass('trumbowyg-editor');

                            $(this).find('[ajax-selector="trumbowyg-box"]').replaceWith('<div class="' + $_content.attr('class') + '" ajax-selector="block-content">'+parseNewLines($_content.html())+'</div>');
                        });
                    }

                    $element.find('[ajax-place="block-content"]').first().replaceWith('<div class="' + $content.attr('class') + '" ajax-selector="block-content">'+parseNewLines($content.html())+'</div>');
                    $trumbowyg.remove();
                }
            }

            if (settings.data.notice) {
                var $notice = $(settings.data.notice);
                $notice.find('[ajax-selector="details"]').attr('href', settings.data.url);
                $element.find('[ajax-place="notice-deleted"]').first().replaceWith($notice);

                $element.addClass('block-closed');
                $element.find('[ajax-selector="block-bottom"]').remove();
                $element.find('[ajax-selector="block block-form"]').remove();
                $element.find('[ajax="delete"]').first().remove();
                $element.addClass('block-disabled');
            } else {
                $element.remove();
            }
        }
    }
});

$.cAjax('editor', {
    ajax: {
        url: '/ajax/language/',
        context: {}
    },
    success: {
        
        default: function(settings, $element) {

            $(this).after(settings.data.button).remove();

            $content = $element.find('[ajax-selector="block-content"]').first();
            $content.before('<div ajax-place="block-content"></div>');
            $content.insertBefore($element.find('[ajax-selector="block-body"]').first());
            $content.trumbowyg(config);
        },

        '/ProfilePostComment/Editor': function(settings, $element) {

            $(this).after(settings.data.button).remove();

            $content = $element.find('[ajax-selector="block-content"]').first().trumbowyg(config);
        }
    }
});


$('body').on('click', '[ajax="remove-recipient"]', function() {

    var $recipient = $(this).closest('[ajax-selector="recipient"]');
    var $list = $(this).closest('[ajax-selector="recipient-list"]');

    $('[ajax-selector="select"]').find('[value="'+$recipient.data('id')+'"]').remove();
    $('[ajax-selector="recipient-list"] [data-id="'+$recipient.data('id')+'"]').remove();

    if ($list.children().length < 2) {
        $list.find('[ajax-selector="no-recipients"]').show();
    }
});


$.cAjax('create', {
    ajax: {
        url: '/ajax/process/',
        method: 'post',
        context: {}
    },
    onload: {
        default: function (settings) {
            settings.ajax.context.text = parseNewLines($(this).closest('[ajax-selector="block block-form"]').find('.trumbowyg-editor').html());
        }
    },
    success: {

        default: function (settings) {

            $block = $(this).closest('[ajax-selector="block block-form"]');
            $block.before(settings.data.content);
            $block.find('.trumbowyg-editor').first().html('');

            $('[ajax-selector="block block-empty"]').remove();
        },

        '/ProfilePost/Create': function (settings) {


            $block = $(this).closest('[ajax-selector="block block-form"]');

            $block.removeClass('block-opened');

            $block.find('.trumbowyg-box textarea').clone().insertBefore($block.find('.trumbowyg-box'));
            $block.find('.trumbowyg-editor').empty();
            $block.find('.trumbowyg-box').remove();
            $block.find('textarea').first().val('');
            $block.find('textarea').first().attr('class', '');
        

            $('[ajax-selector="block-list"]').prepend(settings.data.content);

            $('[ajax-selector="block block-empty"]').remove();
            $block.find('.trumbowyg-editor').first().html('');
        },

        '/ProfilePostComment/Create': function (settings) {

            var $block = $(this).closest('[ajax-selector="block block-form"]');

            $block.removeClass('block-opened');

            $block.find('.trumbowyg-box textarea').clone().insertBefore($block.find('.trumbowyg-box'));
            $block.find('.trumbowyg-editor').empty();
            $block.find('.trumbowyg-box').remove();
            $block.find('textarea').first().val('');
            $block.find('textarea').first().attr('class', '');
            $block.before(settings.data.content);

            $('[ajax-selector="block block-empty"]').remove();
            $block.find('.trumbowyg-editor').first().html('');
        }
    }
});


$.cAjax('edit', {
    ajax: {
        url: '/ajax/process/',
        method: 'post',
        context: {}
    },

    onload: {
        default: function(settings, $element) {
            console.log($element.find('[ajax-selector="block-content"]').html());
            settings.ajax.context.text = parseNewLines($element.find('[ajax-selector="block-content"]').html());
        }
    },

    success: {
    
        default: function(settings, $element) {

            $(this).after(settings.data.button).remove();

            $trumbowyg = $element.find('[ajax-selector="trumbowyg-box"]').first();
            $content = $trumbowyg.find('[ajax-selector="block-content"]').removeClass('trumbowyg-editor');

            $element.find('[ajax-place="block-content"]').replaceWith('<div class="' + $content.attr('class') + '" ajax-selector="block-content">'+parseNewLines($content.html())+'</div>');
            
            $trumbowyg.remove();
        },

        '/ProfilePostComment/Edit': function(settings, $element) {

            $(this).after(settings.data.button).remove();

            $trumbowyg = $element.find('[ajax-selector="trumbowyg-box"]').first();
            $content = $trumbowyg.find('[ajax-selector="block-content"]').removeClass('trumbowyg-editor');

            $trumbowyg.replaceWith('<div class="' + $content.attr('class') + '" ajax-selector="block-content">'+parseNewLines($content.html())+'</div>');
        }
    }
});


$.cAjax('execute', {
    ajax: {
        url: '/ajax/execute/',
        method: 'get',
        context: {}
    },
    success: {
        '/User/Logout': function () {

            var url = window.location.pathname.split('/').filter(item => item);
            if (url[0] == 'user') {
                window.location.href = '/';
                return true;
            }

            location.reload();
        },
        '/User/Mark': function (settings) {

            var $dropdownBody = $(this).closest('[ajax-selector="dropdown-menu"]').find('[ajax-selector="dropdown-body"]');
            var $dropdownRow = $dropdownBody.find('[ajax-selector="dropdown-row"]');

            $dropdownBody.children().remove();
            $dropdownBody.prepend('<'+$dropdownRow.prop('tagName')+' class="'+$dropdownRow.attr('class')+'">'+settings.data.empty+'</'+$dropdownRow.prop('tagName')+'>');
            $(this).closest('[ajax-selector="dropdown"]').find('a[data-count]').removeAttr('data-count');
        }
    }
});

$.cAjax('process', {
    ajax: {
        url: '/ajax/process/',
        method: 'get',
        context: {}
    }
});