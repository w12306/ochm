<?php

namespace App\Repositories;

use App\Models\Account;
use App\Models\LoandList;
use Housekeeper\Eloquent\BaseRepository;
use Housekeeper\Action;


class LoandListRepository extends BaseRepository
{

    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        return LoandList::class;
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
