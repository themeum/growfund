<?php

namespace Growfund\App\Events;

defined( 'ABSPATH' ) || exit;

class SendLocalPickupInstructionEvent
{
    /** @var int */
    public $pledge_id;

    /**
     * Initialize the event with pledge ID.
     * @param int $pledge_id
     */
    public function __construct(int $pledge_id)
    {
        $this->pledge_id = $pledge_id;
    }
}
