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
     * @var String
     */
    private $authUrl = 'https://api.getgo.com/oauth/authorize';

    /**
     * ResponseKey ottenuto dal redirect all'indirizzo precedente.
     * 
     * @var string
     */
    private $oauthCode;
    
    /**
     * Iniziatialize the auth adapter
     * 
     * @param string $apiKey
     */
    public function __construct($apiKey) {
        $this->apiKey   = $apiKey;
    }

    /**
     * Request the Logon address for starting OAUTH.
     * 
     * @return type
     * @throws \Exception
     */
    public function getAuthorizationLogonUrl() {
        $output = \Citrix\Citrix::send($this->authUrl, 'OAUTH', ['client_id' => $this->apiKey]);

        switch (true) {
            case empty($output):
                throw new \Exception('Empty');

            // se prova ad effettuare un redirect, lo passo all'utente
            case preg_match('~Location: (.*)~i', $output, $match):
                return trim($match[1]);
                
            default:
                throw new \Exception('Invalid');
        }
    }
    
    /**
     * Request authorization 'access_token', 'organizer_key'.
     * 
     * @throws \Exception
     */
    public function applyCredentials() {
        if ($this->oauthCode == null) {
            throw new \Exception('Invalid credentials');
        }        
        
        if ($this->accessToken != null && $this->organizerKey != null) {
            return $this;
        }        
        
        $data = [
            'grant_type' => 'authorization_code',
            'code'       => $this->oauthCode,
            'client_id'  => $this->apiKey
        ];
        $output = \Citrix\Citrix::send($this->authUrl, 'POST', $data, ['Accept' => 'application/json']);
        if (empty($output['access_token'])) {
            throw new \Exception('Invalid access token from Citrix.');
        }
        $this->setAccessToken($output['access_token'])
            ->setOrganizerKey($output['organizer_key']);
        return $this;
    }
        
    /**
     * @param string $oauthCode
     * @return $this
     */
    public function setOauthCode($oauthCode) {
        $this->oauthCode = $oauthCode;
        return $this;
    }
    
}
