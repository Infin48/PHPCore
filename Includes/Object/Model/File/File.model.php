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
 * File
 */
class File
{
    /**
     * @var int Flag to remove file extension
     */
    const REMOVE_EXTENSION = 2;

    /**
     * @var int Flag to search only folders
     */
    const ONLY_FOLDERS = 4;

    /**
     * @var int Flag to skip folders
     */
    const SKIP_FOLDERS = 8;

    /**
     * @var int Flag to disable nesting
     */
    const DO_NOT_NEST = 16;

    /**
     * @var int Flag to sort files by last created
     */
    const SORT_BY_DATE = 32;

    /**
     * @var \App\Model\File\Form $form Form
     */
    public \App\Model\File\Form $form;

    /**
     * @var \App\Model\System $system System instance
     */
    public static \App\Model\System $system;

    /**
     * Loads file from form
     *
     * @param  string $file Name of file
     * @param  string $type File type
     * 
     * @return object|array File type
     */
    public function form( string $file, string $type )
    {
        if (str_ends_with($type, '[]'))
        {
            if (!isset($_FILES[$file]))
            {
                return [];
            }

            if (!isset($_FILES[$file]['name'][0]))
            {
                return [];
            }

            if (empty($_FILES[$file]['name'][0]))
            {
                return [];
            }
        }
        if (!isset($_FILES[$file]) or empty($_FILES[$file]['name']))
        {
            return match($type)
            {
                'file/zip' => new \App\Model\File\Type\Zip($this, []),
                'file/misc' => new \App\Model\File\Type\Misc($this, []),
                'file/image' => new \App\Model\File\Type\Image($this, [])
            };
        }

        if (str_ends_with($type, '[]'))
        {
            $array = [];
            
            if (isset($_FILES[$file]['tmp_name']))
            {
                $count = count($_FILES[$file]['tmp_name']);
                
                for ($i = 0; $i <= $count - 1; $i++)
                {
                    foreach ($_FILES[$file] as $name => $data)
                    {
                        $array[$i][$name] = $data[$i];
                    }
                    
                    if ($type === FILE_TYPE_IMAGE)
                    {
                        if ($array[$i]['tmp_name'])
                        {
                            $size = getimagesize($array[$i]['tmp_name']);
                            if ($size)
                            {
                                $array[$i]['width'] = $size[0];
                                $array[$i]['height'] = $size[1];
                            }
                        }
                    }
                }
            }

            foreach ($array as $i => $data)
            {
                $array[$i] = match($type)
                {
                    'file/zip[]' => new \App\Model\File\Type\Zip($this, $data),
                    'file/misc[]' => new \App\Model\File\Type\Misc($this, $data),
                    'file/image[]' => new \App\Model\File\Type\Image($this, $data)
                };
            }


            return $array;
        }


        if ($type === 'file/image')
        {
            if (!empty($_FILES[$file]['tmp_name']))
            {
                $size = getimagesize($_FILES[$file]['tmp_name']);
                if ($size)
                {
                    $_FILES[$file]['width'] = $size[0];
                    $_FILES[$file]['height'] = $size[1];
                }
            }
        }
        return match($type) {
            'file/zip' => new \App\Model\File\Type\Zip($this, $_FILES[$file]),
            'file/misc' => new \App\Model\File\Type\Misc($this, $_FILES[$file]),
            'file/image' => new \App\Model\File\Type\Image($this, $_FILES[$file])
        };
    }
    
    /**
     * Returs file with extensions
     *
     * @param  string $file Path to file
     * @param  string $prefix
     * 
     * @return string
     */
    public function getFile( string $file, string $prefix = '/' )
    {
        $ex = explode('/', $file);
        return $prefix . $ex[count($ex) - 1];
    }
    
    /**
     * Removes all extensions from file
     *
     * @param  string $file File
     * 
     * @return string
     */
    public function removeExt( string $file )
    {
        return explode('.', $file)[0];
    }

    /**
     * Returns file or folder
     *
     * @param  string $file File/folder path
     * 
     * @return string
     */
    public function getType( string $file )
    {
        if (is_dir($file))
        {
            return FILE_TYPE_FOLDER;
        }

        if (in_array(explode('.', array_pop(explode('/', $file)))[1], ['svg', 'jpg', 'jpge', 'png']))
        {
            return FILE_TYPE_IMAGE;
        }

        return FILE_TYPE_MISC;
    }

    /**
     * Returns image width
     *
     * @param  string $image Image path
     * 
     * @return int
     */
    public function getImageWidth( string $image )
    {
        return getimagesize($image)[0] ?? 0;
    }

    /**
     * Returns image height
     *
     * @param  string $image Image path
     * 
     * @return int
     */
    public function getImageHeight( string $image )
    {
        return getimagesize($image)[1] ?? 0;
    }

    /**
     * Returns all files from path
     *
     * @param string $path Path
     * @param int $flag Flags
     * 
     * @return array
     */
    public function getFiles( string $path, int $flag = null, callable $function = null )
    {
        $files = [];

        $glob = glob(ROOT . $path);
        if ($flag & self::SORT_BY_DATE)
        {
            $glob = array_combine($glob, array_map('filectime', $glob));
            arsort($glob);
            $glob = array_keys($glob);
        }

        foreach ($glob as $_path)
        {
            if (is_dir($_path))
            {
                if ($flag & self::DO_NOT_NEST)
                {
                    if ($function)
                    {
                        $function($this, $_path);
                    }

                    array_push($files, $_path);
                    continue;
                }
                if ($flag & self::SKIP_FOLDERS) continue;

                if ($function)
                {
                    $function($this, $_path);
                }

                $files = array_merge($files, $this->getFiles(str_replace(ROOT, '', $_path) . '/*', $flag, $function));

                array_push($files, $_path);
                continue;
            }

            if ($flag & self::ONLY_FOLDERS) continue;

            $file = $_path;
            if ($flag & self::REMOVE_EXTENSION)
            {
                $ex = explode('/', $file);
                $file = explode('.', array_pop($ex))[0];
            }

            if ($function)
            {
                $function($this, $file);
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
     * Returns true if file exists otherwise false
     *
     * @param string $path Path o file
     * 
     * @return bool
     */
    public function exists( string $path )
    {
        return file_exists(ROOT . $path);
    }

    /**
     * Returns true if folder exists otherwise false
     *
     * @param string $path Path o folder
     * 
     * @return bool
     */
    public function is_dir( string $path )
    {
        return is_dir(ROOT . $path);
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
        if (!str_contains($path, ROOT))
        {
            $path = ROOT . $path;
        }

        if (is_file($path))
        {
            @chmod($path, 0777);
            @unlink($path);
            return;
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

    /**
     * Creates folder
     *
     * @param string $path Path to folder
     * 
     * @return void
     */
    public function createFolder( string $path )
    {
        @mkdir(ROOT . $path);
    }
}