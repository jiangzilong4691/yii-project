<?php
namespace common\service\IMService\tencent;

use common\entity\im\ImAccountImportLogEntity;
use common\redis\OldCacheRedis;
use common\service\IM\ImExecutor\IM;
use common\service\IM\InstantMessaging;
use common\service\IMService\ImService;

/**
 * 用户账号管理类
 * Class ImTencentAccountService
 * @package common\service\IMService\tencent
 */
class ImTencentAccountService extends ImService
{

    public static $regisSecret = 'qtRZZgiEfdlRZz1l';

    /**
     * 获取用户 userSig
     *
     * @param   array $userInfo     用户信息
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/21
     * @Time: 19:03
     */
    public function getUserSig($userInfo)
    {
        $userId = $userInfo['userId'];
        $userSigInfo = OldCacheRedis::self()->getImUserSig($userId);
        if(empty($userSigInfo))
        {
            $command = [
                'mission' => IM::TENCENT_MISSION_GET_USERSIG,
                'data' => [
                    'userId' => $userId,
                    'expire' => self::USERSIG_EXPIRE_TIME
                ]
            ];
            $sigInfo = InstantMessaging::server(InstantMessaging::IM_OBJECT_TENCENT)
                ->execute($command);
            if($sigInfo['status'])
            {
                $userSigInfo = [
                    'userSig' => $sigInfo['data'],
                    'failure' => 0,
                    'errMsg'  => ''
                ];
                //缓存时间比密钥有效时间少60s，防止密钥失效后服务端未失效
                OldCacheRedis::self()->setImUserSig($userId,$userSigInfo,(self::USERSIG_EXPIRE_TIME-60));
            }
            else
            {
                return [];
            }
        }
        return $userSigInfo;
    }

    /**
     * 清除已失效用户密钥缓存：主要用于激活平台已禁使用IM的用户
     * 注：不要随意调用
     *
     * @param $userId
     *
     * @return bool|int
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/31
     * @Time: 10:43
     */
    public function delExpiredUserSig($userId)
    {
        return OldCacheRedis::self()->delUserExpiredSig($userId);
    }

    /**
     * 单条导入用户
     *
     * @param   array   $userInfo           用户信息
     * @param   boolean $importFailRetry    是否导入失败后重试
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/23
     * @Time: 17:26
     */
    public function accountImportUserOne($userInfo,$importFailRetry=false)
    {
        //导入失败计数
        static $failCount = 0;
        $command = [
            'mission' => IM::TENCENT_MISSION_IMPORT_USER_ONE,
            'data' => [
                'userInfo' => $userInfo
            ]
        ];
        $execResult = InstantMessaging::server(InstantMessaging::IM_OBJECT_TENCENT)
            ->execute($command);
        if($execResult['status'])
        {
            $importResult = $execResult['data'];
            if($importResult['code'] == '200')
            {
                $returnResult = ['code' => '200','desc' => '单条导入成功'];
            }
            else
            {
                if($importFailRetry)
                {
                    if($importResult['errCode'] == 40005)
                    {
                        // 40005 为昵称脏词过滤失败 昵称重编辑后导入重试
                        $failCount ++;
                        if($failCount > 1)
                        {
                            //默认 重试1次  重试失败后直接返回
                            return [
                                'code' => '201',
                                'desc'=> '单条导入失败:'.$importResult['msg'],
                                'detail'=>[
                                    'errCode' => $importResult['errCode'],
                                    'msg' => $importResult['msg']
                                ]
                            ];
                        }
                        $userInfo['userName'] = 'zhibo'.$userInfo['roomNum'];
                        return $this->accountImportUserOne($userInfo,$importFailRetry);
                    }
                }
                $returnResult = [
                    'code' => '201',
                    'desc'=> '单条导入失败:'.$importResult['msg'],
                    'detail'=>[
                        'errCode' => $importResult['errCode'],
                        'msg' => $importResult['msg']
                    ]
                ];
            }
        }
        else
        {
            $returnResult = ['code' =>'-1','desc' => $execResult['msg']];
        }
        return $returnResult;
    }

    /**
     * 多条批量导入
     *
     * @param array $userIds 用户userId集合 e.g. ['1497428','333','14587']
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/23
     * @Time: 17:31
     */
    public function accountImportUserMulti(array $userIds)
    {
        $userIds = array_map('strval',$userIds);
        $command = [
            'mission' => IM::TENCENT_MISSION_IMPORT_USER_MULTI,
            'data' => [
                'userIds' => $userIds
            ]
        ];
        $multiImportExecResult = InstantMessaging::server(InstantMessaging::IM_OBJECT_TENCENT)
            ->execute($command);
        if($multiImportExecResult['status'])
        {
            $multiImportResult = $multiImportExecResult['data'];
            if($multiImportResult['code'] == '200')
            {
                $returnResult = ['code' => '200','desc' => '批量导入成功'];
            }
            else if($multiImportResult['code'] == '201')
            {
                $returnResult = ['code' => '201','desc' => '部分导入成功，导入失败ID【'.explode(',',$multiImportResult['result']).'】'];
            }
            else
            {
                $returnResult = ['code' => '-1','desc' => $multiImportResult['msg']];
            }
        }
        else
        {
            $returnResult = ['code'=>'-1','desc'=> $multiImportExecResult['msg']];
        }
        return $returnResult;
    }

