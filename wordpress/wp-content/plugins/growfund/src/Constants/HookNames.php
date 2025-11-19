<?php

namespace Growfund\Constants;

defined( 'ABSPATH' ) || exit;

class HookNames
{
    const ONBOARDING_COMPLETED = 'growfund_onboarding_completed';
    const WP = 'wp';
    const WP_HEAD = 'wp_head';
    const BODY_CLASS = 'body_class';
    const PLUGINS_LOADED = 'plugins_loaded';
    const INIT = 'init';
    const ADMIN_INIT = 'admin_init';
    const ADMIN_NOTICES = 'admin_notices';
    const REST_API_INIT = 'rest_api_init';
    const ADMIN_MENU = 'admin_menu';
    const ADMIN_ENQUEUE_SCRIPT = 'admin_enqueue_scripts';
    const WP_ENQUEUE_SCRIPT = 'wp_enqueue_scripts';
    const REGISTER_ROLE = 'register_role';
    const REGISTER_TAXONOMY = 'register_taxonomy';
    const REGISTER_POST_TYPE = 'register_post_type';
    const AJAX_QUERY_ATTACHMENTS_ARGS = 'ajax_query_attachments_args';
    const ADMIN_COMMENT_TYPES_DROPDOWN = 'admin_comment_types_dropdown';
    const WP_MAIL_FROM = 'wp_mail_from';
    const WP_MAIL_FROM_NAME = 'wp_mail_from_name';
    const WP_PHP_MAILER_INIT = 'phpmailer_init';
    const EDITABLE_ROLES = 'editable_roles';
    const WP_TRASH_POST = 'wp_trash_post';
    const LOGIN_REDIRECT = 'login_redirect';
    const TEMPLATE_INCLUDE = 'template_include';
    const GET_BLOCK_TEMPLATES = 'get_block_templates';
    const SHOW_ADMIN_BAR = 'show_admin_bar';
    const STYLE_LOADER_SRC = 'style_loader_src';
    const WP_AUTHENTICATE_USER = 'wp_authenticate_user';
    const USER_REGISTER = 'user_register';
    const PLUGIN_ROW_META = 'plugin_row_meta';

    // Plugin Updater hooks
    const PLUGINS_API = 'plugins_api';
    const UPGRADER_PRE_DOWNLOAD = 'upgrader_pre_download';
    
    // Woocommerce hooks
    const WC_BEFORE_CALCULATE_TOTAL = 'woocommerce_before_calculate_totals';
    const WC_PRODUCT_GET_NAME = 'woocommerce_product_get_name';
    const WC_CHECKOUT_ORDER_PROCESSED = 'woocommerce_checkout_order_processed';
    const WC_STORE_API_CHECKOUT_ORDER_PROCESSED = 'woocommerce_store_api_checkout_order_processed';
    const WC_ORDER_STATUS_CHANGED = 'woocommerce_order_status_changed';
    const WC_AFTER_ORDER_NOTES = 'woocommerce_after_order_notes';
    const WC_STORE_API_CHECKOUT_UPDATE_ORDER_FROM_REQUEST = 'woocommerce_store_api_checkout_update_order_from_request';
    const WC_ADD_TO_CART_VALIDATION = 'woocommerce_add_to_cart_validation';
    const WC_CART_NEEDS_SHIPPING = 'woocommerce_cart_needs_shipping';
    const WC_CLASSIC_CHECKOUT_FIELDS = 'woocommerce_checkout_fields';
    const WC_PACKAGE_RATES = 'woocommerce_package_rates';
    const WC_CLASSIC_CHECKOUT_FORM = 'woocommerce_after_checkout_form';
    const WC_CLASSIC_CHECKOUT_CREATE_ORDER = 'woocommerce_checkout_create_order';
    const WC_CHECKOUT_ORDER_RECEIVED_URL = 'woocommerce_get_checkout_order_received_url';

    // Scheduler hooks
    const SCHEDULED_EMAILS = 'growfund_scheduled_emails';
    const SCHEDULED_CHARGE_BACKERS = 'growfund_scheduled_charge_backers';
    const SCHEDULED_RECURRING = 'growfund_scheduled_recurring';
    const STOP_SCHEDULED_RECURRING = 'growfund_stop_scheduled_recurring';

    // Growfund Hooks
    const GROWFUND_FILTER_PRO_FEATURES = 'growfund/pro-features';
    const GROWFUND_ROUTE_BEFORE_INIT_ACTION = 'growfund/route/before_init';
    const GROWFUND_CAMPAIGN_AFTER_SAVE_ACTION = 'growfund/campaign/after_save';
    const GROWFUND_CAMPAIGN_AFTER_PERMANENT_DELETE_ACTION = 'growfund/campaign/after_permanent_delete';
    const GROWFUND_CAMPAIGN_UPDATE_VALIDATION_RULES_FILTER = 'growfund/campaign/update_validation_rules';
    const GROWFUND_ANALYTICS_DATA_FILTER = 'growfund/analytics/data';
    const GROWFUND_REWARD_VALIDATION_RULES_FILTER = 'growfund/reward/validation_rules';
    const GROWFUND_BACKER_OVERVIEW_FILTER = 'growfund/backer/overview';
    const GROWFUND_DONOR_OVERVIEW_FILTER = 'growfund/donor/overview';
    const GROWFUND_BEFORE_REGISTER_SITE_ROUTES_ACTION = 'growfund/route/before_register_site_routes';
    const GROWFUND_BEFORE_APP_CONFIG_UPDATE_FILTER = 'growfund/app_config/before_update';
    const GROWFUND_BEFORE_OPTION_UPDATE_FILTER = 'growfund/option/before_update';
}
