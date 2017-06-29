<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

/**支出主体信息内容
 * Class InvoiceModel
 * @package App\Models
 */
class ExpensesModel extends Model
{
    /**
     * @var string
     */
    protected $table = 'expenses';

    protected $primaryKey = 'id';

    public $incrementing = true;


    protected $dates = ['created_at'];

    /**
     * @var array
     */
    protected $fillable = [
            'expenses_key',
            'payment_time',
            'expenses_type',
            'cooperation_type',
            'under_partner_id',
            'amount',
            'contract_key',
            'contract_btime',
            'contract_etime',
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
    public function under_partner()
    {
        return $this->belongsTo(UnderPartnersModel::class,'under_partner_id','id' );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function payment_expensess()
    {
        return $this->hasMany(PaymentExpenses::class,'expenses_id','id' );
    }

}
