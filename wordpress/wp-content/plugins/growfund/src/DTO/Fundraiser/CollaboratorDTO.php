<?php

namespace Growfund\DTO\Fundraiser;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;

class CollaboratorDTO extends DTO
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $display_name;
    
    /**
     * @var string
     */
    public $email;

    /**
     * @var string|null
     */
    public $phone;

    /**
     * @var int|null
     */
    public $image;

    /**
     * @var string
     */
    public $status;

    /** @var int */
    public $total_campaign_created;

    /** @var int */
    public $total_number_of_contributions;
}
