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

namespace App\Visualization\Block;

/**
 * Block
 */
class Block extends \App\Visualization\Visualization
{
    /**
     * @var array $translate List of keys which will be translated to language
     */
    protected array $translate = [
        'body.?.body.?.data.button.?.title',
        'body.?.body.?.body.?.data.button.?.title'
    ];

    /**
     * @var array $defaultValues List of keys and their default values
     */
    protected array $defaultValues = [
        'body.?.body.?.data.button' => '',
        'body.?.body.?.data.likes' => '',
        'body.?.body.?.data.images' => '',
        'body.?.body.?.data.attachments' => '',
        'body.?.body.?.data.image_url' => '',
        'body.?.body.?.options.selected' => false,
        'body.?.body.?.options.disabled' => false,
        'body.?.body.?.options.closed' => false
    ];

    /**
     * @var array $parseToPath List of keys which their values will be parsed to path
     */
    protected array $parseToPath = [
        'body.?.options.template',
        'body.?.body.?.options.template',
        'body.?.body.?.body.?.options.template'
    ];

    /**
     * @var array $parseToURL List of keys which their values will be parsed to URLs
     */
    protected array $parseToURL = [];

    /**
     * @var array $buttons List of buttons
     */
    protected array $buttons = [
        'save' => 'Save',
        'like' => 'Like',
        'edit' => 'Edit',
        'quote' => 'Quote',
        'unlike' => 'Unlike',
        'report' => 'Report',
        'delete' => 'Delete'
    ];

    /**
     * Opens current object
     *
     * @return $this
     */
    public function open()
    {
        $this->set('options.closed', false);
        return $this;
    }

    /**
     * Closes current object
     *
     * @return $this
     */
    public function close()
    {
        $this->set('options.closed', true);
        return $this;
    }

    /**
     * Shows notice
     * 
     * @param string $notice Notice name
     *
     * @return $this
     */
    public function notice( string $notice )
    {
        $this->set('data.notice.' . $notice, ['hide' => false]);
        return $this;
    }

    /**
     * Executes code for every object
     *
     * @param  \Visualization\Visualization $visual
     * 
     * @return void|false
     */
    protected function clb_each()
    {
        if ($this->obj->get('data.notice'))
        {
            foreach ($this->obj->get('data.notice') as $noticeName => $notice)
            {    
                if ($this->obj->get('data.notice.' . $noticeName . '.hide') === true)
                {

                    $this->obj->delete('data.notice.' . $noticeName);
                    continue;
                }
                
                $this->obj->set('data.notice.' . $noticeName . '.template', $this->path->build('Root/Style:/Templates/Blocks/Visualization/Block/Notices/' . ucfirst($noticeName) . '.phtml'));

                $this->obj->set('data.notice.' . $noticeName . '.href', match ($noticeName)
                {
                    'deleted' => \App\Model\Url::build('/admin/deleted/show/' . $this->obj->get('data.deleted_id')),
                    'reported' => \App\Model\Url::build('/admin/report/show/' . $this->obj->get('data.report_id')),
                });
            }
        }

        if ($this->obj->get('data.button'))
        {
            foreach ($this->obj->get('data.button') as $btnName => $btn)
            {
                if (!isset($btn['title']))
                {
                    if (!isset($this->buttons[$btnName]))
                    {
                        $this->obj->delete('data.button.' . $btnName);
                        continue;
                    }
                }
                if ($this->obj->get('data.button.' . $btnName . '.hide') == true)
                {
                    $this->obj->delete('data.button.' . $btnName);
                    continue;
                }

                if (is_string($btn) or isset($btn['template']))
                {
                    // Assign button template
                    $this->obj->set('data.button.' . $btnName, $this->path->build(path: $btn['template'] ?? $btn));

                    continue;
                }

                if (isset($this->buttons[$btnName]))
                {
                    // Assign template to button
                    $this->obj->set('data.button.' . $btnName, $this->path->build(path: 'Root/Style:/Templates/Blocks/Visualization/Block/Buttons/' . $this->buttons[$btnName] . '.phtml'));
                }
            }
        }
    }
}
