<?php

namespace Growfund\DTO\User;

defined( 'ABSPATH' ) || exit;

use Growfund\CastAttributes\StringAttribute;
use Growfund\DTO\DTO;

class UserInfoDTO extends DTO
{
    protected $casts = [
        'id' => StringAttribute::class,
    ];

    /** @var string */
    public $id;

    /** @var string */
    public $first_name;

    /** @var string */
    public $last_name;

    /** @var string */
    public $display_name;

    /** @var string */
    public $email;

    /** @var string */
    public $username;

    /** @var array|null */
    public $image;

    /** @var string|null */
    public $phone;
}
