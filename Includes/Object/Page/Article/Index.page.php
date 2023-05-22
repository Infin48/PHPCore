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

namespace App\Page\Article;

/**
 * Index
 */
class Index extends \App\Page\Page
{
    /**
     * @var bool $ID If true - ID from URL will be loaded
     */
    protected bool $ID = true;

    /**
     * @var bool $editor If true - HTML editor will be loaded
     */
    public bool $editor = true;

    /**
     * @var bool $photoSwipe If true - JS library PhotoSwipe will be loaded 
     */
    protected bool $photoSwipe = true;

    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Article/Index.phtml';

    /**
     * @var bool $notification If true - notifications will be displayed
     */
    protected bool $notification = true;

    /**
     * Run ajax according to received item and action
     *
     * @param  string $ajax Received ajax
     * 
     * @return string Name of method
     */
    public function ajax( string $ajax )
    {
        return match($ajax)
        {
            'run/article/delete' => 'deleteArticle',
            'run/article/label' => 'markArticleWithLabels',
            'run/article/stick' => 'stickArticle',
            'run/article/unstick' => 'unstickArticle',

            default => ''
        };
    }

    /**
     * Load data according to received ajax
     *
     * @param  string $ajax Received ajax
     * 
     * @return array Data
     */
    public function ajaxData( string $ajax )
    {
        return match($ajax)
        {
            'run/article/delete',
            'run/article/stick',
            'run/article/unstick' => [
                'id' => STRING
            ],

            'run/article/label' => [
                'id' => STRING,
                'labels' => ARR
            ],

            default => []
        };
    }

