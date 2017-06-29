<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

/**发票主体信息内容
 * Class InvoiceModel
 * @package App\Models
 */
class InvoiceModel extends Model
{
    /**
     * @var string
     */
    protected $table = 'invoice';

    protected $primaryKey = 'id';

    public $incrementing = true;


    protected $dates = ['created_at'];

    /**
     * @var array
     */
    protected $fillable = [
            'invoice_key',
            'invoice_type',
            'invoice_time',
            'amount',
            'remark',
            'del_time',
            'isshow',
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
    public function invoice_deliveys()
    {
        return $this->hasMany(InvoiceTeamModel::class,'invoice_id','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function backcash_invoices()
    {
        return $this->hasMany(BackcashInvoiceModel::class,'invoice_key','invoice_key');
    }


}
