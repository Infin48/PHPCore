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

namespace Page\Admin\Plugin;

use Block\Plugin as BlockPlugin;

use Model\File\File;

use Visualization\Field\Field;
use Visualization\Admin\Block\Block;
use Visualization\Breadcrumb\Breadcrumb;

/**
 * Setup
 */
class Setup extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'id' => int,
        'redirect' => '/admin/plugin/',
        'template' => '/Overall',
        'permission' => 'admin.settings'
    ];
    
    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        // NAVBAR
        $this->navbar->object('settings')->row('plugin')->active();
        
        // BLOCK
        $plugin = new BlockPlugin();

        // PLUGIN DATA
        $data = $plugin->get($this->url->getID()) or $this->error();

        if ($this->url->getFirst() and (is_dir(ROOT . '/Plugins/' .  $data['plugin_name_folder'] . '/Object/Page/Admin/' . ucfirst($this->url->getFirst())) or file_exists(ROOT . '/Plugins/' .  $data['plugin_name_folder'] . '/Object/Page/Admin/' .  ucfirst($this->url->getFirst()) . '.page.php'))) {
            
            $page = 'Page\Plugin\\' . $data['plugin_name_folder'] . '\Admin' . str_replace('Page', '', $this->build(ROOT . '/Plugins/' . $data['plugin_name_folder'] . '/Object/Page/Admin/'));
            
            $this->page = new $page;
            $this->page->url = $this->url;
            $this->page->data = $this->data;
            $this->page->user = $this->user;
            $this->page->style = $this->style;
            $this->page->build = $this->build;
            $this->page->navbar = $this->navbar;
            $this->page->system = $this->system;
            $this->page->process = $this->process;
            $this->page->language = $this->language;
            $this->page->template = $this->template;
            
            $this->page->ini();
            $this->page->body();
            
        } else {
            
            
            // BREADCRUMB
            $breadcrumb = new Breadcrumb('/Admin/Plugin');
            $this->data->breadcrumb = $breadcrumb->getData();
            
            
            // FILE
            $file = new File();
            $data = array_merge($data, json_decode(@file_get_contents(ROOT . '/Plugins/' . $data['plugin_name_folder'] . '/Info.json'), true));
            
            // BLOCK
            $block = new Block('/Plugin');
            $block
            ->object('name')->value($data['name'])
            ->object('version')->value($data['version']['current'])
            ->object('author')
                ->value($data['author']['name'] ?? '?')
                ->href($data['author']['link'] ?? '');
            $this->data->block = $block->getData();

            // PAGES
            $pages = [];
            foreach ($file->getFiles('/Plugins/' . $data['plugin_name_folder'] . '/Object/Page/Plugin/*') as $_file) {
                $path = strtolower(ltrim(explode('.', str_replace(ROOT . '/Plugins/' . $data['plugin_name_folder'] . '/Object/Page/Plugin', '', $_file))[0], '/'));
                $pages[] = [
                    'page_name' =>  $path === 'index' ? '/plugin/' . $data['plugin_id'] . '/' : '/plugin/' . $data['plugin_id'] . '/' . trim('/' . preg_replace('/\/?index$/i', '', $path) . '/', '/') . '/'
                ];
            }
            
            if (file_exists($url = (ROOT . '/Plugins/' . $data['plugin_name_folder'] . '/Object/Page/Admin/Index.page.php'))) {

                $page = 'Page\Plugin\\' . $data['plugin_name_folder'] . '\Admin\Index';
                $this->page = new $page;
                $this->page->url = $this->url;
                $this->page->data = $this->data;
                $this->page->style = $this->style;
                $this->page->user = $this->user;
                $this->page->build = $this->build;
                $this->page->navbar = $this->navbar;
                $this->page->system = $this->system;
                $this->page->process = $this->process;
                $this->page->language = $this->language;
                $this->page->template = $this->template;

                $this->page->settingsPlugin = $plugin->getSettings($data['plugin_name_folder']);

                $this->page->ini();
                $this->page->body();
            }

            // FIELD
            $field = new Field('/Admin/Plugin');
            $field->object('986g_571g24t59846')->fill(data: $pages);
            $field->disButtons();
            $this->data->field = array_merge_recursive($field->getData(), $this->data->field);
            
            $this->data->head['title'] = $this->language->get('L_PLUGIN') . ' - ' . $data['name'];
        }
    }
}