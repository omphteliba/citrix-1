<?php

namespace CitrixOAuth2\Auth;

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
class OAuth extends \CitrixOAuth2\Auth {

	/**
	 * OAuth authentication URL
	 *
	 * @var string
	 */
	private $authUrl = 'https://api.getgo.com/oauth/v2/authorize';

	/**
	 * Url where to ask for an access_token and refresh the token
	 *
	 * @var string
	 */
	private $authTokenUrl = 'https://api.getgo.com/oauth/v2/token';

	/**
	 * ResponseKey obtained from the redirect to the previous address
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
	 * @return string
	 * @throws \Exception
	 */
	public function getAuthorizationLogonUrl() {
		$output = \CitrixOAuth2\Citrix::send($this->authUrl, 'OAUTH', ['response_type' => 'code', 'client_id' => $this->apiKey], ['Accept' => 'text/plain']);

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

		// NEW: curl -X POST "https://api.getgo.com/oauth/v2/token" \
		//  -H "Authorization: Basic {Base64 Encoded consumerKey and consumerSecret}" \
		//  -H "Accept:application/json" \
		//  -H "Content-Type: application/x-www-form-urlencoded" \
		//  -d "grant_type=authorization_code&code={responseKey}&redirect_uri=http%3A%2F%2Fcode.example.com"

		$output = \CitrixOAuth2\Citrix::send($this->authTokenUrl, 'POST', $data, [
			'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':' . $this->apiSecret),
			'Content-Type'  => \CitrixOAuth2\Citrix::MIME_X_WWW_FORM_URLENCODED,
			'Accept'        => \CitrixOAuth2\Citrix::MIME_JSON,
		], FALSE);
		$this->process($output);

		return $this;
	}

	/**
	 * @return $this
	 * @throws \Exception
	 */
	public function refreshTokenInfo() {
		if ($this->refreshToken == null) {
			throw new \RuntimeException('Invalid refreshToken for refreshing the access token');
		}

		if ($this->refreshTokenExpired()) {
			throw new \RuntimeException('RefreshToken expired');
		}

		$data = [
			'grant_type'    => 'refresh_token',
			'refresh_token' => $this->refreshToken,
		];

		//curl -X POST "https://api.getgo.com/oauth/v2/token" \
		//  -H "Authorization: Basic {Base64 Encoded consumerKey and consumerSecret}" \
		//  -H "Accept:application/json" \
		//  -H "Content-Type: application/x-www-form-urlencoded" \
		//  -d "grant_type=refresh_token&refresh_token={refresh_token}"

		// Aus dem Postman Tool nach erfolgreichem Token Refresh
		//<?php
		//
		//$curl = curl_init();
		//
		//curl_setopt_array($curl, array(
		//  CURLOPT_URL => "https://api.getgo.com/oauth/v2/token",
		//  CURLOPT_RETURNTRANSFER => true,
		//  CURLOPT_ENCODING => "",
		//  CURLOPT_MAXREDIRS => 10,
		//  CURLOPT_TIMEOUT => 30,
		//  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		//  CURLOPT_CUSTOMREQUEST => "POST",
		//  CURLOPT_POSTFIELDS => "grant_type=refresh_token&refresh_token=ZIPB5ImTHoG1DUecHNF7cBQVNACgjJ9p",
		//  CURLOPT_HTTPHEADER => array(
		//    "Accept: application/json",
		//    "Content-Type: application/x-www-form-urlencoded",
		//    "Postman-Token: ce2c1fa2-c18d-4a9e-a2c0-17045d1b3bb4",
		//    "cache-control: no-cache"
		//  ),
		//));
		//
		//$response = curl_exec($curl);
		//$err = curl_error($curl);
		//
		//curl_close($curl);
		//
		//if ($err) {
		//  echo "cURL Error #:" . $err;
		//} else {
		//  echo $response;
		//}

		$output = \CitrixOAuth2\Citrix::send($this->authTokenUrl, 'POST', $data, [
			'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':' . $this->apiSecret),
			'Content-Type'  => \CitrixOAuth2\Citrix::MIME_X_WWW_FORM_URLENCODED,
			'Accept'        => \CitrixOAuth2\Citrix::MIME_JSON,
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

	/**
	 * @return bool
	 */
	public function refreshTokenExpired() {
		$tokenExpireDate = new \DateTime($this->getTokenDate());
		$tokenExpireDate->modify('+1 year');

		return $tokenExpireDate < new \DateTime();
	}

}
