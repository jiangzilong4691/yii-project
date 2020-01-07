<?php


namespace common\service\IMService\tencent;


use common\service\IM\ImExecutor\IM;
use common\service\IM\InstantMessaging;
use common\service\IMService\ImService;

/**
 * 用户关系链管理类
 * Class ImTencentFriendsService
 * @package common\service\IMService\tencent
 */
class ImTencentFriendsService extends ImService
{

    /**
     * IM返回结果 好友关系信息映射
     * @var array
     */
    private static $friendsCheckResultTypeMap = [
        self::FRIENDS_CHECK_TYPE_SINGLE => [
            'CheckResult_Type_NoRelation' => 'fromAccount 的好友表中没有 toAccount，但无法确定 toAccount 的好友表中是否有 fromAccount',
            'CheckResult_Type_AWithB'     => 'fromAccount 的好友表中有 toAccount，但无法确定 toAccount 的好友表中是否有 fromAccount'
        ],
        self::FRIENDS_CHECK_TYPE_BOTH => [
            'CheckResult_Type_BothWay' => 'fromAccount 的好友表中有 toAccount，toAccount 的好友表中也有 fromAccount',
            'CheckResult_Type_AWithB'  => 'fromAccount 的好友表中有 toAccount，但 toAccount 的好友表中没有 fromAccount',
            'CheckResult_Type_BWithA'  => 'fromAccount 的好友表中没有 toAccount，但 toAccount 的好友表中有 fromAccount',
            'CheckResult_Type_NoRelation' => 'fromAccount 的好友表中没有 To_Account，toAccount 的好友表中也没有 From_Account'
        ]
    ];

    /**
     * IM返回结果 黑名单关系映射
     * @var array
     */
    private static $blackListCheckResultTypeMap = [
        self::FRIENDS_BLACK_LIST_CHECK_SINGLE => [
            'BlackCheckResult_Type_AWithB' => 'fromAccount 的黑名单中有 toAccount，但无法确定 toAccount 的黑名单中是否有 fromAccount',
            'BlackCheckResult_Type_NO'     => 'fromAccount 的黑名单中没有 toAccount，但无法确定 toAccount 的黑名单中是否有 fromAccount'
        ],
        self::FRIENDS_BLACK_LIST_CHECK_BOTH => [
            'BlackCheckResult_Type_BothWay' => 'fromAccount 的黑名单中有 toAccount，toAccount 的黑名单中也有 fromAccount',
            'BlackCheckResult_Type_AWithB'  => 'fromAccount 的黑名单中有 toAccount，但 toAccount 的黑名单中没有 fromAccount',
            'BlackCheckResult_Type_BWithA'  => 'fromAccount 的黑名单中没有 toAccount，但 toAccount 的黑名单中有 fromAccount',
            'BlackCheckResult_Type_NO'      => 'fromAccount 的黑名单中没有 toAccount，toAccount 的黑名单中也没有 fromAccount'
        ]
    ];

    /**
     * 添加好友：支持批量添加
     *
     * @param   string  $fromAccount    发起添加好友用户
     * @param   array   $friendsInfo    要添加的好友列表
     * [
     *    [
     *      'userId' => '1497428',      必填 平台用户ID
     *      'remark' => 'best friends', 选填 好友备注
     *      'groupName'  => '同学',      选填 分组名称
     *      'addWording' => '快加我好友' 选填 形成好友关系时的附言信息
     *     ],
     *    [
     *      'userId' => '333',
     *      'addWording' => '加你了呀'
     *     ]
     * ]
     * @param   int     $addType        添加类型 ：单向 | 双向 默认单向
     * @param   bool    $force          管理员强制加好友标记：true表示强制加好友，false表示常规加好友方式
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/26
     * @Time: 19:36
     */
    public function addFriends($fromAccount,Array $friendsInfo,$addType=ImService::FRIENDS_ADD_TYPE_SINGLE,$force=false)
    {
        $command = [
            'mission' => IM::TENCENT_MISSION_FRIENDS_ADD,
            'data' => [
                'fromAccount' => $fromAccount,
                'friendsInfo' => $friendsInfo,
                'addType'     => $addType,
                'forceAddFlags' => $force
            ]
        ];
        $execResult = InstantMessaging::server(InstantMessaging::IM_OBJECT_TENCENT)
            ->execute($command);
        if($execResult['status'])
        {
            $resultData = $execResult['data'];
            if($resultData['code'] == '200')
            {
                $returnResult = ['code' => '200','desc' => $resultData['result']];
            }
            else
            {
                $returnResult = ['code' => '201','desc' => $resultData['msg']];
            }
        }
        else
        {
            $returnResult = ['code' => '-1','desc'=>$execResult['msg']];
        }
        return $returnResult;
    }