    /**
     * According to received ajax check if logged user has appropriate permission
     *
     * @param  string $ajax Received ajax
     * 
     * @return string|true Name of permission or true if user has to be logged in
     */
    public function ajaxPermission( string $ajax )
    {
        return match($ajax)
        {
            'run/article/delete' => 'article.delete',

            'run/article/label' => 'article.label',

            'run/article/stick',
            'run/article/unstick' => 'article.stick',

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
        // System
        $system = $data->get('inst.system');

        // User
        $user = $data->get('inst.user');

        // User permission
        $permission = $user->get('permission');

        // If blog mode is disabled
		if ($system->get('site.mode') != 'blog')
		{
            // Show 404 error page
			$this->error404();
		}

        // Get article data
        $row = $db->select('app.article.get()', $this->url->getID()) or $this->error404();

        // Save article data
        $data->set('data.article', $row);

		// File model
        $file = new \App\Model\File\File();
        
        $data->set('data.article.images', []);
        // Search for images
        $file->getFiles(
            path: '/Uploads/Articles/' . $this->url->getID() . '/Images/*',
            function: function ( \App\Model\File\File $file, string $path ) use (&$data)
            {                
                $size = getimagesize($path);
                $data->set('data.article.images.' . mt_rand(), [
                    'path' => str_replace(ROOT, '', $path),

                    // Set default sizes for SVG images
                    'width' => $size[0] ?? 1920,
                    'height' => $size[1] ?? 1080
                ]);
            }
        );

        // File model
        $file = new \App\Model\File\File();

        $data->set('data.article.attachments', []);
        // Search for attachments
        $file->getFiles(
            path: '/Uploads/Articles/' . $this->url->getID() . '/Attachments/*',
            flag: \App\Model\File\File::SORT_BY_DATE,
            function: function ( \App\Model\File\File $file, string $path ) use (&$data)
            {
                $explode = explode('/', str_replace(ROOT, '', $path));
                $filter = array_filter($explode);

                $data->set('data.article.attachments.' . mt_rand(), [
                    'name' => array_pop($filter),
                    'path' => str_replace(ROOT, '', $path)
                ]);
            }
        );
        
        // Set page title
        $data->set('data.head.title', $data->get('data.article.article_name'));

        // Set page description
        $data->set('data.head.description', $data->get('data.article.article_text'));

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Index.json');
        $breadcrumb->create()->jumpTo()->title($data->get('data.article.article_name'))->href($this->build->url->article( data: $data->get('data.article')));
        $data->breadcrumb = $breadcrumb->getDataToGenerate();
        
        // Panel
        $panel = new \App\Visualization\Panel\Panel('Root/Panel:/Formats/Article.json');
        $panel->id($this->url->getID());
        $panel
            // Set position to tools dropdown
            ->elm1('tools')
                // Edit topic button
                ->elm2('edit', function ( \App\Visualization\Panel\Panel $panel ) use ($data, $system, $permission)
                {
                    // This article is from logged user
                    // Or in system is nable to edit article from another user
                    if ($system->get('site.mode.blog.editing') or LOGGED_USER_ID == $data->get('data.article.user_id'))
                    {
                        // Logged user has permission to edit articles
                        if ($permission->has('article.edit'))
                        {
                            // Show 'edit' button
                            $panel->show();
                        }
                    }
                })
                // Delete button
                ->elm2('delete', function ( \App\Visualization\Panel\Panel $panel ) use ($permission)
                {
                    // If logged user has permission to delete article
                    if ($permission->has('article.delete'))
                    {
                        // Show 'delete' button
                        $panel->show();
                    }
                })
                // Stick button
                ->elm2('stick', function ( \App\Visualization\Panel\Panel $panel ) use ($data, $permission)
                {    
                    // If topic is not sticked
                    if ($data->get('data.article.article_sticked') == 0)
                    {
                        // Logged user has permission to stick topic
                        if ($permission->has('article.stick'))
                        {
                            // Show 'stick' button
                            $panel->show();
                        }
                    }
                })
                // Unstick button
                ->elm2('unstick', function ( \App\Visualization\Panel\Panel $panel ) use ($data, $permission)
                {
                    // If topic is not sticked
                    if ($data->get('data.article.article_sticked') == 1)
                    {
                        // Logged user has permission to stick topic
                        if ($permission->has('article.stick'))
                        {
                            // Show 'unstick' button
                            $panel->show();
                        }
                    }
                })
            // Labels dropdown
            ->elm1('labels', function ( \App\Visualization\Panel\Panel $panel ) use ($db, $permission, $data)
            {
                // If logged user has permission to mark article with labels
                if ($permission->has('article.label'))
                {
                    // Show 'labels' dropdown
                    $panel->show();

                    $panel->fill(data: $db->select('app.label.all()'), function: function ( \App\Visualization\Panel\Panel $panel ) use ($data)
                    {
                        if (in_array($panel->get('data.label_id'), array_column($data->get('data.article.labels'), 'label_id')))
                        {
                            $panel->check();
                        }
                    });
                }
            });

        // Finish panel and ret ready for generate
        $data->panel = $panel->getDataToGenerate();

        // Block
        $block = new \App\Visualization\Block\Block('Root/Block:/Formats/Article.json');
        $block->elm1('article')->appTo(data: $data->get('data.article'), function: function ( \App\Visualization\Block\Block $block ) use ($system)
        {
            // Define variables
            $block
                // data.name - Article name
                ->set('data.name', $block->get('data.article_name'))
                // data.text - Article text
                ->set('data.text', $block->get('data.article_text'))
                // data.date - Date of creating article
                ->set('data.date', $this->build->date->long($block->get('data.article_created'), true));

            // If profiles are enabled
            if ($system->get('site.mode.blog.profiles'))
            {
                // Define another variables
                $block
                    // data.user = Link to user
                    ->set('data.user', $this->build->user->link(data: $block->get('data')))
                    // data.group = Group of user
                    ->set('data.group', $this->build->user->group(data: $block->get('data')))
                    // date.user_image = User's profile image
                    ->set('data.user_image', $this->build->user->image(data: $block->get('data'), online: true, role: true, size: '50x50'));

                    // IF user has any reouptation
                    if ($block->get('data.user_reputation'))
                    {
                        // Build reputation
                        $block->set('data.reputation', $this->build->user->reputation($block->get('data.user_reputation')));
                    }
            }

            // If article has header image
            if ($block->get('data.article_image'))
            {
                // Set path to image
                $block->set('data.image_url', '/Uploads/Articles/' . $block->get('data.article_id') . '/Header.' . $block->get('data.article_image'));
            }
        });
        $block->elm1('another')->fill(data: $db->select('app.article.lastExcept()', $this->url->getID()), function: function ( \App\Visualization\Block\Block $block ) use ($system)
        {
            // Define variables
            $block
                // data.link = Link to article
                ->set('data.link', '<a href="' . $this->build->url->article( data: $block->get('data') ) . '">' . $block->get('data.article_name'). '</a>')
                // data.date = Date of created article
                ->set('data.date', $this->build->date->short($block->get('data.article_created'), true))
                // data.text = Text of article
                ->set('data.text', truncate($block->get('data.article_text'), 400))
                // data.views = Number of views
                ->set('data.views', $block->get('data.article_views'));

            // If profiles are enabled
            if ($system->get('site.mode.blog.profiles'))
            {
                $block
                    // data.user = Link to user
                    ->set('data.user', $this->build->user->link(data: $block->get('data')))
                    // data.group = Group of user
                    ->set('data.group', $this->build->user->group(data: $block->get('data')))
                    // date.user_image = User's profile image
                    ->set('data.user_image', $this->build->user->image(data: $block->get('data'), online: true, role: true, size: '40x40'));
            }

            // If article has header image
            if ($block->get('data.article_image'))
            {
                // Set path to image
                $block->set('data.image_url', '/Uploads/Articles/' . $block->get('data.article_id') . '/Header.' . $block->get('data.article_image'));
            }
        });
        $block->split(1, 1);

        // Finish block and ret ready for generate
        $data->block = $block->getDataToGenerate();        

        // Update article views
        $db->update(TABLE_ARTICLES, [
            'article_views' => [PLUS],
        ], $data->get('data.article.article_id'));
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
    public function deleteArticle( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Delete article
        $db->query('
            DELETE a, alb
            FROM ' . TABLE_ARTICLES . '
            LEFT JOIN ' . TABLE_ARTICLES_LABELS . ' ON alb.article_id = a.article_id 
            WHERE a.article_id = ?
        ', [$data->get('data.article.article_id')]);

        // File model
        $file = new \App\Model\File\File();

        // Delete data
        $file->delete('/Uploads/Articles/' . $data->get('data.article.article_id') . '/*');

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $data->get('data.article.article_name'));

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Redirect to index
        $data->set('data.redirect', INDEX);

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
    public function markArticleWithLabels( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        if (count($post->get('labels')) > 5)
        {
            throw new \App\Exception\Notice('article_label_length_max');
        }

        // Delete all labels from article
        $db->query('
            DELETE alb FROM ' . TABLE_ARTICLES_LABELS . '
            WHERE article_id = ?
        ', [$data->get('data.article.article_id')]);

        foreach ($post->get('labels') as $labelID)
        {
            // Insert new labels to article
            $db->insert(TABLE_ARTICLES_LABELS, [
                'article_id' => $data->get('data.article.article_id'),
                'label_id' => $labelID
            ]);
        }

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $data->get('data.article.article_name'));

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Refresh page
        $data->set('options.refresh', true);
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
    public function stickArticle( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Stick topic
        $db->update(TABLE_ARTICLES, [
            'article_sticked' => '1'
        ], $data->get('data.article.article_id'));

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $data->get('data.article.article_name'));

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Refresh page
        $data->set('options.refresh', true);
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
    public function unstickArticle( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // Stick topic
        $db->update(TABLE_ARTICLES, [
            'article_sticked' => '0'
        ], $data->get('data.article.article_id'));

        // Add record to log
        $db->addToLog( name: __FUNCTION__, text: $data->get('data.article.article_name'));

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Refresh page
        $data->set('options.refresh', true);
    }
}