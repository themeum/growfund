<?php

namespace Growfund\Constants;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

class UserDeleteType
{
    use HasConstants;

    const TRASH = 'trash';

    const ANONYMIZE = 'anonymize';

    const PERMANENT = 'permanent';
}
