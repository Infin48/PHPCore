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
 * Path
 */
class Path
{
    /**
     * Builds path
     *
     * @param  string $path Path to file
     * 
     * @return string|bool
     */
    public function build( string $path )
    {
        // If given path already contains ROOT
        // Path was already built
        if (str_contains($path, ROOT))
        {
            // Return given path
            return $path;
        }

        // If is not entered any colon
        if (!str_contains($path, ':'))
        {
            // And path does not contains ROOT
            if (!str_contains($path, ROOT))
            {
                // Return path with root
                return ROOT . $path;
            }

            // Return given path
            return $path;
        }

        $wholePath = $path;

        list($guide, $path) = explode(':', $path);
        
        $guide = explode('/', $guide);

        $from = array_shift($guide);
        $newPath = match($from)
        {
            'Root' => '/Includes',
            'Style' => '/Styles/' . $name = array_shift($guide),
            'Plugin' => '/Plugins/' . $name = array_shift($guide),
            default => throw new \App\Exception\System('Path "' . $wholePath . '" has unsupported format!')
        };

        $what = array_shift($guide);

        if ($what == 'Style')
        {
            $template = new \App\Model\Template();
            $plugin = new \App\Plugin\Plugin();

            switch ($from)
            {
                case 'Root':
                    $p1 = $template->get('path') . '/' . $template->get('template') . $path;
                    $p2 = $template->get('path') . '/Default' . $path;
                break;

                case 'Plugin':
                    $p1 = $newPath . '/Styles/' . $plugin->get($name . '.template') . $path;
                    $p2 = $newPath . '/Styles/Default' . $path;
                break;
            }

            if (file_exists(ROOT . $p1))
            {
                if (str_starts_with($path, '/Templates/'))
                {
                    return ROOT . $p1;
                }

                return $p1;
            }

            if (file_exists(ROOT . $p2))
            {
                if (str_starts_with($path, '/Templates/'))
                {
                    return ROOT . $p2;
                }

                return $p2;
            }

            throw new \App\Exception\System('File "' . $p1 . '" neither "' . $p2 . '" was not found!'); 
        }
        

        $newPath .= match ($what)
        {
            'Block' => '/Object/Visualization/Block',
            'BlockAdmin' => '/Object/Visualization/BlockAdmin',
            'Breadcrumb' => '/Object/Visualization/Breadcrumb',
            'Form' => '/Object/Visualization/Form',
            'Lists' => '/Object/Visualization/Lists',
            'ListsAdmin' => '/Object/Visualization/ListsAdmin',
            'Navbar' => '/Object/Visualization/Navbar',
            'Notification' => '/Object/Visualization/Notification',
            'Panel' => '/Object/Visualization/Panel',
            'Sidebar' => '/Object/Visualization/Sidebar',
            default => throw new \App\Exception\Exception('Path "' . $wholePath . '" has unsupported format!')
        };

        $newPath .= $path;

        if (file_exists(ROOT . $newPath))
        {
            return ROOT . $newPath;
        }

        throw new \App\Exception\System('File "' . $newPath . '" was not found!'); 
    }
}