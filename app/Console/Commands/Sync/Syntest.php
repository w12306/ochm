<?php

namespace App\Console\Commands\Sync;

use Carbon\Carbon;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
Use GuzzleHttp\Exception\ClientException;

/**定时同步标列表到本地
 * @package App\Console\Commands
 */
class Syntest extends Command
{
    private $totalPageCount;
    private $counter        = 1;
    private $concurrency    = 7;  // 同时并发抓取
    protected $appid = "9bf56c0fe51b4cb5b65faf21e6fc7fe5";

    private $users =[1000011597, 1000011613, 42890815, 1000011631,1000011628, 1000010782, 42807637,1000010718,1000010720,1000010719];
    public  $toubiaourl='http://gw.open.ppdai.com/invest/BidService/Bidding';
    protected $appPrivateKey = 'MIICXAIBAAKBgQCOuh/bmpiQNNo/+KL7Sw0jvOaIg5eLRg1Ul+ktnXPuhCuXg6xTVzyzt5q9io/te/L0Uoi5ZrrXUjOJCPrbuaqHdi33s0Bdr+QFhJb5wvT++vK/YtO+qU7nB766DOCSjNneDtUDNR0QTlKTmLctU+Z5T4jU9QAGbVTOQYimvU23pQIDAQABAoGAcD35Hcd3ITkfRd2vtnWwMKG2njb2b4W4qAULUF8Zs8JnUbEwTR4205KZc2PLilTGnNNnSH58gybW9naEaVWav9IBLiqH3FVwVYpRbVFenixZ1xQRwLG5X8wxSL78CEMpSQagnSuxxRfgfbs5B+HiT7g9+XUkHmh4GeGNCBVL7cECQQD+OMmU8s5yfio4iEqrST/FuS0Kf5jcA8aVXR57755fktl1PNSrR/oAYAmfmtvFx1FClgGqlnN6JplYkUm/k/+RAkEAj7mxc0PqAJysfR9mbLQE+J259AdVGzjeuWA1DvzeYptIurT/PC1E8ooqvrp2AlxxUVUQ/xzy2NejWwGyWCbU1QJABfVOxC7sp8JNOwX2ugz9caTlaVmUO5PbE6UbEcyL6bGHZzgFw/3r3hZiI1t9yyTgwq7BWz4rdrFw5qKBF/xgAQJAf3bGI12VyBkq+ITCh6FXz03CvuNJ3SyginMCW1pzt6vT4pHm0m2rehaDNkeTpSBq9yCkRDxeO2Vy4KEuk/NMSQJBAJyMMzduCdLA3+LpNs0dG77uQqF2tijWWMhEKuCu8mQLL6/MsuRVL8LB5DLY9le8kcV2Z+NGnPCsDBOyj579QFk=';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '定时同步标列表到本地';

    /**
     * MakeAccount constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param SyncApiDataService $sdk
     */
    public function handle()
    {
        $workers = [];
        $worker_num = 3;//创建的进程数

        for($i=0;$i<$worker_num ; $i++){
            $process = new swoole_process('process');
            $pid = $process->start();
            $workers[$pid] = $process;
        }

        foreach($workers as $process){
            //子进程也会包含此事件
            swoole_event_add($process->pipe, function ($pipe) use($process){
                $data = $process->read();
                echo "RECV: " . $data.PHP_EOL;
            });
        }

        function process(swoole_process $process){// 第一个处理
            $process->write($process->pid);
            echo $process->pid,"\t",$process->callback .PHP_EOL;
        }

        exit;
        
        $this->totalPageCount = count($this->users);

        $client = new Client();

        $requests = function ($total) use ($client) {
            foreach ($this->users as $key => $user) {
                $uri = $this->toubiaourl;

                $request_data = '{
                  "ListingId": ' .$user . ', 
                  "Amount": 55
                }';

                $access_token='908ec618-b0ce-4c43-9689-18d594b5c2d8';
                $currentTime = Carbon::now();
                $timestamp=Carbon::createFromTimestamp($currentTime->timestamp-8*60*60)->toDateTimeString();

                $appPrivateKey = chunk_split($this->appPrivateKey, 64, "\n");
                $appPrivateKey = "-----BEGIN RSA PRIVATE KEY-----\n$appPrivateKey-----END RSA PRIVATE KEY-----\n";
                openssl_sign($this->appid . $timestamp, $Sign_request, $appPrivateKey);
                $Sign_request = base64_encode($Sign_request);
                openssl_sign($request_data, $Sign, $appPrivateKey);
                $Sign = base64_encode($Sign);
                $header = array();

                $header ['Content-Type'] = 'application/json;charset=UTF-8';
                $header ['X-PPD-TIMESTAMP'] = $timestamp;
                $header ['X-PPD-TIMESTAMP-SIGN'] =  $Sign_request;
                $header ['X-PPD-APPID'] = $this->appid;
                $header ['X-PPD-SIGN'] = $Sign;

                if ($access_token != null) {
                    $header ['X-PPD-ACCESSTOKEN'] =  $access_token;
                }
                $request = new Request('POST',$uri,$header);
                yield function() use ($client, $uri,$request) {
                    return $client->sendAsync($request);
                };
            }
        };

        $pool = new Pool($client, $requests($this->totalPageCount), [
            'concurrency' => $this->concurrency,
            'fulfilled'   => function ($response, $index){

                $res = json_decode($response->getBody()->getContents(),true);

                $this->info("请求第 $index 个请求，用户 " . $this->users[$index] );

                $this->countedAndCheckEnded($res);
            },
            'rejected' => function ($reason, $index){
                $this->error("rejected" );
                $this->error("rejected reason: " . $reason );
                //$this->countedAndCheckEnded();
            },
        ]);

        // 开始发送请求
        $promise = $pool->promise();
        $promise->wait();

    }

    public function countedAndCheckEnded($res)
    {
        var_dump($res);
    }

}
