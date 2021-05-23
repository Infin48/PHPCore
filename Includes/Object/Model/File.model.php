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
class File extends Model
{
    /**
     * @var int $size Max upload image size
     */
    public int $size;

    /**
     * @var array $file File data
     */
    public array $file = [];

    /**
     * @var array $images List of allowed image formats
     */
    public array $images = ['image/jpg', 'image/jpeg', 'image/gif', 'image/png', 'image/svg+xml'];
    
    /**
     * Loads file
     *
     * @param  string $fileName Name of file
     * 
     * @return void
     */
    public function load( string $fileName )
    {
        $this->file = $_FILES[$fileName];
    }

    /**
     * Checks if file is valid
     *
     * @param boolean $size If false - max upload image size will be ignored
     * 
     * @return bool
     */
    public function check( bool $size = true )
    {
        if (!$this->file) {
            return false;
        }

        if ($this->isFileUploaded() === true) {
            if ($this->isImage() === true) {
                if ($size === true) {
                    if ($this->maxImageSize() === true) {
                        return true;
                    }
                } else {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checks if file is uploaded.
     *
     * @return bool
     */
    public function isFileUploaded()
    {
        if (!empty($this->file)) {
            if (file_exists($this->file['tmp_name']) || is_uploaded_file($this->file['tmp_name'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if uploaded file is an image
     *
     * @throws \Exception\Notice If file is not valid image
     * 
     * @return bool
     */
    private function isImage()
    {
        if (in_array($this->file['type'], $this->images)) {
            return true;
        }

        throw new \Exception\Notice('image_format');
        return false;
    }

    /**
     * Checks if uploaded file has valid size
     *
     * @throws \Exception\Notice If size of image is larger than allowed
     * 
     * @return bool
     */
    private function maxImageSize()
    {
        if ($this->file['size'] <= $this->system->settings->get('image.max_size')) {
            return true;
        }

        throw new \Exception\Notice('image_size');
        return false;
    }

    /**
     * Returns uploaded file format
     *
     * @return string Image format
     */
    public function getFormat()
    {
        if (!empty($this->file)) {
            return pathinfo(basename($this->file['name']), PATHINFO_EXTENSION);
        }
    }

    /**
     * Creates directory
     *
     * @param string $path Path
     * 
     * @return bool
     */
    public function createFolder( string $path )
    {
        return @mkdir(ROOT . '/Uploads' . $path);
    }

    /**
     * Uploads file
     *
     * @param string $dir Path to upload
     * 
     * @return bool
     */
    public function upload( string $dir )
    {
        if (!$this->file) {
            return false;
        }

        $this->deleteImage($dir);

        move_uploaded_file($this->file['tmp_name'], ROOT . '/Uploads/' . $dir . '.' . $this->getFormat());

        return true;
    }

    /**
     * Resizes image
     *
     * @param string $path Image path
     * @param int $width Image width
     * @param int $height Image height
     * 
     * @return bool
     */
    function resize( string $path, int $width, int $height )
    {
        if ($this->file['type'] === 'image/svg+xml' or $this->file['type'] === 'image/gif') {
            return true;
        }

        $path = ROOT . '/Uploads' . $path;
        $info = getimagesize($path);

        list($oldWith, $oldHeight) = $info;

        $image = match ($info['mime']) {
            'image/gif' => imagecreatefromgif($path),
            'image/png' => imagecreatefrompng($path),
            'image/jpg', 'image/jpeg' => imagecreatefromjpeg($path)
        };
            
        $resizedImage = imagecreatetruecolor( $width, $height ); 
        imagecolortransparent($resizedImage, imagecolorallocate($resizedImage, 0, 0, 0) ); 
        imagealphablending($resizedImage, false); 
        imagesavealpha($resizedImage, true); 
                            
        imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $width, $height, $oldWith, $oldHeight); 
                
        match ($info['mime']) {
            'image/gif' => imagegif($resizedImage, $path),
            'image/png' => imagepng($resizedImage, $path),
            'image/jpg', 'image/jpeg' => imagejpeg($resizedImage, $path)
        };
         
        return true;
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
        foreach ($this->images as $image) {
            $format = explode('+', explode('/', $image)[1])[0];
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
        $res = $zip->open($fileToUnzip);
        if ($res === true) {
            $zip->extractTo(ltrim($saveTo, '/'));
            $zip->close();
            return true;
        }
        return false;
    }
}