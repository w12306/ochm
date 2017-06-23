<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BusinessException;
use App\Models\Contract;
use App\Repositories\CompanyRepository;
use App\Services\Admin\UploadServiceManager;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Bican\Roles\Models\Role;
use Bican\Roles\Models\Permission;
use App\Traits\Controller\CommonResponse;
use App\Repositories\Criterias\LatestCriteria;
use Illuminate\Database\Eloquent\Model;
use App\Models\AdminUser;
use App\Repositories\AdminUserRepository;
use App\Repositories\AdminUserTeamRepository;
use App\Repositories\DictionaryRepository;
use DB;
use Admin;
use Illuminate\Support\Str;
use Common\Packages\SimpleVerificationCode\Code as VerificationCode;
use App\Services\Admin\ActionLog;

/**
 * 权限控制管理
 *
 * @author  AaronLiu <liukan0926@stnts.com>
 * @package App\Http\Controllers\Admin
 */
class PermissionController extends Controller
{
    use CommonResponse;

    /**
     * @var AdminUserRepository
     */
    protected $adminUserRepository;

    /**
     * @var DictionaryRepository
     */
    protected $dictionaryRepository;

    /**
     * @var AdminUserTeamRepository
     */
    protected $adminUserTeamRepository;

    protected $companyRepository;

    /**
     * @var ActionLog
     */
    protected $actionLog;


    /**
     * @param AdminUserRepository  $adminUserRepository
     * @param DictionaryRepository $dictionaryRepository
     */
    public function __construct(AdminUserRepository $adminUserRepository,
                                AdminUserTeamRepository $adminUserTeamRepository,
                                ActionLog $actionLog,
                                DictionaryRepository $dictionaryRepository,
                                CompanyRepository $companyRepository
)
    {
        $this->adminUserRepository     = $adminUserRepository;
        $this->adminUserTeamRepository = $adminUserTeamRepository;
        $this->dictionaryRepository    = $dictionaryRepository;
        $this->actionLog               = $actionLog;
        $this->companyRepository       = $companyRepository;
    }

