<?php

namespace Growfund\Core;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Capabilities;
use Growfund\Constants\UserTypes\Admin;
use Growfund\Constants\UserTypes\Backer;
use Growfund\Constants\UserTypes\Donor;
use Growfund\Constants\UserTypes\Fundraiser;
use Growfund\Supports\User as UserSupport;
use Growfund\Supports\UserMeta;
use Exception;
use Growfund\Constants\UserTypes\Collaborator;
use Growfund\DTO\User\UserInfoDTO;
use Growfund\Supports\MediaAttachment;

/**
 * @method bool can_switch_themes() Check if user can switch themes
 * @method bool can_edit_themes() Check if user can edit themes
 * @method bool can_activate_plugins() Check if user can activate plugins
 * @method bool can_edit_plugins() Check if user can edit plugins
 * @method bool can_edit_users() Check if user can edit users
 * @method bool can_edit_files() Check if user can edit files
 * @method bool can_manage_options() Check if user can manage options
 * @method bool can_moderate_comments() Check if user can moderate comments
 * @method bool can_manage_categories() Check if user can manage categories
 * @method bool can_manage_links() Check if user can manage links
 * @method bool can_upload_files() Check if user can upload files
 * @method bool can_import() Check if user can import
 * @method bool can_unfiltered_html() Check if user can use unfiltered HTML
 * @method bool can_edit_posts() Check if user can edit posts
 * @method bool can_edit_others_posts() Check if user can edit others posts
 * @method bool can_edit_published_posts() Check if user can edit published posts
 * @method bool can_publish_posts() Check if user can publish posts
 * @method bool can_edit_pages() Check if user can edit pages
 * @method bool can_read() Check if user can read
 * @method bool can_level_10() Check if user has level 10
 * @method bool can_level_9() Check if user has level 9
 * @method bool can_level_8() Check if user has level 8
 * @method bool can_level_7() Check if user has level 7
 * @method bool can_level_6() Check if user has level 6
 * @method bool can_level_5() Check if user has level 5
 * @method bool can_level_4() Check if user has level 4
 * @method bool can_level_3() Check if user has level 3
 * @method bool can_level_2() Check if user has level 2
 * @method bool can_level_1() Check if user has level 1
 * @method bool can_level_0() Check if user has level 0
 * @method bool can_edit_others_pages() Check if user can edit others pages
 * @method bool can_edit_published_pages() Check if user can edit published pages
 * @method bool can_publish_pages() Check if user can publish pages
 * @method bool can_delete_pages() Check if user can delete pages
 * @method bool can_delete_others_pages() Check if user can delete others pages
 * @method bool can_delete_published_pages() Check if user can delete published pages
 * @method bool can_delete_posts() Check if user can delete posts
 * @method bool can_delete_others_posts() Check if user can delete others posts
 * @method bool can_delete_published_posts() Check if user can delete published posts
 * @method bool can_delete_private_posts() Check if user can delete private posts
 * @method bool can_edit_private_posts() Check if user can edit private posts
 * @method bool can_read_private_posts() Check if user can read private posts
 * @method bool can_delete_private_pages() Check if user can delete private pages
 * @method bool can_edit_private_pages() Check if user can edit private pages
 * @method bool can_read_private_pages() Check if user can read private pages
 * @method bool can_delete_users() Check if user can delete users
 * @method bool can_create_users() Check if user can create users
 * @method bool can_unfiltered_upload() Check if user can upload unfiltered files
 * @method bool can_edit_dashboard() Check if user can edit dashboard
 * @method bool can_update_plugins() Check if user can update plugins
 * @method bool can_delete_plugins() Check if user can delete plugins
 * @method bool can_install_plugins() Check if user can install plugins
 * @method bool can_update_themes() Check if user can update themes
 * @method bool can_install_themes() Check if user can install themes
 * @method bool can_update_core() Check if user can update core
 * @method bool can_list_users() Check if user can list users
 * @method bool can_remove_users() Check if user can remove users
 * @method bool can_promote_users() Check if user can promote users
 * @method bool can_edit_theme_options() Check if user can edit theme options
 * @method bool can_delete_themes() Check if user can delete themes
 * @method bool can_export() Check if user can export
 */
class User
{
    /**
     * The WP_User instance
     *
     * @var \WP_User|null
     */
    protected $user = null;

    /**
     * The user meta data with key => value associative array with user id
     *
     * @var array
     */
    protected $user_meta = [];

    /**
     * The user avatar data associative array with user id
     *
     * @var array
     */
    protected $user_avatar = [];

