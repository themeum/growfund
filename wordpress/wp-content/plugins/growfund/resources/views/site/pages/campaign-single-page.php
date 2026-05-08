<?php
/**
 * @var Growfund\Views\Pages\CampaignSinglePage $campaign_single_page
 */

defined( 'ABSPATH' ) || exit;

use Growfund\App\Settings\CampaignSettings;
use Growfund\Constants\Campaign\ReachingAction;
use Growfund\Constants\Contributor\DisplayOptionOrderBy;
use Growfund\Constants\Status\CampaignStatus;
use Growfund\Core\AppSettings;
use Growfund\Supports\Arr;
use Growfund\Supports\CampaignGoal;
use Growfund\Supports\Currency;
use Growfund\Supports\Terms;
use Growfund\Supports\Utils;
use Growfund\Taxonomies\Tag;
use Growfund\Views\Components\Campaign\CampaignBookmarkButton;
use Growfund\Views\Components\Campaign\CampaignCard;
use Growfund\Views\Components\Campaign\CampaignCreatorCard;
use Growfund\Views\Components\Campaign\CampaignPledgeModal;
use Growfund\Views\Components\Form\Button;
use Growfund\Views\Components\UI\Countdown;
use Growfund\Views\Components\UI\Avatar;
use Growfund\Views\Components\UI\MediaSlider;
use Growfund\Views\Components\UI\Modal;
use Growfund\Views\Components\UI\SocialShare;
use Growfund\Views\Components\UI\Tab;
use Growfund\Views\Components\CheckoutFailedToaster;
use Growfund\Views\Components\CheckoutSuccessfulToaster;
use Growfund\Views\Components\Donors\DonorList;
use Growfund\Views\Components\PaymentSuccessToaster;
use Growfund\Views\Components\UI\Badge;

$growfund_campaign_goal_percentage = CampaignGoal::goal_achieved_percentage($campaign_single_page->campaign);
$growfund_campaign_goal_info = CampaignGoal::get_goal_info_for_display($campaign_single_page->campaign);

$growfund_campaign_collaborator_count = count($campaign_single_page->collaborators);
$growfund_avatars_in_view = 1 + min($growfund_campaign_collaborator_count, 2);
$growfund_dynamic_stack_width = 51 + (($growfund_avatars_in_view - 1) * 20);

$campaign_settings = new CampaignSettings();

?>

