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

namespace Model\Build;

/**
 * BuildDate
 */
class BuildDate extends Build
{
    /**
     * Converts date to time
     *
     * @param  string $date Date
     * 
     * @return int
     */
    private function toTime( string $date )
    {
        if (ctype_digit($date)) {
            if (strlen($date) === 13) {
                return $date / 1000;
            }
            return $date;
        }

        return strtotime($date);
    }

    /**
     * If given date is recently - returns selected day
     *
     * @param  int $time The time
     * 
     * @return string|null
     */
    private function getRecently( int $time )
    {
        if (date('d/m/Y', $time) == date('d/m/Y')) {
            return '<time>' . $this->language->get('L_TODAY') . ' ' . $this->language->get('L_AT') . ' ' . date('G:i', $time) . '</time>';
        }

        if (date('d/m/Y', $time) == date('d/m/Y', time() - (24 * 60 * 60))) {
            return '<time>' . $this->language->get('L_TOMORROW') . ' ' . $this->language->get('L_AT') . ' ' . date('G:i', $time) . '</time>';
        }

        return null;
    }

    /**
     * Builds short version of date
     *
     * @param  string $date The date
     * 
     * @return string
     */
    public function short( string $date )
    {
        $time = $this->toTime($date);

        if ($recently = $this->getRecently($time)) {
            return $recently;
        }

        return '<time>' . mb_strtoupper(mb_substr(strftime('%B %e. %Y', $time), 0, 1)) . mb_substr(strftime('%B %e, %Y', $time), 1) . '</time>';
    }

    /**
     * Returns long version of date
     *
     * @param  string $date The date
     * 
     * @return string
     */
    public function long( string $date )
    {
        $time = $this->toTime($date);

        if ($recently = $this->getRecently($time)) {
            return $recently;
        }

        return '<time>' . mb_strtoupper(mb_substr(strftime('%B %e. %Y, %H:%M', $time), 0, 1)) . mb_substr(strftime('%B %e, %Y ' . $this->language->get('L_AT') . ' %H:%M', $time), 1) . '</time>';
    }
}