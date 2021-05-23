/* ===========================================================
 * trumbowyg.emoji.js v0.1
 * Emoji picker plugin for Trumbowyg
 * http://alex-d.github.com/Trumbowyg
 * ===========================================================
 * Author : Nicolas Pion
 *          Twitter : @nicolas_pion
 */

(function ($) {
    'use strict';

    var defaultOptions = {
        emojiList: [
            '&#x1F600',
            '&#x1F601',
            '&#x1F602',
            '&#x1F603',
            '&#x1F604',
            '&#x1F605',
            '&#x1F606',
            '&#x1F607',
            '&#x1F608',
            '&#x1F609',
            '&#x1F60A',
            '&#x1F60B',
            '&#x1F60C',
            '&#x1F60D',
            '&#x1F60E',
            '&#x1F60F',
            '&#x1F610',
            '&#x1F611',
            '&#x1F612',
            '&#x1F613',
            '&#x1F614',
            '&#x1F615',
            '&#x1F616',
            '&#x1F617',
            '&#x1F618',
            '&#x1F619',
            '&#x1F61A',
            '&#x1F61B',
            '&#x1F61C',
            '&#x1F61D',
            '&#x1F61E',
            '&#x1F61F',
            '&#x1F620',
            '&#x1F621',
            '&#x1F622',
            '&#x1F623',
            '&#x1F624',
            '&#x1F625',
            '&#x1F626',
            '&#x1F627',
            '&#x1F628',
            '&#x1F629',
            '&#x1F62A',
            '&#x1F62B',
            '&#x1F62C',
            '&#x1F62D',
            '&#x1F62E',
            '&#x1F62F',
            '&#x1F630',
            '&#x1F631',
            '&#x1F632',
            '&#x1F633',
            '&#x1F634',
            '&#x1F635',
            '&#x1F636',
            '&#x1F637',
            '&#x1F641',
            '&#x1F642',
            '&#x1F643',
            '&#x1F644',
            '&#x1F910',
            '&#x1F911',
            '&#x1F912',
            '&#x1F913',
            '&#x1F914',
            '&#x1F915',
            '&#x1F920',
            '&#x1F921',
            '&#x1F922',
            '&#x1F923',
            '&#x1F924',
            '&#x1F925',
            '&#x1F927',
            '&#x1F928',
            '&#x1F929',
            '&#x1F92A',
            '&#x1F92B',
            '&#x1F92C',
            '&#x1F92D',
            '&#x1F92E',
            '&#x1F92F',
            '&#x1F9D0'
        ]
    };

    // Add all emoji in a dropdown
    $.extend(true, $.trumbowyg, {
        langs: {
            // jshint camelcase:false
            en: {
                emoji: 'Add an emoji'
            },
            cs: {
                emoji: 'Přidat emoji'
            },
            da: {
                emoji: 'Tilføj et humørikon'
            },
            de: {
                emoji: 'Emoticon einfügen'
            },
            fr: {
                emoji: 'Ajouter un emoji'
            },
            zh_cn: {
                emoji: '添加表情'
            },
            ru: {
                emoji: 'Вставить emoji'
            },
            ja: {
                emoji: '絵文字の挿入'
            },
            tr: {
                emoji: 'Emoji ekle'
            },
            ko: {
                emoji: '이모지 넣기'
            },
        },
        // jshint camelcase:true
        plugins: {
            emoji: {
                init: function (trumbowyg) {
                    trumbowyg.o.plugins.emoji = trumbowyg.o.plugins.emoji || defaultOptions;
                    var emojiBtnDef = {
                        dropdown: buildDropdown(trumbowyg)
                    };
                    trumbowyg.addBtnDef('emoji', emojiBtnDef);
                }
            }
        }
    });

    function buildDropdown(trumbowyg) {
        var dropdown = [];

        $.each(trumbowyg.o.plugins.emoji.emojiList, function (i, emoji) {
            if ($.isArray(emoji)) { // Custom emoji behaviour
                var emojiCode = emoji[0],
                    emojiUrl = emoji[1],
                    emojiHtml = '<img src="' + emojiUrl + '" alt="' + emojiCode + '">',
                    customEmojiBtnName = 'emoji-' + emojiCode.replace(/:/g, ''),
                    customEmojiBtnDef = {
                        hasIcon: false,
                        text: emojiHtml,
                        fn: function () {
                            trumbowyg.execCmd('insertImage', emojiUrl, false, true);
                            return true;
                        }
                    };

                trumbowyg.addBtnDef(customEmojiBtnName, customEmojiBtnDef);
                dropdown.push(customEmojiBtnName);
            } else { // Default behaviour
                var btn = emoji.replace(/:/g, ''),
                    defaultEmojiBtnName = 'emoji-' + btn,
                    defaultEmojiBtnDef = {
                        text: emoji,
                        fn: function () {
                            var encodedEmoji = String.fromCodePoint(emoji.replace('&#', '0'));
                            trumbowyg.execCmd('insertText', encodedEmoji);
                            return true;
                        }
                    };

                trumbowyg.addBtnDef(defaultEmojiBtnName, defaultEmojiBtnDef);
                dropdown.push(defaultEmojiBtnName);
            }
        });

        return dropdown;
    }
})(jQuery);
