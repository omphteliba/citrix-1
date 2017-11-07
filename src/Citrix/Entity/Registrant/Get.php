<?php

namespace Citrix\Entity\Registrant;

/**
 * Description of Registrant
 *
 * @author DalPraS
 */
class Get extends \Citrix\Entity\Registrant {

    /**
     * Data for feeding the request.
     * 
     * @var array
     */
    protected $feed = [];

    /**
     * Id of registrant
     * 
     * @var string
     */
    private $registrantKey;
    
    /**
     * Registration date
     * 
     * @var \DateTime
     */
    private $registrationDate;
    
    /**
     * Status of registrant:
     * WAITING - registrant registered and is awaiting approval (where organizer has required approval), 
     * APPROVED - registrant registered and is approved, and 
     * DENIED - registrant registered and was denied.
     * 
     * @var string
     */
    private $status;
    
    /**
     * Url to which join to platform
     * 
     * @var string
     */
    private $joinUrl;
    
    /**
     * Timezone
     *  
     * @var string
     */
    private $timeZone = 'Europe/Rome';
    
    public function getRegistrantKey() {
        return $this->registrantKey;
    }

    public function getRegistrationDate() {
        return $this->registrationDate;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getJoinUrl() {
        return $this->joinUrl;
    }

    public function getTimeZone() {
        return $this->timeZone;
    }

    public function setRegistrantKey($registrantKey) {
        $this->registrantKey = $registrantKey;
        return $this;
    }

    public function setRegistrationDate(\DateTime $registrationDate) {
        $this->registrationDate = $registrationDate;
        return $this;
    }

    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    public function setJoinUrl($joinUrl) {
        $this->joinUrl = $joinUrl;
        return $this;
    }

    public function setTimeZone($timeZone) {
        $this->timeZone = $timeZone;
        return $this;
    }
    
    
}
