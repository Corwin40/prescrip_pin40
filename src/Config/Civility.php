<?php

namespace App\Config;

enum Civility: string
{
    case Mr = 'Mr';
    case Mme = 'Mme';
    case Mlle = 'Mlle';
    case Autre = 'Autre';
}
