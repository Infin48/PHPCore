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

$language = [
    'L_NOTICE' => [
        'L_FAILURE_MESSAGE' => 'Vyskytla se chyba!',

        'L_FAILURE' => [

            'user_name' => 'Zadejte uživatelské jméno.',
            'user_name_exist' => 'Uživatel s tímto uživatelským jménem již existuje.',
            'user_name_does_not_exist' => 'Uživatel s tímto uživatelským jménem neexistuje.',
            'user_name_characters' => 'Uživatelské jméno obsahuje nepovolené znaky.',
            'user_name_length_max' => 'Uživatelské jméno je příliš dlouhé.',
            'user_name_length_min' => 'Uživatelské jméno je příliš krátké.',
            
            'user_password' => 'Zadejte heslo.',
            'user_password_characters' => 'Heslo obsahuje nepovolené znaky.',
            'user_password_length_max' => 'Heslo je příliš dlouhé.',
            'user_password_length_min' => 'Heslo je příliš krátké.',
            
            'user_email' => 'Zadejte e-mail.',
            'user_email_wrong' => 'Zadaný e-mail není platný.',
            'user_email_exist' => 'Uživatel s tímto e-mailem již existuje.',
            'user_email_length_max' => 'E-mail je příliš dlouhý.',
            'user_email_length_min' => 'E-mail je příliš krátký.',

            'notification_name' => 'Zadejte název oznámení.',
            'notification_text' => 'Zadejte text oznámení.',

            'button_link' => 'Zadejte platný odkaz.',
            'button_name' => 'Zadejte název tlačítka.',
            'button_name_length_max' => 'Název tlačítka je příliš dlouhý.',

            'button_sub_link' => 'Zadejte platný odkaz.',
            'button_sub_name' => 'Zadejte název tlačítka.',
            'button_sub_name_length_max' => 'Název tlačítka je příliš dlouhý.',


            'category_name' => 'Zadejte název kategorie.',
            'category_name_length_max' => 'Název kategorie je příliš dlouhý.',
            'category_description' => 'Zadejte popis kategorie.',
            'category_description_length_max' => 'Popis kategorie je příliš dlouhý.',

            'role_name' => 'Zadejte název role.',
            'role_name_length_max' => 'Název role je příliš dlouhý.',

            'forum_name' => 'Zadejte název fóra.',
            'forum_name_length_max' => 'Název fóra je příliš dlouhý.',
            'forum_description' => 'Zadejte popis fóra.',
            'forum_description_length_max' => 'Popis fóra je příliš dlouhý.',


            'label_name' => 'Zadejte název štítku.',
            'label_name_length_max' => 'Název štítku je příliš dlouhý.',

            'group_name' => 'Zadejte název skupiny.',
            'group_name_length_max' => 'Název skupiny je příliš dlouhý.',
            
            'email' => 'Zadejte e-mail.',
            'email_prefix' => 'Zadejte prefix emailu.',

            'page_name' => 'Zadejte název stránky.',
            'page_name_length_max' => 'Název stránky je příliš dlouhý.',

            'file_size' => 'Nahraný obrázek je příliš velký.',
            'file_format' => 'Formát nahraného obrázku není podporován.',

            'site_name' => 'Zadejte název webových stránek.',
            'site_description' => 'Zadejte popis webových stránek.',

            'site_keywords' => 'Zadejte klíčová slova.',

            'settings_url_from' => 'Zadejte URL.',
            'settings_url_to' => 'Zadejte překlad URL.',
            'settings_url_error' => 'Obě URL musí začínat lomítkem a končit pomlčkou nebo lomítkem.',
            'settings_url_end_same' => 'Obě URL musí končit stejným znakem.',

            'image_max_size' => 'Zadejte maximální velikost nahrávání obrázků.',
            'image_gif_size' => 'Nahraný gif musí mít velikost {width}x{height}.',
            'image_svg_size' => 'Nahraný vektorový obrázek musí mít velikost {width}x{height}.',
            
            'searchUser' => 'Uživatel s tímto uživatelským jménem neexistuje.'
        ],

        'L_SUCCESS' => [

            'optimizeTables' => 'Tabulky byly úspěšně optimalizovány.',

            'deletePlugin' => 'Plugin byl úspěšně smazán',
            'installPlugin' => 'Plugin byl úspěšně nainstalován.',
            'uninstallPlugin' => 'Plugin byl úspěšně odinstalován.',
            'editPluginSettings' => 'Plugin byl úspěšně upraven.',
            
            'movePostBack' => 'Příspěvek byl úspěšně vracen zpět do fóra.',
            'deleteDeletedPost' => 'Příspěvek byl úspěšně smazán.',

            'moveTopicBack' => 'Téma bylo úspěšně vraceno zpět do fóra.',
            'deleteDeletedTopic' => 'Téma bylo úspěšně smazáno.',

            'moveProfilePostBack' => 'Profilový příspěvek byl úspěšně vracen zpět na profil.',
            'deleteDeletedProfilePost' => 'Profilový příspěvek byl úspěšně smazán.',

            'moveProfilePostCommentBack' => 'Profilový komentář byl úspěšně vracen zpět pod profilový příspěvek.',
            'deleteDeletedProfilePostComment' => 'Profilový komentář byl úspěšně smazán.',

            'markReportedContentAsClosed' => 'Hlášení bylo úspěšně uzavřeno.',

            'editWebsiteSettings' => 'Nastavení webových stránek bylo úspěšně změněno.',
            'editRegistrationSettings' => 'Nastavení registrace bylo úspěšně změněno.',
            'editEmailSettings' => 'Nastavení e-mailů bylo úspěšně změněno.',
            'sendTestEmail' => 'Testovací e-mail byl úspěšně odeslán.',

            'activeLanguage' => 'Výchozí jazyk byl úspěšně změněn.',
            'deleteLanguage' => 'Jazyk byl úspěšně smazán.',

            'newTranslateForURL' => 'Překlad URL adresy byl úspěšně vytvořen.',
            'deleteTranslateForURL' => 'Překlad URL adresy byl úspěšně smazán.',

            'newSidebarObject' => 'Prvek byl úspěšně přidán.',
            'deleteSidebarObject' => 'Prvek byl úspěšně smazán.',

            'synchronizeRoles' => 'Role byly úspěšně synchronizovány.',
            'synchronizeGroups' => 'Skupiny byly úspěšně synchronizovány.',
            'synchronizeLabels' => 'Štítky byly úspěšně synchronizovány.',
            'synchronizeScripts' => 'Skripty byly úspěšně synchronizovány.',
            'synchronizeStyles' => 'Styly byly úspěšně synchronizovány.',
            'synchronizeTemplate' => 'Šablona byla úspěšně synchronizována.',

            'editUser' => 'Nastavení uživatele bylo úspěšně změněno.',
            'deleteUser' => 'Uživatel byl úspěšně smazán.',
            'promoteUser' => 'Oprávnění bylo úspěšně převedeno.',
            'activateUser' => 'Učet byl úspěšně aktivován.',

            'newCustomPage' => 'Stránka byla úspěšně vytvořena.',
            'deleteCustomPage' => 'Stránka byla úspěšně smazána.',
            'editCustomPageThroughAdminPanel' => 'Stránka byla úspěšně upravena.',

            'newLabel' => 'Štítek byl úspěšně vytvořen.',
            'editLabel' => 'Štítek byl úspěšně upraven.',
            'deleteLabel' => 'Štítek byl úspěšně smazán.',

            'newRole' => 'Role byla úspěšně vytvořena.',
            'editRole' => 'Role byla úspěšně upravena.',
            'deleteRole' => 'Role byla úspěšně smazána.',
            
            'editGroup' => 'Skupina byla úspěšně upravena.',
            'newGroup' => 'Skupina byla úspěšně vytvořena.',
            'deleteGroup' => 'Skupina byla úspěšně smazána.',
            'editGroupPermission' => 'Opravnění skupiny bylo úspěšně změněno.',

            'deleteTemplate' => 'Šablona byla úspěšně smazána.',
            'activateTemplate' => 'Výchozí šablona byla úspěšně změněna.',
            'editTemplateSettings' => 'Nastavení šablony bylo úspěšně změněno.',

            'editForum' => 'Fórum bylo úspěšně upraveno.',
            'newForum' => 'Fórum bylo úspěšně vytvořeno.',
            'deleteForum' => 'Fórum bylo úspěšně smazáno.',
            'editForumPermission' => 'Opravnění fóra bylo úspěšně změněno.',

            'editCategory' => 'Kategorie byla úspěšně upravena.',
            'newCategory' => 'Kategorie byla úspěšně vytvořena.',
            'deleteCategory' => 'Kategorie byla úspěšně smazána.',
            'editCategoryPermission' => 'Opravnění kategorie bylo úspěšně změněno.',

            'editNotification' => 'Oznámení bylo úspěšně upraveno.',
            'newNotification' => 'Oznámení bylo úspěšně vytvořeno.',
            'deleteNotification' => 'Oznámení bylo úspěšně smazána.',

            'editDropdown' => 'Rozbalovací seznam byl úspěšně upraven.',
            'newDropdown' => 'Rozbalovací seznam byl úspěšně vytvořen.',

            'editButton' => 'Tlačítko bylo úspěšně upraveno.',
            'newButton' => 'Tlačítko bylo úspěšně vytvořeno.',
            'deleteButton' => 'Tlačítko bylo úspěšně smazáno.',

            'editSubButton' => 'Tlačítko bylo úspěšně upraveno.',
            'newSubButton' => 'Tlačítko bylo úspěšně vytvořeno.',
            'deleteSubButton' => 'Tlačítko bylo úspěšně smazáno.'
        ]
    ]
];