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
 * Add
 */
class Add extends \App\Page\Page
{
    /**
     * @var bool $editor If true - HTML editor will be loaded
     */
    protected bool $editor = true;

    /**
     * @var int $logged If 1 - page will be require user to be logged in, 2 - page will be require user to be logged out, 3 - it does not matter
     */
    protected int $logged = 1;

    /**
     * @var string $template Page template
     */
    protected string $template = 'Root/Style:/Templates/Article/New.phtml';

    /**
     * @var string $permission Required permission
     */
    protected string $permission = 'article.create';

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
		if ($system->get('site_mode') != 'blog')
		{
            // Show error page
			$this->error404();
		}

        // Breadcrumb
        $breadcrumb = new \App\Visualization\Breadcrumb\Breadcrumb('Root/Breadcrumb:/Formats/Index.json');
        $data->breadcrumb = $breadcrumb->getDataToGenerate();

        // Form
        $form = new \App\Visualization\Form\Form('Root/Form:/Formats/Article.json');
        $form
            ->form('article')
                ->callOnSuccess($this, 'newArticle')
                ->frame('article');
        
        // Setup row with labels in form
        $form->input('article_label', function ( \App\Visualization\Form\Form $form ) use ($permission, $db)
        {
            // If logged user has permission to marks articles with labels 
            if ($permission->has('article.label'))
            {    
                // Show 'labels list
                $form->show()->fill( data: $db->select('app.label.all()'), function: function ( \App\Visualization\Form\Form $form )
                {
                    $form->set('data.value', $form->get('data.label_id'));
                });

            }
        });
        $data->form = $form->getDataToGenerate();
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
    public function newArticle( \App\Model\Data $data, \App\Model\Database\Query $db, \App\Model\Post $post )
    {
        // User
        $user = $data->get('inst.user');

        // User permission
        $permission = $user->get('permission');

        // Increase number of created articles
        $db->update(TABLE_USERS, [
            'user_articles' => $user->get('user_articles') + 1 
        ], $user->get('user_id'));

        // File
        $file = new \App\Model\File\File();

        // Insert article
        $db->insert(TABLE_ARTICLES, [
            'user_id'       	=> LOGGED_USER_ID,
            'article_url'     	=> parse($post->get('article_name')),
            'article_text'    	=> $post->get('article_text'),
            'article_name'    	=> $post->get('article_name')
        ]);

        // Store inserted id
        $id = $db->lastInsertId();
        $file->mkdir('/Uploads/Articles/' . $id);
        $file->mkdir('/Uploads/Articles/' . $id . '/Images');
        $file->mkdir('/Uploads/Articles/' . $id . '/Attachments');

        // Save every uploaded attachment
		foreach ($post->get('article_attachments') as $attachment)
		{
            $attachment->upload('/Uploads/Articles/' . $id . '/Attachments/');
		}

        // Save every uploaded image
		foreach ($post->get('article_images') as $image)
		{
            $image->compress(50);
            $image->upload('/Uploads/Articles/' . $id . '/Images/');
		}

        if ($permission->has('article.label'))
        {
            // Store each labels
            foreach ($post->get('article_label') as $labelID)
            {
                // Insert label to article
                $db->insert(TABLE_ARTICLES_LABELS, [
                    'article_id' => $id,
                    'label_id' => $labelID
                ]);
            }
        }
        // Load uploaded intro image
        $format = '';

        $introImage = $post->get('article_image');
        if ($introImage->exists())
        {
            // Compress
            $introImage->compress(75);

            // Upload image
            $introImage->upload('/Uploads/Articles/' . $id, 'Header');

            $format = $introImage->getFormat() . '?' . mt_rand();
        }

        // Edit article url and image
        $db->update(TABLE_ARTICLES, [
            'article_url'     => $id . '.' . parse($post->get('article_name')),
            'article_image'   => $format
        ], $id);

        // Show success message
        $data->set('data.message.success', __FUNCTION__);
        
        // Sets redirect url
        $data->set('data.redirect', '/article/' . $id . '.' . parse($post->get('article_name')));
    }
}