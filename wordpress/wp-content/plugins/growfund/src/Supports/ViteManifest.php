<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

use Exception;
use InvalidArgumentException;
use Throwable;

class ViteManifest
{
    /**
     * The manifest configurations
     *
     * @var array
     */
    protected $manifest;

    /**
     * The entries from the manifest
     *
     * @var array
     */
    protected $entries;

    /**
     * The algorithm to use for hashing
     *
     * @var string
     */
    protected $algorithm;

    /**
     * The base URI
     *
     * @var string
     */
    protected $base_uri;

    /**
     * The base path
     *
     * @var string
     */
    protected $base_path;

    /**
     * Vite manifest constructor
     *
     * @param string $base_path
     * @param string $base_uri
     * @param string $algorithm
     */
    public function __construct(string $base_path, string $base_uri, string $algorithm = "sha256")
    {
        $manifest_file = $base_path . '/.vite/manifest.json';

        if (!file_exists(realpath($manifest_file))) {
            throw new Exception(esc_html("Manifest file does not exist: $manifest_file"));
        }

        if (!wp_parse_url($base_uri)) {
            throw new Exception(esc_html("Failed to parse URL: $base_uri"));
        }

        $this->base_uri = $base_uri;

        if (!wp_parse_url($base_path)) {
            throw new Exception(sprintf('Failed to parse path: %s', esc_html($base_path)));
        }

        $this->base_path = $base_path;

        if (!in_array($algorithm, ["sha256", "sha384", "sha512", ":manifest:"], true)) {
            throw new InvalidArgumentException(esc_html("Unsupported hashing algorithm: $algorithm"));
        }

        $this->algorithm = $algorithm;

        try {
            $this->manifest = json_decode(
                file_get_contents($manifest_file),
                true
            );
        } catch (Throwable $error_message) {
            throw new Exception(esc_html("Failed loading manifest: $error_message"));
        }
    }

    /**
     * Returns the contents of the manifest file.
     *
     * @return array
     */
    public function get_manifest(): array
    {
        return $this->manifest;
    }

    /**
     * Returns the entrypoint from the manifest.
     *
     * @param string $entrypoint
     * @param bool $hash (optional)
     * @return array
     */
    public function get_entrypoint(string $entrypoint, bool $hash = true): array
    {
        return isset($this->manifest[$entrypoint]) ? [
            "hash" => $hash ? $this->get_file_hash($this->manifest[$entrypoint]) : null,
            "url" => $this->get_url($this->manifest[$entrypoint]["file"])
        ] : [];
    }

    /**
     * Returns all entrypoints from the manifest.
     *
     * @return array
     */
    public function get_entrypoints(): array
    {
        if (!isset($this->entries)) {
            $this->entries = array_filter($this->manifest, function($entry) {
                return isset($entry['isEntry']) && $entry['isEntry'] === true;
            });
        }

        return $this->entries;
    }

    /**
     * Returns all imports for a file listed in the manifest.
     *
     * @param string $entrypoint
     * @param bool $hash (optional)
     * @return array
     */
    public function get_imports(string $entrypoint, bool $hash = true): array
    {
        if (!isset($this->manifest[$entrypoint]) || !isset($this->manifest[$entrypoint]["imports"]) || !is_array($this->manifest[$entrypoint]["imports"])) {
            return [];
        }

        return array_filter(
            array_map(function ($import, $hash) {
                return isset($this->manifest[$import]["file"]) ? [
                    "hash" => $hash ? $this->get_file_hash($this->manifest[$import]) : null,
                    "url" => $this->get_url($this->manifest[$import]["file"])
                ] : [];
            }, $this->manifest[$entrypoint]["imports"], [$hash])
        );
    }

    /**
     * Returns all stylesheets for a file listed in the manifest.
     *
     * @param string $entrypoint
     * @param bool $hash (optional)
     * @return array
     */
    public function get_styles(string $entrypoint, bool $hash = true): array
    {
        if (!isset($this->manifest[$entrypoint])) {
            return [];
        }

        if (isset($this->manifest[$entrypoint]["file"]) && str_ends_with($this->manifest[$entrypoint]["file"], '.css')) {
            return [
                [
                    "hash" => $hash ? $this->get_file_hash($this->manifest[$entrypoint]) : null,
                    "url" => $this->get_url($this->manifest[$entrypoint]["file"])
                ]
            ];
        }

        $styles = [];

        if (isset($this->manifest[$entrypoint]["css"]) && is_array($this->manifest[$entrypoint]["css"])) {
            $entryStyles = array_filter(
                array_map(function ($style, $hash) {
                    return isset($style) ? [
                        "hash" => $hash ? $this->calculate_file_hash($style) : null,
                        "url" => $this->get_url($style)
                    ] : [];
                }, $this->manifest[$entrypoint]["css"], [$hash])
            );
            $styles = array_merge($styles, $entryStyles);
        }

        if (isset($this->manifest[$entrypoint]["imports"]) && is_array($this->manifest[$entrypoint]["imports"])) {
            foreach ($this->manifest[$entrypoint]["imports"] as $import) {
                if (isset($this->manifest[$import]) && isset($this->manifest[$import]["css"]) && is_array($this->manifest[$import]["css"])) {
                    $importStyles = array_filter(
                        array_map(function ($style, $hash) {
                            return isset($style) ? [
                                "hash" => $hash ? $this->calculate_file_hash($style) : null,
                                "url" => $this->get_url($style)
                            ] : [];
                        }, $this->manifest[$import]["css"], [$hash])
                    );
                    $styles = array_merge($styles, $importStyles);
                }
            }
        }

        $uniqueStyles = [];
        $seenUrls = [];
        foreach ($styles as $style) {
            if (isset($style['url']) && !in_array($style['url'], $seenUrls, true)) {
                $seenUrls[] = $style['url'];
                $uniqueStyles[] = $style;
            }
        }
        return $uniqueStyles;
    }

    /**
     * Retrieves the pre-calculated hash from the manifest or calculates it.
     *
     * @param array $entrypoint
     * @return string
     */
    protected function get_file_hash(array $entrypoint): string
    {
        if ($this->algorithm === ":manifest:" && $entrypoint["integrity"]) {
            return $entrypoint["integrity"];
        }

        return $this->calculate_file_hash($entrypoint["file"]);
    }

    /**
     * Calculates the hash of a file.
     *
     * @param string $file
     * @return string
     */
    protected function calculate_file_hash(string $file): string
    {
        return "{$this->algorithm}-" . base64_encode(
            openssl_digest(
                file_get_contents(
                    $this->get_path($file)
                ),
                $this->algorithm,
                true
            )
        );
    }

    /**
     * Resolves URL for a given file path.
     *
     * @param string $relativePath
     * @return string
     */
    protected function get_path(string $relative_path): string
    {
        return $this->base_path . $relative_path;
    }

    protected function get_url(string $relative_uri): string
    {
        return $this->base_uri . $relative_uri;
    }
}
