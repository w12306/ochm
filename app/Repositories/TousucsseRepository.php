<?php

namespace App\Repositories;

use App\Models\Account;
use App\Models\Tousucsse;
use Housekeeper\Eloquent\BaseRepository;
use Housekeeper\Action;
use Common\Packages\Admin\Contracts\UserIdentity;

class TousucsseRepository extends BaseRepository
{

    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        return Tousucsse::class;
    }



    /**
     * Save a new entity in repository
     *
     * @param array $attributes
     * @return mixed|Model
     */
    public function createOrUpdate(array $attributes)
    {
        if(!$this->model->where('open_id','=',$attributes['open_id'])->exists()){
            $this->create($attributes);
        }else{
            $this->model->where('open_id','=',$attributes['open_id'])->update($attributes);
        }
    }

    public function checkloand($ListingId){
        if($this->model->where('ListingId','=',$ListingId)->exists()){
            return false;
        }
        return true;
    }






}
