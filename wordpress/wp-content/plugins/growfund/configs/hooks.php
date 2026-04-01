<?php

defined( 'ABSPATH' ) || exit;

use Growfund\Hooks\Filters\ApiPermissionError;
use Growfund\Hooks\Actions\AuthRedirect;
use Growfund\Hooks\Actions\BackerCapabilities;
use Growfund\Hooks\Actions\ClearFlashMessage;
use Growfund\Hooks\Actions\OnboardingCompleted;
use Growfund\Hooks\Actions\DonorCapabilities;
use Growfund\Hooks\Actions\EnqueueBrandingStyles;
use Growfund\Hooks\Actions\EnqueueScriptDashboard;
use Growfund\Hooks\Actions\EnqueueScriptsAdmin;
use Growfund\Hooks\Actions\EnqueueScriptsSite;
use Growfund\Hooks\Actions\FlushRewriteRules;
use Growfund\Hooks\Actions\ManageDatabaseMigrations;
use Growfund\Hooks\Actions\ManageOnboarding;
use Growfund\Hooks\Actions\NewUserRegistered;
use Growfund\Hooks\Actions\ApplyOnboardingFromCrowdfunding;
use Growfund\Hooks\Actions\RegisterAdminMenu;
use Growfund\Hooks\Actions\RegisterAjaxRouter;
use Growfund\Hooks\Actions\RegisterCampaignBlocks;
use Growfund\Hooks\Actions\RegisterPostType;
use Growfund\Hooks\Actions\RegisterRestApi;
use Growfund\Hooks\Actions\RegisterTaxonomy;
use Growfund\Hooks\Actions\RemoveDuplicateSubmenu;
use Growfund\Hooks\Actions\SMTPConfig;
use Growfund\Hooks\Actions\VerifyEmail;
use Growfund\Hooks\Actions\RestrictUserFromLogin;
use Growfund\Hooks\Actions\ShowAdminNotice;
use Growfund\Hooks\Actions\Woocommerce\AddToCartValidation;
use Growfund\Hooks\Actions\Woocommerce\BeforeCalculateTotal;
use Growfund\Hooks\Actions\Woocommerce\CreateOrderLineItem;
use Growfund\Hooks\Filters\AjaxMedia;
use Growfund\Hooks\Filters\FilterThemeStyles;
use Growfund\Hooks\Filters\HideRolesFromWPUserPage;
use Growfund\Hooks\Filters\MailFromAddress;
use Growfund\Hooks\Filters\MailFromName;
use Growfund\Hooks\Filters\RegisterCustomClassicTemplates;
use Growfund\Hooks\Filters\Woocommerce\CheckoutItemName;
use Growfund\Hooks\Actions\Woocommerce\OrderStatusChange;
use Growfund\Hooks\Actions\Woocommerce\NewOrder;
use Growfund\Hooks\Actions\Woocommerce\NewOrderStoreAPI;
use Growfund\Hooks\Actions\Woocommerce\RemoveCartItem;
use Growfund\Hooks\Actions\Woocommerce\RestrictProductDeletion;
use Growfund\Hooks\Actions\Woocommerce\RestrictProductUpdate;
use Growfund\Hooks\Filters\ActionLinks;
use Growfund\Hooks\Filters\BodyClass;
use Growfund\Hooks\Filters\HideAdminBar;
use Growfund\Hooks\Filters\LoginRedirect;
use Growfund\Hooks\Filters\RegisterCustomBlockTemplates;
use Growfund\Hooks\Filters\Woocommerce\AttachmentUploadAccess;
use Growfund\Hooks\Filters\Woocommerce\BlockCustomCheckoutFields;
use Growfund\Hooks\Filters\Woocommerce\ClassicCustomCheckoutFields;
use Growfund\Hooks\Filters\Woocommerce\CustomSuccessPage;
use Growfund\Hooks\Filters\Woocommerce\DisableCoupon;
use Growfund\Hooks\Filters\Woocommerce\RemoveGrowfundProductQuantityField;
use Growfund\Hooks\Filters\Woocommerce\RemoveTaxFromGrowfundProduct;
use Growfund\Hooks\Scheduler\ChargeBackersScheduler;
use Growfund\Hooks\Scheduler\EmailScheduler;
use Growfund\Hooks\Scheduler\RecurringScheduler;
use Growfund\Hooks\Scheduler\StopRecurringScheduler;

return [
    'actions' => [
        ManageOnboarding::class,
        ManageDatabaseMigrations::class,
        EnqueueScriptsAdmin::class,
        EnqueueScriptDashboard::class,
        EnqueueBrandingStyles::class,
        EnqueueScriptsSite::class,
        RegisterRestApi::class,
        RegisterAdminMenu::class,
        RegisterPostType::class,
        RegisterTaxonomy::class,
        BackerCapabilities::class,
        DonorCapabilities::class,
        FlushRewriteRules::class,
        SMTPConfig::class,
        RemoveDuplicateSubmenu::class,
        VerifyEmail::class,
        NewUserRegistered::class,
        ClearFlashMessage::class,
        AuthRedirect::class,
        ShowAdminNotice::class,
        RegisterCampaignBlocks::class,
        RegisterAjaxRouter::class,
        OnboardingCompleted::class,
        ApplyOnboardingFromCrowdfunding::class,

        // woocommerce
        RestrictProductDeletion::class,
        RestrictProductUpdate::class,
        BeforeCalculateTotal::class,
        NewOrder::class,
        NewOrderStoreAPI::class,
        OrderStatusChange::class,
        AddToCartValidation::class,
        CreateOrderLineItem::class,
        RemoveCartItem::class,
    ],
    'filters' => [
        ActionLinks::class,
        RestrictUserFromLogin::class,
        AjaxMedia::class,
        FilterThemeStyles::class,
        MailFromAddress::class,
        MailFromName::class,
        HideRolesFromWPUserPage::class,
        LoginRedirect::class,
        RegisterCustomClassicTemplates::class,
        RegisterCustomBlockTemplates::class,
        HideAdminBar::class,
        BodyClass::class,
        ApiPermissionError::class,

        // woocommerce
        CheckoutItemName::class,
        ClassicCustomCheckoutFields::class,
        CustomSuccessPage::class,
        AttachmentUploadAccess::class,
        RemoveGrowfundProductQuantityField::class,
        RemoveTaxFromGrowfundProduct::class,
        DisableCoupon::class,
        BlockCustomCheckoutFields::class,
    ],
    'schedulers' => [
        ChargeBackersScheduler::class,
        EmailScheduler::class,
        RecurringScheduler::class,
        StopRecurringScheduler::class,
    ]
];
