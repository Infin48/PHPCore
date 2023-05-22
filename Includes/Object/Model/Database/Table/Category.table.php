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

namespace App\Table;

/**
 * Category
 */
class Category extends Table
{
    /**
     * Returns category
     * 
     * @param int $ID Category ID
     * 
     * @return array
     */
    public function get( int $ID )
    {
        $category = $this->db->query('
            SELECT c.*, cp.*, f.forum_main
            FROM ' . TABLE_CATEGORIES . '
            LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION . ' ON cp.category_id = c.category_id
            LEFT JOIN ' . TABLE_FORUMS . ' ON f.category_id = c.category_id AND f.forum_main = 1
            WHERE c.category_id = ?
        ', [$ID]);

        if (!$category)
        {
            return [];
        }

        $category['permission_see'] = explode(',', $category['permission_see']);

        return $category;
    }

    /**
     * Returns all categories.
     * 
     * @param  int $ignoreCategoryID Given category will not be returned
     *
     * @return array
     */
    public function all( int $ignoreCategoryID = null )
    {
        $categories = $this->db->query('
            SELECT *
            FROM ' . TABLE_CATEGORIES . '
            LEFT JOIN ' . TABLE_CATEGORIES_PERMISSION . ' ON cp.category_id = c.category_id
            ' . ($ignoreCategoryID ? 'WHERE c.category_id <> ' . $ignoreCategoryID : '') . '
            ORDER BY c.position_index DESC
        ', [], ROWS);

        foreach ($categories as $i => $category)
        {
            $categories[$i]['permission_see'] = explode(',', $category['permission_see']);
        }
        return $categories;
    }

    /**
     * Returns all categories except category containing main forum.
     * Categories will be returned without permissions
     * 
     * @param  int $ignoreCategoryID Given category will not be returned
     *
     * @return array
     */
    public function withoutMainForum( int $ignoreCategoryID = null )
    {
        return $this->db->query('
            SELECT c.*
            FROM ' . TABLE_CATEGORIES . '
            LEFT JOIN ' . TABLE_FORUMS . ' ON f.category_id = c.category_id AND f.forum_main = 1
            WHERE f.forum_id IS NULL' . ($ignoreCategoryID ? ' AND c.category_id <> ' . $ignoreCategoryID : '') . '
            ORDER BY c.position_index DESC
        ', [], ROWS);
    }
}
