<?php

namespace Citrix\Entity;

/**
 * Description of Registrant
 *
 * @author DalPraS
 */
class Registrant extends EntityAbstract {

    /**
     * Data for feeding the request.
     * 
     * @var array
     */
    protected $feed = [
        "firstName" => null,
        "lastName"  => null,
        "email"     => null,
    ];

    /**
     * First Name
     * 
     * @var String
     */
    private $firstName;

    /**
     * Last Name
     * 
     * @var String
     */
    private $lastName;

    /**
     * Email Address
     * 
     * @var String
     */
    private $email;

    
    public function getFirstName() {
        return $this->firstName;
    }

    public function getLastName() {
        return $this->lastName;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setFirstName(String $firstName) {
        $this->firstName = $firstName;
        return $this;
    }

    public function setLastName(String $lastName) {
        $this->lastName = $lastName;
        return $this;
    }

    public function setEmail(String $email) {
        $this->email = $email;
        return $this;
    }

}
