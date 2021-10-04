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

namespace Process\ProfilePostComment;

/**
 * Report
 */
class Report extends \Process\ProcessExtend
{    
    /**
     * @var array $require Required data
     */
    public array $require = [
        'form' => [
            'report_reason_text' => [
                'type' => 'text',
                'required' => true,
                'length_max' => 1000,
            ]
        ],
        'data' => [
            'report_type_id'
        ],
        'block' => [
            'user_id',
            'report_id'
        ]
    ];

    /**
     * @var array $options Process options
     */
    public array $options = [
        'success' => SUCCESS_RETURN,
        'verify' => [
            'block' => '\Block\ProfilePostComment',
            'method' => 'get',
            'selector' => 'report_type_id'
        ]
    ];

    /**
     * Body of process
     *
     * @return void
     */
    public function process()
    {
        $this->require(
            process: 'Report/Send',
            data: [
                'user_id' => $this->data->get('user_id'),
                'report_id' => $this->data->get('report_id'),
                'report_type' => 'ProfilePostComment',
                'report_type_id' => $this->data->get('report_type_id'),
                'report_reason_text' => $this->data->get('report_reason_text')
            ]
        );
    }
}