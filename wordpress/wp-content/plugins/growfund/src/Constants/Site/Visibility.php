<?php

namespace Growfund\Constants\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Constant;
use Growfund\Traits\HasConstants;

/**
 * Site Visibility Constants
 * 
 * Constants used for defining visibility levels across the site
 * 
 * @since 1.0.0
 */
class Visibility
{
    use HasConstants;

    /**
     * Public visibility - accessible to everyone
     */
    const PUBLIC = 'public';

    /**
     * Logged-in users only - requires user authentication
     */
    const LOGGED_IN_USERS = 'logged-in-users';

    /**
     * Contributors only - accessible only to campaign contributors/backers
     */
    const CONTRIBUTORS = 'contributors';
}
