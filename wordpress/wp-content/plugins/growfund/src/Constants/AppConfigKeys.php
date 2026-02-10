<?php

namespace Growfund\Constants;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Constant;
use Growfund\Traits\HasConstants;

class AppConfigKeys implements Constant
{
    use HasConstants;

    /**
     * Option to store the general settings
     */
    const GENERAL = 'growfund_general';

    /**
     * Option to store the page settings
     */
    const PAGE = 'growfund_page';

    /**
     * Option to store the campaign settings
     */
    const CAMPAIGN = 'growfund_campaign';

    /**
     * Option to store the user permissions settings
     */
    const USER_PERMISSIONS = 'growfund_user_permissions';

    /**
     * Option to store the payment settings
     */
    const PAYMENT = 'growfund_payment';

    /**
     * Option to store the PDF receipt settings
     */
    const PDF_RECEIPT = 'growfund_pdf_receipt';

    /**
     * Option to store the email and notifications settings
     */
    const EMAIL_AND_NOTIFICATIONS = 'growfund_email_and_notifications';

    /**
     * Option to store the security settings
     */
    const SECURITY = 'growfund_security';

    /**
     * Option to store the integrations settings
     */
    const INTEGRATIONS = 'growfund_integrations';

    /**
     * Option to store the branding settings
     */
    const BRANDING = 'growfund_branding';

    /**
     * Option to store the advanced settings
     */
    const ADVANCED = 'growfund_advanced';

    /**
     * Option to store if its donation mode or not
     */
    const IS_DONATION_MODE = 'growfund_is_donation_mode';

    /**
     * Option to store if the onboarding is completed or not
     */
    const IS_ONBOARDING_COMPLETED = 'growfund_is_onboarding_completed';

    /**
     * Get all the keys
     *
     * @return array
     */
    public static function all()
    {
        return static::get_constant_values();
    }
}
