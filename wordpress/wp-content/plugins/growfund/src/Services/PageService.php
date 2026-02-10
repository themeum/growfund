<?php

namespace Growfund\Services;

defined( 'ABSPATH' ) || exit;

use Exception;
use Growfund\Constants\AppConfigKeys;
use Growfund\Core\AppSettings;
use Growfund\DTO\Page\PageListItemDTO;
use Growfund\Supports\Arr;
use Growfund\Supports\Option;
use Growfund\Supports\PostMeta;
use WP_Post;

class PageService
{
    /**
     * Returns a list of all published pages sorted by menu order and post title.
     * 
     * @param array $args Optional arguments.
     *
     * @return PageListItemDTO[]
     */
    public function all(array $args = [])
    {
        $args = array_merge([
            'post_type'   => 'page',
            'post_status' => 'publish',
            'sort_column' => 'ID',
            'sort_order'  => 'asc',
        ], $args);

        $pages = get_pages($args);

        $result = [];

		foreach ($pages as $page) {
			$result[] = $this->format_data($page);
		}

        return $result;
	}

    /**
     * Formats a page into a PageListItemDTO.
     *
     * @param WP_Post $page The page to format.
     *
     * @return PageListItemDTO
     */
	protected function format_data(WP_Post $page)
	{
		$page_key = PostMeta::get($page->ID, 'page_key');
		$is_growfund_page = PostMeta::get($page->ID, 'is_growfund_page');

		$dto            = new PageListItemDTO();
		$dto->id        = (string) $page->ID;
		$dto->name      = $page->post_title;
		$dto->slug      = get_page_uri($page->ID);
		$dto->url       = get_page_link($page->ID);
		$dto->status    = $page->post_status === 'publish' ? 'published' : 'draft';
		$dto->parent_id = (string) $page->post_parent;
		$dto->page_key  =  $page_key ? $page_key : null;
		$dto->is_growfund_page = $is_growfund_page ? (bool) $is_growfund_page : false;

		return $dto;
	}

    /**
     * Returns a list of all growfund pages sorted by menu order and post title.
     *
     * @return PageListItemDTO[]
     */
	public function get_growfund_pages()
	{
		$default_pages = $this->get_default_page_contents(); 

		$page_ids = array_values(growfund_settings(AppSettings::PAGES)->get() ?? []);
		$pages = [];

		if (!empty($page_ids)) {
			$pages = $this->all([
				'include' => $page_ids,
				'post_status' => $this->get_all_page_statuses(),
			]);
		}

		$all_pages = [];
        
		foreach ($default_pages as $default_page) {
			$page = Arr::make($pages)->find(function ($page) use ($default_page) {
				switch ($default_page['key']) {
					case 'login_page':
						return (int) $page->id === growfund_settings(AppSettings::PAGES)->get_login_page_id();
					case 'registration_page':
						return (int) $page->id === growfund_settings(AppSettings::PAGES)->get_registration_page_id();
					case 'fundraiser_registration_page':
						return (int) $page->id === growfund_settings(AppSettings::PAGES)->get_fundraiser_registration_page_id();
					case 'campaigns_page':
						return (int) $page->id === growfund_settings(AppSettings::PAGES)->get_campaigns_page_id();
					case 'checkout_page':
						return (int) $page->id === growfund_settings(AppSettings::PAGES)->get_checkout_page_id();
					case 'privacy_policy_page':
                        return (int) $page->id === growfund_settings(AppSettings::PAGES)->get_privacy_policy_page_id();
					case 'terms_and_conditions_page':
						return (int) $page->id === growfund_settings(AppSettings::PAGES)->get_terms_and_conditions_page_id();
					default:
						return false;
				}
			});

			if (!empty($page)) {
                $updated_page = PageListItemDTO::from_array($page->to_array());
                $updated_page->page_key = $default_page['key'];
				$all_pages[] = $updated_page;
				continue;
			}

			$all_pages[] = new PageListItemDTO([
				'id' => '',
				'name' => $default_page['title'],
				'slug' => '',
				'url' => '',
				'parent_id' => '',
                'status' => 'not-found',
				'page_key' => $default_page['key'],
				'is_growfund_page' => false
			]);
		}

		return $all_pages;
	}

