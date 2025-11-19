<?php

namespace Growfund\Services\Migration;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\AppreciationType;
use Growfund\Constants\Campaign\GoalType;
use Growfund\Constants\Campaign\ReachingAction;
use Growfund\Constants\DateTimeFormats;
use Growfund\Constants\Reward\QuantityType;
use Growfund\Constants\Reward\RewardType;
use Growfund\Constants\Reward\TimeLimitType;
use Growfund\Constants\Shipping;
use Growfund\Constants\Status\CampaignStatus;
use Growfund\Constants\UserTypes\Fundraiser;
use Growfund\PostTypes\Campaign;
use Growfund\PostTypes\CampaignPost;
use Growfund\PostTypes\Reward;
use Growfund\PostTypes\RewardItem;
use Growfund\QueryBuilder;
use Growfund\Supports\Date;
use Growfund\Supports\Money;
use Growfund\Supports\Terms;
use Growfund\Taxonomies\Category;
use Growfund\Taxonomies\Tag;
use DateTime;
use Exception;
use Growfund\Constants\Campaign\SuggestedOptionType;
use Growfund\Constants\Status\FundraiserStatus;
use Growfund\DTO\Fundraiser\UpdateFundraiserDTO;
use Growfund\DTO\Migration\MigrationResponseDTO;
use Growfund\Services\UserService;
use WP_Query;

class CampaignMigrationService
{
    const BATCH_SIZE = 10;
    const OFFSET_KEY = 'growfund_campaign_migration_offset';
    const TOTAL_KEY = 'growfund_campaign_migration_total';

    /**
     * @return array|false
     */
    public function migrate()
    {
        $offset = $this->get_offset(0);
        $campaigns = $this->get_campaigns($offset);
        $total = $this->get_total();

        $response = new MigrationResponseDTO();
        $response->total = $total;
        $response->completed = $offset;

        if (empty($campaigns)) {
            return $response;
        }

        foreach ($campaigns as $campaign) {
            if (!$this->migrate_campaign($campaign)) {
                $this->set_offset($offset);
				$response->completed = $offset;
                
                return $response;
            }

            ++$offset;
        }

        $this->set_offset($offset);

        $response->total = $total;
        $response->completed = $offset;

        return $response;
    }

    public function change_post_type()
    {
        $is_updated = $this->get_all_campaign_query()->update(['posts.post_type' => Campaign::NAME]);

        if (!$is_updated) {
            return false;
        }
        
        $response = new MigrationResponseDTO();
        $response->total = 0;
        $response->completed = 0;

        return $response;
    }
    
    public function get_all_campaign_query()
    {
        return QueryBuilder::query()->table('posts as posts')
            ->inner_join(
                "term_relationships as term_relationships",
                'posts.ID',
                'term_relationships.object_id'
            )
            ->inner_join(
                "term_taxonomy as term_taxonomy",
                'term_relationships.term_taxonomy_id',
                'term_taxonomy.term_taxonomy_id'
            )
            ->inner_join(
                "terms as terms",
                'term_taxonomy.term_id',
                'terms.term_id'
            )
            ->where('posts.post_type', 'product')
            ->where('posts.post_status', 'publish')
            ->where('term_taxonomy.taxonomy', 'product_type')
            ->where('terms.slug', 'crowdfunding');
    }

    protected function get_campaigns($offset)
    {
        $campaigns = [];

        $args = [
            'post_type'      => 'product',
            'posts_per_page' => static::BATCH_SIZE,
            'offset'         => $offset,
            'post_status'    => 'publish',
            'tax_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
                [
                    'taxonomy' => 'product_type',
                    'field'    => 'slug',
                    'terms'    => 'crowdfunding',
                    'value' => 'crowdfunding'
                ]
            ],
        ];

        $query = new WP_Query($args);

