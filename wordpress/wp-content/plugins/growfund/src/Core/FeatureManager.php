<?php

namespace Growfund\Core;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\Mail\MailKeys;

class FeatureManager
{
    protected $features = [];

    /**
     * The feature manager constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->register_features();
    }

    /**
     * Check if the pro version is activated.
     *
     * @return bool
     */
    public function is_pro_activate()
    {
        if (!function_exists('is_plugin_active')) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        return is_plugin_active('growfund-pro/growfund-pro.php');
    }

    /**
     * Check if the pro is activated and the license is valid.
     *
     * @return bool
     */
    public function is_pro()
    {
        return $this->is_pro_activate();
    }

    /**
     * Get the free features.
     *
     * @return array
     */
    protected function get_free_features()
    {
        return [
            'campaign' => [
                'rewards' => [
                    'limit' => 3,
                ],
                'reward_items' => [
                    'limit' => 5,
                ],
                'collaborators' => false,
                'start_date' => false,
                'continue' => false,
                'suggested_options' => false,
                'allow_custom_donation' => false,
                'post_an_update' => false,
                'make_a_copy' => false,
                'overview' => false,
                'allow_pledging_without_reward' => false,
            ],
            'donor' => [
                'overview' => false,
            ],
            'fundraisers' => false,
            'settings' => [
                'general' => ['donation_form_options' => false],
                'campaign' => [
                    'enable_comments' => false,
                    'tribute' => false,
                    'funds' => false,
                    'visibility' => false,
                ],
                'user_permissions' => [
                    'fundraisers' => false,
                    'anonymous_contributions' => false,
                    'contributor_comments' => false,
                ],
                'pdf_receipt' => [
                    'colors' => false,
                    'tax_information' => false,
                    'signature' => false,
                    'annual_receipt' => false
                ],
                'email_notifications' => [
                    'template' => [
                        'colors' => false
                    ],
                    MailKeys::ADMIN_CAMPAIGN_SUBMITTED_FOR_REVIEW => false,
                    MailKeys::ADMIN_CAMPAIGN_POST_UPDATE => false,
                    'all_fundraiser_mails' => false,
                    MailKeys::DONOR_CAMPAIGN_POST_UPDATE => false,
                    MailKeys::DONOR_CAMPAIGN_HALF_MILESTONE_ACHIEVED => false,
                    MailKeys::DONOR_TRIBUTE_MAIL => false,
                    MailKeys::BACKER_CAMPAIGN_HALF_FUNDED => false,
                    MailKeys::BACKER_CAMPAIGN_POST_UPDATE => false,
                ],
                'branding' => [
                    'colors' => false,
                ],
                'security' => [
                    'email_verification' => false
                ],
                'payment' => [
                    'guest_checkout' => false,
                    'admin_commission' => false,
                ],
            ],
            'analytics' => false
        ];
    }

    /**
     * Get the pro features.
     *
     * @return array
     */
    protected function get_pro_features()
    {
        return apply_filters(HookNames::GROWFUND_FILTER_PRO_FEATURES, []);
    }

    /**
     * Register the features.
     *
     * @return self
     */
    public function register_features()
    {
        $free_features = $this->get_free_features();
        $pro_features = $this->get_pro_features();

        $features = [];

        foreach ($free_features as $name => $feature) {
            $features = array_merge($features, $this->generate_feature($name, $feature, false));
        }

        foreach ($pro_features as $name => $feature) {
            $features = array_merge($features, $this->generate_feature($name, $feature, true));
        }

        $this->features = $features;

        return $this;
    }

    protected function generate_feature(string $name, $config, bool $is_pro = false)
    {
        $features = $features ?? [];

        if (is_bool($config)) {
            $features[$name] = [
                'limit' => $config ? -1 : 0,
                'is_pro' => $is_pro,
            ];
            return $features;
        }

        if (is_int($config)) {
            $features[$name] = [
                'limit' => $config,
                'is_pro' => $is_pro,
            ];
            return $features;
        }

        if (is_array($config)) {
            $limit = $config['limit'] ?? -1;
            $pro = $config['is_pro'] ?? $is_pro;

            unset($config['limit'], $config['is_pro']);

            $features[$name] = [
                'limit' => $limit,
                'is_pro' => $pro,
            ];

            if (empty($config)) {
                return $features;
            }

            foreach ($config as $key => $value) {
                $features = array_merge($features, $this->generate_feature($name . '.' . $key, $value, $is_pro));
            }
        }
        return $features;
    }

    /**
     * Check if a feature is registered.
     *
     * @param string $feature_name The feature name.
     * @return bool
     */
    public function has_feature(string $feature_name)
    {
        if (!isset($this->features[$feature_name])) {
            return false;
        }

        return true;
    }

    /**
     * Get a feature.
     *
     * @param string $feature_name The feature name.
     * @return array|null
     */
    public function get_feature(string $feature_name)
    {
        if (!$this->has_feature($feature_name)) {
            return null;
        }

        return $this->features[$feature_name];
    }

    /**
     * Check if a feature is available.
     *
     * @param string $feature_name The feature name.
     * @param int $used The number of times the feature has been used.
     * @return bool
     */
    public function is_available(string $feature_name, int $used = 0)
    {
        $feature = $this->get_feature($feature_name);

        if (!$feature) {
            return false;
        }

        $limit = $feature['limit'] ?? -1;
        $is_pro_feature = $feature['is_pro'] ?? false;

        if ($is_pro_feature && !$this->is_pro()) {
            return false;
        }

        if ($limit === -1) {
            return true;
        }

        if ($limit <= $used) {
            return false;
        }

        return true;
    }

    /**
     * Get all features.
     *
     * @return array
     */
    public function all()
    {
        return $this->features;
    }
}
