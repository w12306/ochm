<?php

namespace App\Services\Admin;

use App\Repositories\AdminUserTeamRepository;
use Illuminate\Contracts\Foundation\Application;
use App\Exceptions\BusinessException;

/**
 * 管理员数据权限
 *
 * @author
 * @package App\Services\Admin
 */
class PermissionTeam
{
    /**
     * @var $adminUserTeamRepository
     */
    protected $adminUserTeamRepository;


    /**
     * @param Application $app
     */
    public function __construct(Application $app,
                                AdminUserTeamRepository $adminUserTeamRepository
                                )
    {
        $this->adminUserTeamRepository  =   $adminUserTeamRepository;
    }

    /**
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function setUserPermissionTeam($adminUser){
        $this->getUserTeamids($adminUser);
    }



    /**
     * 获得当前登录人的所属小组信息 多个用字符分隔
     *
     * 如果同小组出现相同的客户和合作方 这里将会去重
     * @author YangWei<yangwei@foxmail.com>
     *
     * @throws BusinessException
     */
    public function getUserTeamids($adminUser)
    {
        $team=[];
        $company=[];
        $partner=[];
        $this->adminUserTeamRepository->getUserTeams($adminUser->id)->map(function($data) use (&$team,&$company,&$partner){
            $team[$data->team]      = $data->team;
            if(!empty($data->company_ids)){
                $companyArr=explode(',',$data->company_ids);
                foreach($companyArr as $v){
                    $company[$v]   = $v;
                }
            }
            /*if(!empty($data->partner_ids)){
                $partnerArr=explode(',',$data->partner_ids);
                foreach($partnerArr as $v){
                    $partner[$v]   = $v;
                }
            }*/
        });

        if(empty($team)){
            $team=0;
        }else{
            $team=implode(',',$team);
        }

        if(empty($company)){
            $company=0;
        }else{
            $company=implode(',',$company);
        }

        $company_sql=' 1=1 ';
        $team_sql=' 1=1 ';
        $company_id_sql=' 1=1 ';
        $team_id_sql=' 1=1 ';
        if($adminUser->is_system!=1){
            $company_sql    =   ' company_id in ('.$company.') ';
            $team_sql       =   ' team in ('.$team.') ';
            $company_id_sql =' id in ('.$company.') ';
            $team_id_sql    =' id in ('.$team.') ';
        }
        session(['teamids'=>$team,
                'companyids'=>$company,
                'team_sql'=>$team_sql,
                'company_sql'=>$company_sql,
                'team_id_sql'=>$team_id_sql,
                'company_id_sql'=>$company_id_sql,
                'isSystem'  =>$adminUser->is_system,
        ]);
        /*if(empty($partner)){
            $partner=0;
        }else{
            $partner=implode(',',$partner);
        }*/

        //'partnerids'=>$partner
    }






}