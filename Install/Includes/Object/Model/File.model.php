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
 * File
 */
class File
{
    /**
     * Creates directory
     *
     * @param string $path Path
     * 
     * @return bool
     */
    public function mkdir( string $path )
    {
        return @mkdir(ROOT . $path);
    }

    public function delete( string $path )
    {
        if (!str_contains($path, ROOT))
        {
            $path = ROOT . $path;
        }

        foreach (glob($path) as $file)
        {
            if (file_exists($file))
            {
                if (is_file($file))
                {
                    @chmod($file, 0777);
                    @unlink($file);
                    continue;
                }

                foreach (glob($file . '/*') as $_file)
                {
                    if (is_dir($_file))
                    {
                        $this->delete($_file . '/*');
                        continue;
                    }
                    @unlink($_file);
                }

                @chmod($file, 0777);
                @rmdir($file);
            }
        }
        $folder = str_replace('/*', '', $path);
        if (is_dir($folder))
        {
            @chmod($folder, 0777);
            @rmdir($folder);  
        }
    }
}