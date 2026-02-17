<?php

namespace App\Config;

enum StatusPrescription: string
{
    case OpenByAdministrator = 'Dossier ouvert par un administrateur';
    case OpenByMediator = 'Médiateur';
    case OpenByPrescriptor = 'Prescripteur';
    case finished = 'Dossier validé';
}
