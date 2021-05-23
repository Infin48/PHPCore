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

namespace Process\Admin;

use Model\File;

/**
 * Update
 */
class Update extends \Process\ProcessExtend
{
    /**
     * @var array $require Required data
     */
    public array $require = [
        'data' => [
            'tag',
            'path'
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        $file = new File();
        $file->delete(ROOT . '/Update/');
        $file->download($this->data->get('path'), 'Update.zip');
        
        $file->unZip('Update.zip', '/Update/');
        $glob = glob(rtrim(ROOT, '/') . '/Update/*', GLOB_ONLYDIR);

        if (file_exists($glob[0] . '/Install/Update.php')) {
            require $glob[0] . '/Install/Update.php';
        }

        $file->delete($glob[0] . '/Install/');
        $file->delete($glob[0] . '/.htaccess');

        $file->delete($glob[0] . '/Includes/Settings/.htdata.json');
        $file->delete($glob[0] . '/Includes/Settings/Settings.json');
        $file->delete($glob[0] . '/Includes/Settings/Statistics.json');
        $file->delete($glob[0] . '/Includes/Settings/URL.json');

        $file->delete($glob[0] . '/Includes/Template/css/Group.min.css');
        $file->delete($glob[0] . '/Includes/Template/css/Label.min.css');
        
        $file->delete($glob[0] . '/Includes/Template/js/Editor/Big.min.js');
        $file->delete($glob[0] . '/Includes/Template/js/Editor/Medium.min.js');
        $file->delete($glob[0] . '/Includes/Template/js/Editor/Small.min.js');


        $file->copyRec($glob[0], ROOT);
        $file->delete(ROOT . '/Update.zip');
        $file->delete(ROOT . '/Update/');

        $this->system->settings->set('site.version', $this->data->get('tag'));
        $this->system->settings->set('site.updated', date(DATE));

        $this->updateSession();

        // ADD RECORD TO LOG
        $this->log();
    }
}