<div class="growfund-campaign-single-page">
    <div class="growfund-campaign-single-page-header">
        <div class="growfund-campaign-single-page-left-content-wrapper">
            <h1 class="growfund-campaign-single-page-title"><?php echo esc_html($campaign_single_page->campaign->title); ?></h1>
            <p class="growfund-campaign-single-page-description"><?php echo esc_html($campaign_single_page->campaign->description); ?></p>
       
            <?php
            $growfund_media_slider = new MediaSlider();
            $growfund_media_slider->images = $campaign_single_page->campaign->images;
            $growfund_media_slider->video = $campaign_single_page->campaign->video;

            growfund_render($growfund_media_slider);
            ?>

            <div class="growfund-campaign-single-page-tags-social-share">
                <div class="growfund-campaign-single-page-tags">
                    <?php if (!empty($campaign_single_page->campaign->tags)) : ?>
                        <?php 
                            $growfund_campaign_tags = Terms::get_terms((int) $campaign_single_page->campaign->id, Tag::NAME);
                            $growfund_campaign_tag_names = Arr::make($growfund_campaign_tags ?? [])->pluck('name')->join(', ');
                        ?>
                        <?php growfund_echo_safe_html($campaign_single_page->get_svg_icon('assets/site/icon/tags.svg')); ?>
                        <span><?php echo esc_html($growfund_campaign_tag_names); ?></span>
                    <?php endif; ?>
                </div>
                <div class="growfund-campaign-single-page-social-share">
                    <?php 
                        $growfund_social_share = new SocialShare();
                        $growfund_social_share->title = $campaign_single_page->campaign->title;
                        $growfund_social_share->label = __('Share:', 'growfund');
                        $growfund_social_share->url = growfund_campaign_url($campaign_single_page->campaign->slug);

                        growfund_render($growfund_social_share);
                    ?>
                </div>
            </div>
        </div>

        <div class="growfund-campaign-single-page-right-content-wrapper">
            <div class="growfund-campaign-single-page-right-content-raised-header">
                <h2 class="growfund-campaign-single-page-right-content-raised-amount"><?php echo esc_html(Currency::format($campaign_single_page->campaign->fund_raised ?? 0)); ?></h2>
                <div class="growfund-campaign-single-page-right-content-raised-amount-progress-bar">
                    <div 
                        class="growfund-campaign-single-page-right-content-raised-amount-progress-fill" 
                        style="width: <?php echo esc_attr($growfund_campaign_goal_percentage); ?>%;"
                    ></div>
                </div>
                <p class="growfund-campaign-single-page-right-content-backer-count">
                    <strong><?php echo esc_html($campaign_single_page->campaign->number_of_contributors ?? 0); ?></strong>
                    <?php
                    echo growfund_app()->is_donation_mode() 
                        ? esc_html(_n('donor has contributed', 'donors have contributed', $campaign_single_page->campaign->number_of_contributors, 'growfund'))
                        : esc_html(_n('backer has contributed', 'backers have contributed', $campaign_single_page->campaign->number_of_contributors, 'growfund'))
                    ?>
                </p>
            </div>

            <?php if (!empty($campaign_single_page->campaign->end_date)) : ?>
                <div class="growfund-campaign-single-page-right-content-countdown-container">
                    <?php
                    $growfund_countdown = new Countdown();
                    $growfund_countdown->end_date = $campaign_single_page->campaign->end_date;

                    growfund_render($growfund_countdown);
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="growfund-campaign-single-page-right-content-back-button-wrapper">
                <?php if ($campaign_single_page->campaign->is_ended) : ?>
                    <?php 
                        $growfund_badge = new Badge();
                        $growfund_badge->message = $campaign_single_page->campaign->status === CampaignStatus::FUNDED 
                            ? __('Campaign is funded', 'growfund') 
                            : __('Campaign is ended', 'growfund');
                        $growfund_badge->variant = $campaign_single_page->campaign->status === CampaignStatus::FUNDED ? "success" : "warning";

                        growfund_render($growfund_badge);
                    ?>
					<?php
                    else :

                        if (
                            $campaign_single_page->campaign->reaching_action === ReachingAction::CONTINUE 
                            && $campaign_single_page->campaign->status === CampaignStatus::FUNDED
                        ) {
                            $growfund_badge = new Badge();
                            $growfund_badge->message = __('Campaign is funded', 'growfund');
                            $growfund_badge->variant = "info";

                            growfund_render($growfund_badge);
                        }

                        $growfund_campaign_checkout_button = new Button();
                        $growfund_campaign_checkout_button->classname = 'growfund-campaign-single-page-right-content-back-button growfund-campaign-contribution-button growfund-branding-btn';

                        if (growfund_app()->is_donation_mode()) {
                            $growfund_campaign_checkout_button->label = __('Donate Now', 'growfund');
                            $growfund_campaign_checkout_button->has_link = true;
                            $growfund_campaign_checkout_button->href = Utils::get_checkout_url($campaign_single_page->campaign->id);
                            $growfund_campaign_checkout_button->disabled = !$campaign_single_page->is_donation_allowed_for_this_campaign();
                        } else {
                            $growfund_campaign_checkout_button->label = __('Back this campaign', 'growfund');

                            $growfund_campaign_checkout_button->disabled = !$campaign_single_page->is_pledge_allowed_for_this_campaign()
                            || !$campaign_single_page->is_pledge_allowed_without_reward();
                        }

                        growfund_render($growfund_campaign_checkout_button);

                        $growfund_bookmark_button = new CampaignBookmarkButton();
                        $growfund_bookmark_button->campaign = $campaign_single_page->campaign;
                        $growfund_bookmark_button->id = 'growfund_campaign_single_page_bookmark_button';
                        $growfund_bookmark_button->classname = 'growfund-campaign-single-page-right-content-bookmark-button';

                        growfund_render($growfund_bookmark_button);
                    endif;
                    ?>
            </div>

            <?php if ($campaign_settings->display_contributor_list_publicly() && growfund_app()->is_donation_mode() && $campaign_single_page->campaign->number_of_contributions > 0) : ?>

                <div class="growfund-campaign-single-page-right-content-donor-list-container"
                data-campaign-id="<?php echo esc_attr($campaign_single_page->campaign->id); ?>"
                data-sort-key="<?php echo esc_attr(growfund_settings(AppSettings::CAMPAIGNS)->display_contributor_option_order_by()); ?>"
                data-limit="<?php echo esc_attr(growfund_settings(AppSettings::CAMPAIGNS)->display_contributor_option_limit()); ?>">
				<?php
				$growfund_donor_list = new DonorList();
				$growfund_donor_list->campaign = $campaign_single_page->campaign;
				growfund_render($growfund_donor_list);
				?>
                </div>
			<?php endif; ?>

            <div class="growfund-campaign-single-page-right-content-info">
                <?php if ($campaign_single_page->campaign->has_goal && !$campaign_single_page->campaign->is_ended) : ?>
                    <p class="growfund-campaign-single-page-right-content-funding-type" id="growfund_single_page_notice">
                        <span class="growfund-campaign-single-page-right-content-info-bold">
                            <?php echo esc_html__('All or nothing.', 'growfund'); ?>
                        </span> 
                        <span>
                            <?php
                            echo esc_html(
                                $campaign_single_page->campaign->reaching_action === ReachingAction::CLOSE 
                                    ? __('This campaign will only be funded if it reaches its goal by', 'growfund') 
                                    : __('This campaign will be ended by', 'growfund')
                                );
							?>
                        </span>
                        <span data-growfund-datetime="<?php echo esc_attr($campaign_single_page->campaign->end_date); ?>"></span>
                    </p>
                <?php endif; ?>
                <div class="growfund-campaign-single-page-right-content-location-wrapper">
                    <span class="growfund-campaign-single-page-right-content-location-icon">
                        
                    <?php growfund_echo_safe_html( $campaign_single_page->get_svg_icon('assets/site/icon/location.svg') ); ?>
                
                    </span>
                    <span class="growfund-campaign-single-page-right-content-location-text">
                        <?php
                        echo esc_html(
                            $campaign_single_page->campaign->location 
                                ? growfund_pretty_location($campaign_single_page->campaign->location)
                                : __('Not specified', 'growfund')
                            )
						?>
                    </span>
                </div>
            </div>
            <div class="growfund-campaign-single-page-right-content-creator-list-container">
                <div class="growfund-campaign-single-page-right-content-creator-list-collapsed">
                    <div class="growfund-campaign-single-page-right-content-creator-list-image-stack" style="width: <?php echo esc_attr($growfund_dynamic_stack_width); ?>px;">
                        <?php if (!empty($campaign_single_page->fundraiser)) : ?>
                            <div
                                class="growfund-campaign-single-page-right-content-creator-list-stack-item"
                                style=" z-index:<?php echo esc_attr($growfund_campaign_collaborator_count); ?>;"
                            >
                                    <?php
                                    $growfund_avatar = new Avatar();
                                
                                    $growfund_avatar->avatar_name = $campaign_single_page->fundraiser->display_name ?? ''; 
                                    $growfund_avatar->use_acronym = true; 
                                    $growfund_avatar->src = $campaign_single_page->fundraiser->image['url'] ?? '';
                                    $growfund_avatar->classname = 'growfund-single-page-creator-avatar';

                                    growfund_render($growfund_avatar);
                                    ?>
                            
                            </div>
                        <?php endif; ?>
                        <?php foreach (array_slice($campaign_single_page->collaborators, 0, 2) as $growfund_index => $growfund_collaborator) : ?>
                            <div
                                class="growfund-campaign-single-page-right-content-creator-list-stack-item"
                                style="left:<?php echo esc_attr(($growfund_index + 1) * 20); ?>px; z-index:<?php echo esc_attr($growfund_campaign_collaborator_count - ($growfund_index + 1)); ?>;"
                            >
                                <?php
                                $growfund_avatar = new Avatar();
                            
                                $growfund_avatar->avatar_name = $growfund_collaborator->display_name; 
                                $growfund_avatar->use_acronym = true; 
                                $growfund_avatar->src = $growfund_collaborator->image['url'] ?? '';
                                $growfund_avatar->classname = 'growfund-single-page-creator-avatar';

                                growfund_render($growfund_avatar);
                                ?>
                        
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="growfund-campaign-single-page-right-content-creator-list-info">
                        <p class="growfund-campaign-single-page-right-content-creator-list-names">
                            <?php
                            if ($growfund_campaign_collaborator_count > 0) {
                                if ($growfund_campaign_collaborator_count > 1) {
                                    printf(
                                    /* translators: 1: creator name, 2: collaborator display name, 3: number of extra collaborators */
                                    esc_html__('%1$s, %2$s & +%3$d more', 'growfund'),
                                    esc_html($campaign_single_page->author->display_name ?? ''), 
                                    esc_html($campaign_single_page->collaborators[0]->display_name ?? ''), 
                                    esc_html($growfund_campaign_collaborator_count - 1)
									);
                                } else {
                                    printf(
                                    /* translators: 1: creator name, 2: number of extra collaborators */
                                    esc_html__('%1$s & +%2$d more', 'growfund'),
                                    esc_html($campaign_single_page->author->display_name ?? ''), 
                                    esc_html($growfund_campaign_collaborator_count)
                                    );
                                }
								
                            } else {
                                echo esc_html($campaign_single_page->author->display_name ?? '');
                            }
                                
							?>
                        </p>

                        <?php if ($campaign_single_page->campaign->show_collaborator_list) : ?>
                            <button
                                type="button"
                                class="growfund-campaign-single-page-right-content-creator-list-show-all-toggle-btn"
                                data-action="show-all"
                            >
                                <?php echo esc_html__('Show All Creators', 'growfund'); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="growfund-campaign-single-page-right-content-creator-list">
                    <div class="growfund-campaign-single-page-right-content-creator-list-expanded">
                        <?php  
                        if (!empty($campaign_single_page->fundraiser)) {
                            $growfund_creator = new CampaignCreatorCard();
                            $growfund_creator->display_name = $campaign_single_page->fundraiser->display_name ?? '';
                            $growfund_creator->avatar_class = 'growfund-single-page-creator-avatar';
                            $growfund_creator->avatar_src = $campaign_single_page->fundraiser->image['url'] ?? '';
    
                            growfund_render($growfund_creator);
                        }

                        if ($growfund_campaign_collaborator_count > 0) {
                            foreach ($campaign_single_page->collaborators as $growfund_collaborator) {
                                $growfund_creator = new CampaignCreatorCard();
								$growfund_creator->display_name = $growfund_collaborator->display_name;
								$growfund_creator->avatar_class = 'growfund-single-page-creator-avatar';
								$growfund_creator->avatar_src = $growfund_collaborator->image['url'] ?? '';
                                
								growfund_render($growfund_creator);
                            }
                            
                        }
                        ?>

                        <?php if ($campaign_single_page->campaign->show_collaborator_list) : ?>
                            <button type="button" class="growfund-campaign-single-page-right-content-creator-list-show-less-toggle-btn" data-action="show-less">
                                <?php echo esc_html__('Show Less', 'growfund'); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="growfund-campaign-single-page-middle-section">
        <?php
		$growfund_tab_contents = new Tab();
        $growfund_tab_contents->id = "growfund_campaign_single_page_tabs";
		$growfund_tab_contents->tabs = $campaign_single_page->get_tab_contents();

        if (!$campaign_single_page->campaign->is_ended) {
            $growfund_tab_contents->allow_header_button = true;
            $growfund_tab_contents->header_button = $growfund_campaign_checkout_button;
        }

		growfund_render($growfund_tab_contents);
		?>

    </div>
    <?php if (!empty($campaign_single_page->recommended_campaigns)) : ?>
        <section class="growfund-campaign-single-page-growfund-recommendations">
            <div class="growfund-campaign-single-page-growfund-recommendations-header">
                <h2 class="growfund-campaign-single-page-growfund-recommendations-title">
                    <?php echo esc_html__('We also recommend', 'growfund'); ?>
                </h2>
                <a
                    href="<?php echo esc_url(growfund_campaign_archive_url()); ?>"
                    class="growfund-campaign-single-page-growfund-recommendations-explore"
                >
                    <?php echo esc_html__('Explore more', 'growfund'); ?>
                    <span class="growfund-campaign-single-page-growfund-explore-icon">
                        <?php growfund_echo_safe_html($campaign_single_page->get_svg_icon('assets/site/icon/arrow-right-circle.svg')); ?>
                    </span>
                </a>
            </div>
            <div class="growfund-campaign-single-page-growfund-recommendations-grid">
                    <?php
					foreach ($campaign_single_page->recommended_campaigns as $growfund_recommended_campaign) {
						$growfund_recommended_campaign_card = new CampaignCard();
                        $growfund_recommended_campaign_card->campaign = $growfund_recommended_campaign;
                        growfund_render($growfund_recommended_campaign_card);
					}
                        
                    ?>
            
            </div>
        </section>
    <?php endif; ?>

    <?php
	if (!growfund_app()->is_donation_mode() && $campaign_single_page->campaign->is_launched && !$campaign_single_page->campaign->is_ended) {
		$growfund_modal = new Modal();
        $growfund_modal->id = 'growfund_pledge_modal';
        $growfund_modal->title = __('Back This Campaign', 'growfund');
        $growfund_modal->classname = 'growfund-campaign-single-page-back-campaign-modal';
        $growfund_modal->show_footer = false;
        $growfund_modal->header_icon = 'assets/site/icon/gift.svg';
        
        $growfund_pledge = new CampaignPledgeModal();
        $growfund_pledge->campaign = $campaign_single_page->campaign;
        $growfund_pledge->rewards = $campaign_single_page->rewards;

        $growfund_modal->body_content = growfund_get_html($growfund_pledge);

		growfund_render($growfund_modal);
	}
        
	?>


