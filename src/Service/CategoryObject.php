<?php

namespace App\Service;

use App\Repository\CategoryRepository;

class CategoryObject
{
    public function get( $name, CategoryRepository $repo )
    {
        return $repo->findOneBy([ 'name' => $name ]);
    }

}