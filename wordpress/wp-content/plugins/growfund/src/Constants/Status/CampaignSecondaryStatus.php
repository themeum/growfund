<?php

namespace Growfund\Constants\Status;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

class CampaignSecondaryStatus
{
    use HasConstants;

    const END = 'end';
    const HIDE = 'hide';
    const VISIBLE = 'visible';
    const PAUSE = 'pause';
    const RESUME = 'resume';
}
