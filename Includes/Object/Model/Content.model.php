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

namespace App\Model;

/**
 * Content
 */
class Content
{
    /**
     * @var array $data Context data
     */
    public array $data = [];

    /**
     * @var string $URL Content URL
     */
    public string $URL = '';

    /**
     * @var array $item List of items
     */
    public array $item = [
        'post' => 'post',
        'topic' => 'topic',
        'message' => 'message',
        'content' => 'content',
        'profile-post' => 'profile',
        'profile-post-comment' => 'comment'
    ];
    
    /**
     * Checks if parametr in URL exists
     *
     * @param string $item Type of content
     * @param int $ID ID of content
     * 
     * @return bool
     */
    public function get( string $item = null, int $id = null, string $url = null, array $data = [] )
    {
        // Set default protocol to http://
        $protocol = 'http://';
        if (str_contains($_SERVER['SERVER_PROTOCOL'], 'HTTPS'))
        {
            $protocol = 'https://';
        }

        if (!is_null($id))
        {
            $data['id'] = $id;
        } else $item = 'content';

        if (!is_null($url))
        {
            $path = new \App\Model\Path();

            $data['url'] = $path->build($url);
        }

        // Build query data for HTTP context
        $data = http_build_query($data);

        // Pass token through session by default
        $name = session_name();
        $value = session_id();

        // But if user is logged through cookie
        // Pass data through cookie
        $cookie = new \App\Model\Cookie();
        if ($cookie->exists('token'))
        {
            $name = 'token';
            $value = $cookie->get('token');
        }

        // Create stream context
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header'=> ['Content-Type: application/x-www-form-urlencoded', 'Content-Length: ' . strlen($data), 'Cookie: ' . $name . '=' . $value . '; HttpOnly;'],
                'content' => $data
            ]
        ]);

        // Save end end session
        session_write_close();

        return file_get_contents($protocol . $_SERVER['HTTP_HOST'] . '/get/' . $this->item[$item] . '/action-get/' , false, $context);
    }
}