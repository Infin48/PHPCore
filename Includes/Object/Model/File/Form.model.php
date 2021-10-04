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

/**
 * Form
 */
class Form extends \Model\Model
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
     * @var \Model\File\File $file File
     */
    public \Model\File\File $file;

    /**
     * Constructor
     * 
     * @param  string $file File name
     */
    public function __construct( \Model\File\File $file, string $uploadedFile )
    {
        parent::__construct();

        $this->file = $file;

        $this->uploadedFile = $_FILES[$uploadedFile] ?? [];

        if (empty($this->uploadedFile['tmp_name'])) {
            $this->uploadedFile = [];
        }

        if (method_exists($this, 'ini')) {
            $this->{'ini'}();
        }
    }

    /**
     * Checks file
     * 
     * @throws \Exception\Notice If is found an error
     * 
     * @return void|true
     */
    public function check()
    {
        if (!$this->uploadedFile) {
            return false;
        }
        
        if (!in_array($this->uploadedFile['type'], $this->formats)) {
            throw new \Exception\Notice('file_format');
        }

        if ($this->ignoreLimit === false) {
            if ($this->uploadedFile['size'] > $this->size) {
                throw new \Exception\Notice('file_size');
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
    public function upload( string $path )
    {
        if (move_uploaded_file($this->uploadedFile['tmp_name'], ROOT . $path . '.' . $this->getFormat())) {
            return true;
        }

        return false;
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
}