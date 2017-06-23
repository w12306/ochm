<?php namespace App\Http\ViewComposers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Common\Packages\Admin\Contracts\Guard;
use App\Models\AdminUser;
use Illuminate\Routing\Router;
use App\Services\Admin\MenuManager;
use App\Services\Admin\MenuManager\Menu;

/**
 * 后台通用信息处理类
 *
 * @author  AaronLiu <liukan0926@stnts.com>
 * @package App\Http\ViewComposers
 */
class MainLayoutComposer
{

    /**
     * @var Guard
     */
    protected $auth;

    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|null|AdminUser
     */
    protected $adminUser;

    /**
     * @var MenuManager
     */
    protected $menuManager;


    /**
     * @param Guard $auth
     */
    public function __construct(Guard $auth, MenuManager $menuManager)
    {
        $this->auth        = $auth;
        $this->menuManager = $menuManager;
        $this->adminUser   = $auth->user();
    }

    /**
     * 绑定数据给view
     *
     * @param  View $view
     * @return void
     */
    public function compose(View $view)
    {
        /**
         * 附加菜单信息
         */
        $menus = $this->menuManager->getMenus();
        //提出左侧当前菜单
        $leftMenus = $this->getCurrentLeftMenus($menus);
        $view->with('menus', $menus);
        $view->with('leftMenus', $leftMenus);

        /**
         * 附加当前用户信息
         */
        $view->with('admin', $this->adminUser);

    }

    /**
     * 得到当前的左侧菜单
     *
     * @param Menu $menus
     * @return mixed
     */
    protected function getCurrentLeftMenus($menus)
    {
        $leftMenus = $menus->first(function ($key, $menu) {
            /**
             * @var Menu $menu
             */
            return $menu->isActive();
        });

        //如果没有匹配任何路径，则使用第一组菜单
        if (is_null($leftMenus)) {
            $leftMenus = $menus->first();
        }

        return $leftMenus;
    }


}