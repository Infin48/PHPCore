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
    'L_PERMISSION' => [
        'L_INHERIT' => 'Zdědit oprávnění',

        'L_CATEGORY' => [
            'article' => 'Články',
            'post' => 'Příspěvky',
            'topic' => 'Témata',
            'profilepost' => 'Profilové příspěvky a komentáře',
            'image' => 'Nahrávání obrázků',
            'admin' => 'Administrátorský panel'
        ],

        'L_LIST' => [
            'article.create' => 'Vytvářet články',
            'article.edit' => 'Upravovat články',
            'article.delete' => 'Mazat články',
            'article.label' => 'Přidávat štítky k článkům',
            'article.stick' => 'Lepit články',

            'topic.move' => 'Hýbat s tématy',
            'topic.edit' => 'Upravovat témata',
            'topic.lock' => 'Zamykat témata',
            'topic.image' => 'Nahrávat titulní obrázky k tématům',
            'topic.label' => 'Přidávat štítky k tématům',
            'topic.stick' => 'Lepit témata',
            'topic.delete' => 'Mazat témata',
            'topic.create' => 'Vytvářet témata',

            'post.edit' => 'Upravovat příspěvky',
            'post.delete' => 'Mazat příspěvky',
            'post.create' => 'Vytvářet příspěvky',

            'profilepost.edit' => 'Upravit profilové příspěveky a komentáře',
            'profilepost.create' => 'Vytvářet profilové příspěvky a komentáře',
            'profilepost.delete' => 'Mazat profilové příspěvky a komentáře',

            'image.gif' => 'Nahrávat GIF obrázky',

            'admin.url' => 'Správa překladu URL adres',
            'admin.log' => 'Audit log',
            'admin.forum' => 'Správa fóra',
            'admin.group' => 'Správa skupin',
            'admin.settings' => 'Správa webových stránek',
            'admin.index' => 'Přístup do administrátorského panelu',
            'admin.user' => 'Správa uživatelů',
            'admin.role' => 'Správa uživatelských rolí',
            'admin.menu' => 'Správa navigačního menu',
            'admin.notification' => 'Správa oznámení',
            'admin.page' => 'Správa vlastních stránek',
            'admin.template' => 'Správa vzhledů',
            'admin.label' => 'Správa štítků',
            'admin.sidebar' => 'Správa postranního panel',
            'admin.plugin' => 'Správa pluginů',
            '*' => 'Veškeré oprávnění',
        ],

        'L_DESC' => [

            'article.create' => 'Může vytvářet články.',
            'article.edit' => 'Může upravovat své články, a pokud je zapnutý režim <strong>blog</strong> je možné v nastavení v administrátorském panelu povolit i upravování článků od cizích uživatelů.',
            'article.delete' => 'Může mazat všechny články.',
            'article.label' => 'Může přidávat a upravovat štítky u článků.',
            'article.stick' => 'Může lepit články.',

            'post.edit' => 'Může upravovat vlastní příspěvky ve fórech, kde má oprávnění vytvářet příspěvky.',
            'post.create' => 'Může vytvářet příspěvky ve fórech, kde má oprávnění vytvářet příspěvky.',
            'post.delete' => 'Může mazat veškeré příspěvky bez omezení pouze ve fórech, kde má oprávnění vytvářet příspěvky.',
            
            'topic.edit' => 'Může upravovat vlastní témata ve fórech, kde má oprávnění vytvářet témata.',
            'topic.create' => 'Může vytvářet témata ve fórech, kde má oprávnění vytvářet témata.',
            'topic.delete' => 'Může mazat veškerá témata bez omezení pouze ve fórech, kde má oprávnění vytvářet témata.',
            'topic.stick' => 'Může lepit témata ve fórech, kde má oprávnění vytvářet témata.',
            'topic.lock' => 'Může zamykat témata ve fórech, kde má oprávnění vytvářet témata.',
            'topic.label' => 'Může přidělovat štítky tématům ve fórech, kde má oprávnění vytvářet témata.',
            'topic.move' => 'Může přesouvat témata z jednoho fóra do jiného, kde má oprávnění vytvářet témata.',
            'topic.image' => 'Může nahrávat titulní obrázky ke svým tématům.',

            'profilepost.create' => 'Může vytvářet profilové příspěvky a komentáře.',
            'profilepost.edit' => 'Může upravovat vlastní profilové příspěvky a komentáře.',
            'profilepost.delete' => 'Může mazat veškeré profilové příspěvky a komentáře bez omezení.',

            'image.gif' => 'Může nahrávat GIF obrázky jako profilový obrazek, záhlaví profilu nebo titulní obrázek tématu.<br><strong>Pozor!</strong> Je nutné povolit nahrávání GIF obrázků v administrátorském panelu v sekci nastavení.',

            'admin.log' => 'Může vidět audit log.',
            'admin.url' => 'Můžet vytvářet a mazat překlady URL adres.',
            'admin.settings' => 'Může měnit nastavení webových stránek, registrační nastavení, nastavení e-mailů a měnit výchozí jazyk',
            'admin.notification' => 'Může vytvářet, mazat, upravovat a měnit uspořádání oznámení',
            'admin.menu' => 'Může vytvářet, mazat, upravovat a měnit uspořádání tlačítek v navigačním menu.',
            'admin.page' => 'Může vytvářet, mazat a upravovat vlastní stránky.',
            'admin.label' => 'Může vytvářet, mazat, upravovat a měnit uspořádání štítků. Toto oprávnění se nevztahuje na možnost přidávat štítky k tématům.',
            'admin.user' => 'Může měnit uživatelovy registrační údaje. Mazat uživatele a jejich profilové obrázky, záhlaví a podpisy. Přesunout uživatele do jedné ze skupin s nižším postavením, než je skupina přihlášeného uživatele.',
            'admin.role' => 'Může vytvářet, upravovat, mazat a měnit uspořádání rolí.',
            'admin.plugin' => 'Může instalovat, mazat, odinstalovávat a spravovat všechny dostupné pluginy.',
            'admin.template' => 'Může měnit výchozí vzhledy, mazat je a zobrazovat náhledy.',
            'admin.sidebar' => 'Může přidávat, mazat a měnit uspořádání prvků na postranním panelu.',
            'admin.index' => 'Může vidět titulní stránku, statistiky a stav.',
            'admin.group' => 'Může vytvářet, mazat, upravovat, měnit oprávnění a uspořádání skupin s nižším postavením, než je skupina přihlášeného uživatele. V případě, že uživatel má i oprávnění spravovat uživatelé, může měnit i skupiny jednotlivých uživatelů.',
            'admin.forum' => 'Může vytvářet, mazat, upravovat, měnit uspořádání a oprávnění jednotlivých fór a kategorií. Uživatel získá oprávnění vidět nahlášený a smazaný obsah a dále s ním nakládat v administrátorkám panelu.',
            '*' => 'Uživatel bude mít všechna zmíněná oprávnění.'
        ]
    ]
];