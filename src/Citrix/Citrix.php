<?php

namespace Citrix;

use ArrayObject;
use Citrix\Auth;
use Citrix\Entity\EntityAbstract;
use Exception;

/**
 * Citrix data loader
 * 
 * Use Citrix to connect to GoToWebinar Citrix Service for direct 
 * interoperability.
 */
class Citrix {
    
    const endpoint = 'https://api.getgo.com/G2W/rest';
    
    /**
     * Authentication Client
     * 
     * @var Auth
     */
    private $client;
    
    public function __construct(Auth $client) {
        $this->client = $client;
    }

    /**
     * Map response to an entity or collection of entities.
     * 
     * @param array $response 
     *          The response to process for having a collection or object
     * @param boolean $single
     *          Define if the response is a collection or not
     * @param EntityAbstract $entity
     *          The Entity to be hydrated or cloned
     * @return ArrayObject
     * @throws Exception
     */
    private function process($response, $single, EntityAbstract $entity) {
        switch (true) {
            case isset($response['int_err_code']):
                throw new Exception($response['msg']);
            
            case isset($response['Details']):
                throw new Exception($response['Details']);
                
            case isset($response['description']):
                throw new Exception($response['description']);
        }
        
        if ($single === true) {
            /* @var $entity EntityAbstract */
            return $entity->hydrate($response);
        } else {
            $collection = new ArrayObject([]);
            foreach ($response as $entity) {
                /* @var $clone EntityAbstract */
                $clone = clone $entity;
                $clone->hydrate($entity);
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
     * @param array|EntityAbstract $data
     * @param string $oauthToken
     * @return array
     */
    static public function send($url, $method, $data, $oauthToken = null) {
        if (is_object($data) ) {
            $data = $data->feed();
        }
        
        $ch  = curl_init(); // initiate curl

        switch (true) {
            
            case $method == 'POST':
                curl_setopt($ch, CURLOPT_POST, true); // tell curl you want to post something
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // define what you want to post
                break;
            
            case $method == 'GET':
                $query = http_build_query($data);
                $url   = $url . '?' . $query;
                break;
            
            case $method == 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
                
            case $method == 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                break;
        }
        
        if ( $oauthToken != null ) {
            $headers = [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: OAuth oauth_token=' . $oauthToken
            ];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // return the output in string format
        $output = curl_exec($ch); // execute
        curl_close($ch); // close curl handle
        return (array) json_decode($output, true, 512);
    }    
    
    /**
     * Get upcoming webinars.
     * 
     * @return ArrayObject
     */
    public function getUpcoming() {
        $url = self::endpoint . '/organizers/' . $this->client->getOrganizerKey() . '/upcomingWebinars';
        $response = self::send($url, 'GET', [], $this->client->getAccessToken());
        return $this->process($response, false, new Entity\Webinar\Get());
    }

    /**
     * Get all webinars.
     *
     * @return ArrayObject
     */
    public function getWebinars() {
        $url = self::endpoint . '/organizers/' . $this->client->getOrganizerKey() . '/webinars';
        $response = self::send($url, 'GET', [], $this->client->getAccessToken());
        return $this->process($response, false, new Entity\Webinar\Get());
    }

    /**
     * Get all past webinars in date range.
     * 
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @return ArrayObject
     */
    public function getPast($startDate, $endDate = null) {
        if ($endDate === null) {
            $endDate = new \DateTime('now');
        }
        $utcTimeZone = new \DateTimeZone('UTC');
        $url         = self::endpoint . '/organizers/' . $this->client->getOrganizerKey() . '/historicalWebinars';
        $data      = [
            'fromTime' => $startDate->setTimezone($utcTimeZone)->format('Y-m-d\TH:i:s\Z'),
            'toTime'   => $endDate->setTimezone($utcTimeZone)->format('Y-m-d\TH:i:s\Z')
        ];
        $response = self::send($url, 'GET', $data, $this->client->getAccessToken());
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
        $url = self::endpoint . '/organizers/' . $this->client->getOrganizerKey() . '/webinars/' . $webinarKey;
        $response = self::send($url, 'GET', [], $this->client->getAccessToken());
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
        $url = self::endpoint . '/organizers/' . $this->client->getOrganizerKey() . '/webinars';
        $response = self::send($url, 'POST', $entity, $this->client->getAccessToken());
        return $this->process($response, true, $entity);
    }

    /**
     * Update an existing webinar.
     * 
     * @param Webinar\Put $entity
     * @return array
     */
    public function updateWebinar($entity) {
        $url = self::endpoint . '/organizers/' . $this->client->getOrganizerKey() . '/webinars/' . $entity->getWebinarKey();
        $response = self::send($url, 'PUT', $entity, $this->client->getAccessToken());
        return $response;
    }

    /**
     * Delete a webinar.
     * 
     * @param string $webinarKey
     * @return type
     */
    public function deleteWebinar($webinarKey) {
        $url = self::endpoint . '/organizers/' . $this->client->getOrganizerKey() . '/webinars/' . $webinarKey;
        $response = self::send($url, 'DELETE', [], $this->client->getAccessToken());
        return $response;
    }

    /**
     * Get all registrants for a given webinar.
     * 
     * @param int $webinarKey
     * @return Consumer
     */
    public function getRegistrants($webinarKey) {
        $url = self::endpoint . '/organizers/' . $this->client->getOrganizerKey() . '/webinars/' . $webinarKey . '/registrants';
        $response = self::send($url, 'GET', [], $this->client->getAccessToken());
        return $this->process($response, false, new Entity\Registrant\Get());
    }

    /**
     * Get a single registrant for a given webinar.
     *
     * @param int $webinarKey
     * @param int $registrantKey
     * @return Consumer
     */
    public function getRegistrant($webinarKey, $registrantKey) {
        $url = self::endpoint . '/organizers/' . $this->client->getOrganizerKey() . '/webinars/' . $webinarKey . '/registrants/' . $registrantKey;
        $response = self::send($url, 'GET', [], $this->client->getAccessToken());
        return $this->process($response, true, new Entity\Registrant\Get());
    }

    /**
     * Get all attendees for a given webinar.
     *
     * @param int $webinarKey
     * @return Consumer
     */
    public function getAttendees($webinarKey) {
        $url = self::endpoint . '/organizers/' . $this->client->getOrganizerKey() . '/webinars/' . $webinarKey . '/attendees';
        $response = self::send($url, 'GET', [], $this->client->getAccessToken());
        return $this->process($response, false, new Entity\Registrant\Get());
    }

    /**
     * Register user for a webinar
     * 
     * @param int $webinarKey
     * @param Entity\Registrant\Post $entity
     * @return GoToWebinar
     */
    public function register($webinarKey, $entity) {
        $url = self::endpoint . '/organizers/' . $this->client->getOrganizerKey() . '/webinars/' . $webinarKey . '/registrants';
        $response = self::send($url, 'POST', $entity, $this->client->getAccessToken());
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
        $url = self::endpoint . '/organizers/' . $this->client->getOrganizerKey() . '/webinars/' . $webinarKey . '/registrants/' . $registrantKey;
        $response = self::send($url, 'DELETE', [], $this->client->getAccessToken());
        return $response;
    }
    
}