        foreach ($query->posts as $post) {
            $campaign_id = $post->ID;

            $images = get_post_meta($campaign_id, '_product_image_gallery', true);
            $images = !empty($images) ? explode(',', $images) : [];
            $thumbnail = get_post_thumbnail_id($campaign_id);
            $images = array_merge([$thumbnail], $images);
            $images = array_filter($images);

            $video = get_post_meta($campaign_id, 'wpneo_funding_video', true);

            if (!empty($video)) {
                $video = [
                    'id' => wp_generate_uuid4(),
                    'url' => $video
                ];
            }

            $terms = Terms::get_terms($campaign_id, 'product_cat');
            $category = 0;
            $subcategory = 0;

            foreach ($terms as $term) {
                if ($term->parent === 0) {
                    $category = $term->name;
                } else {
                    $subcategory = $term->name;
                }
            }

            $tags = Terms::get_terms($campaign_id, 'product_tag') ?? [];

            if (!empty($tags)) {
                $tags = array_map(function ($tag) {
                    return $tag->name;
                }, $tags);
            }

            $campaign = [
                'id'                         => $campaign_id,
                'title'                      => get_the_title($campaign_id),
                'slug'                       => get_post_field('post_name', $campaign_id),
                'description'                => wpautop($post->post_excerpt),
                'story'                      => wpautop($post->post_content),
                'images'                     => $images,
                'video'                      => $video,
                'is_featured'                => false,
                'category'                   => $category,
                'subcategory'                => $subcategory,
                'start_date'                 => Date::sql_safe(get_post_meta($campaign_id, '_nf_duration_start', true)),
                'end_date'                   => Date::sql_safe(get_post_meta($campaign_id, '_nf_duration_end', true)),
                'location'                   => get_post_meta($campaign_id, '_nf_location', true),
                'tags'                       => $tags,
                'show_collaborator_list'     => filter_var(get_post_meta($campaign_id, 'wpneo_show_contributor_table', true), FILTER_VALIDATE_BOOLEAN),
                'status'                     => $post->post_status === 'publish' ? CampaignStatus::PUBLISHED : CampaignStatus::DRAFT,
                'has_goal'                   => true,
                'goal_type'                  => GoalType::RAISED_AMOUNT,
                'goal_amount'                => Money::prepare_for_storage(get_post_meta($campaign_id, '_nf_funding_goal', true) ?? 0),
                'reaching_action'            => ReachingAction::CLOSE,
                'author_id'                  => $post->post_author,
            ];

            if (growfund_app()->is_donation_mode()) {
                // Donation mode specific fields  
                $suggested_options = get_post_meta($campaign_id, 'wpcf_predefined_pledge_amount', true) ?? [];
                $suggested_options = !empty($suggested_options) ? explode(',', $suggested_options) : [];
                
                if (!empty($suggested_options) && is_array($suggested_options)) {
					$campaign['suggested_options'] = [];

					foreach ($suggested_options as $key => $option) {
						$campaign['suggested_options'][] = [
							'amount' => Money::prepare_for_storage($option) ?? 0,
							'is_default' => $key === 0,
						];
					}
				}

                $campaign['allow_custom_donation'] = false;
                $campaign['suggested_option_type'] = SuggestedOptionType::AMOUNT_ONLY;
                $campaign['min_donation_amount'] = Money::prepare_for_storage(get_post_meta($campaign_id, 'wpneo_funding_minimum_price', true));
                $campaign['max_donation_amount'] = Money::prepare_for_storage(get_post_meta($campaign_id, 'wpneo_funding_maximum_price', true));
                $campaign['has_tribute'] = false;
                
            } else {
                // Pledge mode specific fields
                $campaign['appreciation_type'] = AppreciationType::GOODIES;
                $campaign['allow_pledge_without_reward'] = true;
                $campaign['min_pledge_amount'] = Money::prepare_for_storage(get_post_meta($campaign_id, 'wpneo_funding_minimum_price', true));
                $campaign['max_pledge_amount'] = Money::prepare_for_storage(get_post_meta($campaign_id, 'wpneo_funding_maximum_price', true));

                // Fetch rewards
                $reward_items = get_post_meta($campaign_id, 'wpneo_reward', true);
                $reward_items = growfund_is_valid_json($reward_items) ? json_decode($reward_items, true) : [];

                if (!empty($reward_items) && is_array($reward_items) && count($reward_items) > 0) {
                    $campaign['rewards'] = array_map(function ($reward) {
                        return [
                            'amount' => (int) round(((float) $reward['wpneo_rewards_pladge_amount']) * 100, 0),
                            'image' => $reward['wpneo_rewards_image_field'],
                            'description' => $reward['wpneo_rewards_description'],
                            'quantity_type' => QuantityType::UNLIMITED,
                            'quantity' => $reward['wpneo_rewards_item_limit'] ?? 1,
                            'time_limit_type' => TimeLimitType::NO_LIMIT,
                            'reward_type' => RewardType::PHYSICAL_GOODS,
                            'estimated_delivery_date' => $this->get_first_date_by_month_year($reward['wpneo_rewards_endmonth'], $reward['wpneo_rewards_endyear']),
                            'shipping_costs' => [
                                [
                                    'location' => Shipping::SHIPPING_REST_OF_THE_WORLD,
                                    'cost' => 0
                                ]
                            ]
                        ];
                    }, $reward_items) ?? [];
                }
            }

            // Fetch updates
            $campaign['updates'] = get_post_meta($campaign_id, 'wpneo_campaign_updates', true);
            $campaign['updates'] = growfund_is_valid_json($campaign['updates']) ? json_decode($campaign['updates'], true) : [];

            $campaigns[] = $campaign;
        }

