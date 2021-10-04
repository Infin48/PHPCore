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

namespace Page\Update;

/**
 * End
 */
class End extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'template' => '/Update/End'
    ];

    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        $this->data->breadcrumb = [
            'list' => [
                'database',
                'update',
                'end',
            ],
            'active' => [
                'database',
                'update',
                'end',
            ]
        ];

        $API = json_decode(@file_get_contents('https://api.github.com/repos/Infin48/PHPCore/releases', false, stream_context_create(['http' => ['method' => 'GET','header' => ['User-Agent: PHP']]])), true);

        $this->data->data['update_name'] = '?';
        foreach ($API ?? [] as $release) {
            if ($release['tag_name'] === PHPCORE_VERSION) {
                $this->data->data['update_name'] = $release['name'] ?? $release['tag_name'];
                break;
            }
        }
    }
}