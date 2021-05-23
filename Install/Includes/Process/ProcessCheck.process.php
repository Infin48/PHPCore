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

namespace Process;

/**
 * ProcessCheck
 */
class ProcessCheck
{    
    /**
     * Returns lenght of given string, integer or array
     *
     * @param  mixed $var
     * 
     * @return int
     */
    private function getCount( mixed $var )
    {
        return (double)match (gettype($var)) {
            'array' => count($var),
            'string' => strlen($var),
            'integer', 'double' => $var
        };
    }

    /**
     * Checks max length of given variable
     *
     * @param  int|array|string $var
     * @param  string $key
     * @param  int $length Max length
     * 
     * @throws \Exception\Notice If length of variable is greater than the limit
     * 
     * @return bool
     */
    public function maxLength( int|array|string $var, string $key, int $length )
    {
        if ($this->getCount($var) <= $length) {
            return true;
        }

        throw new \Exception\Notice($key . '_length_max');
    }

    /**
     * Checks min length of given variable
     *
     * @param  int|array|string $var
     * @param  string $key
     * @param  int $length Min length
     * 
     * @throws \Exception\Notice If length of variable is less than the limit
     * 
     * @return bool
     */
    public function minLength( int|array|string $var, string $key, int $length )
    {
        if ($this->getCount($var) >= $length) {
            return true;
        }

        throw new \Exception\Notice($key . '_length_min');
    }

    /**
     * Checks if given string contains valid characters
     *
     * @param  string $var
     * @param  string $key
     * 
     * @throws \Exception\Notice If given string coontains illegal characters
     * 
     * @return bool
     */
    private function characters( string $var, string $key )
    {
        if (preg_match("/^[\p{L}0-9\_\&]+\$/u", utf8_decode($var))) {
            return true;
        }
        
        throw new \Exception\Notice($key . '_characters');
        return false;
    }
    
    /**
     * Checks if given e-mail is valid
     *
     * @param  string $email The e-mail
     * 
     * @throws \Exception\Notice If given e-mail is not valid
     * 
     * @return bool
     */
    public function email( string $email )
    {
        if ($this->minLength(var: $email, key: 'email', length: 4)) {
            if ($this->maxLength(var: $email, key: 'email', length: 254)) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return true;
                }
                throw new \Exception\Notice('user_email_wrong');
            }
        }
        return false;
    }
    
    /**
     * Checks if given username is valid
     *
     * @param  string $userName The username
     * 
     * @throws \Exception\Notice If given username is not valid
     * 
     * @return bool
     */
    public function userName( string $userName )
    {
        if ($this->minLength(var: $userName, key: 'user_name', length: 5)) {
            if ($this->maxLength(var: $userName, key: 'user_name', length: 16)) {
                if ($this->characters(var: $userName, key: 'user_name')) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Checks if given password is valid
     *
     * @param  string $password The password
     * 
     * @throws \Exception\Notice If given password is not valid
     * 
     * @return bool
     */
    public function password( string $password )
    {
        if ($this->minLength(var: $password, key: 'user_password', length: 6)) {
            if ($this->maxLength(var: $password, key: 'user_password', length: 40)) {
                if ($this->characters(var: $password, key: 'user_password')) {
                    return true;
                }
            }
        }
        return false;
    }
}