<?php

namespace Growfund\Views\Components\Donors;

use Growfund\Constants\Contributor\DisplayOptionOrderBy;
use Growfund\DTO\Campaign\CampaignDTO;
use Growfund\DTO\Donor\DonorDisplayDTO;
use Growfund\View;

defined('ABSPATH') || exit;

class DonorModal extends View
{
    /** * The array of donation 
     * @var DonorDisplayDTO[] 
     */

    public $donors;

    /** @var CampaignDTO */

    public $campaign;

    /** * The title of the modal
     * @var string 
     */
    
    public $title;

    /** @var bool Whether to show newest/top tabs */
    public $show_tabs = false;

    /** @var string Optional: top/recent display order */
    public $display_order = DisplayOptionOrderBy::TOP_AND_RECENT;

    public $casts = [
        'campaign' => CampaignDTO::class,
	];



	protected function get_template_dir()
	{
		return 'site/components/donors';
	}

	/**
	 * Enqueue styles specifically for the modal content layout
	 */
	protected function enqueue_styles()
	{
		wp_enqueue_style(
			'growfund-donor-modal-styles',
			GROWFUND_DIR_URL . 'resources/assets/site/styles/components/donors/donor-modal.css',
			['growfund-main-styles'],
			GROWFUND_VERSION
		);
	}
	protected function enqueue_scripts()
	{
		$script_url = GROWFUND_DIR_URL . 'resources/assets/site/scripts/components/donors/donor-modal.js';
		wp_enqueue_script('growfund-donor-modal-script', $script_url, ['growfund-core'], GROWFUND_VERSION, true);
	}
}