    /**
     * 管理员列表
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getAdminUserList(Request $request)
    {
        $renderData = [];

        //获得通用数据 ----------------------------------------------------------

        //读取所有权限角色
        $roles               = Role::all();
        $renderData['roles'] = &$roles;

        //读取所有执行小组
        $teams               = $this->dictionaryRepository->getEntriesByType('team');
        $renderData['teams'] = &$teams;

        //查询条件 --------------------------------------------------------------

        $where = [];

        //角色
        if ($request->has('role')) {
            $queryRole               = $request->input('role');
            $where[]                 = function ($query) use (&$queryRole) {
                $query->whereHas('roles', function ($query) use (&$queryRole) {
                    $query->where('role_id', $queryRole);
                });
            };
            $renderData['queryRole'] = &$queryRole;
        }

        //执行小组
        if ($request->has('teams')) {
            $queryTeams               = $request->input('teams', []);
            $where[]                  = function ($query) use (&$queryTeams) {
                $query->whereHas('teams', function ($query) use (&$queryTeams) {
                    $query->whereIn('team', $queryTeams);
                });
            };
            $renderData['queryTeams'] = &$queryTeams;
        }

        //真实姓名
        if ($request->has('real_name')) {
            $where[] = ['real_name', 'like', ('%' . $request->input('real_name') . '%')];
        }

        //用户名
        if ($request->has('username')) {
            $where[] = ['username', 'like', ('%' . $request->input('username') . '%')];
        }

        //查询数据 --------------------------------------------------------------

        $adminUsers               = $this->adminUserRepository
            ->with(['roles', 'teams'])
            ->applyWhere($where)
            ->paginate(20);

        $teamDept=$this->dictionaryRepository->getDeptTeam();
        $adminUsersEnd=$adminUsers->map(function ($user) use ($teamDept){
            $team_text='';
            $teams='';
            $team=$user->teams->pluck('team');
            $index=1;
            foreach($team as $t){
                if(empty($teams)){
                    $teams=$teamDept[$t];
                }else{
                    $teams=$teams.','.$teamDept[$t];
                }
                if($index<=3){
                    $team_text=$teams;
                }
                $index++;
            }
            return [
                    'id'=>$user->id,
                    'real_name'=>$user->real_name,
                    'username'=>$user->username,
                    'rolename'=>$user->roles->pluck('name')->toBase()->implode(',') ?: '--',
                    'team'=>!empty($teams)?$teams : '--',
                    'team_text'=>!empty($team_text)?$team_text.'...' : '--',
                    'created_at'=>$user->created_at->toDateString(),
                    'status'=>$user->status,
                    'status_text'=>$user->status_text,
                    'is_system'=>$user->is_system,
            ];
        });
        $renderData['adminUsersEnd'] = $adminUsersEnd;
        $renderData['adminUsers'] = $adminUsers;

        return view('admin.permission.admin-user-list', $renderData);
    }

    /**
     * 获得管理员信息，如果不传管理员用户ID则为新建
     *
     * @param Request $request
     * @param null    $adminUserId
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function getApiAdminUserInfo(Request $request, $adminUserId = null)
    {
        $returnData = [
            'id'         => '',
            'login_name' => '',
            'password'   => '',
            'real_name'  => '',
            'role'       => [],
            'team'       => [],
        ];

        /**
         * 获得通用数据
         */
        //获得所有权限角色
        $roles              = Role::all();
        $returnData['role'] = $roles->map(function ($role) {
            return [
                'key'   => $role->id,
                'value' => $role->name,
                's'     => 0,
            ];
        })->toArray();
        $teams=[];
        /**
         * 如果是修改管理员用户信息，则准备对应的数据
         */
        if ($adminUserId) {
            $adminUser = $this->adminUserRepository
                ->with(['roles', 'teams'])
                ->find($adminUserId);

            $returnData['id']         = $adminUser->id;
            $returnData['login_name'] = $adminUser->username;
            $returnData['password']   = '******';
            $returnData['real_name']  = $adminUser->real_name;
            $returnData['is_system']  = $adminUser->is_system;

            foreach ($returnData['role'] as $key => $teamData) {
                if ( ! $adminUser->roles->whereLoose('id', $teamData['key'])->isEmpty()) {
                    $returnData['role'][$key]['s'] = 1;
                }
            }
            foreach ($returnData['team'] as $key => $teamData) {
                if ( ! $adminUser->teams->whereLoose('team', $teamData['key'])->isEmpty()) {
                    $returnData['team'][$key]['s'] = 1;
                }
            }
            $teams=$adminUser->teams;
        } else {
            unset($returnData['id']);
        }

        //获得所有执行小组 对应的客户信息
        $returnData['team']=$this->serializationCompany($teams);

		//登录人的身份
		$returnData['login_is_system']=$this->serializationCompany($teams);