    /**
     * 导入好友
     *
     * @param   string  $fromAccount    当前用户 e.g. '1497428'
     * @param   array   $friendsInfo    好友信息集合
     * e.g. [
               [
                    'userId' => '45678',        必填
                    'remark' => 'best friend',  选填
                    'remarkTime' => 0,          选填
                    'groupName'  => [           选填
                        '朋友',
                        '同事'
                    ],
                    'addWording' => '快加我好友', 选填
                    'addTime'    => 0,          选填
               ],
               [
                    'userId' => '555',
                    'remark' => 'hello my friend'
               ]
            ]
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/27
     * @Time: 16:28
     */
    public function importFriends($fromAccount,Array $friendsInfo)
    {
        $command = [
            'mission' => IM::TENCENT_MISSION_FRIENDS_IMPORT,
            'data' => [
                'fromAccount' => $fromAccount,
                'friendsInfo' => $friendsInfo
            ]
        ];
        $execResult = InstantMessaging::server(InstantMessaging::IM_OBJECT_TENCENT)
            ->execute($command);
        if($execResult['status'])
        {
            $resultData = $execResult['data'];
            if($resultData['code'] == '200')
            {
                $returnResult = ['code' => '200','desc' => $resultData['result']];
            }
            else
            {
                $returnResult = ['code' => '201','desc' => $resultData['msg']];
            }
        }
        else
        {
            $returnResult = ['code' => '-1','desc'=>$execResult['msg']];
        }
        return $returnResult;
    }

    /**
     * 好友关系校验
     *
     * @param string    $fromAccount    当前用户
     * @param array     $toAccounts     目标用户ID 集合 e.g. ['1497428','555','666','777']
     * @param int       $checkType      校验类型：单向 | 双向 默认单向
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/26
     * @Time: 19:53
     */
    public function checkFriends($fromAccount,Array $toAccounts,$checkType=ImService::FRIENDS_CHECK_TYPE_SINGLE)
    {
        $toAccounts = array_map('strval',$toAccounts);
        $command = [
            'mission' => IM::TENCENT_MISSION_FRIENDS_CHECK,
            'data' => [
                'fromAccount' => (string)$fromAccount,
                'toAccount'   => $toAccounts,
                'checkType'   => $checkType
            ]
        ];
        $execResult = InstantMessaging::server(InstantMessaging::IM_OBJECT_TENCENT)
            ->execute($command);
        if($execResult['status'])
        {
            $resultData = $execResult['data'];
            if($resultData['code'] == '200')
            {

                $resultItems = $resultData['result'];
                foreach ($resultItems['item']['items'] as &$info)
                {
                    $info['Relation'] = strtr(self::$friendsCheckResultTypeMap[$checkType][$info['Relation']],['fromAccount'=>$fromAccount,'toAccount'=>$info['To_Account']]);
                }
                $returnResult = ['code' => '200','desc' => $resultItems];
            }
            else
            {
                $returnResult = ['code' => '201','desc' => $resultData['msg']];
            }
        }
        else
        {
            $returnResult = ['code' => '-1','desc'=>$execResult['msg']];
        }
        return $returnResult;
    }

    /**
     * 更新好友 目前只支持 更改好友备注，好友分组及自定义
     *
     * @param string    $fromAccount    当前用户
     * @param array     $friendsInfo    要更新的信息
     * e.g. [
               [
                    'userId' => '4701015', string  必填  要更新用户的ID
                    'remark' => '好友',     string  选填  好友备注
                    'group'  => [           array  选填  好友分组
                        '好友',
                        '同事'
                    ]
               ],
               [
                    'userId' => '145268',
                    'remark' => 'hi friend'
               ],

           ]
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/28
     * @Time: 14:53
     */
    public function updateFriends($fromAccount,Array $friendsInfo)
    {
        $command = [
            'mission' => IM::TENCENT_MISSION_FRIENDS_UPDATE,
            'data' =>[
                'fromAccount' => $fromAccount,
                'friendsInfo' => $friendsInfo
            ]
        ];
        $execResult = InstantMessaging::server(InstantMessaging::IM_OBJECT_TENCENT)
            ->execute($command);
        if($execResult['status'])
        {
            $resultData = $execResult['data'];
            if($resultData['code'] == '200')
            {
                $returnResult = ['code' => '200','desc' => $resultData['result']];
            }
            else
            {
                $returnResult = ['code' => '201','desc' => $resultData['msg']];
            }
        }
        else
        {
            $returnResult = ['code' => '-1','desc'=>$execResult['msg']];
        }
        return $returnResult;
    }

