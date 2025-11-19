<?php

namespace Growfund\Payments\DTO;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;
use Growfund\Payments\Constants\PaymentGatewayType;

class PaymentGatewayDTO extends DTO
{
    /** @var string */
    public $name = '';

    /** @var string */
    public $download_url = '';

    /** @var string */
    public $type = PaymentGatewayType::ONLINE;

    /** @var bool */
    public $supports_future_payments = false;

    /** @var string */
    public $frontend_script = '';

    /** @var string */
    public $form_file = '';

    /** @var string */
    public $class = '';

    /** @var array */
    public $config;

    /** @var bool */
    public $is_installed = false;

    /** @var bool */
    public $is_enabled = false;

    /** @var array */
    public $fields = [];
}
