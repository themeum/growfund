<?php

namespace Growfund\Payments\DTO;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;
use InvalidArgumentException;

class CustomerDTO extends DTO
{
    /** @var int */
    public $user_id;

    /** @var string */
    public $name;

    /** @var string|null */
    public $email;

    public function __construct(array $data)
    {
        parent::__construct($data);

        $this->validate();
    }

    protected function validate()
    {
        if (empty($this->user_id)) {
            throw new InvalidArgumentException('User ID is required.');
        }

        if (empty($this->email)) {
            throw new InvalidArgumentException('Email is required.');
        }
    }
}
