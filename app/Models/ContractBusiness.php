<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 合同与业务关联表
 *
 * @package App\Models
 */
class ContractBusiness extends Model
{

    /**
     * @var string
     */
    protected $table = 'contract_business';

    protected $primaryKey = 'id';

    public $incrementing = true;

    /**
     * @var array
     */
    protected $fillable = [
        'contract_id',
        'business_id',
    ];

}
