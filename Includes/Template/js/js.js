function parseNewLines(t) {
    var e = [],
        o = [],
        n = null,
        a = null;
    (t = $(t)),
        $.each(t, function (t, o) {
            (e[t] = $(o).prop("outerHTML")), (html = $(o).html() || ''), (html = html.replace(/&nbsp;/g, ' ')),
                ("" !=
                    html
                        .replace(/(<([^>]+)>)/gi, "").trim()) &&
                    (null == a && (a = t), (n = t));
        });
    for (var i = a; i <= n; i++) o[i] = e[i];
    return o.join("");
}
function removeNewLines(t) {
    var e = [];
    t = $(t);
    return (
        $.each(t, function (o, n) {
            "" != $(n).text() ? (e[o] = $(n).prop("outerHTML")) : delete t[o];
        }),
        e.join("")
    );
}
$("body").on("click", '[ajax-action="quote"]', function () {
    var t = $(this).closest('[js="block"]');
    $('[js="block block-form"] .trumbowyg-editor').html(
        '<blockquote><span data-user="' + $.trim(t.find('[js="block-head"] [js="user_name"]').text()) + " " + $(this).attr("ajax-data") + '"></span>' + t.find('[js="block-content"').html() + "</blockquote><p><br></p>"
    );
    window.location = $(this).attr('href');
}),
    $('[js="title"]').after($('[js="title"]').clone()),
    $("body").on("mouseover", '[ajax="title"]', function () {
        var t = $('[js="title"]:not(.title-active)').first();
        t.attr("class", "title title-active").fadeIn(),
            $(this).attr("ajax-class") && t.addClass($(this).attr("ajax-class")),
            t.find('[js="text"]').text($.trim($(this).attr("ajax-title"))),
            t.css({ left: $(this).offset().left, top: $(this).offset().top - 40 }),
            t.width() + $(this).offset().left > $("html").width() ? t.addClass("title-edge") : t.removeClass("title-edge");
    }),
    $("body").on("mouseleave", '[ajax="title"]', function () {
        $('[js="title"].title-active').fadeOut(function () {
            $(this).attr("class", "title");
        });
    }),
    $("body").on("click", '[ajax="show"]', function () {
        $(this).closest('[js="block"]').first().removeClass("block-closed"), $(this).closest('[js="block"]').first().find('[ajax="hide"]').first().show(), $(this).hide();
    }),
    $("body").on("click", '[ajax="hide"]', function () {
        $(this).closest('[js="block"]').first().addClass("block-closed"), $(this).closest('[js="block"]').first().find('[ajax="show"]').first().show(), $(this).hide();
    }),
    $('[js="navbar navbar-default"] [js="navbar-content"]').clone().appendTo('[js="navbar navbar-mobile"]'),
    $('[ajax="close"]').on("click", function () {
        $(this).closest('[js="alert"]').remove();
    }),
    (window.FontAwesomeConfig = { searchPseudoElements: !0 }),
    $('[ajax="collapse-navbar"]').on("click", function (t) {
        t.preventDefault(), t.stopPropagation(), t.stopImmediatePropagation();
        var e = $('[js="navbar navbar-mobile"]');
        if (e.hasClass("navbar-opened")) return e.removeClass("navbar-opened"), void $('[js="opacity"]').addClass("opacity-hide").removeClass("opacity-show");
        e.addClass("navbar-opened"), $('[js="opacity"]').addClass("opacity-show").removeClass("opacity-hide");
    }),
    $("body").on("click", function (t) {
        $('[js="navbar navbar-mobile"]').hasClass("navbar-opened") &&
            0 == $(t.target).parents('[js*="navbar navbar-mobile"]').length &&
            "navbar navbar-mobile" != $(t.target).attr("js") &&
            ($('[js="opacity"]').addClass("opacity-hide").removeClass("opacity-show"), $('[js="navbar navbar-mobile"]').removeClass("navbar-opened")),
            $('[js="window"]').hasClass("window-active") && 0 == $(t.target).parents('[js="window"]').length && ($('[js="opacity"]').addClass("opacity-hide").removeClass("opacity-show"), $('[js="window"]').removeClass("window-active"));
    }),
    $("body").on("click", '[js~="dropdown"] > a', function () {
        var t = $(this).closest('[js~="dropdown"]');
        t.hasClass("dropdown-opened") ? t.removeClass("dropdown-opened") : t.addClass("dropdown-opened");
    }),
    $('input[type="submit"], a[ajax-action]').on("click", function (t) {
        $(".trumbowyg-textarea").length && $(".trumbowyg-textarea").val(parseNewLines($(".trumbowyg .trumbowyg-editor").html()));
    }),
    $(document).on(
        {
            mouseenter: function () {
                $(this).closest('[js~="dropdown"]').addClass("dropdown-opened");
            },
            mouseleave: function () {
                $(this).closest('[js~="dropdown"]').removeClass("dropdown-opened");
            },
        },
        '[js="navbar navbar-default"] [js~="dropdown"] > a, [js="navbar navbar-default"] [js="dropdown-menu"], [js="panel"] [js="dropdown"] > a, [js="panel"] [js="dropdown-menu"]'
    );
