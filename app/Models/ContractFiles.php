<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 合同与文件关联表
 *
 * @package App\Models
 */
class ContractFiles extends Model
{

    /**
     * @var string
     */
    protected $table = 'contract_files';

    protected $primaryKey = 'id';

    public $incrementing = true;

    /**
     * @var array
     */
    protected $fillable = [
        'contract_id',
        'filepath',
        'md5',
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class,'contract_id','id' );
    }

}
