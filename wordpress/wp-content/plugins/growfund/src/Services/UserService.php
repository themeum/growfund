<?php

namespace Growfund\Services;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Tables;
use Growfund\Constants\UserDeleteType;
use Growfund\Constants\WP;
use Growfund\Exceptions\ValidationException;
use Growfund\Http\Response;
use Growfund\Sanitizer;
use Growfund\Supports\UserMeta;
use Growfund\DTO\Backer\CreateBackerDTO;
use Growfund\DTO\Backer\UpdateBackerDTO;
use Growfund\DTO\Backer\UpdateBackerNotificationsDTO;
use Growfund\DTO\Backer\UpdateBackerNotificationSettingsDTO;
use Growfund\DTO\Donor\UpdateDonorNotificationsDTO;
use Growfund\DTO\Donor\CreateDonorDTO;
use Growfund\DTO\Donor\UpdateDonorDTO;
use Growfund\DTO\Fundraiser\CreateFundraiserDTO;
use Growfund\DTO\Fundraiser\UpdateFundraiserDTO;
use Growfund\DTO\Fundraiser\UpdateFundraiserNotificationsDTO;
use Growfund\DTO\User\UpdatePasswordDTO;
use Growfund\QueryBuilder;
use Growfund\DTO\User\UserDTO;
use Growfund\Mails\PasswordResetLinkMail;
use Growfund\Supports\Arr;
use Growfund\Supports\User as UserSupport;
use Exception;
use Growfund\Constants\UserTypes\Admin;
use Growfund\Constants\UserTypes\Collaborator;
use Growfund\Constants\UserTypes\Fundraiser;
use Growfund\Core\User;
use Growfund\DTO\User\UserInfoDTO;
use Growfund\PostTypes\Campaign;
use WP_User;

/**
 * User service class 
 * 
 * @since 1.0.0
 */
class UserService
{
    /**
     * Create new user
     * @param CreateDonorDTO|CreateBackerDTO|CreateFundraiserDTO $dto
     * 
     * @return int User ID
     */
    public function store($dto)
    {
        if (! $dto instanceof CreateDonorDTO && ! $dto instanceof CreateBackerDTO && ! $dto instanceof CreateFundraiserDTO) {
            throw new Exception(esc_html__('Invalid DTO instance', 'growfund'));
        }

        $user = get_user_by('login', $dto->username);

        $is_soft_deleted = UserSupport::is_soft_deleted($user->ID ?? null);

        if ($user && !$is_soft_deleted) {
            throw ValidationException::with_errors(['username' => [esc_html__('Username already exists', 'growfund')]], esc_html__('Username already exists', 'growfund'));
        }

        if (!$user || $user->user_email !== $dto->email) {
            $user = get_user_by('email', $dto->email);

            $is_soft_deleted = UserSupport::is_soft_deleted($user->ID ?? null);

            if ($user && !$is_soft_deleted) {
                throw ValidationException::with_errors(['email' => [esc_html__('Email already exists', 'growfund')]], esc_html__('Email already exists', 'growfund'));
            }
        }

        if ($user && $is_soft_deleted) {
            if ($user->user_login === $dto->username && $user->user_email === $dto->email) {
                UserMeta::delete($user->ID, UserSupport::SOFT_DELETE_KEY);
    
                return $user->ID;
            }

            throw ValidationException::with_errors([
                'email' => [esc_html__('Email already exists', 'growfund')],
                'username' => [esc_html__('Username already exists', 'growfund')]
			], esc_html__('Username or Email already exists', 'growfund'));
        }

        return $this->create_user($dto);
    }

