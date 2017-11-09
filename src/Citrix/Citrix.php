<?php

namespace Citrix;

/**
 * Citrix data loader
 * 
 * Use Citrix to connect to GoToWebinar Citrix Service for direct 
 * interoperability.
 */
class Citrix {
    
    const endpoint = 'https://api.getgo.com';

    /**
     * Mime content types
     */
    const MIME_X_WWW_FORM_URLENCODED = 'application/x-www-form-urlencoded';
    const MIME_MULTIPART_FORM_DATA   = 'multipart/form-data';
    const MIME_JSON                  = 'application/json';

    /**
     * Authentication Client
     * 
     * @var Auth
     */
    private $auth;
    
    public function __construct(Auth $auth) {
        $this->auth = $auth;
    }

    /**
     * Map response to an entity or collection of entities.
     * 
     * @param array $response 
     *          The response to process for having a collection or object
     * @param boolean $single
     *          Define if the response is a collection or not
     * @param Entity\EntityAbstract $entity
     *          The Entity to be hydrated or cloned
     * @return \ArrayObject
     * @throws \Exception
     */
    private function process($response, $single, Entity\EntityAbstract $entity) {
        switch (true) {
            case isset($response['int_err_code']):
                throw new \Exception($response['msg']);
            
            case isset($response['Details']):
                throw new \Exception($response['Details']);
                
            case isset($response['description']):
                throw new \Exception($response['description']);
        }
        
        if ($single === true) {
            /* @var $entity Entity\EntityAbstract */
            return $entity->hydrate($response);
        } else {
            $collection = new \ArrayObject([]);
            foreach ($response as $data) {
                /* @var $clone Entity\EntityAbstract */
                $clone = clone $entity;
                $clone->hydrate($data);
                $collection->append($clone);
            }
            return $collection;
        }
    }
    
    /**
     * Send API request, but pass the $oauthToken first.
     * Return array of Citrix API call.
     * 
     * @param string $url
     * @param string $method
     * @param array|Entity\EntityAbstract $data
     * @param string $headers
     * @return array
     */
    static public function send($url, $method, $data, $headers = null) {
        if (is_object($data) ) {
            $data = $data->feed();
        }
        
        $ch  = curl_init();
        
        // Content-Type is The MIME type of the body of the request (used with POST and PUT requests).
        if (in_array($method, ['POST', 'PUT']) && isset($headers['Content-Type']) ) {
            switch ($headers['Content-Type']) {
                case \Citrix\Citrix::MIME_X_WWW_FORM_URLENCODED:
                    $body = http_build_query($data);
                    break;

                case \Citrix\Citrix::MIME_JSON:
                    $body = json_encode($data);
                    break;

                case Citrix::MIME_MULTIPART_FORM_DATA:
                    $body = $data;
                    break;
            }
        }

        // CURLOPT_HEADER = TRUE: 
        //      to include the header in the output. 
        //      
        // CURLOPT_POST = TRUE;   
        //      to do a regular HTTP POST. This POST is the 
        //      normal application/x-www-form-urlencoded kind, most commonly used by HTML forms. 
        //      
        // CURLOPT_POSTFIELDS = MIXED;
        //      This parameter can either be passed as a urlencoded string 
        //      like 'para1=val1&para2=val2&...' or as an array with the field 
        //      name as key and field data as value or as a string json_encoded.
        //      
        // CURLOPT_FOLLOWLOCATION = TRUE;  
        //      to follow any "Location: " header that the server sends as 
        //      part of the HTTP header (note this is recursive, 
        //      PHP will follow as many "Location: " headers that it is sent, 
        //      unless CURLOPT_MAXREDIRS is set). 
        //      
        // CURLOPT_MAXREDIRS = INT;
        //      The maximum amount of HTTP redirections to follow.
        //      
        // CURLOPT_CUSTOMREQUEST = 'PUT', 'DELETE', ecc
        //      A custom request method to use instead of "GET" or "HEAD" when 
        //      doing a HTTP request. 
        switch (true) {
            // insert
            case $method == 'POST':
                curl_setopt($ch, CURLOPT_POST, true); // tell curl you want to post something
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body); // define what you want to post
                break;

            // update
            case $method == 'PUT':
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
                break;

            // auth
            case $method == 'OAUTH':
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
                $query = http_build_query($data);
                $url   = $url . '?' . $query;
                break;
                
            // get
            case $method == 'GET':
                curl_setopt($ch, CURLOPT_HEADER, false); 
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
                $query = http_build_query($data);
                $url   = $url . '?' . $query;
                break;

            // delete
            case $method == 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                break;
            
            default:
                throw new \Exception("Invalid method {$method}!");
        }