    /**
     * Constructor populates the current user instance
     * 
     * @param int|null $user_id
     *
     * @return void
     */
    public function __construct($user_id = null)
    {
        if (!empty($user_id)) {
            $this->user = \get_user_by('id', $user_id);
        } else {
            $this->user = \wp_get_current_user();
        }
    }

    /**
     * Sync the user by provided user id.
     *
     * @param int $user_id
     * @return static
     */
    public function sync(int $user_id)
    {
        $this->user = \get_user_by('id', $user_id);

        return $this;
    }

    /**
     * Get the current user instance
     *
     * @return WP_User|null
     */
    public function get()
    {
        return $this->user;
    }

    /**
     * Get the current user data
     * 
     * @return UserInfoDTO|null
     */
    public function get_data()
    {
        if (empty($this->get())) {
            return null;
        }

        $dto = new UserInfoDTO();

        $dto->id = $this->get_id();
        $dto->first_name = $this->get_first_name();
        $dto->last_name = $this->get_last_name();
        $dto->display_name = $this->get_display_name();
        $dto->email = $this->get_email();
        $dto->username = $this->get_username();
        $dto->image = $this->get_avatar();
        $dto->phone = $this->get_meta('phone');

        return $dto;
    }

    /**
     * Get the current user meta data
     *
     * @return array
     */
    public function get_user_meta(string $key = '')
    {
        return \get_user_meta($this->get_id(), growfund_with_prefix($key), !empty($key));
    }

    /**
     * Get the current user ID
     *
     * @return int
     */
    public function get_id()
    {
        return $this->user->ID ?? 0;
    }

    /**
     * Get the current user email
     *
     * @return string
     */
    public function get_email()
    {
        return $this->user->user_email ?? '';
    }

    /**
     * Get the username
     *
     * @return string
     */
    public function get_username()
    {
        return $this->user->user_login ?? '';
    }

    /**
     * Get the current user avatar
     *
     * @return array
     */
    public function get_avatar()
    {
        if (isset($this->user_avatar[$this->get_id()])) {
            return $this->user_avatar[$this->get_id()];
        }

        $image = $this->get_meta('image');

        $avatar = !empty($image) ? MediaAttachment::make((int) $image) : null;

        $this->user_avatar[$this->get_id()] = $avatar;

        return $avatar;
    }

    /**
     * Get the current user display name
     *
     * @return string
     */
    public function get_display_name()
    {
        return $this->user->display_name ?? '';
    }

    /**
     * Get the current user first name
     *
     * @return string
     */
    public function get_first_name()
    {
        if (!empty($this->user->first_name)) {
            return $this->user->first_name;
        }

        $display_name = $this->get_display_name();

        return explode(' ', $display_name)[0] ?? '';
    }

    /**
     * Get the current user last name
     *
     * @return string
     */
    public function get_last_name()
    {
        if (!empty($this->user->last_name)) {
            return $this->user->last_name;
        }

        $display_name = explode(' ', $this->get_display_name());

        if (count($display_name) > 1) {
            return array_shift($display_name);
        }

        return '';
    }

    /**
     * Get the current user roles
     *
     * @return array
     */
    public function get_roles()
    {
        return $this->user->roles ?? [];
    }

    /**
     * Set the current user role
     *
     * @param string $role
     * @return bool
     */
    public function set_role($role)
    {
        try {
            if (is_null($this->user)) {
                return false;
            }

            $this->user->set_role($role);
            return true;
        } catch (Exception $error) {
            return false;
        }
    }

    /**
     * Add new role to the current user
     *
     * @param string $role
     * @return bool
     */
    public function add_new_role($role)
    {
        try {
            if (is_null($this->user)) {
                return false;
            }

            $this->user->add_role($role);
            return true;
        } catch (Exception $error) {
            return false;
        }
    }

    /**
     * Check if the current user has a specific role
     *
     * @param string $role
     * @return bool
     */
    public function has_role(string $role)
    {
        return in_array($role, $this->get_roles(), true);
    }

    /**
     * Check if the current user is an admin
     *
     * @return bool
     */
    public function is_admin()
    {
        return $this->has_role(Admin::ROLE);
    }

    /**
     * Check if the current user is a fundraiser
     *
     * @return bool
     */
    public function is_fundraiser()
    {
        return growfund_app()->has_growfund_pro() && $this->has_role(Fundraiser::ROLE);
    }

    /**
     * Check if the current user is a collaborator of a campaign
     *
     * @return bool
     */
    public function is_collaborator()
    {
        return $this->has_role(Collaborator::ROLE);
    }

