<?php

/**
 * Parses a URI into the components and fragment identifier as specified
 * by RFC 3986.
 */
class HTMLPurifier_URIParser
{

    /**
     * Instance of HTMLPurifier_PercentEncoder to do normalization with.
     */
    protected $percentEncoder;

    public function __construct()
    {
        $this->percentEncoder = new HTMLPurifier_PercentEncoder();
    }

    /**
     * Parses a URI.
     * @param $uri string URI to parse
     * @return HTMLPurifier_URI representation of URI. This representation has
     *         not been validated yet and may not conform to RFC.
     */
    public function parse($uri)
    {
        $uri = $this->percentEncoder->normalize($uri);

        // Regexp is as per appendix b.
        // Note that ["<>] are an addition to the rfc's recommended
        // characters, because they represent external delimeters.
        $r_URI = '!'.
            '(([a-zA-Z0-9\.\+\-]+):)?'. // 2. scheme
            '(//([^/?#"<>]*))?'. // 4. authority
            '([^?#"<>]*)'.       // 5. path
            '(\?([^#"<>]*))?'.   // 7. query
            '(#([^"<>]*))?'.     // 8. fragment
            '!';

        $matches = array();
        $result = preg_match($r_URI, $uri, $matches);

        if (!$result) return false; // *really* invalid uri

        // seperate out parts
        $scheme     = !empty($matches[1]) ? $matches[2] : null;
        $authority  = !empty($matches[3]) ? $matches[4] : null;
        $path       = $matches[5]; // always present, can be empty
        $query      = !empty($matches[6]) ? $matches[7] : null;
        $fragment   = !empty($matches[8]) ? $matches[9] : null;

        // further parse authority
        if ($authority !== null) {
            $r_authority = "/^((.+?)@)?(\[[^\]]+\]|[^:]*)(:(\d*))?/";
            $matches = array();
            preg_match($r_authority, $authority, $matches);
            $userinfo   = !empty($matches[1]) ? $matches[2] : null;
            $host       = !empty($matches[3]) ? $matches[3] : '';
            $port       = !empty($matches[4]) ? (int) $matches[5] : null;
        } else {
            $port = $host = $userinfo = null;
        }

        return new HTMLPurifier_URI(
            $scheme, $userinfo, $host, $port, $path, $query, $fragment);
    }

}

// vim: et sw=4 sts=4