	public function generate_growfund_pages() 
	{
		$default_pages = $this->get_default_page_contents();

		$created_pages = $this->all([
			'meta_key' => growfund_with_prefix('is_growfund_page'),
			'meta_value' => '1',
			'post_status' => $this->get_all_page_statuses(),
		]);

		$growfund_pages = $this->get_growfund_pages();

		$settings = growfund_settings(AppSettings::PAGES)->get() ?? [];

		foreach ($default_pages as $default_page) {
            
			$growfund_page = Arr::make($growfund_pages)->find(function ($page) use ($default_page) {
				return $page->status !== 'not-found' && $page->page_key === $default_page['key']; 
			});

			$growfund_page_id = !empty($growfund_page) ? $growfund_page->id : null;

            if ($default_page['key'] === 'privacy_policy_page' && empty($growfund_page_id)) {
                $growfund_page_id = Option::get('wp_page_for_privacy_policy');
                $growfund_page_id = !empty($growfund_page_id) && is_page($growfund_page_id) ? (int) $growfund_page_id : null;
            }

			if (!empty($growfund_page_id)) {
                if (!empty($growfund_page) && $growfund_page->status !== 'publish') {
					wp_update_post([
						'ID' => $growfund_page_id,
						'post_status' => 'publish'
					]);
                }

				$settings[$default_page['key']] = $growfund_page_id;
				continue;
			}
            
			$created_page = Arr::make($created_pages)->find(function ($page) use ($default_page) {
				return $page->page_key === $default_page['key'];
			});

			if (!empty($created_page)) {
                if ($created_page->status !== 'publish') {
                    wp_update_post([
						'ID' => $created_page->id,
						'post_status' => 'publish'
					]);
                }
                
				$settings[$default_page['key']] = $created_page->id;
				continue;
			}
        
			$page_id = wp_insert_post([
				'post_title' => $default_page['title'],
				'post_content' => $default_page['content'],
				'post_status' => 'publish',
				'post_type' => 'page',
				'post_name' => $default_page['slug'],
			]);

			if (is_wp_error($page_id)) {
				/* translators: %s: page title */
				throw new Exception(sprintf(esc_html__('Failed to create page: %s', 'growfund'), esc_html($default_page['title'])));
			}

			PostMeta::add($page_id, 'is_growfund_page', '1');
			PostMeta::add($page_id, 'page_key', $default_page['key']);

			$settings[$default_page['key']] = (string) $page_id;
		}

		Option::update(AppConfigKeys::PAGE, $settings);

		return true;
	}

    protected function get_all_page_statuses() {
        return array_keys(get_post_statuses());
    }

	public function get_default_page_contents() {
		return [
			[
				'key'     => 'login_page',
				'title'   => __('Login', 'growfund'),
				'slug'    => 'growfund-login',
				'content' => '[growfund_login]',
			],
			[
				'key'     => 'registration_page',
				'title'   => __('Register', 'growfund'),
				'slug'    => 'growfund-register',
				'content' => '[growfund_register]',
			],
			[
				'key'     => 'fundraiser_registration_page',
				'title'   => __('Become a Fundraiser', 'growfund'),
				'slug'    => 'growfund-register-fundraiser',
				'content' => '[growfund_register user_type="fundraiser"]',
			],
			[
				'key'     => 'campaigns_page',
				'title'   => __('Campaigns', 'growfund'),
				'slug'    => 'growfund-campaigns',
				'content' => '[growfund_campaigns]',
			],
			[
				'key'     => 'checkout_page',
				'title'   => __('Checkout', 'growfund'),
				'slug'    => 'growfund-checkout',
				'content' => '[growfund_checkout]',
			],
			[
				'key'     => 'privacy_policy_page',
				'title'   => __('Privacy Policy', 'growfund'),
				'slug'    => 'growfund-privacy-policy',
				'content' => '',
			],
			[
				'key'     => 'terms_and_conditions_page',
				'title'   => __('Terms and Conditions', 'growfund'),
				'slug'    => 'growfund-terms',
				'content' => '',
			],
		];
	}
}
