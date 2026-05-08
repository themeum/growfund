<?php 

namespace Growfund\Views\Pages;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Campaign\AppreciationType;
use Growfund\Constants\Comment\CampaignUpdateVisibility;
use Growfund\Constants\Comment\CommentVisibility;
use Growfund\Core\AppSettings;
use Growfund\DTO\Campaign\CampaignDTO;
use Growfund\View;
use Growfund\DTO\RewardDTO;
use Growfund\DTO\Pledge\PledgeDTO;
use Growfund\DTO\Donation\DonationDTO;
use Growfund\DTO\User\UserInfoDTO;
use Growfund\Services\PledgeService;
use Growfund\Supports\Arr;
use Growfund\Views\Components\Campaign\Tabs\CampaignContent;
use Growfund\Views\Components\Campaign\Tabs\CommentContent;
use Growfund\Views\Components\Campaign\Tabs\FaqContent;
use Growfund\Views\Components\Campaign\Tabs\RewardContent;
use Growfund\Views\Components\Campaign\Tabs\UpdateContent;

class CampaignSinglePage extends View {
    /** @var CampaignDTO */
    public $campaign;

    /** @var CampaignDTO[] */
    public $recommended_campaigns;

    /** @var UserInfoDTO[] */
    public $collaborators;

    /** @var UserInfoDTO|null */
    public $fundraiser;

    /** @var RewardDTO[] */
    public $rewards;

    /** @var bool */
    public $has_toaster = false;

    /** @var PledgeDTO */
    public $pledge;

    /** @var DonationDTO */
    public $donation;

    protected function get_template_dir() {
        return 'site/pages';
    }
    protected function enqueue_scripts()
    {
        $script_url = GROWFUND_DIR_URL . 'resources/assets/site/scripts/pages/campaign-single-page.js';
        wp_enqueue_script('growfund-campaign-single-page-script', $script_url, ['growfund-core'], GROWFUND_VERSION, true);

        $script_url = GROWFUND_DIR_URL . 'resources/assets/site/scripts/components/donors/donor-list.js';
        wp_enqueue_script('growfund-donor-list-script', $script_url, ['growfund-campaign-single-page-script'], GROWFUND_VERSION, true);
    }

    protected function enqueue_styles()
    {
        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/pages/campaign-single-page.css';

        wp_enqueue_style(
            'growfund-campaign-filters-styles',
            $main_styles_url,
            ['growfund-main-styles'],
            GROWFUND_VERSION
        );
    }

    protected function get_casts()
    {
        return [
            'campaign' => CampaignDTO::class,
            'recommended_campaigns.*' => CampaignDTO::class,
            'collaborators.*' => UserInfoDTO::class,
            'author' => UserInfoDTO::class,
            'rewards.*' => RewardDTO::class,
            'pledge' => PledgeDTO::class,
            'donation' => DonationDTO::class
        ];
    }

    /**
     * Check if pledge is allowed when pledge without reward is not allowed
     * 
     * @return bool
     */
    public function is_pledge_allowed_without_reward() {
        if (growfund_app()->is_donation_mode()) {
            return false;
        }

        $pledge_service = new PledgeService();

        if (
            $this->campaign->appreciation_type === AppreciationType::GOODIES 
            && !$this->campaign->allow_pledge_without_reward
        ) {
            return Arr::make($this->rewards ?? [])->some(function ($reward) use ($pledge_service) {
                return $pledge_service->check_reward_constraints($reward, false); 
            });
		}

        return true;
    }

    /**
     * Check if pledge is allowed for this campaign
     * 
     * @return bool
     */
    public function is_pledge_allowed_for_this_campaign() {
        $pledge_service = new PledgeService();

        return !growfund_app()->is_donation_mode() && $pledge_service->check_campaign_constraints($this->campaign, false);
    }

    /**
     * Check if donation is allowed for this campaign
     * 
     * @return bool
     */
    public function is_donation_allowed_for_this_campaign() {
        $donation_service = new PledgeService();

        return growfund_app()->is_donation_mode() && $donation_service->check_campaign_constraints($this->campaign, false);
    }

    /**
     * Get all tab contents
     */
    public function get_tab_contents() {
        return Arr::make([
			[
				'key' => 'campaign',
				'label' => growfund_app()->is_donation_mode() ? __('Info', 'growfund') : __('Campaign', 'growfund'),
			],
			[
				'key' => 'rewards',
				'label' => __('Rewards', 'growfund'),
			],
			[
				'key' => 'faq',
				'label' => __('FAQ', 'growfund'),
			],
			[
				'key' => 'updates',
				'label' => __('Updates', 'growfund'),
			],
			[
				'key' => 'comments',
				'label' => __('Comments', 'growfund'),
			],
		])->filter(function ($tab) {
            if ($tab['key'] === 'rewards') {
                if (growfund_app()->is_donation_mode()) {
                    return false;
                }

                if (empty($this->rewards)) {
                    return false;
                }
			}

            if ($tab['key'] === 'updates') {
                switch (growfund_settings(AppSettings::CAMPAIGNS)->campaign_update_visibility()) {
                    case CampaignUpdateVisibility::LOGGED_IN_USER:
                        if (!growfund_user()->is_logged_in()) {
                            return false;
                        }
                        break;
                    case CampaignUpdateVisibility::CONTRIBUTORS:
                        if (!growfund_user()->can_contribute()) {
                            return false;
                        }
                        break;
                    default:
                        return true;
                }
			}

            if ($tab['key'] === 'comments') {
                if (!growfund_settings(AppSettings::CAMPAIGNS)->allow_comments()) {
                    return false;
                }

				switch (growfund_settings(AppSettings::CAMPAIGNS)->comment_visibility()) {
                    case CommentVisibility::LOGGED_IN_USER:
                        if (!growfund_user()->is_logged_in()) {
                            return false;
                        }
                        break;
                    case CommentVisibility::CONTRIBUTORS:
                        if (!growfund_user()->can_contribute()) {
                            return false;
                        }
                        break;
                    default:
                        return true;
                        
                }
            }

            if ($tab['key'] === 'faq' && empty($this->campaign->faqs)) {
                return false;
			}

            return true;
		})->map(function($tab) {
            switch ($tab['key']) {
                case 'campaign':
                    $campaign_tab = new CampaignContent();
					$campaign_tab->campaign = $this->campaign;
					$campaign_tab->rewards = $this->rewards;
                    $tab['content'] = growfund_get_html($campaign_tab);
                    break;
                case 'rewards':
                    $rewards_tab = new RewardContent();
					$rewards_tab->campaign = $this->campaign;
					$rewards_tab->rewards = $this->rewards;
                    $tab['content'] = growfund_get_html($rewards_tab);
                    break;
                case 'faq':
                    $faqs_tab = new FaqContent();
					$faqs_tab->faqs = $this->campaign->faqs;
                    $tab['content'] = growfund_get_html($faqs_tab);
                    break;
                case 'updates':
                    $updates_tab = new UpdateContent();
					$updates_tab->campaign_id = $this->campaign->id;
                    $tab['content'] = growfund_get_html($updates_tab);
                    break;
                case 'comments':
                    $comment_tab = new CommentContent();
					$comment_tab->campaign_id = $this->campaign->id;
					$comment_tab->has_faqs = !empty($this->campaign->faqs);
                    $tab['content'] = growfund_get_html($comment_tab);
                    break;
				default:
                    $tab['content'] = '';
                    break;
			}

            return $tab;
		})->toArray();
    }
}
