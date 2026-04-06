<?php

namespace Growfund\Services\Migration;

use Growfund\Constants\HookNames;
use Growfund\Supports\Arr;
use Growfund\Supports\Option;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Campaign\AppreciationType;
use Growfund\Constants\Campaign\GoalType;
use Growfund\Constants\Campaign\ReachingAction;
use Growfund\Constants\Reward\QuantityType;
use Growfund\Constants\Reward\RewardType;
use Growfund\Constants\Reward\TimeLimitType;
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
use Growfund\Supports\Location;
use Growfund\Supports\User as UserSupport;
use WP_User;

class CampaignMigrationService
{
    const OFFSET_KEY = 'growfund_campaign_migration_offset';
    const TOTAL_KEY = 'growfund_campaign_migration_total';
    public static $batch_size = 50;

    public function __construct()
    {
        static::$batch_size = apply_filters(HookNames::GROWFUND_CAMPAIGN_MIGRATION_BATCH_SIZE_FILTER, static::$batch_size);
    }

    /**
     * @return MigrationResponseDTO
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
            try {
                $this->migrate_campaign($campaign);
            } catch (Exception $e) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
                // @todo: implement failed campaign tracking later to keep track if needed
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
            ->where('term_taxonomy.taxonomy', 'product_type')
            ->where('terms.slug', 'crowdfunding');
    }

    protected function get_campaigns($offset)
    {
        $campaigns = [];

        $posts = $this->get_all_campaign_query()
            ->limit(static::$batch_size)
            ->offset($offset)
            ->get();

        foreach ($posts as $post) {
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

            $status = $this->get_campaign_status($post->post_status);

            $start_date = get_post_meta($campaign_id, '_nf_duration_start', true);
            $start_date = !empty($start_date) && strtotime($start_date) ? $start_date : $post->post_date;
            $end_date = get_post_meta($campaign_id, '_nf_duration_end', true);

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
                'start_date'                 => Date::sql_safe($start_date),
                'end_date'                   => !empty($end_date) && strtotime($end_date) ? Date::sql_safe($end_date) : null,
                'location'                   => get_post_meta($campaign_id, '_nf_location', true),
                'tags'                       => $tags,
                'show_collaborator_list'     => filter_var(get_post_meta($campaign_id, 'wpneo_show_contributor_table', true), FILTER_VALIDATE_BOOLEAN),
                'status'                     => $status,
                'has_goal'                   => true,
                'goal_type'                  => GoalType::RAISED_AMOUNT,
                'goal_amount'                => Money::prepare_for_storage(get_post_meta($campaign_id, '_nf_funding_goal', true) ?? 0),
                'reaching_action'            => ReachingAction::CLOSE,
                'author_id'                  => $post->post_author,
                'is_ended'                   => $status === CampaignStatus::COMPLETED,
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

                $min_donation_amount = Money::prepare_for_storage(get_post_meta($campaign_id, 'wpneo_funding_minimum_price', true));
                $max_donation_amount = Money::prepare_for_storage(get_post_meta($campaign_id, 'wpneo_funding_maximum_price', true));

                $campaign['allow_custom_donation'] = !empty($min_donation_amount) && !empty($max_donation_amount);
                $campaign['suggested_option_type'] = SuggestedOptionType::AMOUNT_ONLY;
                $campaign['min_donation_amount'] = $min_donation_amount;
                $campaign['max_donation_amount'] = $max_donation_amount;
                $campaign['has_tribute'] = false;
                
            } else {
                $min_pledge_amount = Money::prepare_for_storage(get_post_meta($campaign_id, 'wpneo_funding_minimum_price', true));
                $max_pledge_amount = Money::prepare_for_storage(get_post_meta($campaign_id, 'wpneo_funding_maximum_price', true));
                // Pledge mode specific fields
                $campaign['appreciation_type'] = AppreciationType::GOODIES;
                $campaign['allow_pledge_without_reward'] = !empty($min_pledge_amount) && !empty($max_pledge_amount);
                $campaign['min_pledge_amount'] = $min_pledge_amount;
                $campaign['max_pledge_amount'] = $max_pledge_amount;

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
                                    'location' => Location::REST_OF_THE_WORLD,
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

    protected function get_campaign_status($post_status) {

        switch ($post_status) {
            case 'publish':
                return CampaignStatus::PUBLISHED;
            case 'pending':
                return CampaignStatus::PENDING;
            case 'trash':
                return CampaignStatus::TRASHED;
            default:
                return CampaignStatus::DRAFT;
        }
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
            $author = get_user($campaign['author_id']);

            if ($author) {
                $this->ensure_user_role($author);
                $this->update_fundraiser_info($author);
            }
        }

        wp_update_post([
            'ID' => $campaign['id'],
            'post_content' => $campaign['description'] ?? $campaign['story'] ?? '',
            'post_status' => Campaign::DEFAULT_POST_STATUS
        ]);

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
        QueryBuilder::begin_transaction();

        try {
            $posts = Arr::make($rewards)->map(function ($reward, $key) use ($campaign_id, $author_id) {
                return [
                    'post_type'    => Reward::NAME,
                    'post_title'   => 'Reward ' . (string) (((int) $key) + 1),
                    'post_content' => $reward['description'] ?? '',
                    'post_excerpt' => '',
                    'post_name'    => sanitize_title('Reward ' . (string) (((int) $key) + 1)),
                    'post_status'  => Reward::DEFAULT_POST_STATUS,
                    'post_author'  => $author_id ?? 0,
                    'post_parent'  => $campaign_id,
                    'post_date'    => current_time('mysql'),
                    'post_date_gmt' => current_time('mysql', true),
                    'post_modified' => current_time('mysql'),
                    'post_modified_gmt' => current_time('mysql', true),
                    'ping_status' => 'closed',
                    'to_ping' => '',
                    'pinged' => ''
                ];
            })->toArray();

            QueryBuilder::query()->table('posts')->insert($posts);
            
            $first_id = QueryBuilder::get_db()->insert_id;

            $this->generate_guid();

            $first_item_id = $this->insert_reward_items($campaign_id, $author_id, $rewards);

            $metas = [];

            foreach ($rewards as $key => $reward) {
                $reward_id = $first_id + $key;
                $metas[] = [
                    'post_id' => $reward_id,
                    'meta_key' => '_thumbnail_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                    'meta_value' => $reward['image'] // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
                ];

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

                $metas[] = [
                    'post_id' => $reward_id,
                    'meta_key' => growfund_with_prefix('items'), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                    'meta_value' => maybe_serialize([ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
                        [
                            'id' => $first_item_id + $key,
                            'quantity' => $reward['quantity']
                        ]
                    ])
                ];
            }

            QueryBuilder::query()->table('postmeta')->insert($metas);
            QueryBuilder::commit();
        } catch (Exception $e) {
            QueryBuilder::rollback();

            throw $e;
        }
    }

    protected function insert_reward_items($campaign_id, $author_id, $rewards)
    {
        $reward_items = Arr::make($rewards)->map(function ($item) use ($campaign_id, $author_id) {
            return [
                'post_type'    => RewardItem::NAME,
                'post_title'   => 'Reward Item',
                'post_content' => $item['description'] ?? '',
                'post_status'  => RewardItem::DEFAULT_POST_STATUS,
                'post_author'  => $author_id ?? 0,
                'post_parent'  => $campaign_id,
            ];
        })->toArray();

        QueryBuilder::query()->table('posts')->insert($reward_items);
        
        $first_id = QueryBuilder::get_db()->insert_id;

        $this->generate_guid();

        $metas = Arr::make($rewards)->map(function ($item, $key) use ($first_id) {
            return [
				'post_id' => $first_id + $key,
				'meta_key' => '_thumbnail_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                    'meta_value' => $item['image'] // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			];
        })->toArray();

        QueryBuilder::query()->table('postmeta')->insert($metas);

        return $first_id;
    }

    protected function insert_campaign_updates($campaign_id, $author_id, $items)
    {
        $now = Date::current_sql_safe(true);

        $updates = Arr::make($items)->map(function ($item) use ($campaign_id, $author_id, $now) {
            $date = !empty($item['date']) ? Date::sql_safe($item['date'], true) : $now;
            $title = $item['title'] ?? '';

            return [
                'post_type'             => CampaignPost::NAME,
                'post_title'            => $title,
                'post_name'             => sanitize_title($title), // Essential for permalinks
                'post_content'          => $item['details'] ?? '',
                'post_excerpt'          => '', 
                'post_date'             => get_date_from_gmt($date),
                'post_date_gmt'         => $date,
                'post_modified'         => get_date_from_gmt($date),
                'post_modified_gmt'     => $date,
                'post_status'           => CampaignPost::DEFAULT_POST_STATUS,
                'post_author'           => $author_id ?? 0,
                'post_parent'           => $campaign_id,
                'ping_status'           => 'closed',
                'to_ping'               => '',
                'pinged'                => '',
                'post_content_filtered' => '',
            ];
        });

        QueryBuilder::query()->table('posts')->insert($updates->toArray());

        $this->generate_guid();
    }

    /**
     * Ensure user has the donor role
     * 
     * @param WP_User|null $user
     * @return void
     */
    protected function ensure_user_role($user)
    {
        if (empty($user) || UserSupport::is_admin($user) || UserSupport::is_fundraiser($user)) {
            return;
        }

        $user->add_role(Fundraiser::ROLE);
    }

    protected function update_fundraiser_info(WP_User $user)
    {
        $data = [
            'id'      => (string) $user->ID,
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

        return $user_service->update($user->ID, UpdateFundraiserDTO::from_array($data));
    }

    protected function generate_guid()
    {
        $table = QueryBuilder::prefix('posts');

        QueryBuilder::raw("UPDATE {$table} SET guid = CONCAT(:home_url, '/?p=', ID)", [
            'home_url' => get_home_url()
        ]);
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
        return (int) Option::get(static::OFFSET_KEY) ?? $default;
    }
    
    protected function set_offset(int $offset)
    {
        Option::set(static::OFFSET_KEY, $offset);
    }

    public function get_total()
    {
        $total = (int) Option::get(static::TOTAL_KEY);

        if (!$total) {
            $total = $this->get_all_campaign_query()->count();
            Option::set(static::TOTAL_KEY, $total);
        }
        
        return $total;
    }

    public function remove_migration_data()
    {
        Option::delete(static::OFFSET_KEY);
        Option::delete(static::TOTAL_KEY);
    }
}
