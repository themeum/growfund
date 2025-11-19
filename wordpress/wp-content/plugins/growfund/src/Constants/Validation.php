<?php

namespace Growfund\Constants;

defined( 'ABSPATH' ) || exit;

use Growfund\Validation\Rules\AfterDateRule;
use Growfund\Validation\Rules\AfterRule;
use Growfund\Validation\Rules\ArrayRule;
use Growfund\Validation\Rules\BooleanRule;
use Growfund\Validation\Rules\DateFormatRule;
use Growfund\Validation\Rules\DateRule;
use Growfund\Validation\Rules\DateTimeRule;
use Growfund\Validation\Rules\EmailRule;
use Growfund\Validation\Rules\EmailUniqueRule;
use Growfund\Validation\Rules\ExistsRule;
use Growfund\Validation\Rules\FloatRule;
use Growfund\Validation\Rules\GreaterThanEqualRule;
use Growfund\Validation\Rules\GreaterThanRule;
use Growfund\Validation\Rules\InRule;
use Growfund\Validation\Rules\IntegerRule;
use Growfund\Validation\Rules\IsValidImageIdRule;
use Growfund\Validation\Rules\LessThanEqualRule;
use Growfund\Validation\Rules\LessThanRule;
use Growfund\Validation\Rules\MaxRule;
use Growfund\Validation\Rules\MinRule;
use Growfund\Validation\Rules\NotInRule;
use Growfund\Validation\Rules\NullableRule;
use Growfund\Validation\Rules\NumberRule;
use Growfund\Validation\Rules\ObjectRule;
use Growfund\Validation\Rules\PostExists;
use Growfund\Validation\Rules\ProhibitedIfRule;
use Growfund\Validation\Rules\ProhibitedRule;
use Growfund\Validation\Rules\RegexRule;
use Growfund\Validation\Rules\RequiredIfExists;
use Growfund\Validation\Rules\RequiredIfRule;
use Growfund\Validation\Rules\RequiredRule;
use Growfund\Validation\Rules\SameAsRule;
use Growfund\Validation\Rules\Sanitizer;
use Growfund\Validation\Rules\StringRule;
use Growfund\Validation\Rules\TributeFieldsRule;
use Growfund\Validation\Rules\UrlRule;
use Growfund\Validation\Rules\UserExists;

class Validation
{
    /**
     * The rules to validator method map
     * 
     * @var array
     * 
     * @since 3.3.0
     */
    const RULE_MAP = [
        'required'  => RequiredRule::class,
        'string'    => StringRule::class,
        'array'     => ArrayRule::class,
        'object'    => ObjectRule::class,
        'boolean'   => BooleanRule::class,
        'integer'   => IntegerRule::class,
        'number'    => NumberRule::class,
        'float'     => FloatRule::class,
        'email'     => EmailRule::class,
        'email_unique' => EmailUniqueRule::class,
        'url'       => UrlRule::class,
        'min'       => MinRule::class,
        'max'       => MaxRule::class,
        'in'        => InRule::class,
        'not_in'    => NotInRule::class,
        'regex'     => RegexRule::class,
        'sanitize'  => Sanitizer::class,
        'same_as'   => SameAsRule::class,
        'nullable'  => NullableRule::class,
        'date'      => DateRule::class,
        'datetime'  => DateTimeRule::class,
        'date_format' => DateFormatRule::class,
        'is_valid_image_id' => IsValidImageIdRule::class,
        'post_exists'  => PostExists::class,
        'required_if' => RequiredIfRule::class,
        'prohibited' => ProhibitedRule::class,
        'prohibited_if' => ProhibitedIfRule::class,
        'exists' => ExistsRule::class,
        'required_if_exists' => RequiredIfExists::class,
        'user_exists' => UserExists::class,
        'after' => AfterRule::class,
        'after_date' => AfterDateRule::class,
        'gt' => GreaterThanRule::class,
        'gte' => GreaterThanEqualRule::class,
        'lt' => LessThanRule::class,
        'lte' => LessThanEqualRule::class,
        'tribute_fields' => TributeFieldsRule::class,
    ];
}
