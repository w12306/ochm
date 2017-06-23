<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

/**发票金额分配关联业务小组表
 * Class InvoiceModel
 * @package App\Models
 */
class InvoiceDeliveyModel extends Model
{
    /**
     * @var string
     */
    protected $table = 'invoice_delivey';

    protected $primaryKey = 'id';

    public $incrementing = true;


    /**
     * @var array
     */
    protected $fillable = [
            'invoice_id',
            'invoice_key',
            'delivey_id',
            'team_id',
            'active_amount',
            'partner_id',
            'team',
            'team_month',
            'team_amount',
            'business_key',
            'remark',
            'isshow',

    ];
    /**
     * @var array
     */
    protected $hidden = [
            'updated_at',
            'created_at',
    ];


    public function delivery()
    {
        return $this->belongsTo(DeliveryModel::class ,'delivey_id','id');
    }

    /**
     *
     * @author YangWei<yangwei@stnts.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice()
    {
        return $this->belongsTo(InvoiceModel::class );
    }

    public function backcash_invoices()
    {
        return $this->hasMany(BackcashInvoiceModel::class,'invoice_delivery_id','id');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function business_team()
    {
        return $this->belongsTo(BusinessTeamModel::class,'team_id','id' );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function business()
    {
        return $this->belongsTo(BusinessModel::class,'business_key','business_key' );
    }

    /**
     *
     * @author YangWei<yangwei@stnts.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public  function partner(){
        return $this->belongsTo(PartnersModel::class, 'partner_id', 'id' );
    }

}
