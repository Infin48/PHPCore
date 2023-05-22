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

namespace App\Page\Profile\Tab;

/**
 * Activity
 */
class Activity extends \App\Page\Page
{
    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Profile/Activity.phtml';

    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // User
        $user = $data->get('inst.user');

        // User permission
        $permission = $user->get('permission');

        // Language
        $language = $data->get('inst.language');

        // If logged user has permission to see deleted content
        $deleted = false;
        if ($permission->has('admin.forum'))
        {
            $deleted = true;
        }
        
        // Pagination
        $pagination = new \App\Model\Pagination();
        $pagination->max(MAX_ACTIVITY);
        $pagination->url($this->url->getURL());
        $pagination->total($db->select('app.user.activityCount()', $this->url->getID(), $deleted));
        $data->pagination = $pagination->getData();

        $activity = $db->select('app.user.activity()', $this->url->getID(), $deleted);
        foreach ($activity as &$item)
        {
            $item = array_merge($item, $data->get('data.profile'));
        }

        // Block
        $list = new \App\Visualization\Lists\Lists('Root/Lists:/Formats/Activity.json');
        $list->elm1('activity')->fill(data: $activity, function: function ( \App\Visualization\Lists\Lists $list ) use ($language)
        {
            if ($list->get('data.text'))
            {
                $list->set('data.text', '<div class="trumbowyg">' . truncate($list->get('data.text'), 150) . '</div>');
            }
            $list->set('data.user', $this->build->user->link(data: $list->get('data')));
            $list->set('data.date', $this->build->date->short($list->get('data.created')));
            $list->set('data.user_image', $this->build->user->image(data: $list->get('data'), size: '40x40'));

            if ($list->get('data.deleted_id'))
            {
                $list->disable();
            }

            switch ($list->get('data.type'))
            {
                case 'topic':
                case 'post':
                case 'topiclike':
                case 'postlike':
                
                    $list->set('data.topic_url', $list->get('data.url'));

                    switch ($list->get('data.type'))
                    {
                        case 'topic':
                        case 'topiclike':
        
                            $list->set('data.topic_id', $list->get('data.item_id'));
                        break;
        
                        case 'post':
                        case 'postlike':
        
                            $list->set('data.post_id', $list->get('data.item_id'));
                            $list->set('data.topic_id', $list->get('data.parent_id'));
                        break;
                    }
                break;

                case 'profilepost':
                case 'profilepostcomment':

                    switch ($list->get('data.type'))
                    {
                        case 'profilepost':
        
                            $list->set('data.profile_post_id', $list->get('data.item_id'));
                        break;
        
                        case 'profilepostcomment':
        
                            $list->set('data.profile_post_position', $list->get('data.parent_position'));
                            $list->set('data.profile_post_comment_position', $list->get('data.position'));

                            $list->set('data.profile_post_id', $list->get('data.parent_id'));
                            $list->set('data.profile_post_comment_id', $list->get('data.item_id'));


                        break;
                    }
                break;
            }

            $type = match ($list->get('data.type'))
            {
                'postlike' => 'post',
                'topiclike' => 'topic',
                default => $list->get('data.type')
            };

            $name = $language->get('L_USER.L_ACTIVITY.L_LIST.' . $list->get('data.type'));
            $name = strtr($name, [
                '{url}' => $this->build->url->{$type}($list->get('data')),
                '{name}' => $list->get('data.name')
            ]);


            $list->set('data.name', $name);
        });
        $data->list = $list->getDataToGenerate();
    }
}