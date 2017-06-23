<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class ProductModel extends Model
{
    use SoftDeletes;
    /**
     * @var string
     */
    protected $table = 'product';

    protected $primaryKey = 'id';

    public $incrementing = true;


    protected $dates = [
            'deleted_at',
            'updated_at',
            'created_at',];

    /**
     * @var array
     */
    protected $fillable = [
            'name',
            'type',
            'company_id',
            'game_screen',
            'mode_type',
            'game_theme',
            'screen_style',
            'business_model',
            'charging_mode',
            'game_type',
    ];


    /**
     * @var array
     */
    protected $hidden = [

    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function businesses()
    {
        return $this->hasMany(BusinessModel::class, 'product_id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function expenses_deliveys()
    {
        return $this->hasMany(ExpensesDeliveyModel::class, 'product_id','id');
    }

    public function company()
    {
        return $this->belongsTo(
                CompanyModel::class,
                'company_id',
                'id'
                );
    }

	/**
	 *
	 * @author YangWei<yangwei@stnts.com>
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public  function executives(){
		return $this->hasMany(Executive::class, 'product_id', 'id' );
	}


}
