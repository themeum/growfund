<?php

namespace Growfund\Services;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\AppConfigKeys;
use Growfund\Constants\OptionKeys;
use Growfund\Constants\UserTypes\Backer;
use Growfund\Constants\UserTypes\Donor;
use Growfund\Constants\UserTypes\Fundraiser;
use Growfund\Constants\WP;
use Growfund\Contracts\Migration;
use Growfund\Core\MailConfig;
use Growfund\PostTypes\Campaign;
use Growfund\PostTypes\CampaignPost;
use Growfund\PostTypes\Reward;
use Growfund\PostTypes\RewardItem;
use Growfund\QueryBuilder;
use Growfund\Supports\Arr;
use Growfund\Taxonomies\Category;
use Growfund\Taxonomies\Tag;
use Exception;
use Growfund\Constants\UserTypes\Collaborator;
use Growfund\Supports\Option;

/**
 * CleanupService
 * 
 * Service for cleaning up growfund data from the database.
 * This service provides methods to delete all growfund-related data
 * including campaigns, rewards, pledges, donations, and related metadata.
 * 
 * @since 1.0.0
 */
class CleanupService
{
    /** @var \wpdb  */
    protected $db;

    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
    }

    /**
     * Erase all the growfund application data upon plugin uninstallation
     *
     * @return bool
     * @since 1.0.0
     */
    public function erase()
    {
        QueryBuilder::begin_transaction();

        try {
            $this->cleanup_growfund_posts();
            $this->cleanup_growfund_taxonomies();
            $this->cleanup_growfund_users();
            $this->undo_migrations();
            $this->cleanup_growfund_options();

            QueryBuilder::commit();
            return true;
        } catch (Exception $error) {
            QueryBuilder::rollback();
            return false;
        }
    }

    /**
     * Run the undoing the migrations on plugin uninstallation.
     *
     * @return void
     */
    protected function undo_migrations()
    {
        $app = growfund_app();
        $migrations = $app->tagged('app.migrations');

        if (empty($migrations)) {
            return;
        }

        $migrations = array_reverse($migrations);

        foreach ($migrations as $migration) {
            if (!$migration instanceof Migration) {
                throw new Exception(
                    sprintf(
                        /* translators: 1: Migration class name, 2: Migration interface name */
                        esc_html__('Class %1$s must implement %2$s.', 'growfund'),
                        esc_html(get_class($migration)),
                        Migration::class
                    )
                );
            }

            $migration->handle_down();
        }

        Option::delete(OptionKeys::DATABASE_MIGRATION_TRACKER);
    }

    /**
     * Cleanup the growfund posts
     *
     * @return bool
     * @since 1.0.0
     */
    protected function cleanup_growfund_posts()
    {
        $post_types = [
            Campaign::NAME,
            CampaignPost::NAME,
            Reward::NAME,
            RewardItem::NAME,
        ];

        return $this->delete_wp_posts($post_types);
    }

    /**
     * Delete the WordPress posts
     *
     * @param array $post_types
     * @return bool
     * @since 1.0.0
     */
    protected function delete_wp_posts(array $post_types)
    {
        $post_table = QueryBuilder::query()->table(WP::POSTS_TABLE)->get_table_name();
        $post_meta_table = QueryBuilder::query()->table(WP::POST_META_TABLE)->get_table_name();

        $post_types = Arr::make($post_types)->map(function ($post_type) {
            return sprintf("'%s'", $post_type);
        })->join(',');

        $meta_sql = sprintf(
            "DELETE post_meta FROM %s as post_meta 
            INNER JOIN %s as post ON post_meta.post_id = post.ID 
            WHERE post.post_type IN (%s)",
            $post_meta_table,
            $post_table,
            $post_types
        );

        $meta_result = $this->db->query($meta_sql);

        $posts_sql = sprintf(
            "DELETE FROM %s WHERE post_type IN (%s)",
            $post_table,
            $post_types
        );

        $posts_result = $this->db->query($posts_sql);

        return $meta_result !== false && $posts_result !== false;
    }

    /**
     * Cleanup the growfund taxonomies
     *
     * @return bool
     * @since 1.0.0
     */
    protected function cleanup_growfund_taxonomies()
    {
        $taxonomy_types = [
            Category::NAME,
            Tag::NAME,
        ];

        return $this->delete_wp_taxonomies($taxonomy_types);
    }

    /**
     * Delete the WordPress taxonomies
     *
     * @param array $taxonomy_types
     * @return bool
     * @since 1.0.0
     */
    protected function delete_wp_taxonomies(array $taxonomy_types)
    {
        $terms_table = QueryBuilder::query()->table(WP::TERM_TABLE)->get_table_name();
        $term_taxonomy_table = QueryBuilder::query()->table(WP::TERM_TAXONOMY_TABLE)->get_table_name();
        $term_relationships_table = QueryBuilder::query()->table(WP::TERM_RELATIONSHIPS_TABLE)->get_table_name();
        $term_meta_table = QueryBuilder::query()->table(WP::TERM_META_TABLE)->get_table_name();

        $taxonomy_types = Arr::make($taxonomy_types)->map(function ($taxonomy_type) {
            return sprintf("'%s'", $taxonomy_type);
        })->join(',');

        $results = [];

        $relationships_sql = sprintf(
            "DELETE term_relationships FROM %s as term_relationships 
            INNER JOIN %s as term_taxonomy ON term_relationships.term_taxonomy_id = term_taxonomy.term_taxonomy_id 
            WHERE term_taxonomy.taxonomy IN (%s)",
            $term_relationships_table,
            $term_taxonomy_table,
            $taxonomy_types
        );
        $results[] = $this->db->query($relationships_sql);


        $meta_sql = sprintf(
            "DELETE term_meta FROM %s as term_meta 
            INNER JOIN %s as term_taxonomy ON term_meta.term_id = term_taxonomy.term_id 
            WHERE term_taxonomy.taxonomy IN (%s)",
            $term_meta_table,
            $term_taxonomy_table,
            $taxonomy_types
        );
        $results[] = $this->db->query($meta_sql);

        $taxonomy_sql = sprintf(
            "DELETE FROM %s WHERE taxonomy IN (%s)",
            $term_taxonomy_table,
            $taxonomy_types
        );
        $results[] = $this->db->query($taxonomy_sql);

        $terms_sql = sprintf(
            "DELETE terms FROM %s as terms 
            LEFT JOIN %s as term_taxonomy ON terms.term_id = term_taxonomy.term_id 
            WHERE term_taxonomy.term_id IS NULL",
            $terms_table,
            $term_taxonomy_table
        );
        $results[] = $this->db->query($terms_sql);

        return !in_array(false, $results, true);
    }

    /**
     * Cleanup the growfund users
     *
     * @return bool
     * @since 1.0.0
     */
    protected function cleanup_growfund_users()
    {
        $user_roles = [
            Fundraiser::ROLE,
            Collaborator::ROLE,
            Backer::ROLE,
            Donor::ROLE,
        ];

        $is_deleted = $this->delete_wp_user_meta();
        $this->remove_growfund_roles_from_user($user_roles);
        
        return $is_deleted;
    }

    /**
     * Delete the WordPress users
     *
     * @return bool
     * @since 1.0.0
     */
    protected function delete_wp_user_meta()
    {
        $user_meta_table = QueryBuilder::query()->table(WP::USER_META_TABLE)->get_table_name();

        $meta_sql = sprintf(
            "DELETE user_meta FROM %s as user_meta 
            WHERE user_meta.meta_key LIKE '%s'",
            $user_meta_table,
            'growfund_%'
        );

        $meta_result = $this->db->query($meta_sql);

        return $meta_result !== false;
    }

    /**
     * Delete the WordPress users
     *
     * @param array $user_roles
     * @return bool
     * @since 1.1.1
     */
    protected function remove_growfund_roles_from_user(array $user_roles)
    {
        $role_conditions = $this->build_role_like_conditions($user_roles);

        QueryBuilder::query()
            ->table(WP::USER_META_TABLE)
            ->select(['user_id', 'meta_value'])
            ->where('meta_key', QueryBuilder::prefix('capabilities'))
            ->where_raw($role_conditions)
            ->chunk_by_id(1000, function($growfund_user_capabilities) use ($user_roles) {
                foreach ($growfund_user_capabilities as $user_meta) {
                    $capabilities = maybe_unserialize($user_meta->meta_value);

                    if (!is_array($capabilities)) {
                        continue;
                    }

                    foreach ($user_roles as $role) {
                        if (!isset($capabilities[$role])) {
                            continue;
                        }

                        unset($capabilities[$role]);
                    }

                    update_user_meta(
                        $user_meta->user_id,
                        QueryBuilder::prefix('capabilities'),
                        $capabilities
                    );
                }
            }, 'ASC', 'user_id');
    }

    /**
     * Build the role like conditions
     *
     * @param array $user_roles
     * @return string
     * @since 1.0.0
     */
    protected function build_role_like_conditions(array $user_roles)
    {
        $conditions = [];
        foreach ($user_roles as $role) {
            $conditions[] = sprintf("meta_value LIKE '%%\"%s\"%%'", $role);
        }
        return implode(' OR ', $conditions);
    }

    /**
     * Cleanup the growfund options
     *
     * @return bool
     * @since 1.0.0
     */
    public function cleanup_growfund_options()
    {
        return QueryBuilder::query()->table(WP::OPTIONS_TABLE)->where_in('option_name', $this->get_all_option_keys())->delete();
    }

    public function get_all_option_keys()
    {
        $options = OptionKeys::get_constant_values();
        $configs = AppConfigKeys::get_constant_values();
        $email_contents = (new MailConfig())->get_content_keys();

        $payment_options = QueryBuilder::query()
            ->table(WP::OPTIONS_TABLE)
            ->select(['option_name'])
            ->where_raw("option_name LIKE :option_key", [
                'option_key' => OptionKeys::PAYMENT_GATEWAY_CONFIG_PREFIX . '%',
            ])
            ->get();

        $payment_options = !empty($payment_options) ? wp_list_pluck($payment_options, 'option_name') : [];

        return array_merge($options, $configs, $email_contents, $payment_options);
    }
}
