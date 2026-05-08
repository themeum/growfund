<?php

defined( 'ABSPATH' ) || exit;

use Growfund\AjaxRouter;
use Growfund\Controllers\Site\CampaignPostController;
use Growfund\Controllers\Site\CampaignController;
use Growfund\Controllers\Site\CommentController;
use Growfund\Controllers\Site\DonorController;
use Growfund\Controllers\Site\RewardController;

AjaxRouter::add_action('growfund_ajax_get_paginated_campaigns', [CampaignController::class, 'paginated'])->with_nonce(growfund_with_prefix('site_nonce'));
AjaxRouter::add_action('growfund_ajax_get_featured_campaigns', [CampaignController::class, 'paginated_featured_campaigns'])->with_nonce(growfund_with_prefix('site_nonce'));

AjaxRouter::add_action('growfund_ajax_get_campaign_post_update_detail', [CampaignPostController::class, 'get_campaign_post_update_detail'])->with_nonce(growfund_with_prefix('site_nonce'));
AjaxRouter::add_action('growfund_ajax_get_paginated_campaign_post_updates', [CampaignPostController::class, 'paginated_campaign_post_updates'])->with_nonce(growfund_with_prefix('site_nonce'));


AjaxRouter::add_action('growfund_ajax_get_campaign_rewards', [RewardController::class, 'get_campaign_rewards'])->with_nonce(growfund_with_prefix('site_nonce'));
AjaxRouter::add_action('growfund_ajax_get_comments', [CommentController::class, 'get_comments'])->with_nonce(growfund_with_prefix('site_nonce'));
AjaxRouter::add_action('growfund_ajax_get_comment_by_id', [CommentController::class, 'get_comment_by_id'])->with_nonce(growfund_with_prefix('site_nonce'));
AjaxRouter::add_action('growfund_ajax_create_comment', [CommentController::class, 'create'])->with_nonce(growfund_with_prefix('site_nonce'));
// Bookmark routes
AjaxRouter::add_action('growfund_ajax_bookmark_campaign', [CampaignController::class, 'bookmark_campaign'])
    ->with_nonce(growfund_with_prefix('site_nonce'))
    ->for_authenticated();

AjaxRouter::add_action('growfund_ajax_donor_list', [DonorController::class, 'get_public_list'])
    ->with_nonce(growfund_with_prefix('site_nonce'))
    ->for_authenticated();