var mentionWindowHTML = $('[js="window-mention"]').html(),
    allowed = !0;
$(document).on("mouseover", function (t) {
    if ((t.preventDefault(), t.stopPropagation(), t.stopImmediatePropagation(), ($window = $(".window-mention")), !$(t.target).closest('[js="window-mention"]').length)) {
        if ($(t.target).hasClass("mention") && $(t.target).attr("mention")) {
            if (0 == allowed) return;
            allowed = !1;
            var e = $(t.target).offset();
            return (
                $window.html(mentionWindowHTML),
                void $.post("/action-ajax/", { ajax: "run/mention-user", id: $(t.target).attr("mention") }, function (t) {
                    "ok" == (t = JSON.parse(t)).status &&
                        ($window.find('[js="window-mention-posts-lang"]').replaceWith(t.data.posts.lang),
                        $window.find('[js="window-mention-posts-count"]').replaceWith(t.data.posts.count),
                        $window.find('[js="window-mention-topics-lang"]').replaceWith(t.data.topics.lang),
                        $window.find('[js="window-mention-topics-count"]').replaceWith(t.data.topics.count),
                        $window.find('[js="window-mention-name"]').replaceWith(t.data.name),
                        $window.find('[js="window-mention-group"]').replaceWith(t.data.group),
                        $window.find('[js="window-mention-reputation"]').replaceWith(t.data.reputation),
                        $window.find('[js="window-mention-image"]').replaceWith(t.data.image),
                        $window.find('[js="window-mention-role"]').replaceWith(t.data.role),
                        $window.find('[js="window-mention-background"]').css("background-image", "url(" + t.data.background + ")"),
                        $window.css({ left: e.left + "px", top: e.top - $window.outerHeight() + 5 + "px" }),
                        $window.addClass("window-show").removeClass("window-hide"));
                })
            );
        }
        $window.hasClass("window-show") &&
            ($window.removeClass("window-show").addClass("window-hide"),
            setTimeout(function () {
                allowed = !0;
            }, 150));
    }
}),
    $.cAjax({
        submit: {
            "run/?/label": function (t, e) {
                var o = [];
                e.find('input[name="topic_label[]"],input[name="article_label[]"]').each(function () {
                    $(this).is(":checked") && o.push($(this).val());
                }),
                    (t.context.labels = o);
            },
            "run/topic/move": function (t, e) {
                e.find('input[name="new_forum_id"]:checked') && (t.context.forum_id = e.find('input[name="new_forum_id"]:checked').val());
            },
            "run/add-recipient": function (t, e) {
                var o = [];
                $('[js="form"] select[name="to[]"] option').each(function () {
                    o.push($(this).val());
                }),
                    (t.context.list = o),
                    (t.context.user_name = e.find('input[name="add_recipient"]').val());
            },
            "run/user": function (t) {
                (t.context.user_name = $('[js~="form-row-add-recipient"]').find('input[type="text"]').first().val()),
                    (t.context.ids = $("form")
                        .find('select[name="to[]"] option')
                        .map(function () {
                            return $(this).val();
                        })
                        .get()
                        .join(","));
            },
            "run/profile-post-comment/previous": function (t) {
                (t.context.comments = "all"), (t.url = "/ajax/content/"), (t.context.url = "/ajax/get/block/profile/");
            },
            "run/post/create": function (t) {
                t.context.text = parseNewLines($(this).closest('[js="block block-form"]').find(".trumbowyg-editor").html());
            },
            "run/message/create": function (t) {
                t.context.text = parseNewLines($(this).closest('[js="block block-form"]').find(".trumbowyg-editor").html());
            },
            "run/profile-post/create": function (t) {
                (t.context.text = parseNewLines($(this).closest('[js="block block-form"]').find(".trumbowyg-editor").html())), (t.context.text = removeNewLines(t.context.text));
            },
            "run/profile-post-comment/create": function (t) {
                (t.context.text = parseNewLines($(this).closest('[js="block block-form"]').find(".trumbowyg-editor").html())), (t.context.text = removeNewLines(t.context.text));
            },
            "run/?/edit": function (t, e) {
                (t.context.text = parseNewLines(e.find('[js="block-content"]').html())), ("run/profile-post/edit" == t.context.ajax || "run/profile-post-comment/edit" == t.context.ajax) && (t.context.text = removeNewLines(t.context.text));
            },
            "run/?/report": function (t) {
                t.context.report_reason_text = $('[js="window"] textarea').val();
            },
        },
        success: {
            "run/profile-post/previous": function (t) {
                var e = $(this).closest("#" + t.context.id + '[js="block"]');
                e.replaceWith(t.data.data.content), e.find('[ajax="hide"]').show().find('[ajax="show"]').hide();
            },
            "run/add-recipient": function (t) {
                $('[js~="form-row-recipients"]').replaceWith(t.data.data.content), $('[js~="form-row-add-recipient"] input').val("");
            },
            "run/post/create": function (t) {
                ($block = $(this).closest('[js="block block-form"]')),
                    $('[js="block block-empty"]').remove(),
                    $block.before(t.data.data.content),
                    $block.find(".trumbowyg-editor").empty(),
                    t.data.data.form && $block.replaceWith(t.data.data.form);
            },
            "run/message/create": function (t) {
                ($block = $(this).closest('[js="block block-form"]')),
                    $('[js="block block-empty"]').remove(),
                    $block.before(t.data.data.content),
                    $block.find(".trumbowyg-editor").empty(),
                    t.data.data.form && $block.replaceWith(t.data.data.form);
            },
            "run/profile-post/create": function (t) {
                ($block = $(this).closest('[js="block block-form"]')), $('[js="block block-empty"]').remove(), $block.after(t.data.data.content), t.data.data.form && $block.replaceWith(t.data.data.form);
            },
            "run/profile-post-comment/create": function (t, e) {
                $('[js="block block-empty"]').remove(), e.before(t.data.data.content), t.data.data.form && e.replaceWith(t.data.data.form);
            },
            "run/?/like": function (t, e) {
                e.replaceWith(t.data.data.content);
            },
            "run/?/unlike": function (t, e) {
                e.replaceWith(t.data.data.content);
            },
            "run/?/report": function (t, e) {
                t.data.data.content && e.replaceWith(t.data.data.content);
            },
            "run/?/delete": function (t, e) {
                t.data.data.content && (e.replaceWith(t.data.data.content), e.find('[ajax="hide"]').hide());
            },
            "run/profile-post-comment/editor": function (t, e) {
                $(this).after(t.data.data.button).remove(), ($content = e.find('[js="block-content"]').first().trumbowyg(t.data.data.trumbowyg));
            },
            "run/?/editor": function (t, e) {
                $(this).after(t.data.data.button).remove(),
                    ($content = e.find('[js="block-content"]').first()).before('<div js-place="block-content"></div>'),
                    $content.insertBefore(e.find('[js="block-body"]').first()),
                    $content.trumbowyg(t.data.data.trumbowyg);
            },
            "run/?/edit": function (t, e) {
                e.replaceWith(t.data.data.content);
            },
            "run/delete-attachment": function (t, e) {
                $(this).closest(".attachment").remove(), 0 == $(this).closest('[js="attachment-list"]').children().length && $(this).closest('[js="attachment-list"]').remove();
            },
            "run/mark-user-notifications-as-read": function (t, e) {
                $('[js="dropdown dropdown-notification"] [js="dropdown-body"]').replaceWith(t.data.data.content),
                    $('[js="dropdown dropdown-notification"] [data-count]').removeAttr("data-count"),
                    $('[js="dropdown dropdown-notification"] [js="dropdown-bottom"]').remove();
            },
        },
    }),
    $("body").on("click", '[ajax="remove-recipient"]', function () {
        var t = $(this).closest('[js="recipient"]'),
            e = $(this).closest('[js="recipient-list"]');
        $('[js="select"]')
            .find('[value="' + t.data("id") + '"]')
            .remove(),
            $('[js="recipient-list"] [data-id="' + t.data("id") + '"]').remove(),
            e.children().length < 2 && e.find('[js="no-recipients"]').show();
    });
