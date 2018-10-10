<?php

namespace Citrix;

/**
 * Description of Auth
 *
 * @author DalPraS
 */
abstract class Auth {

    /**
     * API key or Secret Key in Citrix's Developer Portal
     *
     * @var string
     */
    protected $apiKey;

	/**
	 * Consumer Secret in Citrix's Developer Portal
	 *
	 * @var string
	 */
	protected $apiSecret;

	/**
	 * @return mixed
	 */
	public function getApiSecret() {
		return $this->apiSecret;
	}

	/**
	 * @param mixed $apiSecret
	 */
	public function setApiSecret($apiSecret): void {
		$this->apiSecret = $apiSecret;
	}

    /**
     * Access Token
     *
     * @var string
     */
    protected $accessToken;

    /**
     * Organizer Key
     *
     * @var int
     */
    protected $organizerKey;

    /**
     * Error list
     *
     * @var array
     */
    private $errors = [];

    abstract public function applyCredentials();

    /**
     * Process the citrix Authentication output.
     *
     * @param mixed $output
     * @throws \Exception
     */
    public function process($output){
        switch (true) {
            case empty($output):
                $this->addError('Empty response from CITRIX');
                break;
            case isset($output['msg']):
                $this->addError($output['msg']);
                break;
            case isset($output['int_error_code']):
                $this->addError($output['int_error_code']);
                break;
            case isset($output['access_token']) && isset($output['organizer_key']):
                $this->setAccessToken($output['access_token'])
                     ->setOrganizerKey($output['organizer_key']);
                break;
            case is_array($output):
                $this->setErrors($output);
                break;
            default:
                throw new \Exception("Invalid Output: {$output}");
        }
    }

    /**
     * @return $accessToken
     */
    public function getAccessToken() {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken($accessToken) {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @return $organizerKey
     */
    public function getOrganizerKey() {
        return $this->organizerKey;
    }

    /**
     * @param int $organizerKey
     */
    public function setOrganizerKey($organizerKey) {
        $this->organizerKey = $organizerKey;
        return $this;
    }

    public function getErrorsAsString() {
        return implode("n", $this->errors);
    }

    public function hasErrors() {
        return count($this->errors) > 0;
    }

    public function addError($error) {
        $this->errors[] = $error;
        return $this;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function setErrors($errors) {
        $this->errors = $errors;
        return $this;
    }

}
