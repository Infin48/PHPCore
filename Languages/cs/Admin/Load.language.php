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

$language = [];

foreach (glob(ROOT . '/Languages/cs/Admin/*.php') as $path) {
    if (basename($path) != 'Load.language.php') {
        require $path;
    }
}

$language = array_merge($language, [
    
    'L_BY' => 'od',

    'L_POSTS' => 'Příspěvků',
    'L_TOPICS' => 'Témat',
    'L_USERS' => 'Uživatelů',

    'L_OPTIMIZE_TABLES' => 'Optimalizovat tabulky',
    'L_SYNCHRONISE_SCRIPTS' => 'Synchronizovat skripty',
    'L_SYNCHRONISE_STYLES' => 'Synchronizovat styly',
    'L_ICON' => 'Ikona',
    'L_ICON_LIST' => 'Seznam ikon',
    'L_ICON_LIST_DESC' => 'Kliknutím na tlačítko se vám v novém okně otevře stránka FontAwesome.',
    'L_ICON_NAME' => 'Název ikony',
    'L_ICON_STYLE' => 'Styl ikony',
    'L_AUTORIZATION' => 'Oprávnění',
    'L_AUTHOR' => 'Autor',
    'L_VERSION' => 'Verze',

    'L_MANAGE' => 'Spravovat',
    'L_INSTALL' => 'Nainstalovat',
    'L_UNINSTALL' => 'Odinstalovat',
    
    'L_FAS' => 'fas',
    'L_FAR' => 'far',
    'L_FAB' => 'fab',

    'L_INFO' => 'Informace',
    'L_EDIT' => 'Upravit',

    'L_HOST' => 'Server',
    'L_PORT' => 'Port',

    'L_DELETED_USER' => 'Smazaný uživatel',

    'L_OPTIONS' => 'Možnosti',

    'L_UPDATE_ALERT' => 'Aktualizace',

    'L_TODAY' => 'Dnes',
    'L_AT' => 'v',
    'L_TOMORROW' => 'Včera',
    'L_BACK' => 'Zpět',

    'L_REGISTERED' => 'Registrován',

    'L_NO' => 'Ne',
    'L_YES' => 'Ano',

    'L_LINK' => 'Odkaz',

    'L_DETAILS' => 'Podrobnosti',

    'L_REMOVE' => 'Smazat',
    'L_INTERNAL_ERROR' => 'Byla nalezena interní chyba',

    'L_CONTENT_TYPE' => 'Typ obsahu',
    'L_CONTENT_LIST' => [
        'Topic' => 'Téma',
        'Post' => 'Příspěvek',
        'ProfilePost' => 'Profilový příspěvek',
        'ProfilePostComment' => 'Profilový komentář'
    ],

    'L_RECORD' => 'Záznam',
    'L_RECORD_ID' => 'ID záznamu',

    'L_ONLINE' => 'Online',

    'L_DELETE' => 'Smazat',
    'L_SHOW' => 'Zobrazit',

    'L_SUBMIT' => 'Odeslat',

    'L_TOPIC_NAME' => 'Název tématu',

    'L_KEYWORDS' => 'Klíčová slova',
    'L_NAME' => 'Název',
    'L_TEXT' => 'Text',
    'L_EMAIL' => 'E-mail',
    'L_USERNAME' => 'Uživatelské jméno',
    'L_DESCRIPTION' => 'Popis',

    'L_PASSWORD' => 'Heslo',

    'L_PASSWORD_NEW' => 'Nové heslo', 
    'L_PASSWORD_DESC' => 'Heslo musí obsahovat minimálně 6 znaků.', 

    'L_TOPIC_ID' => 'ID Tématu',
    'L_TOPIC_ID_DESC' => 'ID pod kterým je téma uchováno v databázi.',
    'L_POST_ID' => 'ID Příspěvku',
    'L_POST_ID_DESC' => 'ID pod kterým je příspěvek uchován v databázi.',
    'L_PROFILE_POST_ID' => 'ID Profilového příspěvku',
    'L_PROFILE_POST_ID_DESC' => 'ID pod kterým je profilový příspěvek uchován v databázi.',
    'L_PROFILE_POST_COMMENT_ID' => 'ID Profilového komentáře',
    'L_PROFILE_POST_COMMENT_ID_DESC' => 'ID pod kterým je profilový komentář uchován v databázi.',

    'L_POST_TOPIC_NAME' => 'Téma ve kterém se příspěvek nachází',
    'L_TOPIC_NAME' => 'Název tématu',

    'L_ALERT' => 'Bezpečnostní informace',

    'L_MOVE_UP' => 'Posunout nahoru',
    'L_MOVE_DOWN' => 'Posunout dolů',
    'L_MAIN_ADMIN' => 'Hlavní správce',
    'L_EXTERNAL_LINK' => 'Externí odkaz',

    'L_KEY' => 'Klíč',
    'L_SHOW_MORE' => 'Zobrazit více',
    'L_CREATED_BY' => 'Vytvořil',

    'L_BUTTON' => [

        'L_DOCUMENTATION' => 'Dokumentace',

        'L_SYNCHRONISE' => 'Synchronizovat',
        'L_DOWNLOAD' => 'Stáhnout',
        'L_OPTIMIZE' => 'Optimalizovat',

        'L_REPORT_CLOSE' => 'Uzavřít hlášení',
        'L_UPDATE_SEARCH' => 'Vyhledat aktualizace',

        'L_EDIT' => 'Upravit',
        'L_DELETE' => 'Smazat',
        'L_TRANSFER_OWNERSHIP' => 'Převést oprávnění',
        'L_ACTIVATE' => 'Aktivovat',
        'L_PREVIEW' => 'Náhled',
        'L_SHOW' => 'Zobrazit',
        'L_ICON_LIST' => 'Seznam ikon',
        'L_DETAILS' => 'Podrobnosti',
        'L_SUBMIT' => 'Odeslat',
        'L_PERMISSION' => 'Oprávnění',
        'L_INSTALL' => 'Nainstalovat',
        'L_UNINSTALL' => 'Odinstalovat',
        'L_MANAGE' => 'Spravovat',

        'L_RETURN' => 'Vrátit zpět',

        'L_SEND' => 'Poslat',

        'L_NEW_DROPDOWN' => 'Nový rozbalovací seznam',
        'L_NEW_BUTTON' => 'Nové tlačítko',
        'L_NEW_NOTIFICATION' => 'Nové upozornění',
        'L_NEW_FORUM' => 'Nové fórum',
        'L_NEW_CATEGORY' => 'Nová kategorie',

        'L_ADD_BUTTON' => 'Přidat tlačítko'
    ]
]);
