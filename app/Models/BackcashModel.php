<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

/**回款主体信息内容
 * Class BackcashModel
 * @package App\Models
 */
class BackcashModel extends Model
{
    /**
     * @var string
     */
    protected $table = 'backcash';

    protected $primaryKey = 'id';

    public $incrementing = true;


    protected $dates = ['created_at'];

    /**
     * @var array
     */
    protected $fillable = [
            'backcash_key',
            'backtime',
            'bank',
            'backtype',
            'amount',
            'isshow',
            'remark',
    ];


    /**
     * @var array
     */
    protected $hidden = [
            'updated_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function backcash_invoices()
    {
        return $this->hasMany(BackcashInvoiceModel::class,'backcash_id','id');
    }



}
