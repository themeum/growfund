<?php

namespace Growfund\Managers;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\OptionKeys;
use Growfund\Contracts\Executor;
use Growfund\Supports\Option;

class VersionManager implements Executor {
    /**
     * @var static
     */
    protected static $instance;

    /**
     * @var array
     */
    protected $version_details = [];

    /**
     * @return VersionManager
     */
    public static function get_instance()
    {
        if (static::$instance) {
            return static::$instance;
        }

        static::$instance = new static();

        return static::$instance;
    }

    /**
     * Execute the version manager.
     * @return void
     */
    public function run()
    {
        if ($this->is_current_version_already_installed()) {
            return;
		}
            
        $plugin_updates  = require GROWFUND_DIR_PATH . 'configs/plugin-updates.php';
        $plugin_update_keys = array_keys($plugin_updates);

        usort($plugin_update_keys, function ($a, $b) {
			return version_compare($a, $b);
		});
        
        $installed_versions = $this->get_installed_versions();

        foreach ($plugin_update_keys as $version) {
            if (version_compare($version, GROWFUND_VERSION, '<=') && !$this->is_already_installed($version)) {
                $callback = $plugin_updates[$version];
                $callback();
                $installed_versions[] = $version;
            }
        }

        $this->update_installed_version($installed_versions);
    }

    /**
     * @return array
     */
    protected function get_installed_version_details()
    {
        if (!empty($this->version_details)) {
            return $this->version_details;
        }

        $default = [
            'current_version' => '0.0.0',
            'installed_versions' => []
        ];

        $version_details = Option::get(OptionKeys::INSTALLED_VERSION, $default);

        // For backward compatibility check. If the option is not set or is not an array, set it to the default.
        if (
            !is_array($version_details) 
            || !isset($version_details['installed_versions'], $version_details['current_version'])
        ) {
            $version_details = $default;
        }

        $this->version_details = $version_details;

        return $this->version_details;
    }

    /**
     * @return string
     */
    protected function get_current_installed_version()
    {
        return $this->get_installed_version_details()['current_version'];
    }

    /**
     * @return array
     */
    protected function get_installed_versions()
    {
        return $this->get_installed_version_details()['installed_versions'];
    }

    /**
     * @param array $installed_versions
     * @return bool
     */
    protected function update_installed_version($installed_versions = [])
    {
        $installed_versions = array_unique($installed_versions);
        usort($installed_versions, function ($a, $b) {
			return version_compare($a, $b);
		});

        $version_details = [
            'current_version' => GROWFUND_VERSION,
            'installed_versions' => $installed_versions
        ];

        $is_updated = Option::update(OptionKeys::INSTALLED_VERSION, $version_details);

        if (!empty($is_updated)) {
            $this->version_details = $version_details;
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function is_current_version_already_installed()
    {
        return (bool) version_compare($this->get_current_installed_version(), GROWFUND_VERSION, '>=');
    }

    /**
     * @param string $version
     * @return bool
     */
    protected function is_already_installed(string $version)
    {
        $installed_versions = $this->get_installed_versions();

        return in_array($version, $installed_versions, true);
    }
}
