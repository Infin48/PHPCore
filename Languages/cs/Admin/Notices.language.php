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

$language['L_NOTICE'] = [

    'L_FAILURE_MESSAGE' => 'Vyskytla se chyba!',

    'L_FAILURE' => [

        'user_name' => 'Zadejte uživatelské jméno.',
        'user_name_exist' => 'Uživatel s tímto uživatelským jménem již existuje.',
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

        'button_name' => 'Zadejte název tlačítka.',
        'button_sub_name' => 'Zadejte název tlačítka.',

        'category_name' => 'Zadejte název kategorie.',
        'category_description' => 'Zadejte popis kategorie.',

        'forum_name' => 'Zadejte název fóra.',
        'forum_description' => 'Zadejte popis fóra.',

        'label_name' => 'Zadejte název štítku.',
        'label_color' => 'Zadejte barvu štítku.',

        'group_name' => 'Zadejte název skupiny.',
        'group_color' => 'Zadejte barvu skupiny.',
        
        'email' => 'Zadejte e-mail.',
        'email_prefix' => 'Zadejte prefix emailu.',

        'page_name' => 'Zadejte název stránky.',

        'file_size' => 'Nahraný soubor je příliš velký.',
        'file_format' => 'Formát nahraného souboru není podporován.',

        'site_name' => 'Zadejte název webových stránek.',
        'site_description' => 'Zadejte popis webových stránek.',

        'site_keywords' => 'Zadejte klíčová slova.',

        'settings_url_from' => 'Zadejte URL adresu.',
        'settings_url_to' => 'Zadejte překlad URL adresy.',
        'settings_url_start_with_slash' => 'URL adresa i její překlad musejí začínat a končit lomítkem.',

        'image_max_size' => 'Zadejte maximální velikost nahrávání obrázků.',

        'button_link' => 'Zadejte platný odkaz.',
        'button_sub_link' => 'Zadejte platný odkaz.',
        
        '/Admin/User/Search' => 'Uživatel s tímto uživatelským jménem neexistuje.'
    ],

    'L_SUCCESS' => [

        '/Admin/Optimize' => 'Tabulky byly úspěšně optimalizovány.',

        '/Admin/Plugin/Setup' => 'Plugin byl úspěšně upraven.',
        '/Admin/Plugin/Delete' => 'Plugin byl úspěšně smazán',
        '/Admin/Plugin/Install' => 'Plugin byl úspěšně nainstalován.',
        '/Admin/Plugin/Uninstall' => 'Plugin byl úspěšně odinstalován.',
        
        '/Admin/Deleted/Post/Back' => 'Příspěvek byl úspěšně vracen zpět do fóra.',
        '/Admin/Deleted/Post/Delete' => 'Příspěvek byl úspěšně smazán.',

        '/Admin/Deleted/Topic/Back' => 'Téma bylo úspěšně vraceno zpět do fóra.',
        '/Admin/Deleted/Topic/Delete' => 'Téma bylo úspěšně smazáno.',

        '/Admin/Deleted/ProfilePost/Back' => 'Profilový příspěvek byl úspěšně vracen zpět na profil.',
        '/Admin/Deleted/ProfilePost/Delete' => 'Profilový příspěvek byl úspěšně smazán.',

        '/Admin/Deleted/ProfilePostComment/Back' => 'Profilový komentář byl úspěšně vracen zpět pod profilový příspěvek.',
        '/Admin/Deleted/ProfilePostComment/Delete' => 'Profilový komentář byl úspěšně smazán.',

        '/Admin/Report/Close' => 'Hlášení bylo úspěšně uzavřeno.',

        '/Admin/Settings/Index' => 'Nastavení webových stránek bylo úspěšně změněno.',
        '/Admin/Settings/Other' => 'Nastavení webových stránek bylo úspěšně změněno.',
        '/Admin/Settings/Registration' => 'Nastavení registrace bylo úspěšně změněno.',
        '/Admin/Settings/Email' => 'Nastavení e-mailů bylo úspěšně změněno.',
        '/Admin/Settings/EmailSend' => 'Testovací e-mail byl úspěšně odeslán.',
        '/Admin/Settings/Language/Activate' => 'Výchozí jazyk byl úspěšně změněn.',
        '/Admin/Settings/Language/Delete' => 'Jazyk byl úspěšně smazán.',

        '/Admin/Settings/URL/Create' => 'Překlad URL adresy byl úspěšně vytvořen.',
        '/Admin/Settings/URL/Delete' => 'Překlad URL adresy byl úspěšně smazán.',

        '/Admin/Synchronize/Scripts' => 'Skripty byly úspěšně synchronizovány.',
        '/Admin/Synchronize/Styles' => 'Styly byly úspěšně synchronizovány.',

        '/Admin/User/Edit' => 'Nastavení uživatele bylo úspěšně změněno.',
        '/Admin/User/Delete' => 'Uživatel byl úspěšně smazán.',
        '/Admin/User/Promote' => 'Oprávnění bylo úspěšně převedeno.',
        '/Admin/User/Activate' => 'Učet byl úspěšně aktivován.',

        '/Admin/Page/Delete' => 'Stránka byla úspěšně smazána.',
        '/Admin/Page/Edit' => 'Stránka byla úspěšně upravena.',

        '/Admin/Label/Create' => 'Štítek byl úspěšně vytvořen.',
        '/Admin/Label/Edit' => 'Štítek byl úspěšně upraven.',
        '/Admin/Label/Delete' => 'Štítek byl úspěšně smazán.',
        
        '/Admin/Group/Edit' => 'Skupina byla úspěšně upravena.',
        '/Admin/Group/Create' => 'Skupina byla úspěšně vytvořena.',
        '/Admin/Group/Delete' => 'Skupina byla úspěšně smazána.',
        '/Admin/Group/Permission' => 'Opravnění skupiny bylo úspěšně změněno.',

        '/Admin/Template/Activate' => 'Výchozí šablona byla úspěšně změněna.',
        '/Admin/Template/Delete' => 'Šablona byla úspěšně smazána.',

        '/Admin/Forum/Edit' => 'Fórum bylo úspěšně upraveno.',
        '/Admin/Forum/Create' => 'Fórum bylo úspěšně vytvořeno.',
        '/Admin/Forum/Delete' => 'Fórum bylo úspěšně smazáno.',
        '/Admin/Forum/Permission' => 'Opravnění fóra bylo úspěšně změněno.',

        '/Admin/Category/Edit' => 'Kategorie byla úspěšně upravena.',
        '/Admin/Category/Create' => 'Kategorie byla úspěšně vytvořena.',
        '/Admin/Category/Delete' => 'Kategorie bylo úspěšně smazána.',
        '/Admin/Category/Permission' => 'Opravnění kategorie bylo úspěšně změněno.',

        '/Admin/Notification/Edit' => 'Fórum bylo úspěšně upraveno.',
        '/Admin/Notification/Create' => 'Oznámení bylo úspěšně vytvořeno.',
        '/Admin/Notification/Delete' => 'Oznámení bylo úspěšně smazána.',

        '/Admin/Menu/Dropdown/Edit' => 'Rozbalovací seznam byl úspěšně upraven.',
        '/Admin/Menu/Dropdown/Create' => 'Rozbalovací seznam byl úspěšně vytvořen.',

        '/Admin/Menu/Button/Edit' => 'Tlačítko bylo úspěšně upraveno.',
        '/Admin/Menu/Button/Create' => 'Tlačítko bylo úspěšně vytvořeno.',
        '/Admin/Menu/Button/Delete' => 'Tlačítko bylo úspěšně smazáno.',

        '/Admin/Menu/ButtonSub/Edit' => 'Tlačítko bylo úspěšně upraveno.',
        '/Admin/Menu/ButtonSub/Create' => 'Tlačítko bylo úspěšně vytvořeno.',
        '/Admin/Menu/ButtonSub/Delete' => 'Tlačítko bylo úspěšně smazáno.'
    ]
];