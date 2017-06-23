<?php

namespace App\Repositories;

use App\Models\Account;
use Housekeeper\Eloquent\BaseRepository;
use Housekeeper\Action;
use Common\Packages\Admin\Contracts\UserIdentity;

class AccountRepository extends BaseRepository
{

    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        return Account::class;
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






}
