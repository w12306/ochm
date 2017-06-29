<?php

namespace App\Services\Admin;

use Illuminate\Contracts\Foundation\Application;
use App\Exceptions\BusinessException;
use App\Repositories\AdminUserActionLogRepository;
use Carbon\Carbon;

/**
 * 管理员操作日志
 *
 * @author  AaronLiu <liukan0926@foxmail.com>
 * @package App\Services\Admin
 */
class ActionLog
{
    /**
     * @var array
     */
    protected $termSets;

    /**
     * @var array
     */
    protected $formats;

    /**
     * @var AdminUserActionLogRepository
     */
    protected $adminUserActionLogRepository;


    /**
     * @param Application $app
     */
    public function __construct(Application $app,
                                AdminUserActionLogRepository $adminUserActionLogRepository)
    {
        $this->termSets = $app->make('config')['services']['actionLog']['termSets'];
        $this->formats  = $app->make('config')['services']['actionLog']['formats'];

        $this->adminUserActionLogRepository = $adminUserActionLogRepository;
    }

    /**
     * 记录操作日志
     *
     * @param       $formatName
     * @param       $adminUserId
     * @param       $keyData
     * @param array $old
     * @param array $new
     * @throws BusinessException
     */
    public function log($formatName,
                        $adminUserId,
                        $keyData,
                        $old = [],
                        $new = [])
    {
        //转换对象为数组
        $keyData = is_array($keyData) ? $keyData : $keyData->toArray();
        $old     = is_array($old) ? $old : $old->toArray();
        $new     = is_array($new) ? $new : $new->toArray();

        $format = $this->getFormat($formatName);

        $logData = $this->formatLog(
            $format,
            $keyData
        );

        //保存原始数据
        $logData['raw'] = json_encode([
            'old' => $old,
            'new' => $new,
        ]);

        $this->storeLog($adminUserId, $logData);
    }

    /**
     * 生成日志数据
     *
     * @param       $format
     * @param array $keyData
     * @return array
     * @throws BusinessException
     */
    protected function formatLog($format, $keyData)
    {
        if ( ! isset($keyData[$format['mainIdKey']])) {
            throw new BusinessException("日志的主要数据没有提供，KEY为：“{$format['mainIdKey']}”");
        }

        /*if ( ! isset($keyData[$format['companyIdKey']])) {
            throw new BusinessException("日志的主要数据没有提供，KEY为：“{$format['companyIdKey']}”");
        }*/

        //$terms = $this->termSets[$format['termSet']];

        return [
            'module'     => $format['module'],
            'main_id'    => $keyData[$format['mainIdKey']],
            'company_id' => '',//$keyData[$format['companyIdKey']],
            'message'    => $this->parseMessage($format['message'], $keyData),
        ];
    }

    /**
     * 储存操作日志
     *
     * @param       $adminUserId
     * @param array $rawLog
     */
    protected function storeLog($adminUserId, array $rawLog)
    {
        $addData = array_merge($rawLog, [
            'admin_user_id' => $adminUserId,
            'created_at'    => new Carbon(),
        ]);

        $log = $this->adminUserActionLogRepository->create($addData);
    }

    /**
     * 对比旧数据和新数据，得出被改变的部分
     *
     * @param array $old
     * @param array $new
     * @return array
     */
    protected function onlyDifferences($old, $new)
    {
        $changed = [];

        $oldKeys = array_keys($old);
        $newKeys = array_keys($new);

        $insKeys  = array_intersect($oldKeys, $newKeys);
        $diffKeys = array_merge(array_diff($oldKeys, $insKeys), array_diff($newKeys, $insKeys));

        //首先处理在新旧数据中不是共同拥有的数据项
        foreach ($diffKeys as $key) {
            if (isset($old[$key])) {
                $changed[$key] = [
                    $old[$key],
                    null //代表未提供
                ];
            } else {
                $changed[$key] = [
                    null, //代表未提供
                    $new[$key]
                ];
            }
        }

        //然后处理新旧数据中发生变化的共同拥有的数据项
        foreach ($insKeys as $key) {
            if ($new[$key] != $old[$key]) {
                $changed[$key] = [
                    $old[$key],
                    $new[$key]
                ];
            }
        }

        return $changed;
    }

    /**
     * 渲染得到日志信息
     *
     * @param       $messageTemplate
     * @param array $keyData
     * @return string
     */
    protected function parseMessage($messageTemplate, $keyData)
    {
        $callback = function ($matches) use (&$keyData) {
            $key = trim($matches[1], '{}');

            if ( ! isset($keyData[$key])) {
                throw new BusinessException("日志信息中需要的数据“{$key}”未提供");
            }

            return $keyData[$key];
        };

        $message = preg_replace_callback(
            '/({[a-zA-Z0-9-_]+?})/',
            $callback,
            $messageTemplate
        );

        return $message;
    }

    /**
     * 将数据项发生的变动转为描述性文字（字符串）
     *
     * @param array $differences
     * @param array $terms
     * @return array
     */
    protected function parseChangeLog($differences, array $terms)
    {
        $changes = [];

        foreach ($differences as $key => $values) {
            $name = isset($terms[$key]) ? $terms[$key] : $key;
            list($oldValue, $newValue) = $values;

            //暂时只处理非数组类型
            if ( ! is_array($oldValue) && ! is_array($newValue)) {
                $oldValue = $oldValue ?: ' - ';
                $newValue = $newValue ?: ' - ';

                $changes[] = "{$name}：{$oldValue}→{$newValue}";
            }
        }

        return implode('，', $changes);
    }

    /**
     * 检查关键数据是否提供，如未提供则抛出异常
     *
     * @param array $mustHaveKeys
     * @param array $keyData
     * @throws BusinessException
     */
    protected function keyDataMustExists(array &$mustHaveKeys, &$keyData)
    {
        foreach ($mustHaveKeys as $key) {
            if ( ! isset($keyData[$key])) {
                throw new BusinessException("日志中所需要的关键数据“{$key}”没有提供");
            }
        }
    }

    /**
     * @param $formatName
     * @return mixed
     * @throws BusinessException
     */
    protected function getFormat($formatName)
    {
        if ( ! isset($this->formats[$formatName])) {
            throw new BusinessException('日志记录格式不存在');
        }

        return $this->formats[$formatName];
    }

}