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

namespace App\Model;

/**
 * Check
 */
class Check 
{
    /**
     * Returns lenght of given string, integer or array
     *
     * @param  mixed $input
     * 
     * @return int
     */
    private function getCount( mixed $input )
    {
        return (double)match (gettype($input)) {
            'array' => count($input),
            'string' => strlen($input),
            'integer', 'double' => $input
        };
    }

    /**
     * Checks max length of given variable
     *
     * @param  int|array|string $input
     * @param  int $length Max length
     * 
     * @return bool
     */
    public function maxLength( int|array|string $input, int $length )
    {
        if ($this->getCount($input) <= $length)
        {
            return true;
        }

        return false;
    }

    /**
     * Checks min length of given variable
     *
     * @param  int|array|string $input
     * @param  int $length Min length
     * 
     * @return bool
     */
    public function minLength( int|array|string $input, int $length )
    {
        if ($this->getCount($input) >= $length)
        {
            return true;
        }

        return false;
    }

    /**
     * Checks if given string contains valid characters
     *
     * @param  string $string
     * 
     * @return bool
     */
    private function characters( string $string )
    {
        if (preg_match("/^[\p{L}0-9\_\&]+\$/u", utf8_decode($string)))
        {
            return true;
        }

        return false;
    }
    
    /**
     * Checks if given e-mail is valid
     *
     * @param  string $email The e-mail
     * 
     * @throws \App\Exception\Notice If email is not valid
     * 
     * @return bool
     */
    public function email( string $email )
    {
        if ($this->minLength($email, 4))
        {
            if ($this->maxLength($email, 254))
            {
                if (filter_var($email, FILTER_VALIDATE_EMAIL))
                {
                    return true;
                }

                throw new \App\Exception\Notice('email_wrong');
            }

            throw new \App\Exception\Notice('email_max_length');
        }

        throw new \App\Exception\Notice('email_min_length');

        return false;
    }
    
    /**
     * Checks if given user name is valid
     *
     * @param  string $userName The user name
     * 
     * @throws \App\Exception\Notice If user name is not valid
     * 
     * @return bool
     */
    public function userName( string $userName )
    {
        if ($this->minLength($userName, 5))
        {
            if ($this->maxLength($userName, 16))
            {
                if ($this->characters($userName))
                {
                    return true;
                }

                throw new \App\Exception\Notice('user_name_characters');
            }

            throw new \App\Exception\Notice('user_name_length_max');
        }

        throw new \App\Exception\Notice('user_name_length_min');

        return false;
    }
    
    /**
     * Checks if given password is valid
     *
     * @param  string $password The password
     * 
     * @throws \App\Exception\Notice If password is not valid
     * 
     * @return bool
     */
    public function password( string $password )
    {
        if ($this->minLength( $password, 6))
        {
            if ($this->maxLength( $password, 30))
            {
                if ($this->characters( $password ))
                {
                    return true;
                }

                throw new \App\Exception\Notice('user_password_characters');
            }

            throw new \App\Exception\Notice('user_password_length_max');
        }

        throw new \App\Exception\Notice('user_password_length_min');

        return false;
    }
    
    /**
     * Checks if given passwords are same
     *
     * @param  string $password
     * @param  string $passwordComapre
     * @param  string $throw Error
     * 
     * @throws \App\Exception\Notice If passwords are different
     * 
     * @return bool
     */
    public function passwordMatch( string $password, string $passwordComapre, string $throw = 'user_password_no_match' )
    {
        $hashed = null;
        if (strlen($password) === 60)
        {
            $hashed = $password;
            $clear = $passwordComapre;
        }

        if (strlen($passwordComapre) === 60)
        {
            $hashed = $passwordComapre;
            $clear = $password;
        }

        if (!empty($hashed))
        {
            if (password_verify($clear, $hashed))
            {
                return true;
            }
        }

        if ($password === $passwordComapre)
        {
            return true;
        }

        throw new \App\Exception\Notice($throw);
    }
}
