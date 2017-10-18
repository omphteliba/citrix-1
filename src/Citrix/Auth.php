<?php

namespace Citrix;

/**
 * Citrix Authentication
 *
 * Use this class to authenticate into Citrix APIs.
 */
class Auth  {

    /**
     * Authentication URL
     * @var String
     */
    private $authorizeUrl = 'https://api.getgo.com/oauth/access_token';

    /**
     * API key or Secret Key in Citrix's Developer Portal
     * @var String
     */
    private $apiKey;

    /**
     * Access Token
     * @var String
     */
    private $accessToken;

    /**
     * Organizer Key
     * @var int
     */
    private $organizerKey;

    /**
     * Iniziatialize the auth adapter
     * 
     * @param string $apiKey
     */
    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
    }

    /**
     * Authenticate by passing username and password. Those are
     * the same username and password that you use to login to
     * www.gotowebinar.com
     *
     * @param String $username
     * @param String $password
     */
    public function auth($username, $password) {

        if ($this->getApiKey() == null) {
            throw new Exception('Direct Authentication requires API key. Please provide API key.');
        }

        if ( $username == null || $password == null ) {
            throw new Exception('Direct Authentication requires username and password. Please provide username and password.');
        }

        $params = [
            'grant_type' => 'password',
            'user_id'    => $username,
            'password'   => $password,
            'client_id'  => $this->getApiKey()
        ];

        $response = \Citrix\Citrix::send($this->authorizeUrl, 'GET', $params, null);
        
        switch (true) {
            case empty($response):
                throw new Exception('Empty response from auth adapter!');

            case isset($response['int_err_code']):
                throw new Exception($response['msg']);

            default:
                $this   ->setAccessToken($response['access_token'])
                        ->setOrganizerKey($response['organizer_key']);
        }        
        return $this;
    }

    /**
     * @return the $apiKey
     */
    public function getApiKey() {
        return $this->apiKey;
    }

    /**
     * @param String $apiKey
     */
    public function setApiKey($apiKey) {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * @return the $accessToken
     */
    public function getAccessToken() {
        return $this->accessToken;
    }

    /**
     * @param String $accessToken
     */
    public function setAccessToken($accessToken) {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @return the $authorizeUrl
     */
    public function getAuthorizeUrl() {
        return $this->authorizeUrl;
    }

    /**
     * @param string $authorizeUrl
     */
    public function setAuthorizeUrl($authorizeUrl) {
        $this->authorizeUrl = $authorizeUrl;
        return $this;
    }

    /**
     * @return the $organizerKey
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

}
