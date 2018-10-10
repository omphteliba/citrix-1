<?php

namespace Citrix\Auth;

/**
 * Citrix Authentication
 *
 * The authentication engine of Citrix add a responseKey at the end of CallbackUrl
 * defined in Citrix APP.
 * ?code={responseKey}
 *
 * When opened, the page at this address have to grap the code and store it for
 * requesting a user authorization:
 * curl -X POST -H "Accept:application/json"
 *      -H "Content-Type: application/x-www-form-urlencoded"
 *      "https://api.getgo.com/oauth/access_token"
 *      -d 'grant_type=authorization_code&code={responseKey}&client_id={consumerKey}'
 */
class OAuth extends \Citrix\Auth {

	/**
	 * OAuth authentication URL
	 *
	 * @var string
	 */
	private $authUrl = 'https://api.getgo.com/oauth/v2/authorize';

	/**
	 * Url where to ask for an access_token
	 *
	 * @var string
	 */
	private $authTokenUrl = 'https://api.getgo.com/oauth/v2/token';

	/**
	 * ResponseKey ottenuto dal redirect all'indirizzo precedente.
	 *
	 * @var string
	 */
	private $responseKey;

	/**
	 * Iniziatialize the auth adapter
	 *
	 * @param string $apiKey
	 * @param null   $apiSecret
	 */
	public function __construct($apiKey, $apiSecret = null) {
		$this->apiKey = $apiKey;
		if ($apiSecret !== null) {
			$this->apiSecret = $apiSecret;
		}
	}

	/**
	 * Request the Logon address for starting OAUTH.
	 *
	 * @return type
	 * @throws \Exception
	 */
	public function getAuthorizationLogonUrl() {
		$output = \Citrix\Citrix::send($this->authUrl, 'OAUTH', ['response_type' => 'code', 'client_id' => $this->apiKey], ['Accept' => 'text/plain']);

		switch (TRUE) {
			case empty($output):
				throw new \RuntimeException('Empty');

			// if you try to make a redirect, I pass it to the user
			case preg_match('~Location: (.*)~i', $output, $match):
				return trim($match[1]);

			default:
				throw new \RuntimeException('Invalid! URL: ' . $this->authUrl . ' Output: ' . $output);
		}
	}

	/**
	 * Request authorization 'access_token', 'organizer_key'.
	 *
	 * @return $this
	 * @throws \Exception
	 */
	public function applyCredentials() {
		if ($this->accessToken != null && $this->organizerKey != null) {
			return $this;
		}

		if ($this->responseKey == null) {
			throw new \RuntimeException('Invalid responseKey for obtaining Citrix credentials');
		}

		$data = [
			'grant_type' => 'authorization_code',
			'code'       => $this->responseKey,
		];
		// OLD: curl -X POST "https://api.getgo.com/oauth/access_token"
		// -H "Accept:application/json"
		// -H "Content-Type: application/x-www-form-urlencoded"
		// -d 'grant_type=authorization_code&code={responseKey}&client_id={consumerKey}'

		// NEW: curl -X POST "https://api.getgo.com/oauth/v2/token" \
		//  -H "Authorization: Basic {Base64 Encoded consumerKey and consumerSecret}" \
		//  -H "Accept:application/json" \
		//  -H "Content-Type: application/x-www-form-urlencoded" \
		//  -d "grant_type=authorization_code&code={responseKey}&redirect_uri=http%3A%2F%2Fcode.example.com"

		$output = \Citrix\Citrix::send($this->authTokenUrl, 'POST', $data, [
			'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':' . $this->apiSecret),
			'Content-Type'  => \Citrix\Citrix::MIME_X_WWW_FORM_URLENCODED,
			'Accept'        => \Citrix\Citrix::MIME_JSON,
		], FALSE);
		$this->process($output);

		return $this;
	}

	/**
	 * @param string $responseKey
	 *
	 * @return $this
	 */
	public function setResponseKey($responseKey) {
		$this->responseKey = $responseKey;

		return $this;
	}

}
