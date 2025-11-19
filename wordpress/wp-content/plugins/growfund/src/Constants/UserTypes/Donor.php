<?php

namespace Growfund\Constants\UserTypes;

defined( 'ABSPATH' ) || exit;

/**
 * Donor user type class
 * @since 1.0.0
 */
class Donor
{
    /**
     * Donor user type name
     */
    const NAME = 'growfund_donor';

    /**
     * Donor user role
     */
    const ROLE = 'growfund_donor';

    /**
     * Donor role title
     */
    const TITLE = 'Donor';

    /**
     * Default Donor status
     */
    const DEFAULT_STATUS = 'active';
}
