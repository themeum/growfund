<?php

namespace Growfund\DTO\Fundraiser;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\DateTimeAttribute;
use Growfund\DTO\DTO;

class FundraiserDTO extends DTO
{
    protected $casts = [
        'joined_at' => DateTimeAttribute::class,
    ];

    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $first_name;

    /**
     * @var string
     */
    public $last_name;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string|null
     */
    public $phone;

    /**
     * @var int|null
     */
    public $image;

    /**
     * @var string|null
     */
    public $joined_at;

    /**
     * @var string
     */
    public $status;

    /**
     * @var string
     */
    public $decline_reason;

    /** @var bool */
    public $is_verified;

    /**
     * @var string
     */
    public $created_by;
}
