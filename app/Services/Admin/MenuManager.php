<?php

namespace App\Services\Admin;

use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use App\Services\Admin\MenuManager\Menu;

/**
 * 后台菜单管理类
 *
 * @author  AaronLiu <liukan0926@foxmail.com>
 * @package App\Services\Admin
 */
class MenuManager
{

    /**
     * 配置文件(config/menus.php)中保存的原始数据。
     *
     * @var array
     */
    protected $menus;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $magicMenus;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var string
     */
    protected $routeNamePrefix;

    /**
     * @var string
     */
    protected $currentRouteName;


    /**
     * @param Router $router
     * @param array  $menus menu配置数组
     * @param string $routeNamePrefix
     */
    public function __construct(Router $router, array $menus, $routeNamePrefix = 'admin.')
    {
        $this->menus           = $menus;
        $this->router          = $router;
        $this->routeNamePrefix = $routeNamePrefix;

        $this->magicMenus = $this->prepareMenus($menus);
    }

    /**
     * 得到路由名称
     * route配置中的路由名称为了避免冲突，统一加了前缀，所以要在这里去掉前缀
     *
     * @return mixed
     */
    protected function currentRouteName()
    {
        if ( ! $this->currentRouteName) {
            $this->currentRouteName = $this->router->currentRouteName();
        }

        return $this->currentRouteName;
    }

    /**
     * 得到当前匹配的次级菜单
     *
     * @return array|mixed
     */
    public function getMenus()
    {
        return $this->magicMenus;
    }

    /**
     * @param           $menus
     * @param null|Menu $parentMenu
     * @return Menu
     */
    protected function prepareMenus($menus, $parentMenu = null)
    {
        /**
         * 返回的菜单对象都属于一个父级菜单节点
         */
        $returnMagicMenus = new Menu([
            'key'        => 'root',
            'name'       => 'root',
            'permission' => '*',
            'active'     => true,
            'route'      => null,
        ]);

        //获得当前URL的路由别名
        $currentRouteName = $this->currentRouteName();

        foreach ($menus as $key => $menu) {
            //得到菜单的key，如果有父级菜单，则以父级菜单的key加上“.”作为前缀
            $menuKey = is_null($parentMenu) ?
                $key :
                ($parentMenu->getKey() . '.' . $key);

            //计算当前菜单是否为激活状态
            $isActive = $this->matchRoute($menu['match'], $currentRouteName);

            $magicMenu = new Menu([
                'key'        => $menuKey,
                'name'       => $menu['name'],
                'permission' => $menu['permission'],
                'active'     => $isActive,
                'route'      => (isset($menu['route']) ? $menu['route'] : null),
            ]);

            /**
             * 如果在菜单配置中，当前菜单还拥有子菜单，
             * 则得到所有子菜单并附加到当前菜单的子菜单
             */
            if (isset($menu['children']) && ! empty($menu['children'])) {
                $this
                    ->prepareMenus($menu['children'], $magicMenu)
                    ->each(function ($childMenu) use (&$magicMenu) {
                        $magicMenu->appendChildMenu($childMenu);
                    });
            }

            $returnMagicMenus->appendChildMenu($magicMenu);
        }

        return $returnMagicMenus;
    }

    /**
     * 用菜单配置中的match规则生成正则表达式
     *
     * @param $match
     * @return mixed|string
     */
    protected function makeRegexpIfIsNot($match)
    {
        if (preg_match('/^\/.*\/([a-z]*)$/', $match)) {
            return $match;
        }

        $regexp = $match;
        $regexp = preg_replace('/\./', '\\.', $regexp);
        $regexp = preg_replace('/\-/', '\\-', $regexp);
        $regexp = '/^' . $regexp . '(\.|$)/';

        return $regexp;
    }

    /**
     * 将菜单key转换为正则表达式
     *
     * @param $match
     * @param $currentRouteName
     * @return bool
     */
    protected function matchRoute($match, $currentRouteName)
    {
        $regexp = $this->makeRegexpIfIsNot($match);

        return (Boolean)preg_match($regexp, $currentRouteName);
    }


    /**
     * 去掉路由名称的前缀
     *
     * @param $routeName
     * @return string
     */
    protected function removeRouteNamePrefix($routeName)
    {
        if (starts_with($routeName, $this->routeNamePrefix)) {
            return substr($routeName, strlen($this->routeNamePrefix));
        } else {
            return $routeName;
        }
    }

}