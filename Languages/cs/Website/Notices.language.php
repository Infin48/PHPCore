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
            
            'to' => 'Zadejte platné účastníky konverzace.',
            'to_length_max' => 'Maximální počet účastníku včetně odesílatele je 10.',
            
            'report_reason_text' => 'Zadejte důvod nahlášení.',
            'report_reason_text_length_max' => 'Hlášení je příliš dlouhé.',
            
            'conversation_name' => 'Zadejte název konverzace.',
            'conversation_name_length_max' => 'Název konverzace je příliš dlouhý.',
            
            'conversation_text' => 'Zadejte obsah konverzace.',
            'conversation_text_length_max' => 'Obsah konverzace je příliš dlouhý.',

            'conversation_user_exist' => 'Zadaný uživatel je již účastníkem této konverzace.',
            'conversation_user_myself' => 'Již jste účastníkem této konverzace.',

            'text' => 'Zadejte zprávu.',
            'text_length_max' => 'Zpráva je příliš dlouhá.',
            
            'topic_text' => 'Zadejte obsah tématu.',
            'topic_text_length_max' => 'Obsah tématu je příliš dlouhý.',

            
            'topic_label_length_max' => 'Téma může obsahovat maximálně 5 štítků.',
            
            'article_name' => 'Zadejte název článku.',
            'article_name_length_max' => 'Název článku je příliš dlouhý.',
            
            'article_text' => 'Zadejte obsah článku.',
            'article_label_length_max' => 'Článek může obsahovat maximálně 5 štítků.',
            'article_text_length_max' => 'Obsah článku je příliš dlouhý.',

            'topic_name' => 'Zadejte název tématu.',
            'topic_name_length_max' => 'Název tématu je příliš dlouhý.',

            'user_text_length_max' => 'Text je příliš dlouhý.',
            
            'user_location_length_max' => 'Bydliště je příliš dlouhé.',

            'user_name' => 'Zadejte platné uživatelské jméno.',
            'user_name_exist' => 'Uživatel s tímto uživatelským jménem již existuje.',
            'user_name_characters' => 'Uživatelské jméno obsahuje nepovolené znaky.',
            'user_name_length_max' => 'Uživatelské jméno je příliš dlouhé.',
            'user_name_length_min' => 'Uživatelské jméno je příliš krátké.',
            
            'user_password' => 'Zadejte heslo.',
            'user_password_wrong' => 'Zadané heslo je nesprávné.',
            'user_password_confirm' => 'Potvrďte své heslo.',
            'user_password_no_match' => 'Zadaná hesla se neshodují.',
            'user_password_characters' => 'Heslo obsahuje nepovolené znaky.',
            'user_password_length_max' => 'Heslo je příliš dlouhé.',
            'user_password_length_min' => 'Heslo je příliš krátké.',
            
            'user_password_new' => 'Zadejte nové heslo.',
            'user_password_new_confirm' => 'Potvrďte nové heslo.',

            'user_email' => 'Zadejte e-mail.',
            'user_email_wrong' => 'Zadaný e-mail není platný.',
            'user_email_does_not_exist' => 'Uživatel s tímto e-mailem neexistuje.',
            'user_email_exist' => 'Uživatel s tímto e-mailem již existuje.',
            'user_email_length_max' => 'E-mail je příliš dlouhý.',
            'user_email_length_min' => 'E-mail je příliš krátký.',

            'user_signature_length_max' => 'Podpis je příliš dlouhý.',

            'file_size' => 'Nahraný obrázek je příliš velký.',
            'file_format' => 'Formát nahraného obrázku není podporován.',
            'image_gif_size' => 'Nahraný gif musí mít velikost {width}x{height}.',
            'image_svg_size' => 'Nahraný vektorový obrázek musí mít velikost {width}x{height}.',

            'recaptcha' => 'Autorizace byla neúspěšná.',

            'agree' => 'Musíte souhlasit s podmínkami.',

            'forgot_already_sent' => 'Odkaz pro změnu hesla vám byl již odeslán. Pro opětovné odeslání to zkuste za chvíli.',

            'email_verify_already_sent' => 'Odkaz pro aktivování e-mailu byl již odeslán. Pro opětovné odeslání to zkuste za chvíli.',
            'account_verify_already_sent' => 'Odkaz pro aktivování účtu vám byl již odeslán. Pro opětovné odeslání to zkuste za chvíli.',
            'account_not_activated_send' => 'Váš účet není aktivován. Na e-mail jsme vám poslali odkaz k aktivaci účtu.',
            'account_not_activated' => 'Tento účet není aktivován.',

            'login_incorrect' => 'Zadali jste špatné uživatelské jméno nebo heslo.',
        ],

        'L_SUCCESS' => [
            'resetForgottenPassword' => 'Heslo bylo úspěšně změněno.',
            'sendLinkToResetPassword' => 'Odkaz pro změnu hesla jsme vám poslali na váš e-mail.',

            'reportTopic' => 'Hlášení bylo úspěšně odesláno.',
            'stickTopic' => 'Téma bylo úspěšně přilepeno.',
            'unstickTopic' => 'Téma bylo úspěšně odlepeno.',
            'deleteTopic' => 'Téma bylo úspěšně smazáno.',

            'stickArticle' => 'Článek byl úspěšně přilepen.',
            'unstickArticle' => 'Článek byl úspěšně odlepen.',
            'deleteArticle' => 'Článek byl úspěšně smazán.',

            'leaveConversation' => 'Konverzace byla úspěšně opuštěna.',

            'newMessage' => 'Zpráva byla úspěšně vytvořena.',
            'editMessage' => 'Zpráva byla úspěšně upravena.',
            
            'newPost' => 'Příspěvek byl úspěšně vytvořen.',
            'editPost' => 'Příspěvek byl úspěšně upraven.',
            'deletePost' => 'Příspěvek byl úspěšně smazán.',
            'reportPost' => 'Hlášení bylo úspěšně odesláno.',

            'newProfilePost' => 'Profilový příspěvek byl úspěšně vytvořen.',
            'editProfilePost' => 'Profilový příspěvek byl úspěšně upraven.',
            'reportProfilePost' => 'Hlášení bylo úspěšně odesláno.',
            'deleteProfilePost' => 'Profilový příspěvek byl úspěšně smazán.',

            'newProfilePostComment' => 'Profilový komentář byl úspěšně vytvořen.',
            'editProfilePostComment' => 'Profilový komentář byl úspěšně upraven.',
            'reportProfilePostComment' => 'Hlášení bylo úspěšně odesláno.',
            'deleteProfilePostComment' => 'Profilový komentář byl úspěšně smazán.',

            'loginUser' => 'Byli jste úspěšně přihlášeni.',
            'logoutUser' => 'Byli jste úspěšně odhlášeni.',
            'registerUser' => 'Váš účet byl úspěšně vytvořen.<br>Pro aktivaci účtu klikněte na odkaz, který jsme vám poslali na e-mail.',
            'registerUserWithoutEmailVerification' => 'Váš účet byl úspěšně vytvořen.',

            'editAbout' => 'Profilové nastavení bylo úspěšně aktualizováno.',
            'editAccount' => 'Uživatelské nastavení bylo úspěšně změněno. Pokud jste si změnili e-mail, tak si jej potvrďte. Pokud tak neuděláte e-mail nebude změněn.',
            'editSignature' => 'Váš podpis byl změněn.',
            'editSettings' => 'Profilové nastavení bylo úspěšně aktualizováno.',

            'verifyEmail' => 'E-mail by úspěšně ověřen.',
            'verifyAccount' => 'Váš účet byl úspěšně aktivován.'
        ]
    ]
];