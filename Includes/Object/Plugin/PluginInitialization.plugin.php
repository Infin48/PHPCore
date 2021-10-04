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

namespace Plugin;

use Model\Database\QueryCompiler;
use Model\Permission;

use Process\Process;

/**
 * PluginInitialization
 */
class PluginInitialization
{
    /**
     * Sets keys to tables
     *
     * @return void
     */
    public function DBKey( array $keys )
    {
        foreach ($keys as $table => $key) {

            QueryCompiler::addKey($table, $key);
        }
    }

    /**
     * Adds permissions
     *
     * @return void
     */
    public function addPerm( string $category, array $permissions )
    {
        foreach ($permissions as $permission) {

            Permission::add($category, $permission);
        }
    }

    /**
     * Sets keys to processes
     *
     * @return void
     */
    public function processKey( array $keys )
    {
        foreach ($keys as $process => $key) {
            Process::addKey($process, $key);
        }
    }

    /**
     * Sets permissions to processes
     *
     * @return void
     */
    public function processPerm( array $permissions )
    {
        foreach ($permissions as $process => $permission) {

            Process::addPerm($process, $permission);
        }
    }
}