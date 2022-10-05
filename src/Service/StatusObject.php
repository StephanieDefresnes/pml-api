<?php

namespace App\Service;

use App\Repository\StatusRepository;

class StatusObject
{
    public function get( $name, StatusRepository $repo )
    {
        return $repo->findOneBy([ 'name' => $name ]);
    }

}