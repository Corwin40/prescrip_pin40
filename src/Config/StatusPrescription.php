<?php

namespace App\Config;

enum StatusPrescription: string
{
    case OpenByAdministrator = 'Dossier ouvert par un administrateur';
    case OpenByMediator = 'Dossier ouvert par un médiateur';
    case OpenByPrescriptor = 'Dossier ouvert par un prescripteur';
    case finished = 'Dossier validé';
}