    /**
     * 查询用户
     *
     * @param array $userIds    要查询的用户ID集合 e.g. ['1497428','362654','159875']
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/23
     * @Time: 20:44
     */
    public function accountCheck(Array $userIds)
    {
        if(count($userIds) > 100)
        {
            return ['code' => '-1','desc'=>'单次查询请求最多支持100个账号'];
        }
        $userIds = array_map(function ($userId){
            return ['UserID'=>(string)$userId];
        },$userIds);
        $command = [
            'mission' => IM::TENCENT_MISSION_ACCOUNT_CHECK,
            'data' => [
                'userIds' => $userIds
            ]
        ];
        $execResult = InstantMessaging::server(InstantMessaging::IM_OBJECT_TENCENT)
            ->execute($command);
        if($execResult['status'])
        {
            $checkResult = $execResult['data'];
            if($checkResult['code'] == '200')
            {
                $returnResult = ['code'=>'200','desc'=>$checkResult['result']];
            }
            else
            {
                $returnResult = ['code' => '201','desc' => $checkResult['msg']];
            }
        }
        else
        {
            $returnResult = ['code'=>'-1','desc'=> $execResult['msg']];
        }
        return $returnResult;
    }

    /**
     * 批量删除用户：（注：帐号删除时，该用户的关系链、资料等数据也会被删除。）
     * 帐号删除后，该用户的数据将无法恢复，请谨慎使用该接口
     *
     * @param array $userIds
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/23
     * @Time: 21:11
     */
    public function accountDelete(Array $userIds)
    {
        return [
            'code' => '200',
            'desc' => '危险操作，暂时屏蔽'
        ];
        if(count($userIds) > 100)
        {
            return ['code'=>'-1','desc'=>'单次删除请求最多支持100个账号'];
        }
        $userIds = array_map(function ($userId){
            return ['UserID'=>(string)$userId];
        },$userIds);
        $command = [
            'mission' => IM::TENCENT_MISSION_ACCOUNT_DELETE,
            'data' => [
                'userIds' => $userIds
            ]
        ];
        $execResult = InstantMessaging::server(InstantMessaging::IM_OBJECT_TENCENT)
            ->execute($command);
        if($execResult['status'])
        {
            $deleteResult = $execResult['data'];
            if($deleteResult['code'] == '200')
            {
                $returnResult = ['code'=>'200','desc'=>$deleteResult['result']];
            }
            else
            {
                $returnResult = ['code' => '201','desc' => $deleteResult['msg']];
            }
        }
        else
        {
            $returnResult = ['code'=>'-1','desc'=> $execResult['msg']];
        }
        return $returnResult;
    }

    /**
     * 强制用户状态失效
     *
     * @param   int     $userId     失效用户ID e.g. 1497428
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/23
     * @Time: 21:42
     */
    public function accountFailure($userId)
    {
        $command = [
            'mission' => IM::TENCENT_MISSION_ACCOUNT_FAILURE,
            'data' => [
                'userId' => (string)$userId
            ]
        ];
        $execResult = InstantMessaging::server(InstantMessaging::IM_OBJECT_TENCENT)
            ->execute($command);
        if($execResult['status'])
        {
            $failureResult = $execResult['data'];
            if($failureResult['code'] == '200')
            {
                //失效用户缓存
                OldCacheRedis::self()->updateUserSigFailureStatus($userId);
                $returnResult = ['code'=>'200','desc'=>'操作成功'];
            }
            else
            {
                $returnResult = ['code' => '201','desc' => $failureResult['msg']];
            }
        }
        else
        {
            $returnResult = ['code'=>'-1','desc'=> $execResult['msg']];
        }
        return $returnResult;
    }

    /**
     * 导入账号并设置平台用户资料
     *
     * @param   array       $userInfo   平台用户信息
     * @param   boolean     $addLog     是否加入导入日志
     *
     * @return array
     * @throws
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2020/3/23
     * @Time: 13:33
     */
    public function importAndSetProfile($userInfo,$addLog=false)
    {
        $importResult = $this->accountImportUserOne($userInfo,true);
        if($importResult['code'] == '200')
        {
            $toSetProfileInfo = [
                'customRoom' => (string)$userInfo['roomNum'],
            ];
            $setProfileResult = ImTencentProfileService::self()->setProfile($userInfo['userId'],$toSetProfileInfo);
            if($setProfileResult['code'] == '200')
            {
                $result = ['code' => '200','desc' => '账号正常导入，资料设置正常'];
            }
            else
            {
                $result = ['code' => '201','desc' => '账号正常导入，资料设置异常：'.$setProfileResult['desc']];
            }
        }
        else
        {
            $result = ['code' => '-1','desc'=>'账号导入异常：'.$importResult['desc']];
        }
        if($addLog)
        {
            $statusMap = [
                '-1' => ImAccountImportLogEntity::IMPORT_STATUS_FAIL ,
                '201' => ImAccountImportLogEntity::IMPORT_STATUS_UPDATE_FAIL ,
                '200' => ImAccountImportLogEntity::IMPORT_STATUS_SUCCESS
            ];
            ImAccountImportLogEntity::model()->addLog(
                (int)$userInfo['userId'],
                ImAccountImportLogEntity::SERVICE_OBJECT_TENCENT,
                isset($statusMap[$result['code']])?$statusMap[$result['code']] : 0,
                $result['code'] == '200' ? '': $result['desc']
            );
        }
        return $result;
    }

    /**
     * 注册行为 导入注册用户
     *
     * @param $userId
     * @param $userName
     * @param $roomNum
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2020/5/15
     * @Time: 18:27
     */
    public function registerAccountImport($userId,$userName,$roomNum)
    {
        try{
            $ImUserImportInfo = [
                'userId' => $userId,
                'userName' => $userName,
                'userHeadImg' => '/images/video_user.png',  //新注册用户 使用默认头像
                'roomNum' => $roomNum
            ];
            ImTencentAccountService::self()->importAndSetProfile($ImUserImportInfo,true);
        }catch (\Exception $e){

        }
    }
}