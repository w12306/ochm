<?php

namespace App\Services\Admin\MenuManager;

use Illuminate\Support\Collection;

/**
 * Class Menu
 *
 * @package App\Services\Admin\MenuManager
 */
class Menu extends Collection
{

    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var mixed
     */
    protected $permission;

    /**
     * @var bool
     */
    protected $active;

    /**
     * @var string
     */
    protected $route;

    /**
     * @var null|Menu
     */
    protected $parentMenu;


    /**
     * @param array $items
     */
    public function __construct(array $attributes, $items = [])
    {
        $this->mapAttributes($attributes);

        parent::__construct($items);
    }

    /**
     * @param array $attributes
     */
    protected function mapAttributes(array $attributes)
    {
        $this->key        = $attributes['key'];
        $this->name       = $attributes['name'];
        $this->permission = $attributes['permission'];
        $this->active     = $attributes['active'];
        $this->route      = $attributes['route'];
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param Menu $parentMenu
     */
    public function attachTo($parentMenu)
    {
        $this->parentMenu = $parentMenu;
    }

    /**
     * @param Menu $subMenu
     */
    public function appendChildMenu($subMenu)
    {
        $subMenu->attachTo($this);
        $this->push($subMenu);
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @return bool
     */
    public function isLink()
    {
        return $this->route != '';
    }

    /**
     * @param $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return string
     */
    public function route()
    {
        return $this->route;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function permission()
    {
        return $this->permission;
    }

    /**
     * 检查用户对这个菜单是否有权限
     *
     * @param \Bican\Roles\Traits\HasRoleAndPermission $user
     * @return mixed
     */
    public function authorize($user)
    {
        //根据当前目录权限模式，选择合适的判断方式
        $permission = $this->permission();

        if ($permission == '') {
            return true;
        } elseif (is_array($permission)) {
            if ($permission[0] == 'any') {
                return $user->canOne($permission[1]);
            } else {
                return $user->canAll($permission[1]);
            }
        } else {
            return $user->can($permission);
        }
    }

}