    /**
     * 删除好友
     *
     * @param string   $fromAccount         当前用户ID
     * @param array    $deleteAccounts      待删除好友集合 e.g. ['564','75698']
     * @param int      $deleteType          删除类型 单向 | 双向 默认 单向
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/28
     * @Time: 15:43
     */
    public function deleteFriends($fromAccount,Array $deleteAccounts,$deleteType=ImService::FRIENDS_DELETE_TYPE_SINGLE)
    {
        $deleteAccounts = array_map('strval',$deleteAccounts);
        $command = [
            'mission' => IM::TENCENT_MISSION_FRIENDS_DELETE,
            'data' => [
                'fromAccount' => (string)$fromAccount,
                'deleteAccounts' => $deleteAccounts,
                'deleteType' => $deleteType
            ]
        ];
        $execResult = InstantMessaging::server(InstantMessaging::IM_OBJECT_TENCENT)
            ->execute($command);
        if($execResult['status'])
        {
            $resultData = $execResult['data'];
            if($resultData['code'] == '200')
            {
                $returnResult = ['code' => '200','desc' => $resultData['result']];
            }
            else
            {
                $returnResult = ['code' => '201','desc' => $resultData['msg']];
            }
        }
        else
        {
            $returnResult = ['code' => '-1','desc'=>$execResult['msg']];
        }
        return $returnResult;
    }

    /**
     * 删除全部好友
     *
     * @param   string  $fromAccount    当前用户ID
     * @param   int     $deleteType     删除类型 单向 | 双向 默认 单向
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/28
     * @Time: 16:00
     */
    public function deleteFriendsAll($fromAccount,$deleteType=ImService::FRIENDS_DELETE_TYPE_SINGLE)
    {
        $command = [
            'mission' => IM::TENCENT_MISSION_FRIENDS_DELETE_ALL,
            'data' =>[
                'fromAccount' => (string)$fromAccount,
                'deleteType'  => $deleteType
            ]
        ];
        $execResult = InstantMessaging::server(InstantMessaging::IM_OBJECT_TENCENT)
            ->execute($command);
        if($execResult['status'])
        {
            $resultData = $execResult['data'];
            if($resultData['code'] == '200')
            {
                $returnResult = ['code' => '200','desc' => '操作成功'];
            }
            else
            {
                $returnResult = ['code' => '201','desc' => $resultData['msg']];
            }
        }
        else
        {
            $returnResult = ['code' => '-1','desc'=>$execResult['msg']];
        }
        return $returnResult;
    }

    /**
     * 拉取好友
     *
     * @param string    $fromAccount        当前用户ID
     * @param int       $startIndex         分页开始数
     * @param int       $standardSequence   上次拉好友数据时返回的 StandardSequence，如果 StandardSequence 字段的值与后台一致，后台不会返回标配好友数据
     * @param int       $customSequence     上次拉好友数据时返回的 CustomSequence，如果 CustomSequence 字段的值与后台一致，后台不会返回自定义好友数据
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/28
     * @Time: 16:33
     */
    public function pullFriends($fromAccount,$startIndex=0,$standardSequence=0,$customSequence=0)
    {
        $startIndex = ($startIndex >=0) ? $startIndex:0;
        $command = [
            'mission' => IM::TENCENT_MISSION_FRIENDS_PULL,
            'data' => [
                'fromAccount' => (string)$fromAccount,
                'startIndex'  => $startIndex,
                'standardSequence' => (int)$standardSequence,
                'customSequence' => (int)$customSequence
            ]
        ];
        $execResult = InstantMessaging::server(InstantMessaging::IM_OBJECT_TENCENT)
            ->execute($command);
        if($execResult['status'])
        {
            $resultData = $execResult['data'];
            if($resultData['code'] == '200')
            {
                $returnResult = ['code' => '200','desc' => $resultData['result']];
            }
            else
            {
                $returnResult = ['code' => '201','desc' => $resultData['msg']];
            }
        }
        else
        {
            $returnResult = ['code' => '-1','desc'=>$execResult['msg']];
        }
        return $returnResult;
    }

    /**
     * 添加黑名单
     *
     * @param   string $fromAccount     操作用户
     * @param   array  $toAccounts      用户黑名单集合
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/28
     * @Time: 17:36
     */
    public function addFriendsBlackList($fromAccount,Array $toAccounts)
    {
        $command = [
            'mission' => IM::TENCENT_MISSION_FRIENDS_BLACKLIST_ADD,
            'data' => [
                'fromAccount' => $fromAccount,
                'toAccounts'  => $toAccounts
            ]
        ];
        $execResult = InstantMessaging::server(InstantMessaging::IM_OBJECT_TENCENT)
            ->execute($command);
        if($execResult['status'])
        {
            $resultData = $execResult['data'];
            if($resultData['code'] == '200')
            {
                $returnResult = ['code' => '200','desc' => $resultData['result']];
            }
            else
            {
                $returnResult = ['code' => '201','desc' => $resultData['msg']];
            }
        }
        else
        {
            $returnResult = ['code' => '-1','desc'=>$execResult['msg']];
        }
        return $returnResult;
    }

