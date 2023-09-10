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

 namespace App\Page\Get;

/**
 * Topic
 */
class Topic extends \App\Page\Page
{
    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // Form
        $post = new \App\Model\Post();

        // Load ID
        $id = (int)$post->get('id') ?: (int)$this->url->get('id');
        if (!$id)
        {
            throw new \App\Exception\System('Id parameter not found in POST neither GET.');
        }

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

        $file = new \App\Model\File\File();

        // Block
        $row = $db->select('app.topic.get()', $id, $deleted) or $this->error404();

        $row['images'] = [];
        // Search fimages
        $file->getFiles(
            path: '/Uploads/Topics/' . $id . '/Images/*',
            function: function ( \App\Model\File\File $file, string $path ) use (&$row)
            {
                $size = getimagesize($path);
                $row['images'][] = [
                    'path' => str_replace(ROOT, '', $path),
                    'width' => $size[0],
                    'height' => $size[1]
                ];
            }
        );

        $row['attachments'] = [];
        // Search attachments
        $file->getFiles(
            path: '/Uploads/Topics/' . $id . '/Attachments/*',
            flag: \App\Model\File\File::SORT_BY_DATE,
            function: function ( \App\Model\File\File $file, string $path ) use (&$row)
            {
                $explode = array_filter(explode('/', str_replace(ROOT, '', $path)));

                $row['attachments'][] = [
                    'name' => array_pop($explode),
                    'path' => str_replace(ROOT, '', $path)
                ];
            }
        );

        // Block
        $block = new \App\Visualization\Block\Block('Root/Block:/Formats/Topic.json');
        $block->elm1('topic')->show()->appTo(data: $row, function: function ( \App\Visualization\Block\Block $block ) use ($user, $language)
        {
            // Default variables
            $block
                // data.html.ajax-id = ID for ajax requests
                ->set('data.html.ajax-id', $block->get('data.topic_id'))
                // data.text = Text of topic
                ->set('data.text', $block->get('data.topic_text'))
                // data.name = Name of topic
                ->set('data.name', $block->get('data.topic_name'))
                // data.user = Link to user
                ->set('data.user', $this->build->user->link(data: $block->get('data')))
                // data.group = User group
                ->set('data.group', $this->build->user->group(data: $block->get('data')))
                // data.date = Date of topic creating
                ->set('data.date', $this->build->date->long($block->get('data.topic_created'), true))
                // data.user_image = User's profile image
                ->set('data.user_image', $this->build->user->image(data: $block->get('data'), role: true, online: true, size: '50x50'))
                // data.edited = Last time of editing
                ->set('data.edited', $this->build->date->long($block->get('data.topic_edited_at')));

            // But if this topic wasn't edited yet
            if ($block->get('data.topic_edited') == 0)
            {
                // Erase it
                $block->set('data.edited', '');
            }

            // If user has any reputation
            if ($block->get('data.user_reputation'))
            {
                // build reputation block
                $block->set('data.reputation', $this->build->user->reputation($block->get('data.user_reputation')));
            }

            if ($block->get('data.topic_image'))
            {
                $block->set('data.image_url', '/Uploads/Topics/' . $block->get('data.topic_id') . '/Header.' . $block->get('data.topic_image'));
            }

            // Foreach every like on topic
            foreach ($block->get('data.likes') as $key => $like)
            {
                // If like is from logged user
                if (LOGGED_USER_ID == $like['user_id'])
                {
                    // Show "you" instead of username 
                    $block->set('data.likes.' . $key . '.user_name', '<span>' . $language->get('L_YOU') . '</span>');
                    continue;
                }
                // Build link to user
                $block->set('data.likes.' . $key . '.user_name', $this->build->user->link(data: $like, group: false));
            }

            // If topic is not deleted
            if (!$block->get('data.deleted_id'))
            {
                // User is logged
                if ($user->isLogged())
                {
                    // Show 'report' button
                    $block->show('data.button.report');

                    // Topic is not from logged user
                    if (LOGGED_USER_ID != $block->get('data.user_id'))
                    {
                        // User already liked this topic
                        if (in_array(LOGGED_USER_ID, array_column($block->get('data.likes') ?: [], 'user_id'))) {
                            $block->show('data.button.unlike');
                        } else {
                            $block->show('data.button.like');
                        }
                    }
                }
            }
        }); 

        $data->block = $block->getDataToGenerate();
        
        $this->data = $data;

        $this->path = new \App\Model\Path();
        $this->language = $language;

        require $this->path->build('Root/Style:/Templates/Blocks/Visualization/Block/Block.phtml');

        exit();
    }
}