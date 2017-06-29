<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

/**独立付款信息内容
 * Class InvoiceModel
 * @package App\Models
 */
class StandPayment extends Model
{
    /**
     * @var string
     */
    protected $table = 'stand_payment';

    protected $primaryKey = 'id';

    public $incrementing = true;


    protected $dates = ['deleted_at'];

    /**
     * @var array
     */
    protected $fillable = [
            'payment_key',
            'payment_time',
            'payment_amount',
            'invoice_amount',
            'invoice_num',
            'invoice_type',
            'expenses_id',
            'remark',
    ];
    /**
     * @var array
     */
    protected $hidden = [
            'updated_at',
            'created_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function stand_expenses()
    {
        return $this->belongsTo(StandExpensesModel::class,'expenses_id','id' );
    }



}
