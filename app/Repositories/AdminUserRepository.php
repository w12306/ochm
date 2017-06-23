<?php

namespace App\Repositories;

use Housekeeper\Eloquent\BaseRepository;
use App\Models\AdminUser;
use Housekeeper\Action;
use Common\Packages\Admin\Contracts\UserIdentity;

class AdminUserRepository extends BaseRepository
{

    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        return AdminUser::class;
    }

    /**
     * 后台所有用户列表  供查询条件使用
     */
    public function adminUserlist()
    {
        return $this->model->all();
    }

    /**
     * @param       $id
     * @param array $columns
     * @return mixed
     */
    public function findById($id, $columns = ['*'])
    {
        return $this->model->where('id', '=', $id)->get($columns)->first();
    }

    /**
     * 检查用户名是否已存在
     *
     * @param  string     $username
     * @param null|string $exceptId
     * @return bool
     */
    public function isUsernameExists($username, $exceptId = null)
    {
        return $this->wrap(
            array($this, '_isUsernameExists'),
            new Action(__METHOD__, func_get_args(), Action::READ)
        );
    }

    /**
     * @param      $username
     * @param null $exceptId
     * @return bool
     */
    protected function _isUsernameExists($username, $exceptId = null)
    {
        $this->model = $this->model->where('username', '=', $username);

        if ($exceptId) {
            $this->model = $this->model->where('id', '!=', $exceptId);
        }

        return $this->model->exists();
    }

    /**
     * 有用户则返回，否则新建用户并返回
     *
     * @param UserIdentity $user
     * @return static
     */
    public function findByUsernameOrCreate(UserIdentity $user)
    {
        return $this->model->updateOrCreate(
            [
                'username' => $user->getUsername(),
            ],
            [
                'real_name'    => $user->getName(),
                'email'        => $user->getEmail(),
                'uams_user_id' => $user->getUamsUserId(),
            ]
        );
    }

    /**
     *检查密码是否和用户的密码一致
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $id
     * @param $old_psw
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function isOldPassWord($id,$old_psw){
        $data=$this->model->where('id', '=', $id)->first();

        return app('hash')->check($old_psw, $data->password);
    }

    /**
     *
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function getUsersById($id){
        if(is_array($id)){
            return $this->model->whereIn('id',$id)->get();
        }else{
            return $this->model->where('id','=',$id)->get();
        }
    }


}
