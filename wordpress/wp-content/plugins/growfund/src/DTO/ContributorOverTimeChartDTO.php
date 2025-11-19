<?php

namespace Growfund\DTO;

defined( 'ABSPATH' ) || exit;

class ContributorOverTimeChartDTO extends DTO
{
    /** @var string */
    public $date;

    /** @var int */
    public $first_time_total;
    
    /** @var int */
    public $recurring_total;
}