<?php

if ($campaign_single_page->has_toaster) {
	if (growfund_app()->is_donation_mode()) {
		$growfund_donation_toaster = new Modal();
		$growfund_donation_toaster->classname = 'is-active';
		$growfund_donation_toaster->show_footer = false;
		$growfund_donation_toaster->show_header = false;

        if (!empty($campaign_single_page->donation)) {
			$growfund_donation_success = new PaymentSuccessToaster();
			$growfund_donation_success->donation = $campaign_single_page->donation;
			$growfund_donation_toaster->body_content = growfund_get_html($growfund_donation_success);
        } else {
            $growfund_failed_toaster = new CheckoutFailedToaster();
            $growfund_failed_toaster->campaign_id = $campaign_single_page->campaign->id;
            $growfund_donation_toaster->body_content = growfund_get_html($growfund_failed_toaster);
        }

		growfund_render($growfund_donation_toaster);
	} else {
		$growfund_pledge_toaster = new Modal();
		$growfund_pledge_toaster->classname = 'is-active';
		$growfund_pledge_toaster->show_footer = false;
		$growfund_pledge_toaster->show_header = false;

        if (!empty($campaign_single_page->pledge)) {
			$growfund_pledge_success = new CheckoutSuccessfulToaster();
			$growfund_pledge_success->pledge = $campaign_single_page->pledge;
			$growfund_pledge_toaster->body_content = growfund_get_html($growfund_pledge_success);
        } else {
            $growfund_failed_toaster = new CheckoutFailedToaster();
            $growfund_failed_toaster->campaign_id = $campaign_single_page->campaign->id;
            $growfund_pledge_toaster->body_content = growfund_get_html($growfund_failed_toaster);
        }

		growfund_render($growfund_pledge_toaster);
    }
}


    
?>

</div>
    