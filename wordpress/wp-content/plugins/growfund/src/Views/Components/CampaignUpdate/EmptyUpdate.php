<?php

namespace Growfund\Views\Components\CampaignUpdate;

use Growfund\View;

defined( 'ABSPATH' ) || exit;

class EmptyUpdate extends View {

    protected function get_template_dir() {
        return 'site/components/campaign-update';
    }
}