    /**
     * Update existing user
     * @param int $id
     * @param UpdateDonorDTO|UpdateBackerDTO|UpdateFundraiserDTO $dto
     * 
     * @return bool
     * @throws Exception
     */
    public function update(int $id, $dto)
    {
        if (! $dto instanceof UpdateDonorDTO && ! $dto instanceof UpdateBackerDTO && ! $dto instanceof UpdateFundraiserDTO) {
            throw new Exception(esc_html__('Invalid DTO instance', 'growfund'), (int) Response::INTERNAL_SERVER_ERROR);
        }

        $user_obj = get_userdata($id);

        if (! $user_obj) {
            throw new Exception(esc_html__('Invalid user ID.', 'growfund'), (int) Response::NOT_FOUND);
        }

        $userdata = [
            'ID' => $id,
            'first_name' => $dto->first_name,
            'last_name' => $dto->last_name,
            'display_name' => sprintf('%s %s', $dto->first_name, $dto->last_name),
        ];

		if (!empty($dto->username) && $dto->username !== $user_obj->user_login) {
            if (username_exists($dto->username)) {
                throw ValidationException::with_errors(['username' => [esc_html__('Username already exists', 'growfund')]]);
            }

            add_filter('wp_pre_insert_user_data', function($data, $is_update, $user_id, $user_data) {
                if (isset($user_data['user_login'])) {
                    $data['user_login'] = $user_data['user_login'];
                }

                return $data;
            }, 10, 4);
            
            $userdata['user_login'] = $dto->username;
        }

        if (!empty($dto->email) && $dto->email !== $user_obj->user_email) {
            if (email_exists($dto->email)) {
                throw ValidationException::with_errors(['email' => [esc_html__('Email already exists', 'growfund')]]);
            }

            $userdata['user_email'] = $dto->email;
        }

        if (!empty($dto->password) && $dto->password !== $user_obj->user_pass) {
            $userdata['user_pass'] = $dto->password;
        }

        $user_id  = wp_update_user($userdata);

        if (is_wp_error($user_id)) {
            throw new Exception(esc_html($user_id->get_error_message()));
        }

        $meta_input = $dto->get_meta(['username']);

        UserMeta::update_many($user_id, $meta_input);

        return !empty($user_id);
    }

    /**
     * Update the user's Backer/Donor notification settings.
     *
     * @param integer $id
     * @param UpdateBackerNotificationSettingsDTO|UpdateDonorNotificationSettingsDTO|UpdateFundraiserNotificationsDTO $dto
     * 
     * @return boolean|int
     */
    public function update_notifications(int $id, $dto)
    {
        if (
            ! $dto instanceof UpdateBackerNotificationsDTO
            && ! $dto instanceof UpdateDonorNotificationsDTO
            && ! $dto instanceof UpdateFundraiserNotificationsDTO
        ) {
            throw new Exception(esc_html__('Invalid DTO instance', 'growfund'), (int) Response::INTERNAL_SERVER_ERROR);
        }

        $notifications = $dto->to_array();

        return UserMeta::update($id, 'notification_settings', $notifications);
    }

    /**
     * Prepare user DTO
     * @param User $user
     * 
     * @return UserDTO
     */
    public function prepare_user_dto(User $user)
    {
        $pledge_service = new PledgeService();
        $donation_service = new DonationService();

        $latest_contribution_date = growfund_app()->is_donation_mode()
            ? $donation_service->get_latest_donation_date($user->get_id())
            : $pledge_service->get_latest_pledge_date($user->get_id());

        $dto = new UserDTO();

        $notification_settings = $user->get_meta('notification_settings');
        $notification_settings = !empty($notification_settings)
            ? maybe_unserialize($notification_settings)
            : null;

        $dto->id = $user->get_id();
        $dto->first_name = $user->get_first_name();
        $dto->last_name = $user->get_last_name();
        $dto->display_name = $user->get_display_name();
        $dto->email = $user->get_email();
        $dto->username = $user->get_username();
        $dto->image = $user->get_avatar();
        $dto->phone = UserSupport::get_phone_number($user->get_id());
        $dto->active_role = $user->get_active_role();
        $dto->shipping_address = UserSupport::get_shipping_address($user->get_id());
        $dto->is_billing_address_same = UserSupport::is_billing_address_same($user->get_id());
        $dto->billing_address = UserSupport::get_billing_address($user->get_id());
        $dto->is_soft_deleted = $user->is_soft_deleted();
        $dto->notification_settings = $notification_settings;
        $dto->joined_at = $user->get_joined_date();
        $dto->last_contribution_at = $latest_contribution_date;
        $dto->total_number_of_contributions = growfund_app()->is_donation_mode()
            ? $donation_service->get_total_number_of_donations($user->get_id())
            : $pledge_service->get_total_number_of_pledges($user->get_id());
        $dto->is_verified = $user->is_verified();
        $dto->created_by = $user->get_created_by();

        return $dto;
    }

