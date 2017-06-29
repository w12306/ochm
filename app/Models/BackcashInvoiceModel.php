<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

/**回款金额分配关联发票小组表
 * Class BackcashInvoiceModel
 * @package App\Models
 */
class BackcashInvoiceModel extends Model
{
    /**
     * @var string
     */
    protected $table = 'backcash_invoice';

    protected $primaryKey = 'id';

    public $incrementing = true;


    /**
     * @var array
     */
    protected $fillable = [
            'backcash_id',
            'invoice_delivery_id',
            'invoice_id',
            'invoice_key',
            'invoice_amount',
            'invoice_type',
            'invoice_month_amount',
            'delivery_id',
            'team_id',
            'month',
            'month_amount',
            'partner_id',
            'business_key',
            'team',
            'active_amount',
            'isshow',
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
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function backcash()
    {
        return $this->belongsTo(BackcashModel::class );
    }


    /**
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice_delivey()
    {
        return $this->belongsTo(InvoiceDeliveyModel::class );
    }


    /**
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice()
    {
        return $this->belongsTo(InvoiceModel::class ,'invoice_key','invoice_key');
    }

    /**
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function delivery()
    {
        return $this->belongsTo(DeliveryModel::class, 'delivery_id', 'id' );
    }

    /**
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function business_team()
    {
        return $this->belongsTo(BusinessTeamModel::class, 'team_id', 'id' );
    }

    /**
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function business()
    {
        return $this->belongsTo(BusinessModel::class, 'business_key', 'business_key' );
    }
    /**
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public  function partner(){
        return $this->belongsTo(PartnersModel::class, 'partner_id', 'id' );
    }



}
