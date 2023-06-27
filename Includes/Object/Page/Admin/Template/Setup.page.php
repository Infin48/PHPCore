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

namespace App\Page\Admin\Template;

/**
 * Setup
 */
class Setup extends \App\Page\Page
{
    /**
     * @var bool $ID If true - ID from URL will be loaded
     */
    protected bool $ID = true;

    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Overall.phtml';

    /**
     * @var string $permission Required permission
     */
    protected string $permission = 'admin.template';

    /**
     * Run ajax according to received item and action
     *
     * @param  string $ajax Received ajax
     * 
     * @return string Name of method
     */
    protected function ajax( string $ajax )
    {
        return match($ajax)
        {
            'run/template/preview' => 'previewTemplate',

            default => ''
        };
    }

    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        if (!$this->url->get('name'))
        {
            $this->error404();
        }

        // Navbar
        $this->navbar->elm1('appearance')->elm2('template')->active();

        // Language
        $language = $data->get('inst.language');

        // System
        $system = $data->get('inst.system');
        
        // File
        $file = new \App\Model\File\File();

        // Get settings to array
        $JSON = new \App\Model\File\JSON('/Styles/' . $this->url->get('name') . '/Info.json');

        if (!$JSON->exists())
        {
            $this->error404();
        }

        foreach ([$system->get('site_language'), $JSON->get('language')] as $_)
        {
            $INFO = new \App\Model\File\JSON('/Styles/' . $this->url->get('name') . '/Languages/' . $_ . '/Info.json');

            if ($INFO->exists())
            {
                $fce = function ( array $paths ) use ($_)
                {
                    $language = [];
                    foreach ($paths as $__)
                    {
                        require ROOT . '/Styles/' . $this->url->get('name') . '/Languages/' . $_ . '/Admin' . $__;

                        $language = array_merge($language, $language);
                    }

                    return $language;
                };

                $language->set('Style', $fce($INFO->get('tree.admin')));

                break;
            }
        }

        // Block
        $block = new \App\Visualization\BlockAdmin\BlockAdmin('Root/BlockAdmin:/Formats/Template.json');
        $block
            ->elm1('name')
                ->value($JSON->get('name'))
            ->elm1('version')
                ->value($JSON->get('version.version'))
            ->elm1('author')
                ->value($JSON->get('author.name') ?: $language->get('L_UNKNOWN'))
                ->href($JSON->get('author.link'));
        $data->block = $block->getDataToGenerate();

        // Set page title
        $data->set('data.head.title', $language->get('L_TEMPLATE.L_TEMPLATE') . ' - ' . $JSON->get('name'));

        // Save template data
        $data->set('data.template', array_merge($JSON->get(), ['template_name_folder' => $this->url->get('name')]));

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Admin/Template.json');
        $breadcrumb->create()->jumpTo()->title($JSON->get('name'))->href('/admin/template/setup/name-' . $data->get('data.template.template_name_folder') . '/');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        $form = new \App\Visualization\Form\Form([]);
        $data->form = $form->getDataToGenerate();

        if ($data->get('data.template.info'))
        {
            // Form
            $form = new \App\Visualization\Form\Form($data->form);
            $form
                ->append('Root/Form:/Formats/Admin/Template.json')
                ->form('template')
                    ->disButtons()
                    ->frame('template')
                        ->show()
                        ->input('info')
                            ->value($data->get('data.template.info'));
            $data->form = $form->getDataToGenerate();
        }

        // Build page class
        $page = $this->buildPage(
            path: '/Styles/' .  $data->get('data.template.template_name_folder') . '/Object/Page',
            object: 'Style\\' . $data->get('data.template.template_name_folder') . '\Page'
        );

        if ($page)
        {
            $form = new \App\Visualization\Form\Form($data->form);
            if ($data->get('data.template.template_name_folder') != $system->get('site_template'))
            {
                $form->set('body.plugin.body.plugin.body.show', [
                    'options' => [
                        'type' => 'button',
                        'target' => '_blank'
                    ],
                    'data' => [
                        'icon' => 'fa-solid fa-eye',
                        'desc' => 'L_SHOW.L_DESC',
                        'title' => 'L_SHOW.L_SHOW',
                        'button' => 'L_BTN.L_SHOW',
                        'ajax-action' => 'preview'
                    ]
                ]);
                $form->set('body.plugin.body.plugin.data.html.ajax-item', 'template');
            }
            $data->form = $form->getDataToGenerate();
            $page->navbar = $this->navbar;
            $page->navbar->elm1('appearance')->elm2('template')->active();
            
            // Set url
            $page->url->set('/admin/template/setup/name-' . $data->get('data.template.template_name_folder'));

            $page->body( $data, $db );
        }
    }

    /**
     * Form was successfully submitted
     * 
     * @param \App\Model\Data $data Loaded page data
     * @param \App\Model\Database\Query  $db Database query compiler
     * @param \App\Model\Post $post Post data
     *
     * @return void
     */
    public function previewTemplate( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Put template to session
        \App\Model\Session::put('preview', $data->get('data.template.template_name_folder'));

        // Redirect to index page
        $data->set('data.redirect', INDEX);
    }
}