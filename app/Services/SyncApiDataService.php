<?php
namespace App\Services;

use App\Models\FeatureLoand;
use App\Repositories\AccountRepository;
use App\Repositories\FeatureRepository;
use App\Repositories\TousucsseRepository;
use App\Services\SDK\PaiPaiSdk;
use Carbon\Carbon;
use GuzzleHttp\Client;
use DB;

/**
 * 用于刷新基础数据
 * Class ExecutiveInfo
 *
 * @package App\Services\Admin
 */
class SyncApiDataService
{
    protected $sdk;
    protected $accountRepository;
    protected $tousucsseRepository;
    protected $featureRepository;

    public function __construct(
        PaiPaiSdk $paiPaiSdk,
        AccountRepository $accountRepository,
        TousucsseRepository $tousucsseRepository,
        FeatureRepository $featureRepository
    ) {
        $this->sdk = $paiPaiSdk;
        $this->accountRepository = $accountRepository;
        $this->tousucsseRepository = $tousucsseRepository;
        $this->featureRepository=$featureRepository;
    }

    //获得投标列表
    public function syncLoanList()
    {
        $request = '{
          "PageIndex": 1,
          "StartDateTime": "2017-06-16 00:00:00.000"
        }';
        $result = $this->sdk->send($this->sdk->loandlisturl, $request, null);
        $AA_loans = [];
        foreach ($result['LoanInfos'] as $key => $loan) {
            if ($loan['CreditCode'] == 'AA' ) {
                $AA_loans[] = $loan;
            }
        }
        return $AA_loans;
    }

    /**
     * 获得投标人的列表
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function getUserList(){
        return $this->accountRepository->applyWhere([
            ['status','=',1]
        ])->all()->map(
            function ($user) {
                return $user->access_token;
            }
        );

    }


    /**
     * 投标
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function putLoan()
    {
        $users = $this->accountRepository->all()->toArray();
        $users = $users[0];
        $biao = $this->set1();


        if (empty($biao)) {
            echo "无记录";
        }

        foreach ($biao as $k => $v) {
            $request = '{
              "ListingId": ' . $v['ListingId'] . ', 
              "Amount": 55
            }';
            $begin_time = Carbon::now()->toDateTimeString();
            $result = $this->sdk->send($this->sdk->toubiaourl, $request, $users['access_token']);
            $end_time= Carbon::now()->toDateTimeString();
            $type='';
            if($result['Result']==0){
                $type='ok';
            }
                $aut=[
                    'ListingId'=>$v['ListingId'],
                    'CreditCode'=>$v['CreditCode'],
                    'Rate'=>$v['Rate'],
                    'Months'=>$v['Months'],
                    'Title'=>$v['Title'],
                    'Amount'=>$v['Amount'],
                    'message'=>isset($result['ResultMessage'])?$result['ResultMessage']:"",
                    'result'=>isset($result['Result'])?$result['Result']:"",
                    'type'=>$type,
                    'begin_time'=>$begin_time,
                    'end_time'=>$end_time,
                ];

                $this->tousucsseRepository->create($aut);
        }
    }

    public function whileToSet1(){
        $i=1;
        $t=true;

        while ($t) {
            if($i==2000){
                $t=false;
            }
            $this->set1();
            sleep(2);
            echo $i;
            $i++;
        }

    }
    public function whileToSet2(){
        $i=1;
        $t=true;

        while ($t) {
            if($i==7000){
                $t=false;
            }
            $this->set2();

            sleep(2);
            echo $i;
            $i++;
        }

    }

    public function whileToSet3(){
        $i=1;
        $t=true;

        while ($t) {
            if($i==2000){
                $t=false;
            }
            $this->featureLoandDo();
            $this->set3();
            ///sleep(1);
            echo $i;
            $i++;
        }

    }

    /**
     *  AA  13    12-18
     * @author YangWei<yangwei@foxmail.com>
     *
     * @return array
     */
    public function set1()
    {
        $currentTime = Carbon::now();
        $StartDateTime = Carbon::createFromTimestamp($currentTime->timestamp - 10 * 60)->toDateTimeString();
        $request = '{
          "PageIndex": 1,
          "StartDateTime": "' . $StartDateTime . '"
        }';
        $result = $this->sdk->send($this->sdk->loandlisturl, $request, null);
        $AA_loans = [];
        foreach ($result['LoanInfos'] as $key => $loan) {
            if ($loan['CreditCode'] == 'AA' && $loan['Rate'] >=13 && $loan['Months'] >=12 && $loan['Months'] <=18) {
                $AA_loans[] = $loan;
            }
        }


        //$users = $this->accountRepository->all()->toArray();
        //$users = $users[0];
        $access_token='908ec618-b0ce-4c43-9689-18d594b5c2d8';
        $biao = $AA_loans;


        if (empty($biao)) {
            echo "无记录";return '';
        }
        
       // $this->sdk->sendPool($biao,55 , $access_token);



        foreach ($biao as $k => $v) {
            $request = '{
              "ListingId": ' . $v['ListingId'] . ', 
              "Amount": 55
            }';
            $begin_time = Carbon::now()->toDateTimeString();
            $result = $this->sdk->send($this->sdk->toubiaourl, $request, $access_token);
            $end_time= Carbon::now()->toDateTimeString();
            $type='';
            if($result['Result']==0){
                $type='ok';
            }
            $aut=[
                'ListingId'=>$v['ListingId'],
                'CreditCode'=>$v['CreditCode'],
                'Rate'=>$v['Rate'],
                'Months'=>$v['Months'],
                'Title'=>$v['Title'],
                'Amount'=>$v['Amount'],
                'message'=>isset($result['ResultMessage'])?$result['ResultMessage']:"",
                'result'=>isset($result['Result'])?$result['Result']:"",
                'type'=>$type,
                'begin_time'=>$begin_time,
                'end_time'=>$end_time,
            ];

            $this->tousucsseRepository->create($aut);
        }



        return '';
    }

    /**
     *AA  大于13
     * @author YangWei<yangwei@foxmail.com>
     *
     * @return array
     */
    public function set2()
    {
        $currentTime = Carbon::now();
        $StartDateTime = Carbon::createFromTimestamp($currentTime->timestamp - 10 * 60)->toDateTimeString();
        $request = '{
          "PageIndex": 1,
          "StartDateTime": "' . $StartDateTime . '"
        }';
        $result = $this->sdk->send($this->sdk->loandlisturl, $request, null);
        $AA_loans = [];
        foreach ($result['LoanInfos'] as $key => $loan) {
            if ($loan['CreditCode'] == 'AA' && $loan['Rate'] >=13 && $loan['Months'] >=12 && $loan['Months'] <=18) {
                $AA_loans[] = $loan;
            }
        }

        //$users = $this->accountRepository->all()->toArray();
        //$users = $users[0];
        $access_token='908ec618-b0ce-4c43-9689-18d594b5c2d8';
        $biao = $AA_loans;

        if (empty($biao)) {
            echo "无记录";return '';
        }

        $this->sdk->sendPool($biao,55 , $access_token);
        return ;
    }

    /**
     *AA  大于13
     * @author YangWei<yangwei@foxmail.com>
     *
     * @return array
     */
    public function set3()
    {
        $currentTime = Carbon::now();
        $StartDateTime = Carbon::createFromTimestamp($currentTime->timestamp - 10 * 60)->toDateTimeString();
        $request = '{
          "PageIndex": 1,
          "StartDateTime": "' . $StartDateTime . '"
        }';
        $result = $this->sdk->send($this->sdk->loandlisturl, $request, null);
        $AA_loans = [];
        if(!empty($result['LoanInfos'])){
            foreach ($result['LoanInfos'] as $key => $loan) {
                if ($loan['CreditCode'] == 'AA' && $loan['Rate'] >=13 && $loan['Months'] >=6 && $loan['Months'] <=24) {
                    $AA_loans[] = $loan;
                }
            }
        }

        $access_token='58a446b4-0ad1-4d4a-b555-4c3a6ed648ff';
        
        if (!empty($AA_loans)) {
            echo "正在处理".count($AA_loans).'个标单;';
        }else{
            return ;
        }
        foreach ($AA_loans as $k => $v) {
            $this->sdk->syncSend($v['ListingId'], 115, $access_token,$v);//$v['ListingId']
        }


    }



    



    /**
     *AA  大于13
     * @author YangWei<yangwei@foxmail.com>
     *
     * @return array
     */
    public function featureLoandDo()
    {
        $data=$this->featureRepository->all()->toArray();

        $access_token='58a446b4-0ad1-4d4a-b555-4c3a6ed648ff';
        if(!empty($data)){
            foreach ($data as $k => $v) {
                $this->sdk->syncSend($v['ListingId'], 55, $access_token,$v);//$v['ListingId']
            }
        }
    }




    public function customerLoanList()
    {
        $currentTime = Carbon::now();
        $StartDateTime = Carbon::createFromTimestamp($currentTime->timestamp - 20 * 60)->toDateTimeString();
        $request = '{
          "PageIndex": 1,
          "StartDateTime": "' . $StartDateTime . '"
        }';
        $result = $this->sdk->send($this->sdk->loandlisturl, $request, null);
        $AA_loans = [];
        foreach ($result['LoanInfos'] as $key => $loan) {
            //if ($loan['CreditCode'] !== 'AA') {
             //   continue;
           // }
            if ($loan['CreditCode'] == 'AA' && $loan['Rate'] >13.5) {
                $AA_loans[] = $loan;
            }
        }
        // dd($AA_loans);
        return $AA_loans;
    }


    public function  refresh_token(){
        $users=$this->accountRepository->all()->toArray();
        
        foreach ($users as $key=>$v){
            $newdata=$this->sdk->refresh_token($v['open_id'], $v['refresh_token']);

            $attributes['open_id']=$v['open_id'];
            $attributes['access_token']=$newdata['AccessToken'];
            $attributes['refresh_token']=$newdata['RefreshToken'];
            $attributes['expires_in']=$newdata['ExpiresIn'];

            $currentTime = Carbon::now();
            $attributes['updated_at']=$currentTime->toDateTimeString();

            $this->accountRepository->createOrUpdate($attributes);
        }
    }
    //
}