<?php namespace App\Traits\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * 项目通用返回方法封装
 * 例如AJAX
 *
 * @author  AaronLiu <liukan0926@foxmail.com>
 * @package Common\Traits
 */
trait  CommonResponse
{

    /**
     * AJAX返回
     *
     * @param      $status
     * @param null $info
     * @param null $data
     * @param null $auto
     * @return \Illuminate\Http\JsonResponse
     */
    protected function ajaxResponse($status, $info = null, $data = null, $auto = null)
    {
        $responseJson = [
            'status' => $status,
            'info'   => ! is_null($info) ? $info : '',
            'data'   => ! is_null($data) ? $data : '',
        ];

        if ($auto) {
            $responseJson['auto'] = $auto;
        }

        return response()->json($responseJson);
    }

    /**
     * AJAX成功返回
     *
     * @param null $info
     * @param null $data
     * @param null $auto
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    protected function ajaxSuccess($info = null, $data = null, $auto = null)
    {
        return $this->ajaxResponse('success', $info, $data, $auto);
    }

    /**
     * AJAX失败返回
     *
     * @param null $info
     * @param null $data
     * @param null $auto
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    protected function ajaxError($info = null, $data = null, $auto = null)
    {
        return $this->ajaxResponse('error', $info, $data, $auto);
    }

}