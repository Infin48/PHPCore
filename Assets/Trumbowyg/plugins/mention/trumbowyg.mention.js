/* ===========================================================
 * trumbowyg.mention.js v0.3
 * Mention plugin for Trumbowyg
 * http://alex-d.github.com/Trumbowyg
 * ===========================================================
 * Author : Infin48 && Rooseboom
 * Infin48: https://github.com/Infin48
 * This plugin was created by Rooseboom, but was reworked by Infin48
 */

/*
 * Here upload data about users!
 *
 * mentionUserList = {
 *      {user_name}: {
 *          link: {link},      <-- Link to user profile which will be added to mention <a> tag to href attribute (If you want)
 *          class: {class}     <-- Class which will be added to mention <a> tag to class attribute (If you want) 
 *      }
 * };
 */
var mentionUserList = mentionUserList;

var trumbowygEditor = '';

(function ($) {
    'use strict';
    
    var mentionUserNameList = [];
    var lastAtSignCaretPosition = 0;

    // HTML body of mention list
    var mentionListHTML = '<div class="mention-list" js="mention-list"></div>';

    // HTML of mentioned user in editor
    var mentionUserHTML = '<a class="mention {class}" mention="{user_name}" {href} contenteditable="false">@{user_name}</a>&nbsp;';

    // HTML of empty result in mention list
    var mentionListEmptyHTML = '<a class="mention-empty">{lang_empty}</a>';

    // HTML of user in mention list
    var mentionUserInListHTML = '<a mention {class} mention-name="{user_name}" mention-replace="{replace}">{user_name_underlined}</a>';

    // Selector for HTML body
    var mentionListSelector = '[js="mention-list"]';

    // Here will be saved mention list == $(mentionListSelector);
    var mentionList = '';

    $.extend(true, $.trumbowyg, {

        langs: {
            en: {
                empty: 'No results'
            },
            cs: {
                empty: 'Žádné výsledky'
            },
            sk: {
                empty: 'Žiadne výsledky'
            }
        },

        plugins: {
            mention: {
                init: function (trumbowyg) {

                    if (!mentionUserList)
                    {
                        return;
                    }

                    if (!mentionUserNameList.length)
                    {
                        $.each(mentionUserList, function(key, obj) {
                            mentionUserNameList.push(key);
                        });
                    }

                    if (!$(mentionListSelector).length)
                    {
                        $('body').append(mentionListHTML);
                        mentionList = $(mentionListSelector);
                    }

                    $(function()
                    {
                        // Click to button - add mention
                        $(document).on('click', 'a[mention]', function (e) {

                            e.preventDefault();
                            e.stopPropagation();
                            e.stopImmediatePropagation();

                            var content = trumbowygEditor.html();

                            var orgText = $(this).attr('mention-replace');
                            var mention = $(this).attr('mention-name');

                            var regex = new RegExp(orgText, 'g');

                            if (content.match(regex).length >= 2)
                            {
                                return;
                            }

                            var href = '';
                            if (mentionUserList[mention].link)
                            {
                                href = ' href="' + mentionUserList[mention].link + '"';
                            }

                            var userHTML = mentionUserHTML.replace(/{class}/g, mentionUserList[mention].class ? ' ' + mentionUserList[mention].class : '');
                            userHTML = userHTML.replace(/{href}/g, href);
                            userHTML = userHTML.replace(/{user_name}/g, mention);
                            
                            var regex = new RegExp('' + orgText + '', 'g');
                            content = content.replace(regex, userHTML);

                            trumbowygEditor.html(content);

                            lastAtSignCaretPosition = 0;

                            mentionList.hide();
                        });
                        
                        trumbowyg.$box.on('mouseup', function() {

                            mentionList.hide();
                            lastAtSignCaretPosition = 0;
                        });

                        trumbowyg.$ed.on('keyup', function(e)
                        {
                            trumbowygEditor = trumbowyg.$ed;
                            if (e.keyCode == 27 || e.keyCode == 32 || e.keyCode == 38 || e.keyCode == 40 || e.keyCode == 39 || e.keyCode == 37 )
                            {
                                if (e.keyCode == 27)
                                {
                                    mentionList.hide();
                                    return;
                                }

                                lastAtSignCaretPosition = 0;
                                mentionList.hide();

                                return;
                            }

                            var text = trumbowyg.$ed.text();
                            var currentCaretPosition = getCaretCharacterOffsetWithin(trumbowyg.$ed[0]);

                            if (lastAtSignCaretPosition > currentCaretPosition)
                            {
                                lastAtSignCaretPosition = 0;
                                mentionList.hide();

                                return;
                            }

                            if (lastAtSignCaretPosition == currentCaretPosition + 1)
                            {
                                if (e.keyCode == 8)
                                {
                                    lastAtSignCaretPosition = 0;
                                    mentionList.hide();

                                    return;
                                }
                            }

                            
                            if (text.substring(currentCaretPosition - 1, currentCaretPosition) == '@')
                            {
                                lastAtSignCaretPosition = currentCaretPosition;
                            }
                            
                            if (lastAtSignCaretPosition == 0)
                            {
                                mentionList.hide();
                                return;
                            }

                            // Get entered text by last at-sign position
                            text = text.substring(lastAtSignCaretPosition, lastAtSignCaretPosition + currentCaretPosition - lastAtSignCaretPosition);
                            if (text.length <= 2)
                            {
                                mentionList.hide();
                                return;
                            }

                            mentionList.html('');

                            var replaceText = '@' + text;
                            text = text.toLowerCase();
                            
                            var mentionFiltered = mentionUserNameList.filter(function (el)
                            {
                                var regex = new RegExp(el.toLowerCase(), 'g');
                                var match = trumbowygEditor.html().match(regex);
                                if (match != null && match.length >= 2)
                                {
                                    return;
                                }

                                el = el.toLowerCase();
                                return el.indexOf(text) >= 0;
                            });

                            // If result is empty
                            if (mentionFiltered.length == 0)
                            {
                                // where to show to mention box
                                var coords = getSelectionCoords();

                                mentionList.css({top: (coords.y + 20) + 'px', left: (coords.x - 20) + 'px'});
                                mentionList.html(mentionListEmptyHTML.replace(/{lang_empty}/g, trumbowyg.lang.empty));
                                mentionList.show();

                                return;
                            }

                            // Create underline
                            mentionFiltered.forEach(function(name)
                            {
                                var nameUnderLined = name;
                                if (text.length > 0)
                                {
                                    var re = new RegExp(text, 'i');
                                    nameUnderLined = nameUnderLined.replace(re, '<u>@' + text + '</u>');
                                }
                                var _class = '';
                                if (mentionUserList[name].class)
                                {
                                    _class = 'class="' + mentionUserList[name].class + '"';
                                }

                                mentionList.append(mentionUserInListHTML.replace(/{user_name}/g, name).replace(/{class}/g, _class).replace(/{replace}/g, replaceText).replace(/{user_name_underlined}/g, nameUnderLined));
                            })

                            // where to show to mention box
                            var coords = getSelectionCoords();
                            
                            mentionList.css({top: (coords.y + 20) + 'px', left: (coords.x - 20) + 'px'});
                            mentionList.show();
                        })
                    })
                }
            }
        }
    });

    $(document).mouseup(function(e)
    {
        if (!mentionList)
        {
            return;
        }

        // if the target of the click isn't the container nor a descendant of the container
        if (!mentionList.is(e.target) && mentionList.has(e.target).length === 0)
        {
            mentionList.hide();
        }
    })
})(jQuery);

