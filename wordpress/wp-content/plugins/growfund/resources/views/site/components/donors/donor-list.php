<?php
/**
 * @var Growfund\Views\Components\Donors\DonorList $donor_list
 */

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Contributor\DisplayOptionOrderBy;
use Growfund\Core\AppSettings;
use Growfund\Views\Components\Donors\DonorModal;
use Growfund\Views\Components\Form\Button;
use Growfund\Views\Components\UI\Modal;


$growfund_show_button = false;
$growfund_button_label = '';
$growfund_modal_title = __('Donations', 'growfund');
$growfund_show_tabs = false;

switch (growfund_settings(AppSettings::CAMPAIGNS)->display_contributor_option_order_by() ) {
    case DisplayOptionOrderBy::TOP_AND_RECENT:
        $growfund_show_button = true;
        $growfund_button_label = __('See all', 'growfund');
        $growfund_show_tabs = true;
        $growfund_modal_title = __('Donations', 'growfund');
        break;

    case DisplayOptionOrderBy::TOP_ONLY:
        $growfund_show_button = true;
        $growfund_button_label = __('See top', 'growfund');
        $growfund_modal_title = __('Top Donations', 'growfund');
        break;

    case DisplayOptionOrderBy::RECENT_ONLY:
        $growfund_show_button = true;
        $growfund_button_label = __('See recent', 'growfund');
        $growfund_modal_title = __('Recent Donations', 'growfund');
        break;
}

?>

<div class="growfund-donor-list-wrapper">
  
    <div class="growfund-donor-list" id="growfund_donor_list">
    </div>

<?php if ($growfund_show_button) : ?>
    <div class="growfund-donor-list-actions">
        <?php 
            $growfund_see_btn = new Button();
            $growfund_see_btn->label = $growfund_button_label;
            $growfund_see_btn->classname = "growfund-donor-list-button";
            growfund_render($growfund_see_btn);
        ?>
    </div>
<?php endif; ?>

    <?php
    $growfund_donor_modal_content = new DonorModal();
    $growfund_donor_modal_content->donors = $donor_list->donors;
    $growfund_donor_modal_content->show_tabs = $growfund_show_tabs; 
    $growfund_donor_modal_content->display_order = growfund_settings(AppSettings::CAMPAIGNS)->display_contributor_option_order_by();
    $growfund_donor_modal_content->campaign = $donor_list->campaign;

	$growfund_donor_modal = new Modal();
	$growfund_donor_modal->id = 'growfund_donor_list_modal';
	$growfund_donor_modal->classname = 'growfund-donor-modal';
    $growfund_donor_modal->title = $growfund_modal_title;
    $growfund_donor_modal->show_header_icon = false;
	$growfund_donor_modal->body_content = growfund_get_html($growfund_donor_modal_content);


	growfund_render($growfund_donor_modal);
	?>
</div>