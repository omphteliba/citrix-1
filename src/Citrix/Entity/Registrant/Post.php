<?php

namespace Citrix\Entity\Registrant;

/**
 * Registrant Entity
 *
 * Contains all fields for registratns and attendees. Consumer
 * is an entity that merges both registratns and attendees.
 */
class Post extends \Citrix\Entity\Registrant {

    /**
     * Feed data for creating a new Registrant
     * 
     * @var array
     */
    protected $feed = [
        "firstName"            => null,
        "lastName"             => null,
        "email"                => null,
        "address"              => null,
        "city"                 => null,
        "state"                => null,
        "zipCode"              => null,
        "country"              => null,
        "phone"                => null,
        "organization"         => null,
    ];


    /**
     * Address
     * 
     * @var String
     */
    private $address;

    /**
     * City
     * 
     * @var String
     */
    private $city;

    /**
     * State
     * 
     * @var String
     */
    private $state;

    /**
     * ZipCode
     * 
     * @var String
     */
    private $zipCode;

    /**
     * Country
     * 
     * @var String
     */
    private $country;

    /**
     * Phone
     * 
     * @var String
     */
    private $phone;

    /**
     * Organization
     * 
     * @var String
     */
    private $organization;

    /**
     * The Id of the registrant
     * 
     * @var string
     */
    private $registrantKey;
    
    /**
     * The Url to Join
     * 
     * @var string
     */
    private $joinUrl;
    
    public function getFeed() {
        return $this->feed;
    }

    public function getAddress() {
        return $this->address;
    }

    public function getCity() {
        return $this->city;
    }

    public function getState() {
        return $this->state;
    }

    public function getZipCode() {
        return $this->zipCode;
    }

    public function getCountry() {
        return $this->country;
    }

    public function getPhone() {
        return $this->phone;
    }

    public function getOrganization() {
        return $this->organization;
    }

    public function getRegistrantKey() {
        return $this->registrantKey;
    }

    public function getJoinUrl() {
        return $this->joinUrl;
    }

    public function setFeed($feed) {
        $this->feed = $feed;
        return $this;
    }

    public function setAddress(String $address) {
        $this->address = $address;
        return $this;
    }

    public function setCity(String $city) {
        $this->city = $city;
        return $this;
    }

    public function setState(String $state) {
        $this->state = $state;
        return $this;
    }

    public function setZipCode(String $zipCode) {
        $this->zipCode = $zipCode;
        return $this;
    }

    public function setCountry(String $country) {
        $this->country = $country;
        return $this;
    }

    public function setPhone(String $phone) {
        $this->phone = $phone;
        return $this;
    }

    public function setOrganization(String $organization) {
        $this->organization = $organization;
        return $this;
    }

    public function setRegistrantKey($registrantKey) {
        $this->registrantKey = $registrantKey;
        return $this;
    }

    public function setJoinUrl($joinUrl) {
        $this->joinUrl = $joinUrl;
        return $this;
    }
    
}
