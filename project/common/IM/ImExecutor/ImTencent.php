<?php
namespace common\service\IM\ImExecutor;

use common\service\IM\exception\InstantMsgingException;
use common\service\IM\ImVendor\tencent\TLSSigAPIv2;
use common\service\IM\manage\tencent\FriendsManage;
use common\service\IM\manage\tencent\MessageManage;
use common\service\IM\manage\tencent\ProfileManage;
use common\service\IM\manage\tencent\UserAccountManage;
use common\service\IM\manage\tencent\UserStateManage;

class ImTencent extends IM
{
    //账号 appId
    private $appId;

    //密钥
    private $secretKey;

    protected function setAppId($appId)
    {
        $this->appId = $appId;
    }

    protected function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;
    }

    /**
     * 生成 userSig
     * @return string
     * @throws \Exception
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/21
     * @Time: 16:54
     */
    protected function createUserSig($missionData)
    {
//        $this->checkCreateUserSigData($missionData);
        $tlsSigApi = new TLSSigAPIv2($this->appId,$this->secretKey);
        if(isset($missionData['expire']) && (int)$missionData['expire']>0)
        {
            return $tlsSigApi->genSig($missionData['userId'],(int)$missionData['expire']);
        }
        return $tlsSigApi->genSig($missionData['userId']);
    }

    /**
     * 生成userSig方法参数校验
     *
     * @param $data
     *
     * @throws InstantMsgingException
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/23
     * @Time: 10:42
     */
    private function checkCreateUserSigData($data)
    {
        if(!isset($data['userId']) && !empty($data['userId']))
        {
            throw new InstantMsgingException('userId参数必填且应为有效的用户ID');
        }
    }

    /**
     * 用户批量导入
     *
     * @param $missionData
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/23
     * @Time: 21:03
     */
    protected function importUserMulti($missionData)
    {
        $manager = new UserAccountManage();
        $importData = [
            'Accounts' => $missionData['userIds']
        ];
        return $manager->importMulti($importData,function ($managerId){
            $managerSig = $this->createUserSig(['userId'=>$managerId]);
            return [
                'appId' => $this->appId,
                'userSig' => $managerSig
            ];
        });
    }

    /**
     * 用户单条导入
     *
     * @param $missionData
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/23
     * @Time: 16:55
     */
    protected function importUserOne($missionData)
    {
        $manager = new UserAccountManage();
        $importData = [
            'Identifier' => (string)$missionData['userInfo']['userId'],
            'Nick' => $missionData['userInfo']['userName'],
            'FaceUrl' => STATIC_URL.$missionData['userInfo']['userHeadImg']
        ];
        return $manager->importOne($importData,function ($managerId){
            $managerSig = $this->createUserSig(['userId'=>$managerId]);
            return [
                'appId' => $this->appId,
                'userSig' => $managerSig
            ];
        });
    }

    /**
     * 账号查询
     *
     * @param $missionData
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/23
     * @Time: 20:31
     */
    protected function accountCheck($missionData)
    {
        $manager = new UserAccountManage();
        $checkData = [
            'CheckItem' =>$missionData['userIds']
        ];
        return $manager->checkUser($checkData,function($managerId){
            $managerSig = $this->createUserSig(['userId'=>$managerId]);
            return [
                'appId' => $this->appId,
                'userSig' => $managerSig
            ];
        });
    }

    /**
     * 账号删除
     *
     * @param $missionData
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/23
     * @Time: 21:08
     */
    protected function accountDelete($missionData)
    {
        $manager = new UserAccountManage();
        $checkData = [
            'DeleteItem' =>$missionData['userIds']
        ];
        return $manager->deleteUser($checkData,function($managerId){
            $managerSig = $this->createUserSig(['userId'=>$managerId]);
            return [
                'appId' => $this->appId,
                'userSig' => $managerSig
            ];
        });
    }

    /**
     * 强制用户登录状态失效
     *
     * @param $missionData
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/23
     * @Time: 21:37
     */
    protected function accountFailure($missionData)
    {
        $manager = new UserAccountManage();
        $failureData = [
            'Identifier' => $missionData['userId']
        ];
        return $manager->failureUser($failureData,function ($managerId){
            $managerSig = $this->createUserSig(['userId'=>$managerId]);
            return [
                'appId' => $this->appId,
                'userSig' => $managerSig
            ];
        });
    }

    /**
     * 用户在线状态查询
     *
     * @param $missionData
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/24
     * @Time: 14:58
     */
    protected function checkUserState($missionData)
    {
        $manager = new UserStateManage();
        $checkStateData = [
            'To_Account' => $missionData['userIds']
        ];
        return $manager->checkState($checkStateData,function ($managerId){
            $managerSig = $this->createUserSig(['userId'=>$managerId]);
            return [
                'appId' => $this->appId,
                'userSig' => $managerSig
            ];
        });
    }

    /**
     * 单聊发送消息
     *
     * @param $missionData
     *
     * @return array
     * @throws InstantMsgingException
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/25
     * @Time: 13:39
     */
    protected function msgSend($missionData)
    {
        $manager = new MessageManage();
        return $manager->send($missionData,function ($managerId){
            $managerSig = $this->createUserSig(['userId'=>$managerId]);
            return [
                'appId' => $this->appId,
                'userSig' => $managerSig
            ];
        });
    }

    /**
     * 单聊消息撤回
     *
     * @param $missionData
     *
     * @return array|mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/25
     * @Time: 14:03
     */
    protected function msgWithdraw($missionData)
    {
        $manager = new MessageManage();
        return $manager->withdraw($missionData,function ($managerId){
            $managerSig = $this->createUserSig(['userId'=>$managerId]);
            return [
                'appId' => $this->appId,
                'userSig' => $managerSig
            ];
        });
    }

    /**
     * 导入单聊消息
     *
     * @param $missionData
     *
     * @return array|mixed
     * @throws InstantMsgingException
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/25
     * @Time: 15:46
     */
    protected function msgImport($missionData)
    {
        $manager = new MessageManage();
        return $manager->import($missionData,function ($managerId){
            $managerSig = $this->createUserSig(['userId'=>$managerId]);
            return [
                'appId' => $this->appId,
                'userSig' => $managerSig
            ];
        });
    }

    /**
     * 添加好友
     *
     * @param $missionData
     *
     * @return array|mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/26
     * @Time: 17:33
     */
    protected function addFriends($missionData)
    {
        $manager = new FriendsManage();
        return $manager->add($missionData,function ($managerId){
            $managerSig = $this->createUserSig(['userId'=>$managerId]);
            return [
                'appId' => $this->appId,
                'userSig' => $managerSig
            ];
        });
    }

    /**
     * 校验好友关系
     *
     * @param $missionData
     *
     * @return array|mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/26
     * @Time: 19:50
     */
    protected function checkFriends($missionData)
    {
        $manager = new FriendsManage();
        return $manager->check($missionData,function ($managerId){
            $managerSig = $this->createUserSig(['userId'=>$managerId]);
            return [
                'appId' => $this->appId,
                'userSig' => $managerSig
            ];
        });
    }

    /**
     * 导入好友
     *
     * @param $missionData
     *
     * @return array|mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/27
     * @Time: 16:27
     */
    protected function importFriends($missionData)
    {
        $manager = new FriendsManage();
        return $manager->import($missionData,function ($managerId){
            $managerSig = $this->createUserSig(['userId'=>$managerId]);
            return [
                'appId' => $this->appId,
                'userSig' => $managerSig
            ];
        });
    }

    /**
     * 更新好友
     *
     * @param $missionData
     *
     * @return array|mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/28
     * @Time: 15:25
     */
    protected function updateFriends($missionData)
    {
        $manager = new FriendsManage();
        return $manager->update($missionData,function ($managerId){
            $managerSig = $this->createUserSig(['userId'=>$managerId]);
            return [
                'appId' => $this->appId,
                'userSig' => $managerSig
            ];
        });
    }

    /**
     * 删除好友
     *
     * @param $missionData
     *
     * @return array|mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/28
     * @Time: 15:34
     */
    protected function deleteFriends($missionData)
    {
        $manager = new FriendsManage();
        return $manager->delete($missionData,function ($managerId){
            $managerSig = $this->createUserSig(['userId'=>$managerId]);
            return [
                'appId' => $this->appId,
                'userSig' => $managerSig
            ];
        });
    }

    /**
     * 删除全部好友
     *
     * @param $missionData
     *
     * @return array|mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/28
     * @Time: 15:59
     */
    protected function deleteFriendsAll($missionData)
    {
        $manager = new FriendsManage();
        return $manager->deleteAll($missionData,function ($managerId){
            $managerSig = $this->createUserSig(['userId'=>$managerId]);
            return [
                'appId' => $this->appId,
                'userSig' => $managerSig
            ];
        });
    }

    /**
     * 拉取好友
     *
     * @param $missionData
     *
     * @return array|mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/28
     * @Time: 16:31
     */
    protected function pullFriends($missionData)
    {
        $manager = new FriendsManage();
        return $manager->pull($missionData,function ($managerId){
            $managerSig = $this->createUserSig(['userId'=>$managerId]);
            return [
                'appId' => $this->appId,
                'userSig' => $managerSig
            ];
        });
    }

    /**
     * 添加黑名单
     *
     * @param $missionData
     *
     * @return array|mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/28
     * @Time: 17:35
     */
    protected function blackListAdd($missionData)
    {
        $manager = new FriendsManage();
        return $manager->blackListAdd($missionData,function ($managerId){
            $managerSig = $this->createUserSig(['userId'=>$managerId]);
            return [
                'appId' => $this->appId,
                'userSig' => $managerSig
            ];
        });
    }

    /**
     * 删除黑名单
     *
     * @param $missionData
     *
     * @return array|mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/28
     * @Time: 17:45
     */
    protected function blackListDelete($missionData)
    {
        $manager = new FriendsManage();
        return $manager->blackListDelete($missionData,function ($managerId){
            $managerSig = $this->createUserSig(['userId'=>$managerId]);
            return [
                'appId' => $this->appId,
                'userSig' => $managerSig
            ];
        });
    }

    /**
     * 校验黑名单
     *
     * @param $missionData
     *
     * @return array|mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/28
     * @Time: 18:06
     */
    protected function blackListCheck($missionData)
    {
        $manager = new FriendsManage();
        return $manager->blackListCheck($missionData,function ($managerId){
            $managerSig = $this->createUserSig(['userId'=>$managerId]);
            return [
                'appId' => $this->appId,
                'userSig' => $managerSig
            ];
        });
    }

    /**
     * 黑名单列表
     *
     * @param $missionData
     *
     * @return array|mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/28
     * @Time: 20:42
     */
    protected function blackListPull($missionData)
    {
        $manager = new FriendsManage();
        return $manager->blackListPull($missionData,function ($managerId){
            $managerSig = $this->createUserSig(['userId'=>$managerId]);
            return [
                'appId' => $this->appId,
                'userSig' => $managerSig
            ];
        });
    }

    /**
     * 拉取用户资料
     *
     * @param $missionData
     *
     * @return array|mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/30
     * @Time: 11:39
     */
    protected function pullProfile($missionData)
    {
        $manager = new ProfileManage();
        return $manager->pull($missionData,function ($managerId){
            $managerSig = $this->createUserSig(['userId'=>$managerId]);
            return [
                'appId' => $this->appId,
                'userSig' => $managerSig
            ];
        });
    }

    /**
     * 修改用户资料
     *
     * @param $missionData
     *
     * @return array|mixed
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/30
     * @Time: 14:42
     */
    protected function setProfile($missionData)
    {
        $manager = new ProfileManage();
        return $manager->set($missionData,function ($managerId){
            $managerSig = $this->createUserSig(['userId'=>$managerId]);
            return [
                'appId' => $this->appId,
                'userSig' => $managerSig
            ];
        });
    }
}