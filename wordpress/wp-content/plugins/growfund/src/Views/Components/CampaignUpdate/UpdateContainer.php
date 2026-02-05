<?php

namespace Growfund\Views\Components\CampaignUpdate;

use Growfund\DTO\CampaignPost\CampaignPostDTO;
use Growfund\View;

defined( 'ABSPATH' ) || exit;

class UpdateContainer extends View {

    /** @var string */
    public $campaign_id;

    /** @var CampaignPostDTO[] */
	public $updates = [];


    /** @var string */
    public $id;


    protected function get_template_dir() {
        return 'site/components/campaign-update';
    }

	protected function enqueue_scripts() {
       
        wp_enqueue_script(
			'growfund-update-container-scripts',
			GROWFUND_DIR_URL . 'resources/assets/site/scripts/components/campaign-update/update-container.js',
			['growfund-core'],
			GROWFUND_VERSION,
			true
        );
    }
    public $casts = [
        'updates.*' => CampaignPostDTO::class
    ];
}
