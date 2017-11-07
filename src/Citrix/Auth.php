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
    
    abstract public function applyCredentials();
    
    /**
     * @return the $accessToken
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
