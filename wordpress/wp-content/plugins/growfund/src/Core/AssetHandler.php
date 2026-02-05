<?php

namespace Growfund\Core;

use Closure;

defined( 'ABSPATH' ) || exit;

class AssetHandler {
    protected $assets = [];
    protected $loaded_assets = [];

    public function register(string $hook_name, string $view_name, Closure $closure) {
        if ($this->is_already_loaded($hook_name, $view_name)) {
            return;
        }

        $this->assets[$hook_name][$view_name] = $closure;
    }

    public function load_assets() 
    {
        foreach ($this->assets as $hook_name => $view_names) {
            foreach ($view_names as $view_name => $closure) {
                if ($this->is_already_loaded($hook_name, $view_name)) {
                    continue;
                }

                $this->loaded_assets[$hook_name][$view_name] = $closure;
                $closure();
            }
        }
    }

    protected function is_already_loaded(string $hook_name, $view_name) {
        return isset($this->loaded_assets[$hook_name]) && isset($this->loaded_assets[$hook_name][$view_name]);
    }
}
