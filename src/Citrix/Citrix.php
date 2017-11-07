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
        
//        if ($method == 'OAUTH') {
//            $query = http_build_query($data);
//            $url   = $url . '?' . $query;
//            curl_setopt($ch, CURLOPT_HEADER, true);
//            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
//            curl_setopt($ch, CURLOPT_URL, $url);
//            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // return the output in string format             
//            $output = curl_exec($ch);
//            curl_close($ch); 
//            //            if (preg_match('~Location: (.*)~i', $output, $match)) {
//            //                return trim($match[1]);
//            //            }
//            return $output;
//        }

        switch (true) {
            // insert new data
            case $method == 'POST':
                curl_setopt($ch, CURLOPT_POST, true); // tell curl you want to post something
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // define what you want to post
                break;

            // ger data
            case $method == 'OAUTH':
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
                
            case $method == 'GET':
                $query = http_build_query($data);
                $url   = $url . '?' . $query;
                break;

            // update data
            case $method == 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;

            // delete data
            case $method == 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                break;
            
            default:
                throw new \Exception("Invalid method {$method}!");
        }
        
        if ( !empty($headers) ) {
            foreach ($headers as $header => &$value) {
                $value = "{$header}: {$value}";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_values($headers));
        }        

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // return the output in string format        
        $output = curl_exec($ch);
        curl_close($ch); // close curl handle
        return ($method == 'OAUTH') ? $output : (array) json_decode($output, true, 512);
    }
    
    /**
     * Create the correct headers needed for OAuth,
     * 
     * @param string $oauthToken
     */
    private function authTokenHeaders($oauthToken) {
        $headers = [
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
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
        if ($endDate === null) {
            $endDate = new \DateTime('now');
        }
        $utcTimeZone = new \DateTimeZone('UTC');
        $url         = self::endpoint . '/G2W/rest/organizers/' . $this->auth->getOrganizerKey() . '/historicalWebinars';
        $data      = [
            'fromTime' => $startDate->setTimezone($utcTimeZone)->format('Y-m-d\TH:i:s\Z'),
            'toTime'   => $endDate->setTimezone($utcTimeZone)->format('Y-m-d\TH:i:s\Z')
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
