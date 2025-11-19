<?php

namespace Growfund\DTO\Fundraiser;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Status\FundraiserStatus;
use Growfund\Constants\UserTypes\Fundraiser;
use Growfund\DTO\DTO;
use Growfund\Sanitizer;
use Growfund\Supports\Date;

class CreateFundraiserDTO extends DTO
{
    /**
     * Fields that are considered not part of "meta" data.
     *
     * @var array
     */
    protected static $base_fields = ['first_name', 'last_name', 'email', 'password', 'role'];

    /** @var string */
    public $first_name;

    /** @var string */
    public $last_name;

    /** @var string */
    public $email;

    /** @var string */
    public $password;

    /** @var string */
    public $phone;

    /** @var int */
    public $image;

    /** @var string */
    public $status;

    /** @var string */
    public $role;

    /** @var string */
    public $joined_at;

    /** @var int */
    public $created_by;

    public function __construct(array $data = [])
    {
        $this->role = Fundraiser::ROLE;
        $this->status = growfund_user()->is_admin()
            ? FundraiserStatus::ACTIVE
            : Fundraiser::DEFAULT_STATUS;
        $this->joined_at = growfund_user()->is_admin()
            ? Date::current_sql_safe()
            : null;
        parent::__construct($data);
    }

    public static function validation_rules()
    {
        return [
            'first_name'                    => 'required|string',
            'last_name'                     => 'required|string',
            'email'                         => 'required|email',
            'password'                      => 'required|string|min:6',
            'phone'                         => 'string',
            'image'                         => 'integer|is_valid_image_id',
        ];
    }

    public static function sanitization_rules()
    {
        return [
            'first_name'                    => Sanitizer::TEXT,
            'last_name'                     => Sanitizer::TEXT,
            'email'                         => Sanitizer::EMAIL,
            'password'                      => Sanitizer::TEXT,
            'phone'                         => Sanitizer::TEXT,
            'image'                         => Sanitizer::INT,
        ];
    }
}
