<?php

namespace Growfund\Http;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Response as BaseResponse;
use stdClass;
use WP_REST_Response;

/**
 * Handles standardized REST API responses for the growfund plugin.
 *
 * @since 1.0.0
 */
class Response extends WP_REST_Response implements BaseResponse
{
    /**
     * HTTP status code for a successful request.
     *
     * @since 1.0.0
     * @var int
     */
    const OK = 200;

    /**
     * HTTP status code for a successfully created resource.
     *
     * @since 1.0.0
     * @var int
     */
    const CREATED = 201;

    /**
     * HTTP status code for a successful request with no content.
     *
     * @since 1.0.0
     * @var int
     */
    const NO_CONTENT = 204;

    /**
     * HTTP status code for a multi-status response.
     *
     * @since 1.0.0
     * @var int
     */
    const MULTI_STATUS = 207;

    /**
     * HTTP status code for a bad request.
     *
     * @since 1.0.0
     * @var int
     */
    const BAD_REQUEST = 400;

    /**
     * HTTP status code for an unauthorized request.
     *
     * @since 1.0.0
     * @var int
     */
    const UNAUTHORIZED = 401;

    /**
     * HTTP status code when authentication is required and has failed or has not yet been provided.
     *
     * @since 1.0.0
     * @var int
     */
    const FORBIDDEN = 403;

    /**
     * HTTP status code when a requested resource is not found.
     *
     * @since 1.0.0
     * @var int
     */
    const NOT_FOUND = 404;

    /**
     * HTTP status code when a request method is not allowed.
     *
     * @since 1.0.0
     * @var int
     */
    const METHOD_NOT_ALLOWED = 405;

    /**
     * HTTP status code when a conflict occurs.
     *
     * @since 1.0.0
     * @var int
     */
    const CONFLICT = 409;

    /**
     * HTTP status code when a request is unprocessable.
     *
     * @since 1.0.0
     * @var int
     */
    const UNPROCESSABLE_ENTITY = 422;

    /**
     * HTTP status code for too many requests (rate limiting).
     *
     * @since 1.0.0
     * @var int
     */
    const TOO_MANY_REQUESTS = 429;

    /**
     * HTTP status code for internal server error.
     *
     * @since 1.0.0
     * @var int
     */
    const INTERNAL_SERVER_ERROR = 500;

    /**
     * HTTP status code for not implemented.
     *
     * @since 1.0.0
     * @var int
     */
    const NOT_IMPLEMENTED = 501;

    /**
     * HTTP status code for service unavailable.
     *
     * @since 1.0.0
     * @var int
     */
    const SERVICE_UNAVAILABLE = 503;

    /**
     * The instance of the response.
     *
     * @since 1.0.0
     * @var static
     */
    protected static $instance = null;

    /**
     * Get the instance of the response.
     *
     * @since 1.0.0
     * @return static
     */
    protected static function get_instance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Create a new response instance.
     * This is an alias for `get_instance()` but publicly available.
     *
     * @since 1.0.0
     * @return static
     */
    public static function create()
    {
        return static::get_instance();
    }

    /**
     * Set the headers for the response.
     *
     * @since 1.0.0
     *
     * @param array $headers The headers to set.
     * @return static
     */
    public function with_headers(array $headers)
    {
        $this->set_headers($headers);

        return $this;
    }

    /**
     * Format the response data.
     *
     * @since 1.0.0
     *
     * @param array $response The response data.
     * @param int   $code The HTTP status code.
     * @return array
     */
    protected function format(array $response, int $code)
    {
        $is_success = $code >= 200 && $code < 300;
        $message = $response['message'] ?? '';

        if (!$is_success) {
            $errors = !empty($response['errors']) ? $response['errors'] : new stdClass();

            return [
                'success' => false,
                'errors' => $errors,
                'message' => $message,
            ];
        }

        $data = $response['data'] ?? [];

        return [
            'success' => true,
            'data' => $data,
            'message' => $message,
        ];
    }

    /**
     * Return a JSON-formatted REST response.
     *
     * @since 1.0.0
     *
     * @param array $data The response data.
     * @param int   $code Optional. HTTP status code. Default Response::OK.
     * @return static
     */
    public function json(array $data, int $code = 200)
    {
        $this->set_data($this->format($data, $code));
        $this->set_status($code);
        $this->header('Content-Type', 'application/json', true);

        return $this;
    }

    /**
     * Return a text-formatted REST response.
     *
     * @since 1.0.0
     *
     * @param string $data The response data.
     * @param int    $code Optional. HTTP status code. Default Response::OK.
     * @return static
     */
    public function text(string $data, int $code = 200)
    {
        $this->set_data($data);
        $this->set_status($code);
        $this->header('Content-Type', 'text/plain', true);

        return $this;
    }
}