    /**
     * Check if the current user is a backer
     *
     * @return bool
     */
    public function is_backer()
    {
        return !growfund_app()->is_donation_mode() && $this->has_role(Backer::ROLE);
    }

    /**
     * Check if the current user is a donor
     *
     * @return bool
     */
    public function is_donor()
    {
        return growfund_app()->is_donation_mode() && $this->has_role(Donor::ROLE);
    }

    /**
     * Check if the email is verified or the user is verified.
     *
     * @return boolean
     */
    public function is_verified()
    {
        return UserSupport::is_verified($this->get());
    }

    /**
     * Get the current user role
     *
     * @return string|null
     */
    public function get_active_role()
    {
        if ($this->is_admin()) {
            return Admin::ROLE;
        }

        if ($this->is_fundraiser()) {
            return Fundraiser::ROLE;
        }

        if ($this->is_collaborator()) {
            return Collaborator::ROLE;
        }

        if ($this->is_donor()) {
            return Donor::ROLE;
        }

        if ($this->is_backer()) {
            return Backer::ROLE;
        }

        return null;
    }

    /**
     * Check if the current user has a specific role
     *
     * @param string $role
     * @return bool
     */
    public function has_active_role(string $role)
    {
        return $this->get_active_role() === $role;
    }

    /**
     * Check if the current user is a valid growfund user
     * 
     * @return bool
     */
    public function is_valid_user() {
        return $this->is_admin() || $this->is_fundraiser() || $this->is_collaborator() || $this->is_backer() || $this->is_donor();
    }

    /**
     * Check if the current user is logged in
     *
     * @return bool
     */
    public function is_logged_in()
    {
        return \is_user_logged_in();
    }

    /**
     * Check if the current user can contribute
     *
     * @return bool
     */
    public function can_contribute()
    {
        return $this->is_admin() || $this->is_fundraiser() || $this->is_collaborator() || $this->is_backer() || $this->is_donor();
    }

    /**
     * Get the current user meta
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get_meta(string $key, $default = null)
    {
        if (!$this->get_id()) {
            return $default;
        }

        if (isset($this->user_meta[$this->get_id()]) && isset($this->user_meta[$this->get_id()][$key])) {
            return !is_null($this->user_meta[$this->get_id()][$key]) ? $this->user_meta[$this->get_id()][$key] : $default;
        }
        
        $meta = UserMeta::get($this->get_id(), $key);
        $this->user_meta[$this->get_id()][$key] = $meta;

        return !is_null($meta) && $meta !== '' ? $meta : $default;
    }

    /**
     * Check if the current user is soft deleted
     *
     * @return bool
     */
    public function is_soft_deleted()
    {
        return (bool) $this->get_meta(UserSupport::SOFT_DELETE_KEY, false);
    }

    /**
     * Check if the current user is anonymized
     *
     * @return bool
     */
    public function is_anonymized()
    {
        return (bool) $this->get_meta(UserSupport::IS_ANONYMIZED, false);
    }

    /**
     * Get the current user joined date
     *
     * @return \DateTime|null
     */
    public function get_joined_date()
    {
        $user = $this->get();

        if (empty($user)) {
            return null;
        }
        
        return UserSupport::get_joined_date($user);
    }

    /**
     * Get the current user created_by ID
     *
     * @return string|null
     */
    public function get_created_by()
    {
        return $this->get_meta('created_by');
    }


    /**
     * Checks if the user has a given capability.
     *
     * @param string $capability The capability to check.
     * @param array $args Optional arguments to pass to the capability check.
     * @return bool Whether the user has the capability.
     */
    public function can($capability, ...$args)
    {
        return user_can($this->get_id(), $capability, ...$args);
    }

    /**
     * Magic method to handle dynamic capability checks.
     * Allows calling methods like can_edit_posts(), can_manage_options(), etc.
     * The method name must start with 'can_' followed by a valid capability from Capabilities class.
     *
     * @param string $name The method name being called
     * @param array $arguments Arguments passed to the method (unused)
     * @throws Exception If method doesn't start with 'can_' or capability doesn't exist
     * @return bool Whether the user has the requested capability
     */
    public function __call($name, $arguments = [])
    {
        if (!str_starts_with($name, 'can_')) {
            throw new Exception(sprintf('Method %s does not exist', esc_html($name)));
        }

        $action = preg_replace('/^can_/', '', $name);

        if (!in_array($action, Capabilities::get_constant_values(), true)) {
            throw new Exception(sprintf('Capability %s does not exist', esc_html($action)));
        }

        return $this->can($action, $arguments);
    }
}
