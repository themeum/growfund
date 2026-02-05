<?php

namespace Growfund\Views\Components\Campaign;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\AppreciationType;
use Growfund\View;
use Growfund\DTO\RewardDTO;
use Growfund\DTO\Campaign\CampaignDTO;
use Growfund\Services\PledgeService;
use Growfund\Supports\Arr;

class RewardPledgeButton extends View {

    /** @var RewardDTO */
    public $reward;

    /** @var CampaignDTO */
    public $campaign;

    /** @var string  */
    public $classname;

    protected function get_template_dir() {
        return 'site/components/campaign';
    }

	protected function enqueue_styles() {
        wp_enqueue_style(
			'growfund-pledge-button-styles',
			GROWFUND_DIR_URL . 'resources/assets/site/styles/components/campaign/reward-pledge-button.css',
			['growfund-main-styles'],
			GROWFUND_VERSION
        );
    }

    public $casts = [
        'reward' => RewardDTO::class,
        'campaign' => CampaignDTO::class
    ];

    /**
     * Check if pledge is allowed when pledge without reward is not allowed
     * 
     * @return bool
     */
    public function is_pledge_allowed_without_reward() {
        $pledge_service = new PledgeService();

        if (
            $this->campaign->appreciation_type === AppreciationType::GOODIES 
            && !$this->campaign->allow_pledge_without_reward
        ) {
            return $pledge_service->check_reward_constraints($this->reward, false);
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

        return $pledge_service->check_campaign_constraints($this->campaign, false);
    }
}
