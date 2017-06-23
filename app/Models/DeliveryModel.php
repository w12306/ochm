<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;


/**业务月执行额
 * Class DictionaryModel
 * @package App\Models
 */
class DeliveryModel extends Model
{
    /**
     * @var string
     */
    protected $table = 'delivery';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $dates = ['created_at'];
    /**
     * @var array
     */
    protected $fillable = [
            'team_id',
            'business_id',
            'business_key',
            'team',
            'month',
            'amount',
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
    public function business()
    {
        return $this->belongsTo(BusinessModel::class );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function businessTeam()
    {
        return $this->belongsTo(BusinessTeamModel::class, 'team_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function invoice_deliveys()
    {
        return $this->hasMany(InvoiceDeliveyModel::class,'delivey_id','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function badcash()
    {
        return $this->hasMany(Badcash::class, 'delivery_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function expenses_deliveys()
    {
        return $this->hasMany(ExpensesDeliveyModel::class, 'delivey_id','id' );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function backcash_invoices()
    {
        return $this->hasMany(BackcashInvoiceModel::class, 'delivery_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payment_expensess()
    {
        return $this->hasMany(PaymentExpenses::class, 'delivery_id', 'id');
    }

}
