<?php

namespace Growfund\DTO;

defined( 'ABSPATH' ) || exit;

class RewardItemWithQuantityDTO extends RewardItemDTO
{
    /** @var int */
    public $quantity;
}
