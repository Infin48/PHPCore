<?php

/**
 * Validates a font family list according to CSS spec
 */
class HTMLPurifier_AttrDef_CSS_FontFamily extends HTMLPurifier_AttrDef
{

    protected $mask = null;

    public function __construct()
    {
        $this->mask = '_- ';
        for ($c = 'a'; $c <= 'z'; $c++) {
            $this->mask .= $c;
        }
        for ($c = 'A'; $c <= 'Z'; $c++) {
            $this->mask .= $c;
        }
        for ($c = '0'; $c <= '9'; $c++) {
            $this->mask .= $c;
        } // cast-y, but should be fine
        // special bytes used by utf-8
        for ($i = 0x80; $i <= 0xFF; $i++) {
            // We don't bother excluding invalid bytes in this range,
            // because the our restriction of well-formed utf-8 will
            // prevent these from ever occurring.
            $this->mask .= chr($i);
        }

        /*
            PHP's internal strcspn implementation is
            O(length of string * length of mask), making it inefficient
            for large masks.  However, it's still faster than
            preg_match 8)
          for (p = s1;;) {
            spanp = s2;
            do {
              if (*spanp == c || p == s1_end) {
                return p - s1;
              }
            } while (spanp++ < (s2_end - 1));
            c = *++p;
          }
         */
        // possible optimization: invert the mask.
    }

    /**
     * @param string $string
     * @param HTMLPurifier_Config $config
     * @param HTMLPurifier_Context $context
     * @return bool|string
     */
    public function validate($string, $config, $context)
    {
        static $generic_names = array(
            'serif' => true,
            'sans-serif' => true,
            'monospace' => true,
            'fantasy' => true,
            'cursive' => true
        );
        $allowed_fonts = $config->get('CSS.AllowedFonts');

        // assume that no font names contain commas in them
        $fonts = explode(',', $string);
        $final = '';
        foreach ($fonts as $font) {
            $font = trim($font);
            if ($font === '') {
                continue;
            }
            // match a generic name
            if (isset($generic_names[$font])) {
                if ($allowed_fonts === null || isset($allowed_fonts[$font])) {
                    $final .= $font . ', ';
                }
                continue;
            }
            // match a quoted name
            if ($font[0] === '"' || $font[0] === "'") {
                $length = strlen($font);
                if ($length <= 2) {
                    continue;
                }
                $quote = $font[0];
                if ($font[$length - 1] !== $quote) {
                    continue;
                }
                $font = substr($font, 1, $length - 2);
            }

            $font = $this->expandCSSEscape($font);

            // $font is a pure representation of the font name

            if ($allowed_fonts !== null && !isset($allowed_fonts[$font])) {
                continue;
            }

            if (ctype_alnum($font) && $font !== '') {
                // very simple font, allow it in unharmed
                $final .= $font . ', ';
                continue;
            }

            // bugger out on whitespace.  form feed (0c) really
            // shouldn't show up regardless
            $font = str_replace(array("\n", "\t", "\r", "\x0C"), ' ', $font);

            // Here, there are various classes of characters which need
            // to be treated differently:
            //  - alphanumeric characters are essentially safe.  we
            //    handled these above.
            //  - spaces require quoting, though most parsers will do
            //    the right thing if there aren't any characters that
            //    can be misinterpreted
            //  - dashes rarely occur, but they fairly unproblematic
            //    for parsing/rendering purposes.
            //  the above characters cover the majority of western font
            //  names.
            //  - arbitrary unicode characters not in ascii.  because
            //    most parsers give little thought to unicode, treatment
            //    of these codepoints is basically uniform, even for
            //    punctuation-like codepoints.  these characters can
            //    show up in non-western pages and are supported by most
            //    major browsers, for example: "ｍｓ 明朝" is a
            //    legitimate font-name
            //    <http://ja.wikipedia.org/wiki/ms_明朝>.  see
            //    the css3 spec for more examples:
            //    <http://www.w3.org/tr/2011/wd-css3-fonts-20110324/localizedfamilynames.png>
            //    you can see live samples of these on the internet:
            //    <http://www.google.co.jp/search?q=font-family+ｍｓ+明朝|ゴシック>
            //    however, most of these fonts have ascii equivalents:
            //    for example, 'ms mincho', and it's considered
            //    professional to use ascii font names instead of
            //    unicode font names.  thanks takeshi terada for
            //    providing this information.
            //  the following characters, to my knowledge, have not been
            //  used to name font names.
            //  - single quote.  while theoretically you might find a
            //    font name that has a single quote in its name (serving
            //    as an apostrophe, e.g. dave's scribble), i haven't
            //    been able to find any actual examples of this.
            //    internet explorer's csstext translation (which i
            //    believe is invoked by innerhtml) normalizes any
            //    quoting to single quotes, and fails to escape single
            //    quotes.  (note that this is not ie's behavior for all
            //    css properties, just some sort of special casing for
            //    font-family).  so a single quote *cannot* be used
            //    safely in the font-family context if there will be an
            //    innerhtml/csstext translation.  note that firefox 3.x
            //    does this too.
            //  - double quote.  in ie, these get normalized to
            //    single-quotes, no matter what the encoding.  (fun
            //    fact, in ie8, the 'content' css property gained
            //    support, where they special cased to preserve encoded
            //    double quotes, but still translate unadorned double
            //    quotes into single quotes.)  so, because their
            //    fixpoint behavior is identical to single quotes, they
            //    cannot be allowed either.  firefox 3.x displays
            //    single-quote style behavior.
            //  - backslashes are reduced by one (so \\ -> \) every
            //    iteration, so they cannot be used safely.  this shows
            //    up in ie7, ie8 and ff3
            //  - semicolons, commas and backticks are handled properly.
            //  - the rest of the ascii punctuation is handled properly.
            // We haven't checked what browsers do to unadorned
            // versions, but this is not important as long as the
            // browser doesn't /remove/ surrounding quotes (as ie does
            // for html).
            //
            // With these results in hand, we conclude that there are
            // various levels of safety:
            //  - paranoid: alphanumeric, spaces and dashes(?)
            //  - international: paranoid + non-ascii unicode
            //  - edgy: everything except quotes, backslashes
            //  - nojs: standards compliance, e.g. sod ie. note that
            //    with some judicious character escaping (since certain
            //    types of escaping doesn't work) this is theoretically
            //    ok as long as innerhtml/csstext is not called.
            // We believe that international is a reasonable default
            // (that we will implement now), and once we do more
            // extensive research, we may feel comfortable with dropping
            // it down to edgy.

            // Edgy: alphanumeric, spaces, dashes, underscores and unicode.  use of
            // str(c)spn assumes that the string was already well formed
            // Unicode (which of course it is).
            if (strspn($font, $this->mask) !== strlen($font)) {
                continue;
            }

            // Historical:
            // In the absence of innerhtml/csstext, these ugly
            // transforms don't pose a security risk (as \\ and \"
            // might--these escapes are not supported by most browsers).
            // We could try to be clever and use single-quote wrapping
            // when there is a double quote present, but i have choosen
            // not to implement that.  (note: you can reduce the amount
            // of escapes by one depending on what quoting style you use)
            // $font = str_replace('\\', '\\5c ', $font);
            // $font = str_replace('"',  '\\22 ', $font);
            // $font = str_replace("'",  '\\27 ', $font);

            // font possibly with spaces, requires quoting
            $final .= "'$font', ";
        }
        $final = rtrim($final, ', ');
        if ($final === '') {
            return false;
        }
        return $final;
    }

}

// vim: et sw=4 sts=4
