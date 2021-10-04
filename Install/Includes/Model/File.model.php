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

namespace Model;

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

    /**
     * Recursively deletes files
     *
     * @param string $path 
     * 
     * @return bool
     */
    public function delete( string $path )
    {
        if (file_exists($path)) {

            if (is_file($path)) {

                unlink($path);

                return;
            }

            foreach (array_diff(scandir($path), ['.', '..']) as $file) {

                (is_dir($path . '/' . $file)) ? $this->delete($path . '/' . $file) : unlink($path . '/' . $file);
            }

            return rmdir($path);
        }
    }

    /**
     * Deletes image
     * This method deletes only image with allowed format
     *
     * @param string $path Path to image without format
     * 
     * @return void
     */
    public function deleteImage( string $path )
    {
        foreach (['jpg', 'jpeg', 'png', 'svg', 'gif'] as $format) {

            if (file_exists(ROOT . '/Uploads' . $path . '.' . $format)) {

                unlink(ROOT . '/Uploads' . $path . '.' . $format);
            }
        }
    }

    /**
     * Copies recursively files and folders from path to path
     *
     * @param string $from Path from
     * @param string $to Path to
     * 
     * @return void
     */
    public function copyRec( string $from, string $to )
    {
        $dir = opendir($from);

        @mkdir($to);

        while(( $file = readdir($dir)) ) {

            if (( $file != '.' ) && ( $file != '..' )) {

                if ( is_dir($from . '/' . $file) ) {

                    $this->copyRec($from .'/'. $file, $to .'/'. $file);
                } else {

                    copy($from .'/'. $file,$to .'/'. $file);
                }
            }
        }
        closedir($dir);
    }
}