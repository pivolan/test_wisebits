<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class RequestVisitCountry
{
    /**
     * @var string
     * @Assert\Country
     * @Assert\NotBlank
     */
    public $country;
}