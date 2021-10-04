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

foreach (glob(ROOT . '/Languages/cs/*.php') as $path) {
    if (basename($path) != 'Load.language.php') {
        require $path;
    }
}

$language = array_merge($language, [
    'L_DOMAIN' => $_SERVER['SERVER_NAME'],
    'L_ERROR' => 'Chyba 404',
    'L_ERROR_DESC' => 'Omlouváme se, ale požadovaná stránka nebyla nalezena nebo nemáte patřičná oprávnění pro přístup na tuto stránku',
    'L_TITLE_PAGE' => 'Titulní stránka',
    'L_BY' => 'od',

    'L_PROFILE_IMAGE' => 'Profilový obrázek',

    'L_FORUM' => 'Fórum',

    'L_TERMS' => 'Smlůvní podmínky',

    'L_DETAILS' => 'Podrobnosti',
    'L_REASON' => 'Důvod',

    'L_COOKIE_BUTTON' => 'Rozumím',

    'L_RE' => 'Re',

    'L_DELETED_USER' => 'Smazaný uživatel',

    'L_CONTENT_DELETED' => 'Tento obsah byl smazán',

    'L_BACK' => 'Zpět',

    'L_EDITED' => 'Upraveno',
    'L_CHANGE' => 'Změnit',

    'L_YOU' => 'vy',

    'L_FOUND_ERROR' => 'Vyskytla se chyba',

    'L_ONLINE' => 'Online',

    'L_INTERNAL_ERROR' => 'Byla nalezena interní chyba',

    'L_LINK' => 'Odkaz',

    'L_MESSAGES' => 'Zpráv',
    'L_STATISTICS' => 'Statistiky',
    'L_REGISTERED' => 'Registrován',

    'L_USER_ONLINE_TOTAL' => 'Celkem online uživatelů',

    'L_PAGE' => 'Stránka',
    'L_PAGE_OF' => 'z',

    'L_MENU' => 'Menu',
    'L_CREATED' => 'Vytvořeno',

    'L_WROTE' => 'napsal',

    'L_AT' => 'v',
    'L_TODAY' => 'Dnes',
    'L_TOMORROW' => 'Včera',

    'L_PREVIEW_CLOSE' => 'Zrušit náhled',
    'L_PREVIEW_TEMPLATE' => 'Šablona',

    'L_BUTTON' => [
        'L_CANCEL' => 'Zrušit',
        'L_EDIT' => 'Upravit',
        'L_DELETE' => 'Smazat',
        'L_REPORT' => 'Nahlásit',
        'L_QUOTE' => 'Citovat',
        'L_LIKE' => 'To se mi líbí',
        'L_UNLIKE' => 'Už se mi to nelíbí',
        'L_POST' => 'Přidat příspěvek',
        'L_TOOLS' => 'Nástroje',
        'L_MOVE' => 'Přesunout do',
        'L_LOCK' => 'Zamknout',
        'L_UNLOCK' => 'Odemknout',
        'L_STICK' => 'Přilepit',
        'L_UNSTICK' => 'Odlepit',
        'L_CONFIRM' => 'Potvrdit',
        'L_DETAILS' => 'Podrobnosti',
        'L_SUBMIT' => 'Odeslat',

        'L_REGISTER' => 'Registrovat se',
        'L_LOGIN' => 'Přihlásit se',

        'L_SAVE' => 'Uložit',
        'L_ADD' => 'Přidat',

        'L_HIDE' => 'Skrýt',
        'L_SHOW' => 'Zobrazit',

        'L_NEW_CONVERSATION' => 'Nová konverzace',
        'L_SEND_MESSAGE' => 'Poslat zprávu',
        'L_OPTIONS' => 'Možnosti',
        'L_LEAVE_CONVERSATION' => 'Opustit konverzaci',
        'L_MARK_AS_UNREAD' => 'Označit za nepřečtené',
        'L_NEW_TOPIC' => 'Nové téma'
    ]
]);