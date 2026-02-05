<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

use Exception;

/**
 * Location Helper Class
 * 
 * Provides methods to retrieve and format location data (countries, states, cities)
 * for use in dropdown menus and forms throughout the application.
 */
class Location
{
    /**
     * Cache for location data to avoid repeated file reads
     */
    protected static $cache = [];

    /**
     * Path to the countries JSON file
     */
    const COUNTRIES_FILE_PATH = GROWFUND_DIR_PATH . 'resources/data/countries.json';

    const REST_OF_THE_WORLD = 'rest-of-the-world';

    /**
     * Get all countries with their basic information
     * 
     * @param bool $include_rest_of_the_world Whether to include the "Rest of the World" option
     *
     * @return array Array of countries with code, name, and flag
     */
    public static function get_countries(bool $include_rest_of_the_world = false)
    {
        if (!isset(static::$cache['countries'])) {
            $data = static::load_countries_data();
            static::$cache['countries'] = array_map(function ($country) {
                return [
                    'value' => $country['code'],
                    'label' => $country['name'],
                    'flag' => $country['flag'] ?? '',
                    'phone_code' => $country['phone_code'] ?? '',
                    'currency' => $country['currency'] ?? '',
                    'currency_symbol' => $country['currency_symbol'] ?? '',
                    'states' => isset($country['states']) ? array_map(function ($state) {
                        return [
                            'value' => $state['id'],
                            'label' => $state['name']
                        ];
                    }, $country['states']) : []
                ];
            }, $data);
        }

        $countries = static::$cache['countries'];

        if ($include_rest_of_the_world) {
            $countries[] = [
				'value' => static::REST_OF_THE_WORLD,
				'label' => __('Rest of the World', 'growfund'),
				'flag' => '🌐',
				'phone_code' => '',
				'currency' => '',
				'currency_symbol' => '',
				'states' => []
			];
        }

        return $countries;
    }

    /**
     * Get countries formatted for dropdown options
     *
     * @param bool $include_empty Whether to include an empty option
     * @param string $empty_label Label for the empty option
     * @param bool $include_rest_of_the_world Whether to include the "Rest of the World" option
     * @return array Array formatted for dropdown options
     */
    public static function get_countries_for_dropdown(
        bool $include_empty = true, string $empty_label = 'Select Country', bool $include_rest_of_the_world = false
    ){
        $countries = static::get_countries($include_rest_of_the_world);

        if ($include_empty) {
            array_unshift($countries, [
                'value' => '',
                'label' => $empty_label
            ]);
        }

        return $countries;
    }

    /**
     * Get states/provinces for a specific country
     *
     * @param string $country_code ISO 2-letter country code
     * @return array Array of states with id and name
     */
    public static function get_states(string $country_code)
    {
        $cacheKey = "states_{$country_code}";

        if (!isset(static::$cache[$cacheKey])) {
            $data = static::load_countries_data();
            $country = static::find_country_by_code($data, $country_code);

            static::$cache[$cacheKey] = [];
            if ($country && isset($country['states'])) {
                static::$cache[$cacheKey] = array_map(function ($state) {
                    return [
                        'value' => $state['id'],
                        'label' => $state['name']
                    ];
                }, $country['states']);
            }
        }

        return static::$cache[$cacheKey];
    }

    /**
     * Get states formatted for dropdown options
     *
     * @param string|null $country_code ISO 2-letter country code
     * @param bool $include_empty Whether to include an empty option
     * @param string $empty_label Label for the empty option
     * @return array Array formatted for dropdown options
     */
    public static function get_states_for_dropdown($country_code, bool $include_empty = true, string $empty_label = 'Select State')
    {
        if (empty($country_code)) {
            return [];
        }
        
        $states = static::get_states($country_code);

        if ($include_empty) {
            array_unshift($states, [
                'value' => '',
                'label' => $empty_label
            ]);
        }

        return $states;
    }

    /**
     * Get country information by code
     *
     * @param string $country_code ISO 2-letter country code
     * @param bool $include_rest_of_the_world Whether to include the "Rest of the World" option
     * @return array|null Country information or null if not found
     */
    public static function get_country_by_code(string $country_code, bool $include_rest_of_the_world = false)
    {
        $countries = static::get_countries($include_rest_of_the_world);

        foreach ($countries as $country) {
            if ($country['value'] === strtoupper($country_code)) {
                return $country;
            }
        }

        return null;
    }

    public static function get_pretty_location(string $location, bool $include_rest_of_the_world = false)
    {
        if (empty($location)) {
            return '';
        }

        $location = explode(':', $location);

        $country = static::get_country_by_code($location[0], $include_rest_of_the_world);

        $state = '';

        foreach ($country['states'] as $state_data) {
            if ($state_data['value'] === $location[1]) {
                $state = $state_data['label'];
                break;
            }
        }

        return sprintf('%s, %s', $state, $country['label']);
    }

    /**
     * Load countries data from JSON file
     *
     * @return array Array of countries data
     * @throws Exception If file cannot be read or parsed
     */
    protected static function load_countries_data()
    {
        if (!file_exists(static::COUNTRIES_FILE_PATH)) {
            throw new Exception(esc_html__('Countries data file not found.', 'growfund'));
        }

        $json_content = file_get_contents(static::COUNTRIES_FILE_PATH);

        if ($json_content === false) {
            throw new Exception(esc_html__('Failed to read countries data file.', 'growfund'));
        }

        $data = json_decode($json_content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception(esc_html__('Failed to parse countries data file.', 'growfund'));
        }

        return $data;
    }

    /**
     * Find a country by its code in the data array
     *
     * @param array $data Countries data array
     * @param string $country_code ISO 2-letter country code
     * @return array|null Country data or null if not found
     */
    protected static function find_country_by_code(array $data, string $country_code)
    {
        $country_code = strtoupper($country_code);

        foreach ($data as $country) {
            if ($country['code'] === $country_code) {
                return $country;
            }
        }

        return null;
    }
}
