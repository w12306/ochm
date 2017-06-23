<?php

namespace App\Repositories;

use App\Models\Account;
use App\Models\FeatureLoand;
use Housekeeper\Eloquent\BaseRepository;
use Housekeeper\Action;


class FeatureRepository extends BaseRepository
{

    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        return FeatureLoand::class;
    }



    /**
     * Save a new entity in repository
     *
     * @param array $attributes
     * @return mixed|Model
     */
    public function createOrUpdate(array $attributes)
    {
        if(!$this->model->where('ListingId','=',$attributes['ListingId'])->exists()){
            $this->create($attributes);
        }
        return ;
    }

    public function checkDelete($attributes){
        if($this->model->where('ListingId','=',$attributes['ListingId'])->exists()){
            $this->model->where('ListingId','=',$attributes['ListingId'])->delete();
        }
        return ;
    }






}
