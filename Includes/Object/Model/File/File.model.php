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

namespace Model\File;

use Model\File\Form;


/**
 * File
 */
class File
{
    /**
     * @var int Flag to remove path to file
     */
    const PATH_REMOVE = 1;

    /**
     * @var int Flag to keep file extension
     */
    const EXTENSION_KEEP = 2;

    /**
     * @var int Flag to remove file extension
     */
    const EXTENSION_REMOVE = 4;

    /**
     * @var int Flag to remove all file extensions
     */
    const EXTENSION_REMOVE_FULL = 8;

    /**
     * @var int Flag to search in forlder
     */
    const FOLDER_SEARCH = 16;

    /**
     * @var int Flag to skip folders
     */
    const FOLDER_SKIP = 32;

    /**
     * @var \Model\File\Form $form Form
     */
    public \Model\File\Form $form;

    /**
     * Loads file from form
     *
     * @param  string $file Name of file
     * @param  string $type File type
     * 
     * @return object File type
     */
    public function form( string $file, string $type )
    {
        return match($type) {
            FILE_TYPE_ZIP => new \Model\File\Type\Zip($this, $file),
            FILE_TYPE_IMAGE => new \Model\File\Type\Image($this, $file)
        };
    }

    /**
     * Returns all files from path
     *
     * @param string $path Path
     * @param int $flag Flags
     * 
     * @return array
     */
    public function getFiles( string $path, int $flag = null )
    {
        $files = [];
        foreach (glob(ROOT . $path) as $_path) {
            
            if (is_dir($_path)) {

                if ((bool)($flag & self::FOLDER_SKIP) === false) {
                    $files = array_merge($files, $this->getFiles(str_replace(ROOT, '', $_path) . '/*', $flag));
                }
                continue;
            }
            $file = $_path;
            if ($flag & self::EXTENSION_REMOVE) {
                $ex = explode('.', $file);
                array_pop($ex);
                $file = implode('.', $ex);
            }

            if ($flag & self::EXTENSION_REMOVE_FULL) {
                $file = explode('.', $file)[0];
            }

            if ($flag & self::PATH_REMOVE) {
                $ex = explode('/', $file);
                $file = $ex[count($ex) - 1];
            }

            array_push($files, $file);
        }

        return $files;
    }

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
                }
                else {
                    copy($from .'/'. $file,$to .'/'. $file);
                }
            }
        }
        closedir($dir);
    }

    /**
     * Downloads file from url
     *
     * @param string $url Url to download
     * @param string $path Path where downloaded files will be saved
     * 
     * @return void
     */
    public function download( string $url, string $path )
    {
        $this->delete('/'.$path);
        copy($url, $path, CONTEXT);
    }

    /**
     * Unzips given zip file and saves content of zip to given path
     *
     * @param string $fileToUnzip Path to zip file
     * @param string $saveTo Path where content of the zip will be saved
     * 
     * @return bool
     */
    public function unZip( string $fileToUnzip, string $saveTo )
    {
        $zip = new \ZipArchive;
        $res = $zip->open(ltrim($fileToUnzip, '/'));

        if ($res === true) {
            $zip->extractTo(ltrim($saveTo, '/') . '/');
            $zip->close();
            return true;
        }
        return false;
    }
}