<?php
/**
 * @var Growfund\Views\Components\Campaign\CampaignFilter $campaign_filter
 */

use Growfund\Supports\Arr;
use Growfund\Views\Components\Form\Button;
use Growfund\Views\Components\Form\RadioField;
use Growfund\Views\Components\Form\SelectDropdown;
use Growfund\Views\Components\Form\TextField;

defined( 'ABSPATH' ) || exit;


$growfund_categories = Arr::make($campaign_filter->categories)
    ->map(function ($category) {
        return [
            'label' => $category['name'],
            'value' => $category['slug']
        ];
	})->toArray();

$growfund_sort_order = $campaign_filter->order === 'asc' ? 'desc' : 'asc';
$growfund_selected_order = !empty($campaign_filter->orderby) ? $campaign_filter->orderby . ':' . $growfund_sort_order : null;

$growfund_campaign_sort_options = [
	[
		'value' => 'created_date:asc',
		'label' => __('Created Date (A to Z)', 'growfund')
	],
    [
		'value' => 'created_date:desc',
		'label' => __('Created Date (Z to A)', 'growfund')
	],
	[
		'value' => 'start_date:asc',
		'label' => __('Start Date (A to Z)', 'growfund')
	],
    [
		'value' => 'start_date:desc',
		'label' => __('Start Date (Z to A)', 'growfund')
	],
	[
		'value' => 'end_date:asc',
		'label' => __('End Date (A to Z)', 'growfund')
    ],
    [
		'value' => 'end_date:desc',
		'label' => __('End Date (Z to A)', 'growfund')
	],
];

?>

<div class="growfund-campaign-filter">
    <div class="growfund-campaign-filter-wrapper"> 
        <?php
        $growfund_search_input = new TextField();
        $growfund_search_input->placeholder = __('Search Campaigns', 'growfund');
        $growfund_search_input->id = 'growfund_campaign_filter_search_input';
        $growfund_search_input->name = 'search';
        $growfund_search_input->value = $campaign_filter->search;
        $growfund_search_input->svg_icon = 'assets/site/icon/search.svg';
        $growfund_search_input->allow_clear = true;
        $growfund_search_input->style = "min-width: 250px;";
        $growfund_search_input->wrapper_style = "padding: 0 12px";


        growfund_render($growfund_search_input);
        ?>
        
        <div class="growfund-campaign-mobile-filter ">
            <?php
            $growfund_filter_button = new Button();
            $growfund_filter_button->classname = 'growfund-campaign-mobile-filter-button';
            $growfund_filter_button->svg_icon = 'assets/site/icon/filter.svg'; 
            growfund_render($growfund_filter_button);
            ?>
        </div>

        <div class="growfund-campaign-filter-dropdown">
            <?php
            $growfund_dropdown = new SelectDropdown();
            $growfund_dropdown->placeholder = __('Select Category', 'growfund');
            $growfund_dropdown->name = 'category_slug';
            $growfund_dropdown->options = $growfund_categories;
            $growfund_dropdown->id = 'growfund_campaign_filter_category_slug';
            $growfund_dropdown->default_value = $campaign_filter->category_slug;

            growfund_render($growfund_dropdown);
            ?>
        </div>
    
    </div>
    
    <div class="growfund-campaign-filter-dropdown">
        <?php
        $growfund_dropdown = new SelectDropdown();
        $growfund_dropdown->placeholder = __('Sort By', 'growfund');
        $growfund_dropdown->name = 'orderby';
        $growfund_dropdown->options = $growfund_campaign_sort_options;
        $growfund_dropdown->id = 'growfund_campaign_filter_sortby';
        $growfund_dropdown->default_value = $growfund_selected_order;

        growfund_render($growfund_dropdown);
        ?>
    </div> 
</div>

