<?php
/**
 * @var Growfund\Views\Components\Donors\DonorModal $donor_modal
 */

use Growfund\Constants\Contributor\DisplayOptionOrderBy;
use Growfund\Core\AppSettings;
use Growfund\Supports\Utils;
use Growfund\Views\Components\Form\Button;
use Growfund\Views\Components\UI\InfiniteScroll;

defined( 'ABSPATH' ) || exit;

?>
<div class="growfund-donor-modal-wrapper">
<?php
if (!empty($donor_modal->show_tabs) && $donor_modal->show_tabs) :
	?>
    <div class="growfund-donor-modal-actions">
        <?php 
            $growfund_modal_newest_btn = new Button();
            $growfund_modal_newest_btn->label = __('Newest', 'growfund');
            $growfund_modal_newest_btn->classname = "growfund-donor-modal-newest-button selected";

            growfund_render($growfund_modal_newest_btn);

            $growfund_modal_top_btn = new Button();
            $growfund_modal_top_btn->label = __('Top', 'growfund');
            $growfund_modal_top_btn->classname = "growfund-donor-modal-top-button";
            
            growfund_render($growfund_modal_top_btn);
        ?>
   
    </div>
    <?php endif; ?>
    <div class="growfund-donor-modal-collection"
        data-campaign-id="<?php echo esc_attr($donor_modal->campaign->id); ?>"
        data-sort-key="<?php echo esc_attr(growfund_settings(AppSettings::CAMPAIGNS)->display_contributor_option_order_by()); ?>"
        data-limit="<?php echo esc_attr(growfund_settings(AppSettings::CAMPAIGNS)->display_contributor_option_limit()); ?>">
                
        <div class="growfund-modal-donor-list" id="growfund_modal_donor_list"></div>

        <?php 
            $growfund_infinite_scroll = new InfiniteScroll();
            $growfund_infinite_scroll->id = 'growfund-donor-modal_infinite_scroll';
            growfund_render($growfund_infinite_scroll);
        ?>
    </div>
    
    <?php 
        $growfund_modal_donation_btn = new Button();
        $growfund_modal_donation_btn->label = __('Donate Now', 'growfund');
        $growfund_modal_donation_btn->has_link = true;
        $growfund_modal_donation_btn->href = Utils::get_checkout_url($donor_modal->campaign->id ?? 0);
        $growfund_modal_donation_btn->classname = "growfund-donor-modal-donation-button";
        growfund_render($growfund_modal_donation_btn);
    ?>
    

</div>