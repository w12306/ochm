<?php
/**
 * ADPS相关配置
 *
 * @author AaronLiu <liukan0926@stnts.com>
 */

return [

    'AD'     => [
        /**
         * 广告位
         * 广告位信息都是写死在这里，无论是名称还是id，使用console命令同步到mysql表中
         */
        'SPACES' => [
            [
                'id'            => 10,
                'name'          => 'matrix_top',
                'matrix_navbar' => true,  //此广告位带有Matrix关键字定向
                'width'         => '678',
                'height'        => '122',
            ],
            [
                'id'            => 11,
                'name'          => 'matrix_背投',
                'matrix_navbar' => false,
                'width'         => '',
                'height'        => '',
            ],
            [
                'id'            => 12,
                'name'          => 'matrix_气泡',
                'matrix_navbar' => false,
                'width'         => '',
                'height'        => '',
            ],
            [
                'id'            => 13,
                'name'          => 'matrix_更新位',
                'matrix_navbar' => false,
                'width'         => '590',
                'height'        => '280',
            ],

            [
                'id'            => 14,
                'name'          => 'matrix_更新位相关推荐',
                'matrix_navbar' => false,
                'width'         => '',
                'height'        => '',
            ],
            [
                'id'            => 15,
                'name'          => 'matrix_猜你还想找',
                'matrix_navbar' => false,
                'width'         => '',
                'height'        => '',
            ],
            [
                'id'            => 16,
                'name'          => 'matrix_猜你喜欢',
                'matrix_navbar' => false,
                'width'         => '',
                'height'        => '',
            ],
            [
                'id'            => 17,
                'name'          => '软件',
                'matrix_navbar' => false,
                'width'         => '',
                'height'        => '',
            ],
        ],
    ],

    /**
     * Matrix相关配置
     */
    'MATRIX' => [

        /**
         * Matrix的配置文件相关
         */
        'CONFIG' => [

            /**
             * 配置文件的加密KEY（AES）
             */
            'ENCRYPT_KEY'  => env('APP_MATRIX_CONFIG_ENCRYPT_KEY', ''),

            /**
             * 同步ADPS MATRIX配置的服务器
             *
             * 目前线上用的是：
             * http://58.67.200.166:81/
             * http://58.67.200.167:81/
             * http://210.14.141.198:81/
             * http://210.14.141.199:81/
             * 测试环境是：
             * http://apsres.stnts.com/
             */
            'SYNC_SERVERS' => explode(',', trim(env('APP_MATRIX_CONFIG_SYNC_SERVERS', ''), ',')),
        ],


    ],


];