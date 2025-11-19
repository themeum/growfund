<?php

namespace Growfund\Constants\Status;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

class PledgeStatus
{
    use HasConstants;

    const PENDING = 'pending';
    const IN_PROGRESS = 'in-progress';
    const COMPLETED = 'completed';
    const BACKED = 'backed';
    const FAILED = 'failed';
    const CANCELLED = 'cancelled';
    const REFUNDED = 'refunded';
    const TRASHED = 'trashed';
}
