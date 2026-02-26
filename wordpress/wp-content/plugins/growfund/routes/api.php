<?php

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\AnalyticsType;
use Growfund\Controllers\API\AnalyticsController;
use Growfund\Controllers\API\AppConfigController;
use Growfund\Controllers\API\AuthController;
use Growfund\Controllers\API\BackerController;
use Growfund\Controllers\API\BookmarksController;
use Growfund\Controllers\API\CampaignController;
use Growfund\Controllers\API\CampaignPostController;
use Growfund\Controllers\API\CategoryController;
use Growfund\Controllers\API\DonationController;
use Growfund\Controllers\API\DonorController;
use Growfund\Controllers\API\EmailContentController;
use Growfund\Controllers\API\MediaController;
use Growfund\Controllers\API\MigrationController;
use Growfund\Controllers\API\PageController;
use Growfund\Controllers\API\OptionController;
use Growfund\Controllers\API\PaymentMethodController;
use Growfund\Controllers\API\TimelineController;
use Growfund\Controllers\API\PledgeController;
use Growfund\Controllers\API\RewardController;
use Growfund\Controllers\API\RewardItemController;
use Growfund\Controllers\API\TagController;
use Growfund\Controllers\API\UserController;
use Growfund\Middlewares\API\AdminMiddleware;
use Growfund\Middlewares\API\AdminOrFundraiserMiddleware;
use Growfund\Middlewares\API\AuthMiddleware;
use Growfund\Route;

Route::set_namespace('growfund/v1');

// Public routes
Route::get('/app-config', [AppConfigController::class, 'get']);
Route::get('/donations/{uid}/receipt', [DonationController::class, 'get_receipt'])->where('uid', '[0-9a-zA-Z\-_.]+');
Route::get('/donations/{uid}/ecard', [DonationController::class, 'get_ecard'])->where('uid', '[0-9a-zA-Z\-_.]+');
Route::get('/pledges/{uid}/receipt', [PledgeController::class, 'get_receipt'])->where('uid', '[0-9a-zA-Z\-_.]+');


