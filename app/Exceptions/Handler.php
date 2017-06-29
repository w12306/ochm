<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use App\Exceptions\BusinessException;
use App\Exceptions\ValidationFailedException;
use App\Exceptions\Admin\UploadException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Traits\Controller\CommonResponse;
use Log;
use App\Exceptions\Admin\PermissionDeniedException;
use DB;

class Handler extends ExceptionHandler
{

    use CommonResponse;

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        HttpException::class,
        BusinessException::class,
        UploadException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception               $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        //如果是业务逻辑错误、权限问题等可以显示错误信息的异常，则返回给用户
        if (
            $e instanceof BusinessException ||
            $e instanceof PermissionDeniedException ||
            $e instanceof ValidationFailedException ||
            $e instanceof UploadException
        ) {
            return $this->renderShowableException($request, $e);
        }

        //如果是未找到数据错误则仅显示数据不存在
        if ($e instanceof ModelNotFoundException) {
            return $this->renderShowableException($request, new \Exception('对应数据不存在'));
        }

        //测试环境和线上环境打开自定义错误处理
        //忽略DEBUG开关
        if (app()->environment('test', 'production')) {
            return $this->customRender($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * 自定义错误处理
     *
     * @param           $request
     * @param Exception $e
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    protected function customRender($request, \Exception $e)
    {
        if ($this->isHttpException($e)) {
            return $this->renderHttpException($e);
        } else {
            //记录日志
            Log::emergency($e->getMessage(), [
                'trace' => $e->getTrace()
            ]);

            return response('Error 500', 500);
        }
    }

    /**
     * 可以显示给用户的错误信息
     * 支持AJAX JSON返回
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception               $e
     * @return \Illuminate\Http\Response
     */
    protected function renderShowableException($request, Exception $e)
    {
        if ($request->ajax()) {
            return $this->ajaxError($e->getMessage(), '', 'glance');
        } else {
            return response()->view('admin.common.error', [
                'message' => $e->getMessage()
            ]);
        }
    }
}