    /**
     * Get user by user ID
     * @param int $user_id
     * 
     * @return UserDTO
     */
    public function get_by_user_id(int $user_id)
    {
        $user = growfund_user($user_id);

        if (empty($user)) {
            throw new Exception(esc_html__('User not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        return $this->prepare_user_dto($user);
    }

    /**
     * Get current logged in user
     * 
     * @return UserDTO
     */
    public function get_current_user()
    {
        $user = growfund_user();

        if (empty($user)) {
            throw new Exception(esc_html__('User not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        return $this->prepare_user_dto($user);
    }

    /**
     * Update user password
     *
     * @param UpdatePasswordDTO $dto
     *
     * @return bool
     *
     * @throws Exception
     */
    public function update_password(UpdatePasswordDTO $dto)
    {
        $user = get_user_by('ID', $dto->id);

        if (empty($user)) {
            throw new Exception(esc_html__('User not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        if (!wp_check_password($dto->current_password, $user->user_pass, $user->ID)) {
            throw new Exception(esc_html__('Current password is incorrect', 'growfund'), (int) Response::UNAUTHORIZED);
        }

        wp_set_password($dto->new_password, $user->ID);
        wp_clear_auth_cookie();
		wp_set_current_user($user->ID);
		wp_set_auth_cookie($user->ID);

        return true;
    }

    /**
     * Sends a password reset link to the specified user by user ID.
     *
     * Retrieves the user by ID and generates a password reset URL. 
     * Sends the reset link to the user's email using the PasswordResetLinkMail class.
     *
     * @param int $user_id The ID of the user to send the password reset link to.
     *
     * @return bool Returns true if the password reset link was sent successfully.
     *
     * @throws Exception If the user is not found or if sending the email fails.
     */
    public function send_password_reset_link_by_user_id($user_id)
    {
        $is_scheduled = growfund_scheduler()
            ->resolve(PasswordResetLinkMail::class)
            ->with(['user_id' => $user_id])
            ->group('growfund_user_emails')
            ->schedule_email();

        if (!$is_scheduled) {
            throw new Exception(esc_html__('Failed to send password reset link', 'growfund'), (int) Response::INTERNAL_SERVER_ERROR);
        }

        return true;
    }


    public function delete(int $id, $type = null)
    {
        switch ($type) {
            case UserDeleteType::PERMANENT:
                return $this->delete_permanently($id);
            case UserDeleteType::ANONYMIZE:
                return $this->anonymize_user($id);
            default:
                return $this->trash($id);
        }
    }

    public function trash(int $id)
    {
        return UserMeta::update($id, UserSupport::SOFT_DELETE_KEY, true);
    }

    public function restore(int $id)
    {
        return UserMeta::update($id, UserSupport::SOFT_DELETE_KEY, false);
    }

    public function anonymize_user(int $id)
    {
        $user = get_userdata($id);

        if (!$user) {
            return false;
        }

        if (!$this->is_anonymize_needed($id)) {
            return $this->delete_permanently($id);
        }

        $fake_name = 'Anonymous User ' . $id;
        $fake_name = Sanitizer::apply_rule($fake_name, Sanitizer::TEXT);

        $raw_domain = wp_parse_url(home_url(), PHP_URL_HOST); 
        $domain = preg_replace('/^www\./', '', $raw_domain);
        $fake_email = 'anonymous_' . $id . '@' . $domain;

        $fake_username = 'anonymous_' . $id;

        // Update user data
        wp_update_user([
            'ID'         => $id,
            'user_nicename' => $fake_name,
            'user_email' => Sanitizer::apply_rule($fake_email, Sanitizer::EMAIL),
            'display_name' => $fake_name,
            'first_name' => $fake_name,
            'last_name'  => '',
            'nickname'   => $fake_name,
            'user_pass'  => wp_generate_password(18, true),
        ]);

        // Update the user_login
        QueryBuilder::query()->table(WP::USERS_TABLE)->where('ID', $id)->update(['user_login' => $fake_username]);

        // Clear meta fields
        $meta_keys_to_clear = [
            'first_name',
            'last_name',
            'email',
            'password',
            'phone',
            'image',
            'shipping_address',
            'billing_address',
            'is_billing_address_same'
        ];

        foreach ($meta_keys_to_clear as $key) {
            UserMeta::delete($id, $key);
        }

        UserMeta::update($id, UserSupport::IS_ANONYMIZED, 1);

        return true;
    }

    public function delete_permanently(int $id)
    {
        require_once ABSPATH . 'wp-admin/includes/user.php';

        $is_deleted_user = wp_delete_user($id);
        $is_deleted_campaigns = (new CampaignService())->delete_by_user_id($id);

        return $is_deleted_campaigns && $is_deleted_user;
    }

    /**
     * Create a new user and return the user id.
     * 
     * @param CreateDonorDTO|CreateBackerDTO|CreateFundraiserDTO $dto
     * @param bool $is_guest
     * 
     * @return int User ID
     * 
     * @throws Exception If the user can't be created.
     */
    public function create_user($dto, $is_guest = false)
    {
        $userdata = [
            'user_login' => $dto->username,
            'user_email' => $dto->email,
            'user_pass' => $dto->password,
            'first_name' => $dto->first_name,
            'last_name' => $dto->last_name,
            'role' => $dto->role,
        ];

        $user_id = wp_insert_user($userdata);

        if (is_wp_error($user_id)) {
            throw new Exception(esc_html($user_id->get_error_message()));
        }

        $dto->created_by = growfund_user()->get_id();

        $meta_input = $dto->get_meta(['username']);

        UserMeta::update_many($user_id, $meta_input);

        if ($is_guest) {
            UserSupport::mark_as_guest($user_id);
        }

        if (growfund_user()->is_admin() || growfund_user()->is_fundraiser()) {
            UserSupport::mark_as_verified($user_id);
        }

        return $user_id;
    }

    protected function is_anonymize_needed(int $id)
    {
        $has_campaigns = (new CampaignService())->get_count_by_user_id($id) > 0;
        $has_contributions = growfund_app()->is_donation_mode() ? (new DonationService())->get_total_count_of_donations($id) > 0 : (new PledgeService())->get_total_count_of_completed_pledges($id) > 0;

        return $has_campaigns || $has_contributions;
    }


    /**
     * Check if user is available for fundraiser
     * 
     * @param int $user_id - User id
     * 
     * @return bool
     */
    protected function is_user_accessible_for_fundraiser(int $user_id)
    {
        $user_created_by = UserSupport::get_created_by($user_id);

        if ((int) $user_created_by === growfund_user()->get_id()) {
            return true;
        }

        if (growfund_user()->has_active_role(Admin::ROLE)) {
            return true;
        }

        $campaign_ids = [];

        if (growfund_user()->has_active_role(Fundraiser::ROLE)) {
            $campaign_ids = growfund_campaign_ids_by_fundraiser();
        }

        if (growfund_user()->has_active_role(Collaborator::ROLE)) {
            $campaign_ids = growfund_campaign_ids_by_collaborator();
        }

        if (empty($campaign_ids)) {
            return false;
        }

        $user = QueryBuilder::query()
            ->table(growfund_app()->is_donation_mode() ? Tables::DONATIONS : Tables::PLEDGES)
            ->where('user_id', $user_id)
            ->where_in('campaign_id', $campaign_ids)
            ->count();

        return !empty($user);
    }


    /**
     * Adds a filter to a user query to filter the results by fundraiser.
     *
     * @param string $table The table name to join (donations/pledges).
     * @return callable The filter function.
     */
    protected function apply_fundraiser_filter(string $table)
    {
        return function ($query) use ($table) {
            $users_table = QueryBuilder::prefix(WP::USERS_TABLE);
            $user_meta_table = QueryBuilder::prefix(WP::USER_META_TABLE);
            $created_by_meta_key = growfund_with_prefix('created_by');

            $campaign_ids = [];

            if (growfund_user()->has_active_role(Fundraiser::ROLE)) {
                $campaign_ids = growfund_campaign_ids_by_fundraiser();
            }
            
            if (growfund_user()->has_active_role(Collaborator::ROLE)) {
                $campaign_ids = growfund_campaign_ids_by_collaborator();
            }

            $query->query_from .= " LEFT JOIN {$user_meta_table} AS user_meta_created_by 
                ON ({$users_table}.ID = user_meta_created_by.user_id AND user_meta_created_by.meta_key = '$created_by_meta_key') ";

            if (empty($campaign_ids)) {
                // if campaign_ids are empty then only show users created by current fundraiser
                $query->query_where .= QueryBuilder::get_db()->prepare(
                    " AND user_meta_created_by.meta_value = %d ",
                    [growfund_user()->get_id()]
				);
                return;
            }

            $placeholders = Arr::make($campaign_ids)->map(function () {
                return '%d';
            })->join(',');

            $query->query_from .= " LEFT JOIN {$table} AS tbl_fundraiser_filter ON tbl_fundraiser_filter.user_id = {$users_table}.ID ";

            $query->query_where .= QueryBuilder::get_db()->prepare(
                " AND (tbl_fundraiser_filter.campaign_id IN ($placeholders) OR user_meta_created_by.meta_value = %d) ",
                array_merge($campaign_ids, [growfund_user()->get_id()])
            );
        };
    }

    /**
     * Adds a filter to a user query to filter the results by campaign.
     *
     * @param string $table The name of the table to filter by.
     * @param int   $campaign_id The ID of the campaign to filter by.
     *
     * @return callable The filter function.
     */
    protected function apply_campaign_filter(int $campaign_id, string $table)
    {
        return function ($query) use ($campaign_id, $table) {
            $users_table = QueryBuilder::prefix(WP::USERS_TABLE);

            $query->query_from .= " INNER JOIN {$table} AS tbl_campaign_filter ON tbl_campaign_filter.user_id = {$users_table}.ID ";
            $query->query_where .= QueryBuilder::get_db()->prepare(" AND tbl_campaign_filter.campaign_id = %d ", $campaign_id);
        };
    }


    /**
     * Get total campaigns created by user.
     * 
     * @param int $user_id
     * 
     * @return int
     */
    public function get_total_campaigns_created(int $user_id)
    {
        return (int) count_user_posts($user_id, Campaign::NAME, true);
    }

    /**
     * @deprecated since 1.1.0
     */
    public function prepare_collaborator_dto(WP_User $user)
    {
        $dto = new UserInfoDTO();

        $dto->id = $user->ID;
        $dto->first_name = $user->first_name;
        $dto->last_name = $user->last_name;
        $dto->display_name = $user->display_name;
        $dto->email = $user->user_email;
        $dto->username = $user->user_login;
        $dto->image = UserSupport::get_avatar_image($user->ID);
        $dto->phone = UserSupport::get_phone_number($user->ID);

        return $dto;
    }
}
