<?php

namespace Growfund\Constants;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

class OptionKeys
{
    use HasConstants;

    /**
     * Option to store the whether to flush the rewrite rules
     */
    const SHOULD_FLUSH_REWRITE_RULES = 'growfund_should_flush_rewrite_rules';

    /**
     * Option to store the available salutations
     */
    const SALUTAION = 'growfund_salutation';

    /**
     * Option to store the campaign tribute ecard template
     */
    const ECARD_TEMPLATE = 'growfund_tribute_ecard_template';

    /**
     * Option to store the Pdf Pledge Receipt template
     */
    const PDF_PLEDGE_RECEIPT_TEMPLATE = 'growfund_pdf_pledge_receipt_template';

    /**
     * Option to store the Pdf Donation Receipt template
     */
    const PDF_DONATION_RECEIPT_TEMPLATE = 'growfund_pdf_donation_receipt_template';

    /**
     * Option to store the Pdf Annual Receipt template
     */
    const PDF_ANNUAL_RECEIPT_TEMPLATE = 'growfund_pdf_annual_receipt_template';

    /**
     * Option to store the email notification template
     */
    const EMAIL_NOTIFICATION_TEMPLATE = 'growfund_email_notification_template';

    /**
     * Payment gateway config
     */
    const PAYMENT_GATEWAY_CONFIG_PREFIX = 'growfund_payment_gateway_config_';

    /**
     * SMTP Settings
     */
    const SMTP_SETTINGS = 'growfund_smtp_settings';

    /**
     * Generic product id for woocommerce checkout
     */
    const WC_PRODUCT_ID = 'growfund_wc_product_id';

    /**
     * checked migration consent
     */
    const CHECKED_MIGRATION_CONSENT = 'growfund_checked_migration_consent';


    /**
     * flag to detect is migrated from crowdfunding
     */
    const IS_MIGRATED_FROM_CROWDFUNDING = 'growfund_is_migrated_from_crowdfunding';

    /**
     * Download hash key
     */
    const DOWNLOAD_HASH_KEY = 'growfund_download_hash_key';


    /**
     * Track db migration
     */
    const DATABASE_MIGRATION_TRACKER = 'growfund_database_migration_tracker';

    /**
     * Installed version
     */
    const INSTALLED_VERSION = 'growfund_installed_version';

    /**
     * Is sync wallet transaction
     */
    const IS_SYNCED_WALLET_TRANSACTION = 'growfund_is_synced_wallet_transaction';
}
