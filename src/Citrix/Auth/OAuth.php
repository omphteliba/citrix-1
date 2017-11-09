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
    private $authUrl = 'https://api.getgo.com/oauth/authorize';

    /**
     * Url where to ask for an access_token
     *
     * @var string
     */
    private $authTokenUrl =  'https://api.getgo.com/oauth/access_token';
    
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
        $output = \Citrix\Citrix::send($this->authUrl, 'OAUTH', ['client_id' => $this->apiKey], ['Accept' => 'text/plain']);

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
     * @return $this
     * @throws \Exception
     */
    public function applyCredentials() {
        if ($this->accessToken != null && $this->organizerKey != null) {
            return $this;
        }
        
        if ($this->responseKey == null) {
            throw new \Exception('Invalid responseKey for obtaining Citrix credentials');
        }
        
        $data = [
            'grant_type' => 'authorization_code',
            'code'       => $this->responseKey,
            'client_id'  => $this->apiKey
        ];
        // curl -X POST -H "Accept:application/json" 
        // -H "Content-Type: application/x-www-form-urlencoded" "https://api.getgo.com/oauth/access_token" 
        // -d 'grant_type=authorization_code&code={responseKey}&client_id={consumerKey}'
        $output = \Citrix\Citrix::send($this->authTokenUrl, 'POST', $data, [
            'Content-Type' => \Citrix\Citrix::MIME_X_WWW_FORM_URLENCODED,
            'Accept'       => \Citrix\Citrix::MIME_JSON
        ]);
        $this->process($output);
        return $this;
    }
        
    /**
     * @param string $responseKey
     * @return $this
     */
    public function setResponseKey($responseKey) {
        $this->responseKey = $responseKey;
        return $this;
    }
    
}