        if ( !empty($headers) ) {
            $httpHeaders = [];
            foreach ($headers as $header => $value) {
                $httpHeaders[] = "{$header}: {$value}";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders);
        }        

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // return the output in string format        
        $output = curl_exec($ch);
        curl_close($ch); // close curl handle
        
        switch ($headers['Accept']) {
            case \Citrix\Citrix::MIME_JSON:
                $result = (array) json_decode($output, true, 512);
                break;
            
            default:
                $result = $output;
        }        
        return $result;
        
        // return ($method == 'OAUTH') ? $output : (array) json_decode($output, true, 512);
    }
    
    /**
     * Create the correct headers needed for OAuth,
     * 
     * @param string $oauthToken
     */
    private function authTokenHeaders($oauthToken) {
        $headers = [
            'Content-Type'  => \Citrix\Citrix::MIME_JSON,
            'Accept'        => \Citrix\Citrix::MIME_JSON,
            'Authorization' => 'OAuth oauth_token=' . $oauthToken
        ];
        return $headers;
    }

    public function getAuth() {
        return $this->auth;
    }
    
    /**
     * Return the authentication token from Auth.
     * 
     * @return string
     */
    private function getAccessToken() {
        return $this->auth->getAccessToken();
    }
    
    /**
     * Get upcoming webinars.
     * 
     * @return \ArrayObject
     */
    public function getUpcoming() {
        $this->auth->applyCredentials();
        $url = self::endpoint . '/G2W/rest/organizers/' . $this->auth->getOrganizerKey() . '/upcomingWebinars';
        $response = self::send($url, 'GET', [], $this->authTokenHeaders($this->getAccessToken()));
        return $this->process($response, false, new Entity\Webinar\Get());
    }

    /**
     * Get all webinars.
     *
     * @return \ArrayObject
     */
    public function getWebinars() {
        $this->auth->applyCredentials();
        $url = self::endpoint . '/G2W/rest/organizers/' . $this->auth->getOrganizerKey() . '/webinars';
        $response = self::send($url, 'GET', [], $this->authTokenHeaders($this->getAccessToken()));
        return $this->process($response, false, new Entity\Webinar\Get());
    }

    /**
     * Get all past webinars in date range.
     * 
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @return \ArrayObject
     */
    public function getPast($startDate, $endDate = null) {
        $this->auth->applyCredentials();
        $utcTimeZone = new \DateTimeZone('UTC');
        if ($endDate === null) {
            $endDate = new \DateTime('now', $utcTimeZone);
        }
        $url         = self::endpoint . '/G2W/rest/organizers/' . $this->auth->getOrganizerKey() . '/historicalWebinars';
        $data      = [
            'fromTime' => $startDate->setTimezone($utcTimeZone)->format('Y-m-d\TH:i:s\Z'),
            'toTime'   => $endDate->format('Y-m-d\TH:i:s\Z')
        ];
        $response = self::send($url, 'GET', $data, $this->authTokenHeaders($this->getAccessToken()));
        return $this->process($response, false, new Entity\Webinar\Get());
    }

    /**
     * Get info for a single webinar by passing the webinar id or 
     * in Citrix's terms webinarKey.
     * 
     * @param int $webinarKey
     * @return Webinar
     */
    public function getWebinar($webinarKey) {
        $this->auth->applyCredentials();
        $url = self::endpoint . '/G2W/rest/organizers/' . $this->auth->getOrganizerKey() . '/webinars/' . $webinarKey;
        $response = self::send($url, 'GET', [], $this->authTokenHeaders($this->getAccessToken()));
        return $this->process($response, true, new Entity\Webinar\Get());
    }

    /**
     * Create a new webinar.
     * Return the same entity with the property WebinarKey hydrated.
     * 
     * @param Webinar\Post $entity
     * @return Webinar\Post
     */
    public function createWebinar($entity) {
        $this->auth->applyCredentials();
        $url = self::endpoint . '/G2W/rest/organizers/' . $this->auth->getOrganizerKey() . '/webinars';
        $response = self::send($url, 'POST', $entity, $this->authTokenHeaders($this->getAccessToken()));
        return $this->process($response, true, $entity);
    }

    /**
     * Update an existing webinar.
     * 
     * @param Webinar\Put $entity
     * @return array
     */
    public function updateWebinar($entity) {
        $this->auth->applyCredentials();
        $url = self::endpoint . '/G2W/rest/organizers/' . $this->auth->getOrganizerKey() . '/webinars/' . $entity->getWebinarKey();
        $response = self::send($url, 'PUT', $entity, $this->authTokenHeaders($this->getAccessToken()));
        return $response;
    }

    /**
     * Delete a webinar.
     * 
     * @param string $webinarKey
     * @return type
     */
    public function deleteWebinar($webinarKey) {
        $this->auth->applyCredentials();
        $url = self::endpoint . '/G2W/rest/organizers/' . $this->auth->getOrganizerKey() . '/webinars/' . $webinarKey;
        $response = self::send($url, 'DELETE', [], $this->authTokenHeaders($this->getAccessToken()));
        return $response;
    }

    /**
     * Get all registrants for a given webinar.
     * 
     * @param int $webinarKey
     * @return Consumer
     */
    public function getRegistrants($webinarKey) {
        $this->auth->applyCredentials();
        $url = self::endpoint . '/G2W/rest/organizers/' . $this->auth->getOrganizerKey() . '/webinars/' . $webinarKey . '/registrants';
        $response = self::send($url, 'GET', [], $this->authTokenHeaders($this->getAccessToken()));
        return $this->process($response, false, new Entity\Registrant\Get());
    }

    /**
     * Get a single registrant for a given webinar.
     *
     * @param int $webinarKey
     * @param int $registrantKey
     * @return Entity\Registrant\Get
     */
    public function getRegistrant($webinarKey, $registrantKey) {
        $this->auth->applyCredentials();
        $url = self::endpoint . '/G2W/rest/organizers/' . $this->auth->getOrganizerKey() . '/webinars/' . $webinarKey . '/registrants/' . $registrantKey;
        $response = self::send($url, 'GET', [], $this->authTokenHeaders($this->getAccessToken()));
        return $this->process($response, true, new Entity\Registrant\Get());
    }

    /**
     * Get all attendees for a given webinar.
     *
     * @param int $webinarKey
     * @return \ArrayObject
     */
    public function getAttendees($webinarKey) {
        $this->auth->applyCredentials();
        $url = self::endpoint . '/G2W/rest/organizers/' . $this->auth->getOrganizerKey() . '/webinars/' . $webinarKey . '/attendees';
        $response = self::send($url, 'GET', [], $this->authTokenHeaders($this->getAccessToken()));
        return $this->process($response, false, new Entity\Registrant\Get());
    }

    /**
     * Register user for a webinar
     * 
     * @param int $webinarKey
     * @param Entity\Registrant\Post $entity
     * @return Entity\Registrant\Post
     */
    public function register($webinarKey, $entity) {
        $this->auth->applyCredentials();
        $url = self::endpoint . '/G2W/rest/organizers/' . $this->auth->getOrganizerKey() . '/webinars/' . $webinarKey . '/registrants';
        $response = self::send($url, 'POST', $entity, $this->authTokenHeaders($this->getAccessToken()));
        return $this->process($response, true, $entity);
    }

    /**
     * Register user for a webinar
     * 
     * @param int $webinarKey
     * @param int $registrantKey
     * @return 
     */
    public function unregister($webinarKey, $registrantKey) {
        $this->auth->applyCredentials();
        $url = self::endpoint . '/G2W/rest/organizers/' . $this->auth->getOrganizerKey() . '/webinars/' . $webinarKey . '/registrants/' . $registrantKey;
        $response = self::send($url, 'DELETE', [], $this->authTokenHeaders($this->getAccessToken()));
        return $response;
    }
    
}