    /**
     * 删除黑名单
     *
     * @param string  $fromAccount      操作用户ID
     * @param array   $toAccounts       解除黑名单用户ID集合 e.g. ['1494742','45526']
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/28
     * @Time: 17:46
     */
    public function deleteFriendsBlackList($fromAccount,Array $toAccounts)
    {
        $command = [
            'mission' => IM::TENCENT_MISSION_FRIENDS_BLACKLIST_DELETE,
            'data' => [
                'fromAccount' => $fromAccount,
                'toAccounts'  => $toAccounts
            ]
        ];
        $execResult = InstantMessaging::server(InstantMessaging::IM_OBJECT_TENCENT)
            ->execute($command);
        if($execResult['status'])
        {
            $resultData = $execResult['data'];
            if($resultData['code'] == '200')
            {
                $returnResult = ['code' => '200','desc' => $resultData['result']];
            }
            else
            {
                $returnResult = ['code' => '201','desc' => $resultData['msg']];
            }
        }
        else
        {
            $returnResult = ['code' => '-1','desc'=>$execResult['msg']];
        }
        return $returnResult;
    }

    /**
     * 校验黑名单
     *
     * @param string    $fromAccount    操作用户ID
     * @param array     $toAccounts     待校验用户ID集合 e.g. ['1497428','23658','7859']
     * @param int       $checkType      校验类型 单向 | 双向 默认单向
     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/28
     * @Time: 18:06
     */
    public function checkFriendsBlackList($fromAccount,Array $toAccounts,$checkType=ImService::FRIENDS_BLACK_LIST_CHECK_SINGLE)
    {
        $toAccounts = array_map('strval',$toAccounts);
        $command = [
            'mission' => IM::TENCENT_MISSION_FRIENDS_BLACKLIST_CHECK,
            'data' => [
                'fromAccount' => (string)$fromAccount,
                'toAccounts' => $toAccounts,
                'checkType'  => $checkType
            ]
        ];
        $execResult = InstantMessaging::server(InstantMessaging::IM_OBJECT_TENCENT)
            ->execute($command);
        if($execResult['status'])
        {
            $resultData = $execResult['data'];
            if($resultData['code'] == '200')
            {
                $result = $resultData['result'];
                if(!empty($result))
                {
                    foreach ($result['list']['items'] as $key=>&$item)
                    {
                        $item['Relation'] = strtr(self::$blackListCheckResultTypeMap[$checkType][$item['Relation']],['fromAccount'=>$fromAccount,'toAccount'=>$item['To_Account']]);
                    }
                }
                $returnResult = ['code' => '200','desc' => $result];
            }
            else
            {
                $returnResult = ['code' => '201','desc' => $resultData['msg']];
            }
        }
        else
        {
            $returnResult = ['code' => '-1','desc'=>$execResult['msg']];
        }
        return $returnResult;
    }

    /**
     * 黑名单列表
     *
     * @param   string  $fromAccount    平台用户ID
     * @param   int     $startIndex     拉取的起始位置
     * @param   int     $lastSequence   上一次拉黑名单时后台返回给客户端的 Seq，初次拉取时为0
     * @param   int     $limit          每页最多拉取的黑名单数 默认 30

     *
     * @return array
     *
     * @Author: 姜子龙 <jiangzilong@zhibo.tv>
     * @Date: 2019/12/28
     * @Time: 20:49
     */
    public function pullBlackList($fromAccount,$startIndex=0,$lastSequence=0,$limit = 30)
    {
        $command = [
            'mission' => IM::TENCENT_MISSION_FRIENDS_BLACKLIST_PULL,
            'data' => [
                'fromAccount' => $fromAccount,
                'startIndex'  => $startIndex,
                'limit'       => $limit,
                'lastSequence'=> $lastSequence
            ]
        ];
        $execResult = InstantMessaging::server(InstantMessaging::IM_OBJECT_TENCENT)
            ->execute($command);
        if($execResult['status'])
        {
            $resultData = $execResult['data'];
            if($resultData['code'] == '200')
            {
                $returnResult = ['code' => '200','desc' => $resultData['result']];
            }
            else
            {
                $returnResult = ['code' => '201','desc' => $resultData['msg']];
            }
        }
        else
        {
            $returnResult = ['code' => '-1','desc'=>$execResult['msg']];
        }
        return $returnResult;
    }
}