        return $campaigns;
    }

    protected function migrate_campaign($campaign)
    {
        QueryBuilder::begin_transaction();

        if (!empty($campaign['category'])) {
            $category = get_term_by('name', $campaign['category'], Category::NAME);
            $campaign['category'] = $category ? $category->term_id : wp_insert_term($campaign['category'], Category::NAME)['term_id'];
            wp_set_object_terms($campaign['id'], $campaign['category'], Category::NAME);
        }

        if (!empty($campaign['subcategory'])) {
            $subcategory = get_term_by('name', $campaign['subcategory'], Category::NAME);
            $campaign['subcategory'] = $subcategory ? $subcategory->term_id : wp_insert_term($campaign['subcategory'], Category::NAME, ['parent' => $campaign['category']])['term_id'];
            wp_set_object_terms($campaign['id'], $campaign['subcategory'], Category::NAME, true);
        }

        if (!empty($campaign['tags'])) {
            $campaign['tags'] = array_map(function ($tag) {
                $term = get_term_by('name', $tag, Tag::NAME);
                return $term ? $term->term_id : wp_insert_term($tag, Tag::NAME)['term_id'];
            }, $campaign['tags']);
            wp_set_object_terms($campaign['id'], $campaign['tags'], Tag::NAME);
        }

        if ($campaign['author_id']) {
            $this->ensure_user_role($campaign['author_id']);
            $this->update_fundraiser_info($campaign['author_id']);
        }

        if ($campaign['description']) {
            wp_update_post([
                'ID' => $campaign['id'],
                'post_content' => $campaign['description']
            ]);
        }

        $metas = [];

        foreach ($campaign as $key => $value) {
            if (in_array($key, ['id', 'title', 'slug', 'description', 'author_id', 'category', 'subcategory', 'tags'], true)) {
                continue;
            }

            $metas[] = [
                'post_id' => $campaign['id'],
                'meta_key' => growfund_with_prefix($key), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                'meta_value' => maybe_serialize($value) // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
            ];
        }

        try {
            QueryBuilder::query()->table('postmeta')->where('post_id', $campaign['id'])->delete();
            QueryBuilder::query()->table('postmeta')->insert($metas);

            if (!empty($campaign['rewards'])) {
                $this->insert_rewards($campaign['id'], $campaign['author_id'], $campaign['rewards']);
            }

            if (!empty($campaign['updates'])) {
                $this->insert_campaign_updates($campaign['id'], $campaign['author_id'], $campaign['updates']);
            }

            QueryBuilder::commit();

            return true;
        } catch (Exception $error) {
            QueryBuilder::rollback();

            throw $error;
        }
    }

    protected function insert_rewards($campaign_id, $author_id, $rewards)
    {
        foreach ($rewards as $key => $reward) {
            $reward_id = wp_insert_post([
                'post_type'    => Reward::NAME,
                'post_title'   => 'Reward ' . (string) (((int) $key) + 1),
                'post_content' => $reward['description'] ?? '',
                'post_status'  => Reward::DEFAULT_POST_STATUS,
                'post_author'  => $author_id ?? 0,
                'post_parent'  => $campaign_id,
            ], true);

            if (is_wp_error($reward_id)) {
                throw new Exception(esc_html($reward_id->get_error_message()));
            }

            $metas = [];

            if (!empty($reward['image'])) {
                $metas[] = [
                    'post_id' => $reward_id,
                    'meta_key' => '_thumbnail_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                    'meta_value' => $reward['image'] // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
                ];
            }

            foreach ($reward as $meta_key => $meta_value) {
                if (in_array($meta_key, ['description', 'image'], true)) {
                    continue;
                }

                $metas[] = [
                    'post_id' => $reward_id,
                    'meta_key' => growfund_with_prefix($meta_key), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                    'meta_value' => maybe_serialize($meta_value) // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
                ];
            }

            $item_id = $this->insert_reward_item($campaign_id, $author_id, $reward);

            $metas[] = [
                'post_id' => $reward_id,
                'meta_key' => growfund_with_prefix('items'), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                'meta_value' => maybe_serialize([ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
					[
						'id' => $item_id,
						'quantity' => $reward['quantity']
					]
				])
            ];

            QueryBuilder::query()->table('postmeta')->insert($metas);
        }
    }

    protected function insert_reward_item($campaign_id, $author_id, $item)
    {
        $reward_item_id = wp_insert_post([
            'post_type'    => RewardItem::NAME,
            'post_title'   => 'Reward Item',
            'post_content' => $item['description'] ?? '',
            'post_status'  => RewardItem::DEFAULT_POST_STATUS,
            'post_author'  => $author_id ?? 0,
            'post_parent'  => $campaign_id,
        ], true);

        if (is_wp_error($reward_item_id)) {
            throw new Exception(esc_html($reward_item_id->get_error_message()));
        }

        if (!empty($item['image'])) {
            set_post_thumbnail($reward_item_id, $item['image']);
        }

        return $reward_item_id;
    }

    protected function insert_campaign_updates($campaign_id, $author_id, $items)
    {
        foreach ($items as $item) {
            $item_id = wp_insert_post([
                'post_type'    => CampaignPost::NAME,
                'post_title'   => $item['title'] ?? '',
                'post_content' => $item['details'] ?? '',
                'post_date'    => !empty($item['date']) ? Date::sql_safe($item['date'], true) : date(DateTimeFormats::DB_DATETIME), // phpcs:ignore
                'post_status'  => CampaignPost::DEFAULT_POST_STATUS,
                'post_author'  => $author_id ?? 0,
                'post_parent'  => $campaign_id,
            ], true);

            if (is_wp_error($item_id)) {
                throw new Exception(esc_html($item_id->get_error_message()));
            }
        }
    }

    protected function ensure_user_role($user_id)
    {
        $user = growfund_user($user_id);

        if ($user->is_admin()) {
            return;
        }

        if (!$user->is_fundraiser()) {
            $user->set_role(Fundraiser::ROLE);
        }
    }

    protected function update_fundraiser_info(int $id)
    {
        $user = get_user_by('id', $id);

        $data = [
            'id'      => (string) $id,
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
            'email'   => $user->email,
            'phone'   => null,
            'image'   => null,
            'shipping_address' => [
                'address'   => 'Unknown',
                'address_2' => 'Unknown',
                'city'      => 'Unknown',
                'state'     => 'Unknown',
                'zip_code'  => 'Unknown',
                'country'   => 'US',
            ],
            'billing_address' => [
                'address'   => 'Unknown',
                'address_2' => 'Unknown',
                'city'      => 'Unknown',
                'state'     => 'Unknown',
                'zip_code'  => 'Unknown',
                'country'   => 'US',
            ],
            'is_billing_address_same' => true,
            'status' => FundraiserStatus::ACTIVE,
        ];

        $user_service = new UserService();

        return $user_service->update($id, UpdateFundraiserDTO::from_array($data));
    }

    protected function get_first_date_by_month_year($month, $year)
    {
        $month = ucfirst(strtolower($month));

        $date = DateTime::createFromFormat('M Y', "$month $year");

        if ($date) {
            $last_day = $date->format('Y-m-t');
            return Date::sql_safe($last_day, true);
        }

        return null;
    }

    protected function get_offset(int $default = 0)
    {
        return (int) get_transient(static::OFFSET_KEY) ?? $default;
    }
    
    protected function set_offset(int $offset)
    {
        set_transient(static::OFFSET_KEY, $offset, time() + 24 * 60 * 60);
    }

    protected function get_total()
    {
        $total = (int) get_transient(static::TOTAL_KEY);

        if (!$total) {
            $total = $this->get_all_campaign_query()->count();
            set_transient(static::TOTAL_KEY, $total, time() + 24 * 60 * 60);
        }
        
        return $total;
    }
}
