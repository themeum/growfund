<?php

namespace Growfund\Constants\Mail;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

class MailKeys
{
    use HasConstants;

    const ADMIN_NEW_USER_REGISTRATION              = 'admin_email_new_user_registration';
    const ADMIN_PASSWORD_RESET_REQUEST             = 'admin_email_password_reset_request';
    const ADMIN_CAMPAIGN_POST_UPDATE               = 'admin_email_campaign_post_update';
    const ADMIN_CAMPAIGN_SUBMITTED_FOR_REVIEW      = 'admin_email_campaign_submitted_for_review';
    const ADMIN_CAMPAIGN_FUNDED                    = 'admin_email_campaign_funded';
    const ADMIN_NEW_PLEDGE                         = 'admin_email_new_pledge';
    const ADMIN_NEW_OFFLINE_PLEDGE                 = 'admin_email_new_offline_pledge';
    const ADMIN_REWARD_DELIVERED                   = 'admin_email_reward_delivered';
    const ADMIN_NEW_DONATION                       = 'admin_email_new_donation';
    const ADMIN_NEW_OFFLINE_DONATION               = 'admin_email_new_offline_donation';
    const ADMIN_CAMPAIGN_ENDED                     = 'admin_email_campaign_ended';

    const FUNDRAISER_NEW_USER_REGISTRATION         = 'fundraiser_email_new_user_registration';
    const FUNDRAISER_ACCOUNT_APPROVED              = 'fundraiser_email_account_approved';
    const FUNDRAISER_ACCOUNT_DECLINED              = 'fundraiser_email_account_declined';
    const FUNDRAISER_PASSWORD_RESET_REQUEST        = 'fundraiser_email_password_reset_request';
    const FUNDRAISER_CAMPAIGN_APPROVED             = 'fundraiser_email_campaign_approved';
    const FUNDRAISER_CAMPAIGN_DECLINED             = 'fundraiser_email_campaign_declined';
    const FUNDRAISER_CAMPAIGN_POST_UPDATE          = 'fundraiser_email_campaign_post_update';
    const FUNDRAISER_CAMPAIGN_FUNDED               = 'fundraiser_email_campaign_funded';
    const FUNDRAISER_NEW_PLEDGE                    = 'fundraiser_email_new_pledge';
    const FUNDRAISER_PLEDGE_AMOUNT_CHARGED         = 'fundraiser_email_pledge_amount_charged';
    const FUNDRAISER_PLEDGE_CANCELLED              = 'fundraiser_email_pledge_cancelled';
    const FUNDRAISER_NEW_OFFLINE_PLEDGE            = 'fundraiser_email_new_offline_pledge';
    const FUNDRAISER_REWARD_DELIVERED              = 'fundraiser_email_reward_delivered';
    const FUNDRAISER_NEW_DONATION                  = 'fundraiser_email_new_donation';
    const FUNDRAISER_NEW_OFFLINE_DONATION          = 'fundraiser_email_new_offline_donation';
    const FUNDRAISER_DONATION_AMOUNT_CHARGED       = 'fundraiser_email_donation_amount_charged';
    const FUNDRAISER_DONATION_CANCELLED            = 'fundraiser_email_donation_cancelled';

    const DONOR_DONATION_RECEIPT                   = 'donor_email_donation_receipt';
    const DONOR_DONATION_FAILED                    = 'donor_email_donation_failed';
    const DONOR_NEW_DONOR_REGISTRATION             = 'donor_email_new_donor_registration';
    const DONOR_OFFLINE_DONATION_INSTRUCTIONS      = 'donor_email_offline_donation_instructions';
    const DONOR_CAMPAIGN_POST_UPDATE               = 'donor_email_campaign_post_update';
    const DONOR_CAMPAIGN_HALF_MILESTONE_ACHIEVED   = 'donor_email_campaign_half_milestone_achieved';
    const DONOR_PASSWORD_RESET_REQUEST             = 'donor_email_password_reset_request';
    const DONOR_TRIBUTE_MAIL                       = 'donor_email_tribute_mail';

    const BACKER_PLEDGE_CREATED                    = 'backer_email_pledge_created';
    const BACKER_OFFLINE_PLEDGE_REQUEST            = 'backer_email_offline_pledge_request';
    const BACKER_PLEDGE_PAID                       = 'backer_email_pledge_paid';
    const BACKER_PLEDGE_PAID_WITH_GIVING_THANKS    = 'backer_email_pledge_paid_with_giving_thanks';
    const BACKER_NEW_BACKER_REGISTRATION           = 'backer_email_new_backer_registration';
    const BACKER_CAMPAIGN_POST_UPDATE              = 'backer_email_campaign_post_update';
    const BACKER_PAYMENT_UNSUCCESSFUL              = 'backer_email_payment_unsuccessful';
    const BACKER_PASSWORD_RESET_REQUEST            = 'backer_email_password_reset_request';
    const BACKER_PLEDGE_CANCELLED                  = 'backer_email_pledge_cancelled';
    const BACKER_REWARD_DELIVERED                  = 'backer_email_reward_delivered';
    const BACKER_CAMPAIGN_HALF_FUNDED              = 'backer_email_campaign_half_funded';

    const EMAIL_VERIFICATION                       = 'email_verification';
}
