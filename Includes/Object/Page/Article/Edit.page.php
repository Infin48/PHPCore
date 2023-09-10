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
 * Edit
 */
class Edit extends \App\Page\Page
{
    /**
     * @var bool $ID If true - ID from URL will be loaded
     */
    protected bool $ID = true;

    /**
     * @var bool $editor If true - HTML editor will be loaded
     */
    protected bool $editor = true;

    /**
     * @var bool $photoSwipe If true - JS library PhotoSwipe will be loaded 
     */
    protected bool $photoSwipe = true;

    /**
     * @var int $logged If 1 - page will be require user to be logged in, 2 - page will be require user to be logged out, 3 - it does not matter
     */
    protected int $logged = 1;

    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Article/Edit.phtml';

    /**
     * @var string $permission Required permission
     */
    protected string $permission = 'article.edit';

    /**
     * Body of this page
     *
     * @return void
     */
    public function body( \App\Model\Data $data, \App\Model\Database\Query $db )
    {
        // System
        $system = $data->get('inst.system');

        // If blog mode is disabled
		if (!in_array($system->get('site_mode'), ['blog', 'blog_with_forum']))
		{
            // Show error page
			$this->error404();
		}

        // Article
        $row = $db->select('app.article.get()', $this->url->getID()) or $this->error404();

        // Save article data
        $data->set('data.article', $row);

        // If this article is not from logged user
        if ($data->get('data.article.user_id') != LOGGED_USER_ID)
        {
            // And is not allowed to edit articles from other users
            if ($system->get('site_mode_blog_editing') == 0)
            {
                // Show error page
                $this->error404();
            }
        }

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Index.json');
        $breadcrumb->create()->jumpTo()->title($data->get('data.article.article_name'))->href($this->build->url->article( data: $data->get('data.article') ));
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // File model
        $file = new \App\Model\File\File();

        $images = [];
        // Search fimages
        $file->getFiles(
            path: '/Uploads/Articles/' . $this->url->getID() . '/Images/*',
            function: function ( \App\Model\File\File $file, string $path ) use (&$images)
            {
                $size = getimagesize($path);
                $images[] = [
                    'type' => 'image',
                    'path' => str_replace(ROOT, '', $path),

                    // Set default sizes for SVG images
                    'width' => $size[0] ?? 1920,
                    'height' => $size[1] ?? 1080
                ];
            }
        );

        $attachments = [];
        // Search attachments
        $file->getFiles(
            path: '/Uploads/Articles/' . $this->url->getID() . '/Attachments/*',
            flag: \App\Model\File\File::SORT_BY_DATE,
            function: function ( \App\Model\File\File $file, string $path ) use (&$attachments)
            {
                $explode = explode('/', str_replace(ROOT, '', $path));
                $filter = array_filter($explode);
                $attachments[] = [
                    'name' => array_pop($filter),
                    'path' => str_replace(ROOT, '', $path)
                ];
            }
        );

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Article.json');
        $form
            ->form('article')
                ->callOnSuccess($this, 'editArticle')
                ->data($data->get('data.article'))
                ->frame('article');

        $form->input('delete_article_image', function ( \App\Visualization\Form\Form $form ) use ($data)
        {
            // If header image exists
            if ($data->get('data.article.article_image'))
            {    
                // Show 'delete image' checkbox
                $form->show();
            }
        });

        $form
            // Fill images
            ->input('article_images')->fill($images)
            // Fill attachments
            ->input('article_attachments')->fill($attachments);
        $data->form = $form->getDataToGenerate();

        // Set page title
        $data->set('data.head.title', $data->get('data.article.article_name'));
        
        // Set page description
        $data->set('data.head.description', $data->get('data.article.article_text'));
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
    public function editArticle( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        $file = new \App\Model\File\File();
        $file->mkdir('/Uploads/Articles/' . $data->get('data.article.article_id'));
        $file->mkdir('/Uploads/Articles/' . $data->get('data.article.article_id') . '/Images');
        $file->mkdir('/Uploads/Articles/' . $data->get('data.article.article_id') . '/Attachments');

        // File
        $file = new \App\Model\File\File();
        
        // If delete article image button was pressed
        if ($post->get('delete_article_image')) {

            // Delete image
            $file->delete('/Uploads/Articles/' . $data->get('data.article.article_id') . '/Header.*');

            // Update article image
            $db->update(TABLE_ARTICLES, [
                'article_image' => ''
            ], $data->get('data.article.article_id'));

        } else {

            $image = $post->get('article_image');
            if ($image->exists())
            {
                // Delete old image
                $file->delete('/Uploads/Articles/' . $data->get('data.article.article_id') . '/Header.*');

                $image->compress(75);

                // Upload image
                $image->upload('/Uploads/Articles/' . $data->get('data.article.article_id'), 'Header');

                // Update article image
                $db->update(TABLE_ARTICLES, [
                    'article_image' => $image->getFormat() . '?' . mt_rand()
                ], $data->get('data.article.article_id'));
            }
        }

        // Save every uploaded attachment
		foreach ($post->get('article_attachments') as $attachment)
		{
            $attachment->upload('/Uploads/Articles/' . $data->get('data.article.article_id') . '/Attachments');
		}

        // Save every uploaded image
		foreach ($post->get('article_images') as $image)
		{
            $image->compress(50);
            $image->upload('/Uploads/Articles/' . $data->get('data.article.article_id') . '/Images');
		}

        // Update article
        $db->update(TABLE_ARTICLES, [
            'article_url'       => $data->get('data.article.article_id') . '.' . parse($post->get('article_name')),
            'article_text'      => $post->get('article_text'),
            'article_name'      => $post->get('article_name'),
            'article_edited'    => 1,
            'article_edited_at' => DATE_DATABASE
        ], $data->get('data.article.article_id'));

        // Show success message
        $data->set('data.message.success', __FUNCTION__);

        // Sets redirect url
        $data->set('data.redirect', '/article/' . $data->get('data.article.article_id') . '.' . parse($post->get('article_name')));
    }
}