        return $this->ajaxSuccess('', $returnData);
    }

    /**
     *组织 创建/编辑客户 时分配客户数据权限
     * @author YangWei<yangwei@stnts.com>
     *
     * @return array
     */
    public function serializationCompany($teams){

        $haveTeams=$this->getUserHaveTeams($teams);//已经分配到用户的小组数据
        $teamCompanyArr=[];//
        $data=$this->companyRepository->with(['companyTeams'])->all();//获得全部客户

        $deptTeam=$this->dictionaryRepository->getDeptTeam();//获得全部小组

        foreach($deptTeam as $tid=>$tname){//构建页面显示小组的数据
            foreach($data->toArray() as $v){//查询属于该小组的客户信息
                if(isset($v['company_teams']) && !empty($v['company_teams'])){
                    foreach($v['company_teams'] as $k=>$cv){
                        if($cv['team']==$tid){
                            $teamCompanyArr[$tid][]=['id'=>$v['id'],'name'=>$v['company_name']];
                        }
                    }
                }
            }
            if(empty($teamCompanyArr[$tid])){
                $teamCompanyArr[$tid][]=[];//小组下面还没有关联客户
            }
        }
        $returnArr=[];

        foreach($teamCompanyArr as $k=>$v){
            $s=0;
            $data=[];
            if(!empty($v)) {
                foreach ($v as $ck => $cv) {
                    if(!empty($cv)){
                        $c_s=0;
                        if(isset($haveTeams[$k]) && in_array($cv['id'],$haveTeams[$k]['company'])){
                            $c_s=1;
                        }
                        $data[] =['key'=>$cv['name'],"ckname" =>"companyck_".$k."[]","ckvalue" =>$cv['id'],"s" =>$c_s];
                    }
                }
            }
            if(isset($haveTeams[$k])){
                $s=1;
            }
            $returnArr[]=['key'=>$deptTeam[$k],"ckname"=>"team[]","ckvalue"=>$k,"s"=>$s,'data'=>$data];
        }
        return $returnArr;
    }

    /**
     * 获得用户已经含有的小组 客户 合作方数据
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function getUserHaveTeams($teams){
        $returnArr=[];
        foreach($teams as $t){
            $companyids=[];
            $partnerids=[];
            if(!empty($t->company_ids)){
                $companyids=explode(',',$t->company_ids);
            }
            if(!empty($t->partner_ids)){
                $partnerids=explode(',',$t->partner_ids);
            }
            $returnArr[$t->team]=['company'=>$companyids,'partner'=>$partnerids];
        }
        return $returnArr;
    }


    /**
     * 保存/更新管理员用户信息
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @throws BusinessException
     */
    public function postApiSaveAdminUser(Request $request)
    {
        if ($request->has('id')) {
            return $this->updateAdminUser($request);
        } else {
            return $this->storeAdminUser($request);
        }
    }

    /**
     * 创建管理员信息
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @throws BusinessException
     */
    protected function storeAdminUser(Request $request)
    {
        DB::beginTransaction();

        $queryUsername = $request->input('login_name');
        $addData = [
            'status'    => 1,
            'username'  => $queryUsername,
            'password'  => bcrypt($request->input('password')),
            'email'     => '',
            'real_name' => $request->input('real_name'),
        ];

        //用户名不能重复
        if ($this->adminUserRepository->isUsernameExists($queryUsername)) {
            throw new BusinessException('用户名已存在');
        }

        $adminUser = $this->adminUserRepository->create($addData);


		//附加小组信息
		$this->modifyAdminUserTeams($adminUser, $request);

        //记录日志
        $this->actionLog->log('admin-user.create',
            Admin::user()->id,
            array_merge($adminUser->toArray(), [
                'company_id' => 0,
            ])
        );

        //附加角色信息
        $this->modifyAdminUserRolesByRoleIds($adminUser, [$request->input('role_id')]);

        //附加小组信息
        $this->modifyAdminUserTeams($adminUser, $request);

        DB::commit();

        return $this->ajaxSuccess('新增管理员用户信息成功',['url'=>route('admin.config.permission.admin-user-list')]);
    }

    /**
     * 更新管理员信息
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @throws BusinessException
     */
    protected function updateAdminUser(Request $request)
    {
        DB::beginTransaction();

        $queryId = $request->input('id');

        $oldAdminUser = $this->adminUserRepository->find($queryId);

        $real_name  =$request->input('real_name');
        if(!empty($real_name)){
            $updateData = [
                    'real_name' => $request->input('real_name'),
            ];
        }

        //如果需要修改密码，则重置用户的唯一登陆TOKEN
        if ($request->has('password') && $request->input('password')!="******") {
            $updateData['password']   = bcrypt($request->input('password'));
            $updateData['last_token'] = '';
        }
        if(!isset($updateData) || empty($updateData)){
            throw new BusinessException('没有任何数据更新！');
        }
        $newAdminUser = $this->adminUserRepository->update($request->input('id'), $updateData);

        //附加角色信息
        $this->modifyAdminUserRolesByRoleIds($newAdminUser, [$request->input('role_id')]);

        //附加小组信息
        $this->modifyAdminUserTeams($newAdminUser, $request);

        //记录日志
        $this->actionLog->log('admin-user.edit',
            Admin::user()->id,
            array_merge($newAdminUser->toArray(), [
                'company_id' => 0,
            ]),
            $oldAdminUser->toArray(),
            $newAdminUser->toArray()
        );

        DB::commit();

        return $this->ajaxSuccess('修改管理员用户信息成功',['url'=>route('admin.config.permission.admin-user-list')]);
    }

    /**
     * 编辑管理员信息
     *
     * @param $adminUserId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getEditAdminUser($adminUserId)
    {
        return view('admin.permission.edit-admin-user',
            ['adminUserId'=>$adminUserId]
        );
    }
    /**
     * 创建管理员信息
     *
     * @param $adminUserId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getCreateAdminUser()
    {
        return view('admin.permission.edit-admin-user' ,['adminUserId'=>'']);
    }

    /**
     * 更新管理员信息
     *
     * @param Request $request
     * @param         $adminUserId
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function postUpdateAdminUser(Request $request, $adminUserId)
    {
        DB::beginTransaction();

        /**
         * @var AdminUser $user
         */
        $user = $this->adminUserRepository->with('roles')->find($adminUserId);

        $this->modifyAdminUserRolesByRoleIds($user, $request->get('role_csv', ''));

        DB::commit();

        return $this->ajaxSuccess('修改管理员信息成功', [
            'url' => route('admin.config.permission.admin-user-list')
        ]);
    }

    /**
     * 获得所有权限角色
     *
     * @param Request $request
     * @param null    $adminUserId
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function getApiAllRoles(Request $request, $adminUserId = null)
    {
        $roles = Role::query()->get();

        $returnData = [
            'datalist' => $roles->transform(function ($role) {
                return [
                    'id'   => (string)$role->id,
                    'name' => (string)$role->name,
                ];
            }),
            'datanum'  => $roles->count(),
            'selected' => [],
        ];

        //如果指定了用户ID，则获取该用户拥有的角色返回
        if ($adminUserId) {
            $adminUser = $this->adminUserRepository
                ->with(['roles'])
                ->find($adminUserId);

            $returnData['selected'] = $adminUser->roles->map(function ($role) {
                return [
                    'id'   => (string)$role->id,
                    'name' => (string)$role->name,
                ];
            });
        }

        return $this->ajaxSuccess('', $returnData);
    }

    /**
     * 角色列表
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getRoleList()
    {
        $roles = Role::query()->with('permissions')->paginate(15);

        return view('admin.permission.role-list', compact('roles'));
    }

    /**
     * 创建权限角色
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getCreateRole()
    {
        //读取所有权限并以代码为key，方便调用
        $permissions = Permission::all()->keyBy('slug');

        //读取配置中的权限分组，用以分组显示权限
        $permissionGroups = app('config')['permissions']['groupedPermissions'];

        return view('admin.permission.edit-role', compact(
            'permissions',
            'permissionGroups'
        ));
    }

    /**
     * 保存权限角色
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function postStoreRole(Request $request)
    {
        $inputs = $request->all();

        DB::beginTransaction();

        //判断slug是否已存在，是则提示错误
        $count = Role::where('slug', $inputs['slug'])->count();
        if ($count != 0) {
            return $this->ajaxError('角色代码已存在，请修改');
        }

        $role = Role::create($inputs);

        //给新建的角色附加权限
        $this->modifyRolePermissionsByPermissionSlugs($role, $request->get('permissions', []));

        //记录日志
        /*$this->actionLog->log('permission-role.create',
            Admin::user()->id,
            $role->toArray(),
            [],
            []
        );*/

        DB::commit();

        return $this->ajaxSuccess('创建角色成功', [
            'url' => route('admin.config.permission.role-list')
        ]);
    }

    /**
     * 修改权限角色
     *
     * @param Request $request
     * @param         $roleId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getEditRole(Request $request, $roleId)
    {
        $role = Role::with('permissions')->find($roleId);

        //将角色的权限以slug作为key，方便view中赋值
        $role->permissions = $role->permissions->keyBy('slug');

        //读取所有权限并以代码为key，方便调用
        $permissions = Permission::all()->keyBy('slug');

        //读取配置中的权限分组，用以分组显示权限
        $permissionGroups = app('config')['permissions']['groupedPermissions'];

        return view('admin.permission.edit-role',
            compact('role', 'permissions', 'permissionGroups')
        );
    }

    /**
     * 更新权限角色
     *
     * @param Request $request
     * @param         $roleId
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function postUpdateRole(Request $request, $roleId)
    {
        $oldRole = Role::with('permissions')->find($roleId);

        $inputs = $request->all();

        DB::beginTransaction();

        //判断slug是否已存在，是则提示错误
        $count = Role::query()
            ->where('id', '!=', $oldRole->id)
            ->where('slug', $inputs['slug'])
            ->count();
        if ($count != 0) {
            return $this->ajaxError('角色代码已存在，请修改');
        }

        //更新基本信息
        $oldRole->update($inputs);

        $newRole = Role::with('permissions')->find($roleId);

        //修改角色权限
        $this->modifyRolePermissionsByPermissionSlugs($oldRole, $request->get('permissions', []));

        //记录日志
        /*$this->actionLog->log('permission-role.edit',
            Admin::user()->id,
            $newRole->toArray(),
            $oldRole->toArray(),
            $newRole->toArray()
        );*/

        DB::commit();

        return $this->ajaxSuccess('修改角色成功', [
            'url' => route('admin.config.permission.role-list')
        ]);
    }

    /**
     * [notice]此方法暂未用上
     * 删除权限角色
     *
     * @param $roleId
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function postDeleteRole($roleId)
    {
        /**
         * @var Role $role
         */
        $role = Role::find($roleId);

        $role->detachAllPermissions();

        $role->delete();

        return $this->ajaxSuccess('删除权限角色成功', null);
    }

    /**
     * 修改角色的权限
     *
     * @param Role|Model $role
     * @param array      $permissionSlugs
     */
    protected function modifyRolePermissionsByPermissionSlugs(Role $role,
                                                              array $permissionSlugs)
    {
        //获得slugs对应的权限
        $permissions = collect($permissionSlugs)->isEmpty() ?
            collect([]) :
            Permission::whereIn('slug', $permissionSlugs)->get();

        //如果角色还没有获得权限，则获得
        if ( ! isset($role->permissions)) {
            $role->permissions();
        }

        //将没有继续勾选的的权限删除
        $role->permissions->each(function ($permission) use (&$role, $permissions) {
            if ($permissions->whereLoose('slug', $permission->slug)->isEmpty()) {
                $role->detachPermission($permission);
            }
        });

        //增加以前没有的权限
        $permissions->each(function ($permission) use (&$role) {
            if ($role->permissions->where('slug', $permission)->isEmpty()) {
                $role->attachPermission($permission);
            }
        });
    }

    /**
     * 修改管理员关联的权限角色
     *
     * @param AdminUser $adminUser
     * @param array     $roleIds
     */
    protected function modifyAdminUserRolesByRoleIds(AdminUser $adminUser, $roleIds)
    {
        DB::beginTransaction();

        //获得slugs对应的权限
        $roles = collect($roleIds)->isEmpty() ?
            collect([]) :
            Role::whereIn('id', $roleIds)->get();

        //如果用户的权限角色还没有获得，则获得
        if ( ! isset($adminUser->roles)) {
            $adminUser->roles();
        }

        //将没有继续勾选的的权限角色删除
        $adminUser->roles->each(function ($role) use (&$adminUser, $roles) {
            if ($roles->where('slug', $role->slug)->isEmpty()) {
                $adminUser->detachRole($role);
            }
        });

        //增加以前没有的权限
        $roles->each(function ($role) use (&$adminUser) {
            if ($adminUser->roles->where('slug', $role)->isEmpty()) {
                $adminUser->attachRole($role);
            }
        });

        DB::commit();
    }

    /**
     * 修改管理员关联的执行小组客户信息
     *
     * @param AdminUser $adminUser
     * @param           $teams
     * @throws BusinessException
     */
    protected function modifyAdminUserTeams(AdminUser $adminUser, $request)
    {
        DB::beginTransaction();

        //这些小组信息必须都存在
        $teams=$request->input('team');//被勾选的小组数据
        $this->teamsMustAllExists($teams);

        //清空管理员用户以前关联到的小组信息
        $this->clearAllTeamsOfAdminUser($adminUser);

        //添加小组信息
        if(!empty($teams)) {
            foreach ($teams as $team) {
                //1.组织客户id信息多个用逗号分隔 组成字符串
                $companyck = [];
                $companyck = $request->input('companyck_' . $team);//被勾选的改小组的客户数据
                $company_ids = '';
                if (!empty($companyck)) {
                    $company_ids = implode(',', $companyck);
                }
                //2.组织客户下面 合作方信息 多个用逗号分隔 组成字符串  并去重（有些客户会关联上相同的合作方）
                $partner_ids = '';
                if (!empty($company_ids)) {
                    $partner_ids = $this->getPartnerIdsByCompanyIds($company_ids);
                }

                $this->adminUserTeamRepository->create([
                        'admin_user_id' => $adminUser->id,
                        'team' => $team,
                        'company_ids' => $company_ids,
                        'partner_ids' => $partner_ids,
                ]);
            }
            //如果修改了数据权限需要重新登录
            if (Admin::user()->id==$adminUser->id) {
                $updateData['last_token'] = '';
                $this->adminUserRepository->update($adminUser->id,$updateData);
            }
        }
        DB::commit();
    }

    /**
     * 获得合作方数据的多个ID  用逗号分隔成字符串
     * @author YangWei<yangwei@stnts.com>
     *
     * @param string $companyIds
     * @return string
     */
    public function getPartnerIdsByCompanyIds($companyIds=''){
        $data=$this->companyRepository->getPartneridsByCompanyId($companyIds)->toArray();
        $returnids=[];

        foreach($data as $k=>$v) {
            $returnids[$v['partner_id']]=$v['partner_id'];
        }
        if(!empty($returnids)){
            return implode(',',$returnids);
        }else{
            return '';
        }
    }

    /**
     * 这些小组信息必须都存在
     *
     * @param $teams
     * @throws BusinessException
     */
    protected function teamsMustAllExists($teams)
    {
        if(empty($teams)){return ;}
        foreach ($teams as $team) {
            $notExists = $this->dictionaryRepository
                ->applyWhere([
                    ['type', '=', 'team'],
                    ['id', '=', $team],
                    ['status', '=', 1],
                ])
                ->all()
                ->isEmpty();

            if ($notExists) {
                throw new BusinessException('选择的小组：“' . $team . '”不存在');
            }
        }
    }

    /**
     * 清空管理员用户以前关联到的小组信息
     *
     * @param AdminUser $adminUser
     */
    protected function clearAllTeamsOfAdminUser(AdminUser $adminUser)
    {
        DB::beginTransaction();
        $this->adminUserTeamRepository->deleteByUserId($adminUser->id);
        DB::commit();
    }

    /**
     *更新后台用户的状态
     * @author YangWei<yangwei@stnts.com>
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @throws BusinessException
     */
    protected  function updateStatus(Request $request){
        $status=$request->get('status');
        $id    =$request->get('id');
        if(empty($id)){throw new BusinessException('系统数据错误!');}
        if(empty($status)){$status=0;}
        $this->adminUserRepository->update($id,['status'=>$status]);
        return $this->ajaxSuccess('更新成功!');
    }

    /**
     *个人修改密码
     * @author YangWei<yangwei@stnts.com>
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editUserPw(Request $request)
    {
        $submit=$request->input('submit');
        $id    =$request->input('id');
        if(!empty($submit)){//提交了修改密码的数据
            if(empty($id)){
                return $this->ajaxError('数据缺失!');
            }
            $old_psw=$request->input('old_psw');
            $new_psw=$request->input('new_psw');
            //验证新密码是否正确

            if(!$this->adminUserRepository->isOldPassWord($id,$old_psw)){
                return $this->ajaxError('原密码错误!');
            }

            $updateData['password']   = bcrypt($new_psw);
            $updateData['last_token'] = '';
            try{
                $this->adminUserRepository->update($id, $updateData);
                return $this->ajaxSuccess('更新成功!');
            }catch (\Exception $e){
                return $this->ajaxError('更新数据失败!');
            }
        }else{
            return view('admin.permission.edit-user-pw',['id'=>$id] );
        }
    }

    /**
     *
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function getUsersByTeamIds(Request $request){
        $teams=$request->input('teams');//"12,13";//
        $company_id=$request->input('id');

        if(empty($teams)){
            return $this->ajaxSuccess('查询成功!',[]);
        }

        $user_selected=[];
        if(!empty($company_id)){
            $all_user_info=$this->adminUserTeamRepository->getAllUsers($teams);
            foreach($all_user_info as $user){
                $companys=explode(',',$user->company_ids);
                if(in_array($company_id,$companys)){
                    $user_selected[$user->admin_user_id]=1;
                }
            }
        }

        $user_infos=$this->adminUserTeamRepository->getUsers($teams);
        $userIds=[];

        foreach($user_infos as $user){
            $userIds[]=$user->admin_user_id;
        }
        if(empty($userIds)){return $this->ajaxError('未找到用户数据!');}

        $users=$this->adminUserRepository->getUsersById($userIds);

        $datas=[];
        foreach($users as $user){
            $selected=0;
            if(!empty($user_selected) && isset($user_selected[$user->id])){
                $selected=1;
            }
            $datas[]=[
                'id'=>$user->id,
                'name'=>$user->real_name,
                'selected'=>$selected,
            ];
        }
        return $this->ajaxSuccess('查询成功!',$datas);
    }
}
