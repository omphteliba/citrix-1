<?php

namespace CitrixOAuth2\Entity\Webinar;

class Put extends \CitrixOAuth2\Entity\Webinar {

    /**
     * Data for feeding the request.
     *
     * @var array
     */
    protected $feed = [
        "subject"     => null,
        "description" => null,
        "times"       => [[
            "startTime" => null,
            "endTime"   => null]],
        "timeZone"    => null,
        "locale"      => null
    ];


    /**
     * The Id of the webinar.
     *
     * @var string
     */
    private $webinarKey;

    /**
     * Webinar locale
     *
     * @var string
     */
    private $locale = 'it_IT';

    public function getWebinarKey() {
        return $this->webinarKey;
    }

    public function getLocale() {
        return $this->locale;
    }

    public function setWebinarKey($webinarKey) {
        $this->webinarKey = $webinarKey;
        return $this;
    }

    public function setLocale($locale) {
        $this->locale = $locale;
        return $this;
    }

}
