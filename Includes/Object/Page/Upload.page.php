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

namespace App\Page;

/**
 * Upload
 */
class Upload extends \App\Page\Page
{
    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        $file = new \App\Model\File\File();

        // Load image
        $image = $file->form('file', 'file/image');
        $image->allowGIF();
        
        if ($image->exists())
        {
            if ($image->check())
            {
                $image->compress(50);
                $image->upload('/Uploads/Content');

                echo json_encode([
                    'success' => true,
                    'file' => $image->getPath()
                ]);
                exit();
            }
        }

        exit();
    }
}