function getSelectionCoords(win) {
    win = win || window;
    var doc = win.document;
    var sel = doc.selection, range, rects, rect;
    var x = 0, y = 0;
    if (sel) {
        if (sel.type != "Control") {
            range = sel.createRange();
            range.collapse(true);
            x = range.boundingLeft;
            y = range.boundingTop;
        }
    } else if (win.getSelection) {
        sel = win.getSelection();
        if (sel.rangeCount) {
            range = sel.getRangeAt(0).cloneRange();
            if (range.getClientRects) {
                range.collapse(true);
                rects = range.getClientRects();
                if (rects.length > 0) {
                    rect = rects[0];
                }
                if(rect)
                {
                    x = rect.left;
                    y = $(window).scrollTop() + rect.top;
                }
                else
                {
                    x = 0
                    y = 0
                }
            }
            // Fall back to inserting a temporary element
            if (x == 0 && y == 0) {
                var span = doc.createElement("span");
                if (span.getClientRects) {
                    // Ensure span has dimensions and position by
                    // adding a zero-width space character
                    span.appendChild( doc.createTextNode("\u200b") );
                    range.insertNode(span);
                    rect = span.getClientRects()[0];
                    x = rect.left;
                    y = rect.top;
                    var spanParent = span.parentNode;
                    spanParent.removeChild(span);

                    // Glue any broken text nodes back together
                    spanParent.normalize();
                }
            }
        }
    }
    return { x: x, y: y };
}

function getCaretCharacterOffsetWithin(element) {
    var caretOffset = 0;
    var doc = element.ownerDocument || element.document;
    var win = doc.defaultView || doc.parentWindow;
    var sel;
    if (typeof win.getSelection != "undefined") {
        sel = win.getSelection();
        if (sel.rangeCount > 0) {
            var range = win.getSelection().getRangeAt(0);
            var preCaretRange = range.cloneRange();
            preCaretRange.selectNodeContents(element);
            preCaretRange.setEnd(range.endContainer, range.endOffset);
            caretOffset = preCaretRange.toString().length;
        }
    } else if ( (sel = doc.selection) && sel.type != "Control") {
        var textRange = sel.createRange();
        var preCaretTextRange = doc.body.createTextRange();
        preCaretTextRange.moveToElementText(element);
        preCaretTextRange.setEndPoint("EndToEnd", textRange);
        caretOffset = preCaretTextRange.text.length;
    }
    return caretOffset;
}

function getCaretPosition() {
    if (window.getSelection && window.getSelection().getRangeAt) {
        var range = window.getSelection().getRangeAt(0);
        var selectedObj = window.getSelection();
        var rangeCount = 0;
        var childNodes = selectedObj.anchorNode.parentNode.childNodes;
        for (var i = 0; i < childNodes.length; i++) {
            if (childNodes[i] == selectedObj.anchorNode) {
                break;
            }
            if (childNodes[i].outerHTML)
                rangeCount += childNodes[i].outerHTML.length;
            else if (childNodes[i].nodeType == 3) {
                rangeCount += childNodes[i].textContent.length;
            }
        }
        return range.startOffset + rangeCount;
    }
    return -1;
}