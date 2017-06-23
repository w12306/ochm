<?php

namespace App\Services;

use DB;
/**
 * 用于连接广告部门的数据库的服务
 * Class ExecutiveInfo
 *
 * @package App\Services\Admin
 */
class DataBaseService
{



    public function __construct()
    {

    }

    /**
     *
     * @author YangWei<yangwei@stnts.com>
     *
     * @return \Illuminate\Database\Connection
     */
    public function connection(){
        return DB::connection('ad_dept');
    }

    /**
     * 查询数据
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function select($sql){

    }

    /**
     *删除数据
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $table
     * @param $ids
     * @return bool
     */
    public  function delete($table,$ids){
        if(empty($table) || empty($ids)){return false;}

        return $this->connection()->table($table)->whereIn('sid',$ids)->delete();
    }

    /**
     * 保存数据
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $table
     * @param $data
     * @return bool
     */
    public function stroe($table,$data){
        if(empty($table) || empty($data)){return false;}

        $time=date('Y-m-d H:i:s',time());

        return $this->connection()->table($table)->insert($data);
    }



}