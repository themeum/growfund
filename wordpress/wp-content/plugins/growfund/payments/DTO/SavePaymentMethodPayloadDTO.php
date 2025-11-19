<?php

namespace Growfund\Payments\DTO;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;
use Growfund\Payments\Supports\Currency;
use InvalidArgumentException;

class SavePaymentMethodPayloadDTO extends DTO
{
    /** @var string */
    public $description;

    /** @var string */
    public $customer_id;

    /** @var string */
    public $order_id;

    /** @var string */
    public $currency;

    /** @var string|null */
    public $redirect_url;

    /** @var string|null */
    public $success_url;

    /** @var string|null */
    public $cancel_url;

    /** @var array|null Metadata except order_id */
    public $metadata;

    public function __construct(array $data)
    {
        parent::__construct($data);

        $this->validate();
    }

    protected function validate()
    {
        if (empty($this->currency)) {
            throw new InvalidArgumentException('Currency is required.');
        }

        if (!Currency::is_valid_iso($this->currency)) {
            throw new InvalidArgumentException('Currency must be a valid 3-letter ISO code.');
        }

        if (empty($this->success_url) || empty($this->cancel_url)) {
            throw new InvalidArgumentException('Success and cancel URLs are required.');
        }
    }
}
