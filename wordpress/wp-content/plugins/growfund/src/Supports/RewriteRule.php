<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\OptionKeys;
use Growfund\Supports\Option;

class RewriteRule
{
    public static function schedule_reset()
    {
        Option::update(OptionKeys::SHOULD_FLUSH_REWRITE_RULES, true);
    }

    public static function reset()
    {
        $should_flush_rewrite_rules = Option::get(OptionKeys::SHOULD_FLUSH_REWRITE_RULES, false);

        if ($should_flush_rewrite_rules) {
            flush_rewrite_rules();
            Option::update(OptionKeys::SHOULD_FLUSH_REWRITE_RULES, false);
        }
    }
}
