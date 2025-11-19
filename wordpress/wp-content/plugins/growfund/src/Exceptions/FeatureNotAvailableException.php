<?php

namespace Growfund\Exceptions;

defined( 'ABSPATH' ) || exit;

use Exception;

class FeatureNotAvailableException extends Exception
{
    protected $feature_name;
    protected $error_code;

	/**
	 * Constructor for the FeatureNotAvailableException class.
	 *
	 * @param string $message The exception message.
	 * @param string $feature_name The name of the feature that is not available.
	 * @param int $code The exception code.
	 * @param string $error_code The error code for the exception.
	 * @param null|Exception $previous The previous exception, if any.
	 *
	 * @since 1.0.0
	 */
    public function __construct(string $message = '', string $feature_name = '', int $code = 0, string $error_code = 'FEATURE_NOT_AVAILABLE', $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->feature_name = $feature_name;
        $this->error_code = $error_code;
    }

    public function get_feature_name()
    {
        return $this->feature_name;
    }

    public function get_error_code()
    {
        return $this->error_code;
    }

    public function to_array()
    {
        return [
            'message' => $this->getMessage(),
            'feature_name' => $this->feature_name,
            'error_code' => $this->error_code,
            'code' => $this->getCode()
        ];
    }
}
