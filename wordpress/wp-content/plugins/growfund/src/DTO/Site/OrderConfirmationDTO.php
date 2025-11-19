<?php

namespace Growfund\DTO\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;

class OrderConfirmationDTO extends DTO
{
    /** @var string */
    public $campaign_title;

    /** @var string|null */
    public $reward_title;

    /** @var string|null */
    public $fund_title;

    /** @var string */
    public $ref_number;

    /** @var string */
    public $payment_method;

    /** @var float */
    public $amount;

    /** @var string */
    public $contributor_name;

    /** @var string */
    public $contributor_email;

    /** @var string */
    public $confirmation_title;

    /** @var string */
    public $confirmation_description;
    
    /** @var bool */
    public $is_future_payment = false;
}