Route::group(['middleware' => AuthMiddleware::class], function () {
    Route::post('/migration', [MigrationController::class, 'migrate'])->middleware(AdminMiddleware::class);

    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);

    // App Config routes
    Route::post('/app-config', [AppConfigController::class, 'store'])->middleware(AdminMiddleware::class);
    Route::get('/app-config/woocommerce', [AppConfigController::class, 'get_woocommerce_config'])->middleware(AdminMiddleware::class);
    Route::post('/app-config/onboarding', [AppConfigController::class, 'onboarding'])->middleware(AdminMiddleware::class);

    // Options routes
    Route::get('/options', [OptionController::class, 'get']);
    Route::post('/options', [OptionController::class, 'store'])->middleware(AdminMiddleware::class);

    // Email routes
    Route::get('/email-content', [EmailContentController::class, 'get'])->middleware(AdminMiddleware::class);
    Route::post('/email-content', [EmailContentController::class, 'store'])->middleware(AdminMiddleware::class);
    Route::post('/email-content/restore', [EmailContentController::class, 'restore'])->middleware(AdminMiddleware::class);

    // Payment Methods
    Route::get('/payment-methods/{type}', [PaymentMethodController::class, 'get'])->where('type', '[a-zA-Z0-9\-_]+')->middleware(AdminOrFundraiserMiddleware::class);

    // Users routes
    Route::delete('/delete-my-account', [UserController::class, 'delete_my_account']);
    Route::patch('/users/{id}/notifications', [UserController::class, 'update_notifications'])->where('id', '[\d]+');
    Route::post('/users/{id}/update-password', [UserController::class, 'update_password'])->where('id', '[\d]+');
    Route::post('/users/{id}/password-reset-link', [UserController::class, 'send_reset_password_email'])->where('id', '[\d]+')->middleware(AdminMiddleware::class);

    // Page routes
    Route::get('/wp-pages', [PageController::class, 'all']);
    Route::get('/growfund-pages', [PageController::class, 'growfund_pages']);
    Route::post('/growfund-pages', [PageController::class, 're_generate_pages'])->middleware(AdminMiddleware::class);

    // Generic routes
    Route::post('/upload-media', [MediaController::class, 'upload']);

    // User routes
    Route::post('/validate-email', [UserController::class, 'validate_email']);
    Route::post('/validate-username', [UserController::class, 'validate_username']);
    Route::get('/current-user', [UserController::class, 'get_current_user']);

    // Campaigns routes
    Route::get('/campaigns', [CampaignController::class, 'paginated']);
    Route::get('/campaigns/{id}', [CampaignController::class, 'get_by_id'])->where('id', '[0-9]+');
    Route::get('/campaigns/{id}/overview', [CampaignController::class, 'overview'])->where('id', '[0-9]+');
    Route::post('/campaigns', [CampaignController::class, 'create']);
    Route::patch('/campaigns/{id}', [CampaignController::class, 'update_status'])->where('id', '[0-9]+');
    Route::put('/campaigns/{id}', [CampaignController::class, 'update'])->where('id', '[0-9]+');
    Route::delete('/campaigns/{id}', [CampaignController::class, 'delete'])->where('id', '[0-9]+');
    Route::patch('/campaigns/bulk-actions', [CampaignController::class, 'bulk_actions']);
    Route::delete('/campaigns/empty-trash', [CampaignController::class, 'empty_trash']);
    Route::patch('/campaigns/{id}/update-secondary-status', [CampaignController::class, 'update_secondary_status'])->where('id', '[0-9]+');
    Route::post('/campaigns/{campaign_id}/post-update', [CampaignPostController::class, 'create'])->where('campaign_id', '[\d]+');
    Route::post('/campaigns/{campaign_id}/charge-backers', [CampaignController::class, 'charge_backers'])->where('campaign_id', '[\d]+');

    // Categories routes
    Route::get('/categories', [CategoryController::class, 'list']);
    Route::post('/categories', [CategoryController::class, 'create'])->middleware(AdminMiddleware::class);
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->where('id', '[\d]+')->middleware(AdminMiddleware::class);
    Route::delete('/categories/{id}', [CategoryController::class, 'delete'])->where('id', '[\d]+')->middleware(AdminMiddleware::class);
    Route::get('/categories/top-level', [CategoryController::class, 'top_level']);
    Route::get('/categories/{parent_id}/subcategories', [CategoryController::class, 'sub_categories'])->where('parent_id', '[\d]+');
    Route::patch('/categories/bulk-actions', [CategoryController::class, 'bulk_actions'])->middleware(AdminMiddleware::class);
    Route::delete('/categories/empty-trash', [CategoryController::class, 'empty_trash'])->middleware(AdminMiddleware::class);

    // Tags routes
    Route::get('/tags', [TagController::class, 'list']);
    Route::post('/tags', [TagController::class, 'create']);
    Route::put('/tags/{id}', [TagController::class, 'update'])->where('id', '[\d]+');
    Route::delete('/tags/{id}', [TagController::class, 'delete'])->where('id', '[\d]+');
    Route::patch('/tags/bulk-actions', [TagController::class, 'bulk_actions']);
    Route::delete('/tags/empty-trash', [TagController::class, 'empty_trash']);

    // Rewards routes
    Route::get('/campaigns/{campaign_id}/rewards', [RewardController::class, 'list'])->where('campaign_id', '[\d]+');
    Route::post('/campaigns/{campaign_id}/rewards', [RewardController::class, 'create'])->where('campaign_id', '[\d]+');
    Route::put('/campaigns/{campaign_id}/rewards/{reward_id}', [RewardController::class, 'update'])
        ->where('campaign_id', '[\d]+')
        ->where('reward_id', '[\d]+');
    Route::delete('/campaigns/{campaign_id}/rewards/{reward_id}', [RewardController::class, 'delete'])
        ->where('campaign_id', '[\d]+')
        ->where('reward_id', '[\d]+');

    // Reward items
    Route::get('/campaigns/{campaign_id}/reward-items', [RewardItemController::class, 'all'])->where('campaign_id', '[\d]+');
    Route::post('/campaigns/{campaign_id}/reward-items', [RewardItemController::class, 'create'])->where('campaign_id', '[\d]+');
    Route::put('/campaigns/{campaign_id}/reward-items/{id}', [RewardItemController::class, 'update'])->where('id', '[\d]+')->where('campaign_id', '[\d]+');
    Route::delete('/campaigns/{campaign_id}/reward-items/{id}', [RewardItemController::class, 'delete'])->where('id', '[\d]+')->where('campaign_id', '[\d]+');
    Route::get('/pledges/{uid}/reward-items/{id}/download', [RewardItemController::class, 'get_download_link'])
        ->where('id', '[\d]+')
        ->where('uid', '[0-9a-zA-Z\-_.]+');

    // Backers routes
    Route::get('/backers', [BackerController::class, 'paginated']);
    Route::post('/backers', [BackerController::class, 'create']);
    Route::put('/backers/{id}', [BackerController::class, 'update'])->where('id', '[\d]+');
    Route::get('/backers/{id}/overview', [BackerController::class, 'overview'])->where('id', '[\d]+');
    Route::delete('/backers/{id}', [BackerController::class, 'delete'])->where('id', '[\d]+');
    Route::delete('/backers/empty-trash', [BackerController::class, 'empty_trash']);
    Route::patch('/backers/bulk-actions', [BackerController::class, 'bulk_actions']);
    Route::patch('/backers/{id}/notification-settings', [BackerController::class, 'notification_settings'])->where('id', '[\d]+');
    Route::get('/backers/{backer_id}/campaigns', [BackerController::class, 'campaigns_by_backer'])->where('backer_id', '[\d]+');
    Route::get('/backers/{backer_id}/giving-stats', [BackerController::class, 'giving_stats'])->where('backer_id', '[\d]+');
    Route::get('/backers/{id}/activities', [BackerController::class, 'activities'])->where('id', '[\d]+');
    Route::get('/backers/{backer_id}/pledges', [PledgeController::class, 'backer_pledges'])->where('backer_id', '[\d]+');

    // Pledges routes
    Route::get('/pledges', [PledgeController::class, 'paginated']);
    Route::post('/pledges', [PledgeController::class, 'create']);
    Route::get('/pledges/{id}', [PledgeController::class, 'get_by_id'])->where('id', '[\d]+');
    Route::patch('/pledges/{id}', [PledgeController::class, 'update_status'])->where('id', '[\d]+');
    Route::delete('/pledges/{id}', [PledgeController::class, 'delete'])->where('id', '[\d]+');
    Route::delete('/pledges/empty-trash', [PledgeController::class, 'empty_trash']);
    Route::patch('/pledges/bulk-actions', [PledgeController::class, 'bulk_actions']);
    Route::get('/pledges/{id}/activities', [PledgeController::class, 'activities'])->where('id', '[\d]+');
    Route::post('/pledges/{id}/charge-backer', [PledgeController::class, 'charge_backer'])->where('id', '[\d]+');
    Route::post('/pledges/{id}/retry-failed-payment', [PledgeController::class, 'retry_failed_payment'])->where('id', '[\d]+');

    // Donations routes
    Route::get('/donations', [DonationController::class, 'paginated']);
    Route::get('/donations/{id}', [DonationController::class, 'get_by_id'])->where('id', '[\d]+');
    Route::post('/donations', [DonationController::class, 'create']);
    Route::patch('/donations/{id}', [DonationController::class, 'update_status'])->where('id', '[\d]+');
    Route::delete('/donations/{id}', [DonationController::class, 'delete'])->where('id', '[\d]+');
    Route::delete('/donations/empty-trash', [DonationController::class, 'empty_trash']);
    Route::patch('/donations/bulk-actions', [DonationController::class, 'bulk_actions']);
    Route::get('/donations/{id}/activities', [DonationController::class, 'activities'])->where('id', '[\d]+');

    // Comments routes
    Route::post('/{contribution_name}/{contribution_id}/timelines', [TimelineController::class, 'create'])
        ->where('contribution_name', '(donations|pledges)')
        ->where('contribution_id', '[\d]+');
    Route::delete('/{contribution_name}/{contribution_id}/timelines/{timeline_id}', [TimelineController::class, 'delete'])
        ->where('contribution_name', '(donations|pledges)')
        ->where('contribution_id', '[\d]+')
        ->where('timeline_id', '[\d]+');

    // Donor routes
    Route::get('/donors', [DonorController::class, 'paginated']);
    Route::post('/donors', [DonorController::class, 'create']);
    Route::put('/donors/{id}', [DonorController::class, 'update'])->where('id', '[\d]+');
    Route::get('/donors/{id}/overview', [DonorController::class, 'overview'])->where('id', '[\d]+');
    Route::get('/donors/{id}/donations', [DonorController::class, 'donations'])->where('id', '[\d]+');
    Route::get('/annual-receipts', [DonorController::class, 'get_annual_receipts']);
    Route::get('/annual-receipts/{year}', [DonorController::class, 'get_annual_receipt_detail'])->where('year', '\d{4}');
    Route::delete('/donors/{id}', [DonorController::class, 'delete'])->where('id', '[\d]+');
    Route::delete('/donors/empty-trash', [DonorController::class, 'empty_trash']);
    Route::patch('/donors/bulk-actions', [DonorController::class, 'bulk_actions']);
    Route::get('/donor-stats', [DonorController::class, 'get_stats']);
    Route::get('/donors/{id}/activities', [DonorController::class, 'activities'])->where('id', '[\d]+');

    // Analytics routes
    Route::get('/analytics/{type}', [AnalyticsController::class, 'get_analytics'])
        ->where('type', AnalyticsType::get_regex());

    // Bookmarks routes
    Route::get('/bookmarks', [BookmarksController::class, 'paginated']);
    Route::post('/bookmarks', [BookmarksController::class, 'remove']);
});
