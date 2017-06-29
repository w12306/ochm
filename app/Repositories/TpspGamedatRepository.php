<?php

namespace App\Repositories;

use Housekeeper\Eloquent\BaseRepository;
use App\Models\TpspGamedat;

class TpspGamedatRepository extends BaseRepository {

    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        return TpspGamedat::class;
    }

}
