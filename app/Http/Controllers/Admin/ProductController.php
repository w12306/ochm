<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controllers;
use App\Traits\Controller\CommonResponse;
use App\Exceptions\BusinessException;
use App\Repositories\ProductRepository;
use App\Models\ProductModel;
use App\Repositories\DictionaryRepository;
use App\Repositories\CompanyRepository;
use DB;
use Admin;
use App\Services\Admin\ActionLog;
/**
 * 产品管理管理
 * @author  YangweiWei <yangwei@stnts.com>
 */
class ProductController extends Controller
{
    use CommonResponse;

    protected $productRepository;

    protected $dictionaryRepository;

    protected $productModel;

    protected $companyRepository;

    protected $actionLog;
    /**
     * 
     */
    public function __construct(ProductRepository $productRepository,
                                DictionaryRepository $dictionaryRepository,
                                ProductModel $productModel,
                                CompanyRepository $companyRepository,
                                ActionLog $actionLog)
    {
        $this->productRepository    =$productRepository;
        $this->dictionaryRepository =$dictionaryRepository;
        $this->productModel         =$productModel;
        $this->companyRepository    =$companyRepository;
        $this->actionLog            = $actionLog;
    }

    /**
     * 列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function getList(Request $request)
    {
        //构建查询条件
        $where=' 1=1 ';
        $name=trim($request->get('name'));
        $product_type=$request->get('type');
        if($name!=''){
            $where .= ' and name="'.$name.'"';
        }
        if(!empty($product_type)){
            $product_type_in='';
            foreach($product_type as $k=>$v){
                if(empty($product_type_in)){
                    $product_type_in='"'.$v.'"';
                }else{
                    $product_type_in=$product_type_in.',"'.$v.'"';
                }
            }
            $where.=' and type in ('.$product_type_in.')';
        }

        //查询数据项
        $productTypeArr=$this->dictionaryRepository->returnTypeArray('product_type');

        //$listdata = $this->productModel->company()->whereRaw($where)->orderBy('id', 'desc')->paginate(20);

        $listdata = $this->productModel->whereRaw($where)->orderBy('id', 'desc')->paginate(20);

        return view('admin.product.list',['listdata'=>$listdata,
                'product_type'=>$productTypeArr,
                'company_list'=>$this->companyRepository->getAllCompanyList(1),
        ]);
    }

    /**
     *创建新产品
     * @author YangWei<yangwei@stnts.com>
     *
     * @param Request $request
     */
    public  function createProduct(Request $request){

    }


    /**
     *保存/更新产品数据
     * @author YangWei<yangwei@stnts.com>
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function storeProduct(Request $request){
        $id=$request->get('id');

        $data['name']               =$request->get('name');
        $data['type']               =$request->get('type');//产品类型
        $data['company_id']         =$request->get('company_id');//上游客户

        $mode_type          =   $request->get('mode_type');


        $game_screen        =   $request->get('game_screen');
        $game_theme         =   $request->get('game_theme');
        $screen_style       =   $request->get('screen_style');
        $business_model     =   $request->get('business_model');
        $charging_mode      =   $request->get('charging_mode');
        $game_type          =   $request->get('game_type');

        if(!empty($mode_type)){
            $data['mode_type']          =$request->get('mode_type');
        }


        if(!empty($game_screen)){
            $data['game_screen']        =implode(',',$request->get('game_screen'));
        }
        if(!empty($game_theme)){
            $data['game_theme']         =implode(',',$request->get('game_theme'));
        }
        if(!empty($screen_style)){
            $data['screen_style']       =implode(',',$request->get('screen_style'));
        }
        if(!empty($business_model)){
            $data['business_model']     =implode(',',$request->get('business_model'));
        }
        if(!empty($charging_mode)){
            $data['charging_mode']      =implode(',',$request->get('charging_mode'));
        }
        if(!empty($game_type)){
            $data['game_type']          =implode(',',$request->get('game_type'));
        }

        if($id!=''){//编辑
            $this->updateProduct($id,$data);
            return $this->ajaxSuccess('更新产品成功', [
                    'url' => ''
            ]);
        }
        $productid= $this->productRepository->create($data);

        $this->actionLog->log('product.create',
                Admin::user()->id,
                [  'id'         =>$productid->id,
                   'pname'     =>$data['name'],
                ]
        );
        return $this->ajaxSuccess('添加产品成功', [
                'id' =>$productid->id
        ]);
    }

    /**
     *更新产品数据
     * @author YangWei<yangwei@stnts.com>
     * @$id 编辑ID
     * @$data 更新的数据
     */
    public function updateProduct($id,$data){
        $this->productRepository->update($id,$data);
        $this->actionLog->log('product.edit',
                Admin::user()->id,
                [  'id'         =>$id,
                   'pname'     =>$data['name'],
                ]
        );
        return true;
    }

}
