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

namespace Model\File\Type;

/**
 * Image
 */
class Image extends \Model\File\Form
{
    /**
     * @var array $formats Allowed image formats
     */
    public array $formats = ['image/jpg', 'image/jpeg', 'image/gif', 'image/png', 'image/svg+xml'];

    /**
     * @var int $size Max image size
     */
    public int $size = 0;

    /**
     * Inicialise
     * 
     * @return void
     */
    public function ini()
    {
        $this->size = (int)$this->system->get('image.max_size');
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
    function resize( int $width, int $height )
    {
        if ($this->uploadedFile['type'] === 'image/gif' or $this->uploadedFile['type'] === 'image/svg+xml') {

            $type = 'image_gif_size';
            if ($this->uploadedFile['type'] === 'image/svg+xml') {
                $type = 'image_svg_size';

                $xml = simplexml_load_file($this->uploadedFile['tmp_name']);
                $attr = $xml->attributes();
                $info = [
                    0 => $attr->width,
                    1 => $attr->height
                ];
            } else {
                $info = getimagesize($this->uploadedFile['tmp_name']);
            }

            if ($info[0] != $width or $info[1] != $height) {
                throw new \Exception\Notice($type, [
                    'width' => $width,
                    'height' => $height
                ]);
            }

            return true;
        }

        $path = $this->uploadedFile['tmp_name'];
        $info = getimagesize($path);

        list($oldWith, $oldHeight) = $info;

        $image = match ($info['mime']) {
            'image/png' => imagecreatefrompng($path),
            'image/jpg', 'image/jpeg' => imagecreatefromjpeg($path)
        };
            
        $resizedImage = imagecreatetruecolor( $width, $height ); 
        imagecolortransparent($resizedImage, imagecolorallocate($resizedImage, 0, 0, 0) ); 
        imagealphablending($resizedImage, false); 
        imagesavealpha($resizedImage, true); 
                            
        imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $width, $height, $oldWith, $oldHeight); 
                
        match ($info['mime']) {
            'image/png' => imagepng($resizedImage, $path),
            'image/jpg', 'image/jpeg' => imagejpeg($resizedImage, $path)
        };
         
        return true;
    }
}