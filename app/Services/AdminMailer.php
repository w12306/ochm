<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

class AdminMailer
{
    /**
     * @var array
     */
    protected $adminMailAddresses;

    /**
     * @var string
     */
    protected $mailNotifyAddress;

    /**
     * AdminMailer constructor.
     *
     * @param string $mailNotifyAddress
     * @param array  $adminMailAddresses
     */
    public function __construct()
    {
        $this->mailNotifyAddress   = $this->getNotifyFromAddress();
        $this->admindMailAddresses = $this->getAdminAddresses();
    }

    /**
     * @return string
     */
    protected function getNotifyFromAddress()
    {
        return env('MAIL_NOTIFY_ADDRESS');
    }

    /**
     * @return array
     */
    protected function getAdminAddresses()
    {
        return explode(',',env('ADMIN_MAIL_ADDRESSES'));
    }

    /**
     * @param $json
     * @return mixed
     */
    protected function formatJSON($json)
    {
        return $this->htmlEncoding(json_encode(
            $json,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        ));
    }

    /**
     * 发送错误通知邮件
     *
     * @param       $content
     * @param array $context
     * @param null  $headline
     */
    public function sendErrorNotify($content, $context = [], $headline = null)
    {
        $headline = $headline ?: '系统发现错误';

        $renderData = [
            'headline'   => $headline,
            'alertColor' => '#ff3918',
            'content'    => $content,
            'json'       => (! empty($context)) ? $this->formatJSON($context) : null,
        ];

        return $this->sendMail('emails.error-notify', $renderData, $headline);
    }

    /**
     * 发送错误通知邮件
     *
     * @param       $content
     * @param array $context
     * @param null  $headline
     */
    public function sendErrorIndex($content, $context = [], $headline = null)
    {
        $headline = $headline ?: '系统发现错误';

        $renderData = [
            'headline'   => $headline,
            'alertColor' => '#ff3918',
            'content'    => $content,
            'datas'       => (! empty($context)) ? $context : null,
        ];

        return $this->sendMail('emails.error-index', $renderData, $headline);
    }

    /**
     * 发送系统状态统计邮件
     *
     * @param null $headline
     */
    public function sendSystemStatus($headline = null)
    {
        $headline = $headline ?: '系统状态报告';

        $renderData = [
            'headline'   => $headline,
            'alertColor' => '#10abfc',
        ];

        return $this->sendMail('emails.system-status', $renderData, $headline);
    }

    /**
     * 发送邮件
     *
     * @param       $view
     * @param array $renderData
     * @param null  $title
     */
    protected function sendMail($view, array $renderData, $title = null)
    {
        $title = ($title ? "$title - " : '') . '业务管理后台(ABMP)系统邮件';

        $renderData = array_merge($renderData, [
            'title' => $title,
            'time'  => Carbon::now(),
        ]);

        $fromAddress = $this->getNotifyFromAddress();
        $toAddresses = $this->getAdminAddresses();
        
        if (empty($fromAddress) || empty($toAddresses)) {
            throw new \LogicException("没有配置发件地址或管理员收件地址，请检查");
        }
        Mail::send($view, $renderData, function (Message $message) use ($title, $fromAddress, $toAddresses) {
            $message->subject($title);
            $message->from($fromAddress, "业务管理后台(ABMP)系统通知");
            $message->to($toAddresses);
        });
    }

    /**
     * @param $content
     * @return mixed
     */
    protected function htmlEncoding($content)
    {
        $content = preg_replace('/ /', '&nbsp;', $content);
        $content = preg_replace("(\r\n|\n)", '<br>', $content);

        return $content;
    }
}