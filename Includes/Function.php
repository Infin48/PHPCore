<?php

/**
 * This file is part of the PHPCore forum software
 * 
 * Made by InfinCZ 
 * @link https://github.com/Infin48
 *
 * @copyright (c) PHPCore Limited https://phpcore.cz
 * @license GNU General Public License, version 3 (GPL-3.0)
 */

function redirect( string $pageName )
{
    header('Location: ' . $pageName);
    header('Connection: close');
    exit(0);
}

function refresh()
{
    header('Refresh:0');
    exit();
}

function parse( string $string )
{
    return strtolower(str_replace(' ', '-', preg_replace('/[^A-Za-z0-9 ]/', '', iconv('UTF-8','ASCII//TRANSLIT', $string))));
}

function getProfileImageColor()
{
    return PROFILE_IMAGES_COLORS[mt_rand(0, count(PROFILE_IMAGES_COLORS) - 1)];
}

function getKeysWithPrefix( array $data, string $prefix )
{
    $arr = [];
    foreach ($data as $key => $value)
    {
        if (str_starts_with($key, $prefix))
        {
            $arr[str_replace($prefix, '', $key)] = $value;
        }
    }

    return $arr;
}

function truncate( $text, $length = 100, $options = [] ) {
    $default = [
        'ending' => '...',
        'exact' => true,
        'html' => true
    ];
    $options = array_merge($default, $options);
    extract($options);

    $text = preg_replace('#<p[^>]*><img[^>]*>(.*?)</p>#is', '', $text);
    $text = preg_replace('#<video[^>]*>(.*?)</video>#is', '', $text);

    if ($html) {
        if (mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
            return $text;
        }
        $totalLength = mb_strlen(strip_tags($ending));
        $openTags = [];
        $truncate = '';

        preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER);
        foreach ($tags as $tag) {
            if (!preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s', $tag[2])) {
                if (preg_match('/<[\w]+[^>]*>/s', $tag[0])) {
                    array_unshift($openTags, $tag[2]);
                } else if (preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag)) {
                    $pos = array_search($closeTag[1], $openTags);
                    if ($pos !== false) {
                        array_splice($openTags, $pos, 1);
                    }
                }
            }
            $truncate .= $tag[1];

            $contentLength = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3]));
            if ($contentLength + $totalLength > $length) {
                $left = $length - $totalLength;
                $entitiesLength = 0;
                if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE)) {
                    foreach ($entities[0] as $entity) {
                        if ($entity[1] + 1 - $entitiesLength <= $left) {
                            $left--;
                            $entitiesLength += mb_strlen($entity[0]);
                        } else {
                            break;
                        }
                    }
                }

                $truncate .= mb_substr($tag[3], 0 , $left + $entitiesLength);
                break;
            } else {
                $truncate .= $tag[3];
                $totalLength += $contentLength;
            }
            if ($totalLength >= $length) {
                break;
            }
        }
    } else {
        if (mb_strlen($text) <= $length) {
            return $text;
        } else {
            $truncate = mb_substr($text, 0, $length - mb_strlen($ending));
        }
    }
    if (!$exact) {
        $spacepos = mb_strrpos($truncate, ' ');
        if (isset($spacepos)) {
            if ($html) {
                $bits = mb_substr($truncate, $spacepos);
                preg_match_all('/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER);
                if (!empty($droppedTags)) {
                    foreach ($droppedTags as $closingTag) {
                        if (!in_array($closingTag[1], $openTags)) {
                            array_unshift($openTags, $closingTag[1]);
                        }
                    }
                }
            }
            $truncate = mb_substr($truncate, 0, $spacepos);
        }
    }
    $truncate .= $ending;

    if ($html) {
        foreach ($openTags as $tag) {
            $truncate .= '</'.$tag.'>';
        }
    }

    return $truncate;
}

/**
 * Compares two versions
 *
 * @param  string $version1 First version
 * @param  string $version2 Second version
 * 
 * @return bool
 */
function comapre_version( string $version1, string $version2 )
{
    $ex1 = explode('.', $version1);
    $ex2 = explode('.', $version2);

    if (in_array($ex1[array_key_last($ex1)], ['x', 'X']) or in_array($ex2[array_key_last($ex2)], ['x', 'X']))
    {
        $version1 = substr($version1, 0, -2);
        $version2 = substr($version2, 0, -2);
    }

    return version_compare($version1, $version2, '==');
}