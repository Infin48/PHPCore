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

namespace App\Model\File;

/**
 * Form
 */
class Form
{
    /**
     * @var bool $ignoreLimit If true - file will be ignore max file size limit
     */
    public bool $ignoreLimit = false;

    /**
     * @var array $uploadedFile File data
     */
    public array $uploadedFile = [];

    /**
     * @var string $path Path to file
     */
    public string $path = '';

    /**
     * @var \App\Model\File\File $file File
     */
    protected \App\Model\File\File $file;

    /**
     * Constructor
     * 
     * @param  \Model\File\File $file File name
     * @param  array $data File data
     */
    public function __construct( \App\Model\File\File $file, array $data = [] )
    {
        $this->file = $file;
        $this->uploadedFile = $data;

        if (empty($this->uploadedFile['tmp_name']))
        {
            $this->uploadedFile = [];
        }

        if (method_exists($this, 'ini'))
        {
            $this->{'ini'}();
        }
    }

    /**
     * Checks file
     * 
     * @throws \App\Exception\Notice If is found an error
     * 
     * @return void|true
     */
    public function check()
    {
        if (!$this->uploadedFile) {
            return false;
        }
        
        if (!in_array($this->uploadedFile['type'], $this->formats))
        {
            throw new \App\Exception\Notice('file_format');
        }

        if ($this->ignoreLimit === false)
        {
            if ($this->uploadedFile['size'] > $this->size)
            {
                throw new \App\Exception\Notice('file_size');
            }
        }

        return true;
    }

    /**
     * Uploads file to path
     * 
     * @param string $path The path
     * 
     * @return bool
     */
    public function upload( string $path, string $name = null )
    {
        if (!$this->uploadedFile)
        {
            return false;
        }

        if (is_null($name))
        {
            $name = $this->uploadedFile['name'];
        } else $name .= '.' . $this->getFormat();

        $this->path = $path . '/' . $name;

        if (move_uploaded_file($this->uploadedFile['tmp_name'], ROOT . $this->path)) {
            return true;
        }

        return false;
    }

    /**
     * Compress uploaded file
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
            return;
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

        return true;
    }

    /**
     * Returns uploaded file format
     *
     * @return string Image format
     */
    public function getFormat()
    {
        if (!empty($this->uploadedFile)) {
            return pathinfo(basename($this->uploadedFile['name']), PATHINFO_EXTENSION);
        }
    }

    /**
     * File will be ignore max file size limit
     *
     * @return void
     */
    public function ignoreLimit()
    {
        $this->ignoreLimit = true;
    }

    /**
     * Returns true if file exists
     *
     * @return bool
     */
    public function exists()
    {
        if ($this->uploadedFile)
        {
            return true;
        }

        return false;
    }

    /**
     * Returns path to uploaded file
     *
     * @return bool
     */
    public function getPath()
    {
        return $this->path;
    }
}