<?php

namespace Growfund\Constants;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

class TributeNotificationType
{
    use HasConstants;

    const ECARD = "send-ecard";
    const POST_MAIL = "send-post-mail";
    const BOTH = "send-ecard-and-post-mail";
}