<div id="growfund-filter-modal" class="growfund-filter-modal">
    <div class="growfund-filter-modal-overlay"></div>
    <div class="growfund-filter-modal-content">
        <div class="growfund-filter-modal-header">
            <div class="growfund-filter-modal-header-left">
                <div class="growfund-filter-icon" style="color: currentColor; width: 20px; height: 20px;">
                    <svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18.3337 3H1.66699L8.33366 10.8833V16.3333L11.667 18V10.8833L18.3337 3Z" stroke="#4D4D4D" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </div>
                <h2 class="growfund-filter-modal-title">Filter</h2>
            </div>

            <div class="growfund-campaign-filter-modal-close-icon growfund-campaign-filter-modal-close" style="color: currentColor; width: 20px; height: 20px;">
                <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12.5676 4.30093C12.8071 4.06141 12.8071 3.67307 12.5676 3.43355C12.3281 3.19403 11.9397 3.19403 11.7003 3.43355L8.00058 7.13319L4.30094 3.43355C4.06141 3.19403 3.67307 3.19403 3.43355 3.43355C3.19403 3.67307 3.19403 4.06141 3.43355 4.30093L7.13319 8.00058L3.43355 11.7002C3.19403 11.9398 3.19403 12.3281 3.43355 12.5676C3.67307 12.8071 4.06141 12.8071 4.30094 12.5676L8.00058 8.86796L11.7003 12.5676C11.9397 12.8071 12.3281 12.8071 12.5676 12.5676C12.8071 12.3281 12.8071 11.9398 12.5676 11.7002L8.86796 8.00058L12.5676 4.30093Z" fill="#4D4D4D"></path>
                </svg>
            </div>
        </div>

        <div class="growfund-filter-modal-body">
            <div class="growfund-filter-modal-group">
                <?php 
    
                $growfund_filter_groups = [
                    'Categories' => [
                        'name' => 'category_slug',
                        'selected_value' => $campaign_filter->category_slug,
                        'options' => $growfund_categories
                    ],
                    'Sort By' => [
                        'name' => 'orderby',
                        'selected_value' => $growfund_selected_order,
                        'options' => $growfund_campaign_sort_options
                    ]
                ];

                foreach ($growfund_filter_groups as $growfund_filter_title => $growfund_filter_group) :
                    ?>
                    <div class="growfund-filter-modal-group-wrapper">
                        <h3 class="growfund-filter-modal-group-title">
                            <button type="button" class="growfund-mobile-filter-item-toggle">
                                <span class="growfund-mobile-filter-item-text"><?php echo esc_html($growfund_filter_title); ?></span>
                                <span class="growfund-mobile-filter-item-arrow">
                                    <div class="growfund-mobile-filter-arrow-down-icon">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M6.69087 9.89056C6.97205 9.6094 7.42792 9.6094 7.70911 9.89056L12 14.1815L16.2909 9.89056C16.572 9.6094 17.0278 9.6094 17.3091 9.89056C17.5902 10.1717 17.5902 10.6276 17.3091 10.9088L12.5091 15.7088C12.3741 15.8438 12.1909 15.9197 12 15.9197C11.809 15.9197 11.6259 15.8438 11.4909 15.7088L6.69087 10.9088C6.40968 10.6276 6.40968 10.1717 6.69087 9.89056Z" fill="#4D4D4D"></path>
                                        </svg>
                                    </div>
                                </span>
                            </button>
                        </h3>
                        
                        <div class="growfund-mobile-filter-group-content">
                            <ul class="growfund-mobile-filter-group-content-list" data-name="<?php echo esc_attr($growfund_filter_group['name']); ?>" data-selected-value="">
                                <?php foreach ($growfund_filter_group['options'] as $growfund_filter_option) : ?>
                                    <li class="growfund-mobile-filter-group-content-item">
                                        <?php
                                            $growfund_radio_field = new RadioField();
                                            $growfund_radio_field->name  = $growfund_filter_group['name']; 
                                            $growfund_radio_field->value = $growfund_filter_option['value'];
                                            $growfund_radio_field->label = $growfund_filter_option['label'];
                                            $growfund_radio_field->checked = $growfund_filter_option['value'] === $growfund_filter_group['selected_value'];
                                            growfund_render($growfund_radio_field); 
										?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="growfund-filter-modal-footer">
                <button type="button" class="growfund-campaign-filter-modal-clear-button">
                    <?php echo esc_html__('Clear All', 'growfund'); ?>
                </button>
                <button type="button" class="growfund-campaign-filter-modal-apply-button">
                    <?php echo esc_html__('Apply', 'growfund'); ?>
                </button>
            </div>
    
        </div> 
    </div>
</div>
