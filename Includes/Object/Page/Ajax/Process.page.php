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

use Model\Get;

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
        $get = new Get();

        // REQUIRED QUERY DATA
        $get->get('id') or exit();
        $get->get('method') or exit();
        $get->get('process') or exit();

        // PROCESS NAME
        $process = $get->get('process');

        // TYPE
        $type = explode('/', $get->get('process'))[0];

        // PERMISSION
        $permission = match($get->get('process')) {
            'Post/Create' => 'post.create',
            'Post/Edit' => 'post.edit',
            'Post/Delete' => 'post.delete',
            'ProfilePost/Edit', 'ProfilePostComment/Edit' => 'profilepost.edit',
            'ProfilePost/Create', 'ProfilePostComment/Create' => 'profilepost.create',
            'ProfilePost/Delete', 'ProfilePostComment/Delete' => 'profilepost.delete',
            default => ''
        };

        if ($permission and !$this->user->perm->has($permission)) exit();

        // SET NAME OF ID KEY
        $id = match($get->get('process')) {

            // REPORT
            'Post/Report', 'Topic/Report', 'ProfilePost/Report', 'ProfilePostComment/Report' => 'report_type_id',

            // NEW POSTS REQUIRES PARENT ID
            'Post/Create' => 'topic_id',
            'ConversationMessage/Create' => 'conversation_id',
            'ProfilePost/Create' => 'user_id',
            'ProfilePostComment/Create' => 'profile_post_id',

            'ConversationMessage/Edit' => 'conversation_message_id',
            'Topic/Like', 'Topic/Unlike' => 'topic_id',
            'Post/Edit', 'Post/Delete', 'Post/Like', 'Post/Unlike' => 'post_id',
            'ProfilePost/Edit', 'ProfilePost/Delete' => 'profile_post_id',
            'ProfilePostComment/Edit', 'ProfilePostComment/Delete' => 'profile_post_comment_id',
            default => exit()
        };

        // SET PROCESS METHOD
        $method = $get->get('method') === 'post' ? 'form' : 'call';

        // PROCESS DATA
        $options = [$id => $get->get('id')];

        if ($id == 'report_type_id') {
            $process = 'Report/Send';
            $options['report_type'] = $type;
            $this->process->setBlock('\Block\\' . $type);
        }

        if ($this->process->{$method}(type: $process, mode: 'direct', data: $options)) {

            switch ($get->get('process')) {

                case 'Post/Create':
                case 'ConversationMessage/Create':
                case 'ProfilePost/Create':
                case 'ProfilePostComment/Create':

                    // SET VISUALIZATION NAME
                    $visualization = match($get->get('process')) {
                        'Post/Create' => 'Topic',
                        'ConversationMessage/Create' => 'Conversation',
                        'ProfilePost/Create' => 'ProfilePost',
                        'ProfilePostComment/Create' => 'ProfilePostComment'
                    };

                    // SET TEMPLATE NAME
                    $template = match($get->get('process')) {
                        'ProfilePost/Create' => 'ProfilePost',
                        'ProfilePostComment/Create' => 'ProfilePostComment',
                        default => 'Block'
                    };
                
                    // VERIFY PARENT
                    $block = '\Block\\' . $type;
                    $block = new $block();
                    $blockData = $block->get($this->process->getID()) or exit();

                    // BLOCK
                    $block = new Block($visualization);
                    $block->object(strtolower($type))->appTo(array_merge($blockData, $this->user->get()))->jumpTo();

                    switch ($get->get('process')) {
                        
                        case 'ProfilePost/Create':
                            $block->option('bottom')->show()->up();

                        case 'ProfilePost/Create':
                        case 'ProfilePostComment/Create':
                            $prefix = 'profilepost';
                        break;

                        case 'Post/Create':
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

                    $this->data->data([
                        'content' => $this->file('/Blocks/Block/' . $template . '.phtml', [
                            'data' => $get->get('process') === 'ProfilePostComment/Create' ? $block->getData()['body']['profilepostcomment']['body'][$block->lastInsertName()] : $block->getData(),
                            'variable' => $get->get('process') === 'ProfilePostComment/Create' ? '$row' : '$this->data->block'
                        ]),
                        'status' => 'ok'
                    ]);

                break;

                case 'Post/Report':
                case 'Topic/Report':
                case 'ProfilePost/Report':
                case 'ProfilePostComment/Report':
                
                    $this->data->data([
                        'url' => $this->user->perm->has('admin.forum') ? $this->system->url->build('/admin/report/show/' . $this->process->getID()) : '',
                        'status' => 'ok',
                        'notice' => $this->user->perm->has('admin.forum') ? $this->file('/Blocks/Block/Notices/Reported.phtml') : '',
                        'message' => $this->language->get('L_NOTICE')['L_SUCCESS'][$this->process->getProcess()] ?? ''
                    ]);
                
                break;

                case 'Post/Edit':
                case 'ProfilePost/Edit':
                case 'ProfilePostComment/Edit':
                case 'ConversationMessage/Edit':

                    $this->data->data([
                        'button' => $this->file('/Blocks/Block/Buttons/Edit.phtml'),
                        'status' => 'ok'
                    ]);
                break;

                case 'Post/Delete':
                case 'ProfilePost/Delete':
                case 'ProfilePostComment/Delete':

                    $this->data->data([
                        'url' => $this->system->url->build('/admin/deleted/show/' . $this->process->getID()),
                        'notice' => $this->user->perm->has('admin.forum') ? $this->file('/Blocks/Block/Notices/Deleted.phtml') : '',
                        'status' => 'ok'
                    ]);
                break;

                case 'Post/Like':
                case 'Topic/Like':

                    $this->data->data([
                        'you' => $this->language->get('L_YOU'),
                        'block' => $this->file('/Blocks/Block/Likes.phtml'),
                        'button' => $this->file('/Blocks/Block/Buttons/Unlike.phtml'),
                        'status' => 'ok'
                    ]);
                break;

                case 'Post/Unlike':
                case 'Topic/Unlike':
                    
                    $this->data->data([
                        'button' => $this->file('/Blocks/Block/Buttons/Like.phtml'),
                        'status' => 'ok'
                    ]);
                break;
            }
        }
    }
}