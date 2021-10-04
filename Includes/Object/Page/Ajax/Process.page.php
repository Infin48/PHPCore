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

namespace Page\Ajax;

use Model\Ajax;

use Visualization\Block\Block;

/**
 * Process
 */
class Process extends \Page\Page
{
    /**
     * @var array $settings Page settings
     */
    protected array $settings = [
        'loggedIn' => true
    ];
    
    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        $ajax = new Ajax();
        $ajax->ajax(

            require: ['id', 'method', 'process'],

            exec: function ( \Model\Ajax $ajax ) {
                
                $ajax->process(
                    
                    process: $this->process,
                    permission: $this->user->perm,

                    key: $ajax->get('id'),
                    type: $ajax->get('process'),
                    method: $ajax->get('method') === 'post' ? 'form' : 'call',
                
                    success: function ( \Model\Ajax $ajax ) {

                        // TYPE
                        $type = array_values(array_filter(explode('/', $ajax->get('process'))))[0];

                        switch ($ajax->get('process')) {

                            case '/Post/Create':
                            case '/ConversationMessage/Create':
                            case '/ProfilePost/Create':
                            case '/ProfilePostComment/Create':
            
                                // SET VISUALIZATION NAME
                                $visualization = match($ajax->get('process')) {
                                    '/Post/Create' => '/Topic',
                                    '/ConversationMessage/Create' => '/Conversation',
                                    '/ProfilePost/Create' => '/ProfilePost',
                                    '/ProfilePostComment/Create' => '/ProfilePostComment'
                                };
            
                                // SET TEMPLATE NAME
                                $template = match($ajax->get('process')) {
                                    '/ProfilePost/Create' => 'ProfilePost',
                                    '/ProfilePostComment/Create' => 'ProfilePostComment',
                                    default => 'Block'
                                };
                            
                                // VERIFY PARENT
                                $block = '\Block\\' . $type;
                                $block = new $block();
                                $blockData = $block->get($this->process->getID()) or exit();
            
                                // BLOCK
                                $block = new Block($visualization);
                                $block->object(strtolower($type))->appTo(data: array_merge($blockData, $this->user->get()), function: function ( \Visualization\Block\Block $block) {
                                    
                                    if ($block->obj->get->data('name')) {
                                        $block->obj->set->data('name',  $this->language->get('L_RE') . ': ' . $block->obj->get->data('name'));
                                    }

                                })->jumpTo();
            
                                switch ($ajax->get('process')) {
                                    
                                    case '/ProfilePost/Create':
                                        $block->option('bottom')->show()->up();
            
                                    case '/ProfilePost/Create':
                                    case '/ProfilePostComment/Create':
                                        $prefix = 'profilepost';
                                    break;
            
                                    case '/Post/Create':
                                        $prefix = 'post';
                                    break;
                                }
            
                                if ($prefix ?? false) {
                                    if ($this->user->perm->has($prefix . '.edit') === false) {
                                        $block->delButton('edit');
                                    }
            
                                    if ($this->user->perm->has($prefix . '.delete') === false) {
                                        $block->delButton('delete');
                                    }
                                }
            
                                $data = $block->getData();
            
                                $ajax->data([
                                    'content' => $this->file('/Blocks/Visualization/Block/' . $template . '.phtml', [
                                        'data' => $ajax->get('process') === '/ProfilePostComment/Create' ? $data['body']['profilepostcomment']['body'][$block->lastInsertName()] : $data,
                                        'variable' => $ajax->get('process') === '/ProfilePostComment/Create' ? '$row' : '$this->data->block'
                                    ])
                                ]);
            
                            break;
            
                            case '/Post/Report':
                            case '/Topic/Report':
                            case '/ProfilePost/Report':
                            case '/ProfilePostComment/Report':
                            
                                $ajax->data([
                                    'url' => $this->user->perm->has('admin.forum') ? $this->url->build('/admin/report/show/' . $this->process->getID()) : '',
                                    'notice' => $this->user->perm->has('admin.forum') ? $this->file('/Blocks/Visualization/Block/Notices/Reported.phtml') : '',
                                    'message' => $this->language->get('L_NOTICE')['L_SUCCESS'][$this->process->getProcess()] ?? ''
                                ]);
                            
                            break;
            
                            case '/Post/Edit':
                            case '/ProfilePost/Edit':
                            case '/ProfilePostComment/Edit':
                            case '/ConversationMessage/Edit':
            
                                $ajax->data([
                                    'button' => $this->file('/Blocks/Visualization/Block/Buttons/Edit.phtml')
                                ]);
                            break;
            
                            case '/Post/Delete':
                            case '/ProfilePost/Delete':
                            case '/ProfilePostComment/Delete':
            
                                $ajax->data([
                                    'url' => $this->url->build('/admin/deleted/show/' . $this->process->getID()),
                                    'notice' => $this->user->perm->has('admin.forum') ? $this->file('/Blocks/Visualization/Block/Notices/Deleted.phtml') : ''
                                ]);
                            break;
            
                            case '/Post/Like':
                            case '/Topic/Like':
            
                                $ajax->data([
                                    'you' => $this->language->get('L_YOU'),
                                    'block' => $this->file('/Blocks/Visualization/Block/Likes.phtml'),
                                    'button' => $this->file('/Blocks/Visualization/Block/Buttons/Unlike.phtml')
                                ]);
                            break;
            
                            case '/Post/Unlike':
                            case '/Topic/Unlike':
                                
                                $ajax->data([
                                    'button' => $this->file('/Blocks/Visualization/Block/Buttons/Like.phtml')
                                ]);
                            break;
                        }
                        $ajax->ok();
                    }
                );
            }
        );
        $ajax->end();
    }
}