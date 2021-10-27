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

namespace Page\Update\Ajax;

use Model\JSON;
use Model\File;
use Model\Database;

/**
 * Update
 */
class Update extends \Page\Page
{
    /**
     * Body of this page
     *
     * @return void
     */
    protected function body()
    {
        $API = json_decode(@file_get_contents('https://api.github.com/repos/Infin48/PHPCore/releases', false, stream_context_create(['http' => ['method' => 'GET','header' => ['User-Agent: PHP']]])), true);
        
        foreach ($API ?? [] as $release) {
            if ($release['tag_name'] === PHPCORE_VERSION) {
                $API = $release;
                break;
            }
        }

        $db = new Database(true);
        $db->file('/Install/Update.sql');
        $JSON = new JSON('/Includes/Settings/Settings.json');

        if ($JSON->exist()) {

            $db->table('phpcore_settings', [
                'site.name' =>  $JSON->get('site.name'),
                'site.locale' =>  $JSON->get('site.locale'),
                'site.version' =>  '1.1.1',
                'site.favicon' =>  $JSON->get('site.favicon'),
                'site.updated' =>  DATE_DATABASE,
                'site.started' =>  $JSON->get('site.started'),
                'site.timezone' =>  $JSON->get('site.timezone'),
                'site.language' =>  $JSON->get('site.language'),
                'site.template' =>  $JSON->get('site.template'),
                'site.description' =>  $JSON->get('site.description'),
                'site.language_editor' =>  $JSON->get('site.language_editor'),
                'site.background_image' =>  $JSON->get('site.background_image'),
                'site.background_image_position' =>  $JSON->get('site.background_image_position'),
                'registration.terms' =>  $JSON->get('registration.terms'),
                'registration.enabled' =>  $JSON->get('registration.enabled'),
                'registration.key_site' =>  $JSON->get('registration.key_site'),
                'registration.key_secret' =>  $JSON->get('registration.key_secret'),
                'email.prefix' =>  $JSON->get('email.prefix'),
                'email.smtp_host' =>  $JSON->get('email.smtp_host'),
                'email.smtp_port' =>  $JSON->get('email.smtp_port'),
                'email.smtp_username' =>  $JSON->get('email.smtp_username'),
                'email.smtp_password' =>  $JSON->get('email.smtp_password'),
                'email.smtp_enabled' =>  $JSON->get('email.smtp_enabled'),
                'image.max_size' =>  $JSON->get('image.max_size'),
                'cookie.enabled' =>  $JSON->get('cookie.enabled'),
                'cookie.text' =>  $JSON->get('cookie.text'),
                'session' => RAND,
                'default_group' =>  $JSON->get('default_group')
            ]);
        }
        
        $JSON = new JSON('/Includes/Settings/Statistics.json');

        if ($JSON->exist()) {
            $db->table('phpcore_statistics', [
                'user_deleted' =>  $JSON->get('user_deleted'),
                'post_deleted' =>  $JSON->get('post_deleted'),
                'topic_deleted' =>  $JSON->get('topic_deleted'),
                'profile_post_deleted' =>  $JSON->get('profile_post_deleted'),
                'profile_post_comment_deleted' =>  $JSON->get('profile_post_comment_deleted')
            ]);
        }

        $file = new File();
        $file->delete(ROOT . '/Includes/Settings');
        $file->delete(ROOT . '/Includes/Object/Page/Admin/Update');
        $file->mkdir('/Plugins');

        $JSON = new JSON('/Install/Includes/Settings.json');
        $JSON->set('page', 'end');
        $JSON->save();


        // UPDATE LABELS CSS
        $css = '';
        foreach ($db->query('SELECT label_id, label_class_name, label_color FROM ' . TABLE_LABELS, [], ROWS) as $label) {
            $css .= '.label.label--' . $label['label_class_name'] . '{background-color:' . $label['label_color'] . '}.label-text.label--' . $label['label_class_name'] . '{color:' . $label['label_color'] . ' !important}.label--' . $label['label_class_name'] . ' input[type="checkbox"] + label .checkbox-icon{border-color:' . $label['label_color'] . '}.label--' . $label['label_class_name'] . ' input[type="checkbox"]:checked + label .checkbox-icon{background-color:' . $label['label_color'] . '}';
        }
        file_put_contents(ROOT . '/Includes/Template/css/Label.min.css', $css);


        // UPDATE GROUPS CSS
        $css = '';
        foreach ($this->db->query('SELECT group_id, group_class_name, group_color FROM ' . TABLE_GROUPS, [], ROWS) as $group) {
            $css .= '.username.user--' . $group['group_class_name'] . '{color:' . $group['group_color'] . '}.statue.statue--' . $group['group_class_name'] . '{background-color:' . $group['group_color'] . '}.group--' . $group['group_class_name'] . ' input[type="checkbox"] + label span{border-color:' . $group['group_color'] . '}.group--' . $group['group_class_name'] . ' input[type="checkbox"]:checked + label span{background-color:' . $group['group_color'] . '}';
        }
        file_put_contents(ROOT . '/Includes/Template/css/Group.min.css', $css);


        // RETURN OK
        echo json_encode([
            'status' => 'ok'
        ]);
    
        exit();
    }
}