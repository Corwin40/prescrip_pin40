<?php

namespace App\Service;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrcodeGenerator
{
    public function generate(?string $data): mixed
    {
        if(!$data){
            return null;
        }

        //dd(strlen($data), bin2hex($data));

        $options = new QROptions([
            'version'  => 3,
            //'eccLevel' => QRCode::ECC_L,
        ]);

        return (new QRCode($options))->render($data);
    }
}
