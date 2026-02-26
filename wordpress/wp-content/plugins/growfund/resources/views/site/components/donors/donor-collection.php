<?php
/**
 * @var Growfund\Views\Components\Donors\DonorCollection $donor_collection
 */

use Growfund\Views\Components\Donors\DonorItem;

defined('ABSPATH') || exit;
?>

<?php if (!empty($donor_collection->donors)) : ?>
        <?php foreach ($donor_collection->donors as $growfund_donor) : ?>
            <?php
            $growfund_donor_item = new DonorItem();
            $growfund_donor_item->donor = $growfund_donor;
			growfund_render($growfund_donor_item);
            ?>
        <?php endforeach; ?>
		<?php
        endif; 
