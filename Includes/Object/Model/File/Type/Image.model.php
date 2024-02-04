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

namespace App\Model\File\Type;

/**
 * Image
 */
class Image extends \App\Model\File\Form
{
    /**
     * @var array $formats Allowed image formats
     */
    public array $formats = ['image/jpg', 'image/jpeg', 'image/png', 'image/svg+xml', 'image/webp'];

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
        $this->size = (int)$this->file::$system->get('image_max_size');

        if (isset($this->uploadedFile['tmp_name']))
        {
            $exif = exif_read_data($this->uploadedFile['tmp_name']);
            $this->uploadedFile['orientation'] = $exif['Orientation'];
        }
    }
    
    /**
     * Allows GIFs
     *
     * @return void
     */
    public function allowGIF()
    {
        $this->formats[] = 'image/gif';
    }

    /**
     * Returns image width
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->uploadedFile['width'];
    }

    /**
     * Returns image height
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->uploadedFile['height'];
    }

    /**
     * Resizes image
     *
     * @param int $width Image width
     * @param int $height Image height
     * 
     * @return bool
     */
    public function resize( int $width, int $height )
    {
        if ($this->uploadedFile['type'] === 'image/gif' or $this->uploadedFile['type'] === 'image/svg+xml')
        {
            $type = 'image_gif_size';
            if ($this->uploadedFile['type'] === 'image/svg+xml')
            {
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

            if ($info[0] != $width or $info[1] != $height)
            {
                throw new \App\Exception\Notice($type, [
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

    /**
     * Compress uploaded image
     * 
     * @param string $quality Compress quality
     * 
     * @return bool
     */
    public function compress( int $quality = 25 )
    {
        if (!$this->uploadedFile)
        {
            return false;
        }

        $info = getimagesize($this->uploadedFile['tmp_name']);
        if (!$info['mime'])
        {
            return false;
        }

        if ($info['mime'] == 'image/gif')
        {
            return true;
        }

        $image = match ($info['mime'])
        {
            'image/webp' => imagecreatefromwebp($this->uploadedFile['tmp_name']),
            'image/jpeg' => imagecreatefromjpeg($this->uploadedFile['tmp_name']),
            'image/gif' => imagecreatefromgif($this->uploadedFile['tmp_name']),
            'image/png' => imagecreatefrompng($this->uploadedFile['tmp_name']),

            default => ''
        };
        
        if (!$image)
        {
            return true;
        }

        imagejpeg($image, $this->uploadedFile['tmp_name'], $quality);
        $this->uploadedFile['type'] = 'image/jpg';

        return true;
    }

    /**
     * Check image orientation before upload
     * 
     * @return bool
     */
    public function beforeUpload()
    {
        if (in_array($this->uploadedFile['type'], ['image/gif', 'image/svg+xml']))
        {
            return false;
        }

        if (empty($this->uploadedFile['orientation']))
        {
            return false;
        }

        if ($this->uploadedFile['orientation'] == 1)
        {
            return false;
        }

        $image = match ($this->uploadedFile['type']) {
            'image/png' => imagecreatefrompng($this->uploadedFile['tmp_name']),
            'image/jpg', 'image/jpeg' => imagecreatefromjpeg($this->uploadedFile['tmp_name']),
            default => throw new \Exception\System('Unsupported image format!')
        };

        $image = match ($this->uploadedFile['orientation'])
        {
            8 => imagerotate($image, 90, 0),
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            default => $image
        };

        match ($this->uploadedFile['type']) {
            'image/png' => imagepng($image, $this->uploadedFile['tmp_name']),
            'image/jpg', 'image/jpeg' => imagejpeg($image, $this->uploadedFile['tmp_name']),
            default => throw new \Exception\System('Unsupported image format!')
        };

        return true;
    }
}