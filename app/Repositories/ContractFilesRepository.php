<?php

namespace App\Repositories;

use App\Exceptions\BusinessException;
use App\Models\ContractFiles;
use Housekeeper\Eloquent\BaseRepository;


/**
 * 合同文件
 *
 * @author  yangwei <yangwei@foxmail.com>
 * @package App\Repositories
 */
class ContractFilesRepository extends BaseRepository
{
    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        return ContractFiles::class;
    }

    /**
     *根据合同id 查找已经存在的合同文件附件
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $ContractId
     * @return array
     */
    public function getFilesArrByContractId($ContractId){
        $files=$this->model->where('contract_id','=',$ContractId)->get();
        if(!empty($files) && !$files->isEmpty()){
            $fileArr=[];
            foreach($files as $f){
                $fileArr[$f->filepath]=$f->id;
            }
            return $fileArr;
        }else{
            return [];
        }
    }

}
