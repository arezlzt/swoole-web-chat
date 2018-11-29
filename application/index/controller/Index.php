<?php

namespace app\index\controller;

use app\push\controller\WebSocketClient;
use chat\library\Message;
use chat\service\Chat as ChatService;
use think\Controller;
use think\Db;
use think\exception\HttpResponseException;
use think\Request;
use think\Response;

class Index extends Controller
{

    protected $status = null;

    protected $message = '';

    protected $data = [];

    protected $avatar = [
        '/static/img/avatar/haijiaoluoluo.jpg',
        '/static/img/avatar/qianxing.jpg',
        '/static/img/avatar/qingsong.jpg',
        '/static/img/avatar/redsun.gif',
        '/static/img/avatar/apple.jpg',
        '/static/img/avatar/wangnima.jpg',
        '/static/img/avatar/en.png',
    ];

    protected $chatService = null;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->chatService = new ChatService();
    }

    /**
     * 聊天界面
     * @return mixed
     * @throws \think\Exception
     */
    public function index()
    {
        if (!($user = session('user'))) {
            $this->redirect(url('login'));
        }
        $uid = session('user')['id'];
       $friends = $this->chatService->getFriendList($uid);
        $count = $this->chatService->getUserUnreadMessageCount($uid);
        $this->assign(compact('user', 'friends', 'count'));
        return $this->fetch();
    }

    public function test()
    {
        $chatMessage = new Message();
        $chatMessage->sendMessage($send = [
            'fid' => 1,
            'uid' => 1,
            'message' => '点击发送发送到发送到是打发斯蒂芬是打发斯蒂芬点击发送发送到发送到是打发斯蒂芬是打发斯蒂芬是打发斯蒂点击发送发送到发送到是打发斯蒂芬是打发斯蒂芬是打发斯蒂点击发送发送到发送到是打发斯蒂芬是打发斯蒂芬是打发斯蒂点击发送发送到发送到是打发斯蒂芬是打发斯蒂芬是打发斯蒂点击发送发送到发送到是打发斯蒂芬是打发斯蒂芬是打发斯蒂点击发送发送到发送到是打发斯蒂芬是打发斯蒂芬是打发斯蒂点击发送发送到发送到是打发斯蒂芬是打发斯蒂芬是打发斯蒂是打发斯蒂芬水电费所发生的发送到发送到水电费水电费水电费水电费水电费是否'
        ]);
    }

    /**
     * @return mixed|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login()
    {

        if (session('user')) {
            $this->redirect(url('index'));
        }
        if (request()->isPost()) {
            $data = [
                'username' => input('username', '', 'trim'),
                'password' => input('password', '', 'trim'),
            ];
            if (empty($data['username'])) {
                list($this->status, $this->message) = [0, '请输入用户名'];
                return;
            }
            if (empty($data['password'])) {
                list($this->status, $this->message) = [0, '请输入密码'];
                return;
            }

            $user = Db::name('user')->where('username', $data['username'])->find();
            if (empty($user)) {
                list($this->status, $this->message) = [0, '用户名不存在'];
                return;
            }
            if ($data['password'] !== $user['password']) {
                list($this->status, $this->message) = [0, '密码不存在'];
                return;
            }

            session('user', $user);
            list($this->status, $this->message) = [1, '登录成功'];
            return;
        }
        return $this->fetch();
    }

    /**
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function register()
    {
        if (session('user')) {
            $this->redirect(url('index'));
        }
        if (request()->isPost()) {
            $data = [
                'username' => input('username', '', 'trim'),
                'nickname' => input('nickname', '', 'trim'),
                'password' => input('password', '', 'trim'),
                'repeat_password' => input('repeat_password', '', 'trim'),
            ];
            $data['update_time'] = time();
            if (empty($data['username'])) {
                list($this->status, $this->message) = [0, '请输入用户名'];
                return;
            }
            if (empty($data['nickname'])) {
                list($this->status, $this->message) = [0, '请输入昵称'];
                return;
            }
            if (empty($data['password'])) {
                list($this->status, $this->message) = [0, '请输入密码'];
                return;
            }
            if (empty($data['repeat_password'])) {
                list($this->status, $this->message) = [0, '请确认密码'];
                return;
            }
            if ($data['repeat_password'] !== $data['password']) {
                list($this->status, $this->message) = [0, '密码不一致'];
                return;
            }
            $user = Db::name('user')->where('nickname', $data['nickname'])->find();
            if ($user) {
                list($this->status, $this->message) = [0, '用户名已存在'];
                return;
            }
            $user = Db::name('user')->where('nickname', $data['nickname'])->find();
            if ($user) {
                list($this->status, $this->message) = [0, '昵称已存在'];
                return;
            }
            $data['avatar'] = $this->avatar[mt_rand(0, 7)];
            $data['create_time'] = time();
            unset($data['repeat_password']);
            $res = Db::name('user')->insert($data);
            if ($res) {
                list($this->status, $this->message) = [1, '注册成功'];
                return;
            } else {
                list($this->status, $this->message) = [0, '注册失败'];
                return;
            }
        }
        return $this->fetch();
    }

    /**
     * 析构方法
     */
    public function __destruct()
    {
        if (!is_null($this->status)) {
            $result = [
                'status' => $this->status,
                'message' => $this->message,
                'data' => $this->data,
            ];
            $response = Response::create($result, 'json')->header([]);
            throw new HttpResponseException($response);
        }
    }

    /**
     * 搜索好友
     * @throws \think\Exception
     */
    public function search() {
        $search = input('search', '', 'trim');
        $uid = input('uid', 0, 'intval');
        $list = Db::name('user')
            ->alias('t1')
            ->field('t1.id, t1.nickname, t1.avatar')
            ->whereNotExists('( SELECT id from chat_friend t2 where t2.uid = '.$uid.' and t2.friend_id = t1.id )')
            ->where('t1.nickname', 'like', "%{$search}%")
            ->where('t1.id', 'neq', $uid)
            ->limit(6)
            ->select();
        $html = '';
        foreach ($list as $item) {
            $html .= <<<EOF
            <div class="col-sm-4">
                    <div class="contact-box animated pulse">
                        <div class="col-sm-6">
                            <div class="text-center">
                                <img alt="image" class="img-circle m-t-xs img-responsive"
                                     src="{$item['avatar']}">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <h6><strong>{$item['nickname']}</strong></h6>
                            <button class="btn btn-sm btn-min btn-primary" onclick="postMessage(this)" data-uid="{$item['id']}" style="margin-top: 2px;" type="button"><i
                                    class="fa fa-plus"></i> 好友
                            </button>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
EOF;
        }
        list($this->status, $this->message, $this->data) = [1, '获取成功', $html];
    }

    /**
     * @throws \think\Exception
     */
    public function getMessage() {
        $uid = input('uid', 0, 'intval');
        $list = Db::name('message')
            ->alias('t1')
            ->field('t2.nickname,t2.avatar, t1.id,t1.content,t1.status')
            ->join('user t2', 't2.id = t1.from_uid')
            ->where('t1.to_uid', $uid)
            ->order('t1.create_time desc')
            ->select();
        $html = '';
        foreach ($list as $item) {
            if($item['status'] == 0) {
//                $status = '<span class="label label-info">待回应</span>';
                $status = '<button class="btn btn-sm btn-primary pull-right" data-id="'.$item['id'].'" onclick="passFriendRequest(this);" type="button"><strong>通 过</strong></button>';
            }else if($item['status']  == 1) {
                $status = '<span class="label label-primary pull-right">已添加</span>';
            }else {
                $status = '<span class="label label-danger pull-right">已拒绝</span>';
            }
            $html .= <<<EOF
                    <tr>
                        <td class="client-avatar"><img alt="image" src="{$item['avatar']}"></td>
                        <td>{$item['nickname']}</td>
                        <td>{$item['content']}</td>
                        <td class="client-status">
                            {$status}
                        </td>
                    </tr>
EOF;
        }
        list($this->status, $this->message, $this->data) = [1, '获取成功', $html];
    }

    /**
     *@throws \think\Exception
     */
    public function addMessage() {
        $data = [
            'to_uid'=>input('to_uid', 0, 'intval'),
            'from_uid'=>input('from_uid', 0, 'intval'),
            'content'=>'请求添加你为好友'
        ];
        $friend = Db::name('friend')->where('uid', $data['to_uid'])->where('friend_id', $data['from_uid'])->find();
        $friend2 = Db::name('friend')->where('uid', $data['from_uid'])->where('friend_id', $data['to_uid'])->find();
        if($friend || $friend2) {
            list($this->status, $this->message) = [0, '对方已经是你的好友了'];
            return;
        }
        $message = Db::name('message')->where('to_uid', $data['to_uid'])->where('from_uid', $data['from_uid'])->find();
        if($message) {
            list($this->status, $this->message) = [-1, '你已经发送过请求了'];
            return;
        }
        $data['create_time'] = time();
        $result = Db::name('message')->insert($data);
        if($result) {
            $this->chatService->sendMessageNotice($data['from_uid'], $data['to_uid']);
            list($this->status, $this->message) = [1, '好友请求发送成功'];
        }else {
            list($this->status, $this->message) = [0, '好友请求发送失败'];
        }
    }

    /**
     * 通过好友求情
     */
    public function passFriendRequest() {
        $msg_id = input('msg_id', 0, 'intval');
        if(empty($msg_id)) {
            list($this->status, $this->message) = [0, '参数错误'];
            return;
        }
        $message = Db::name('message')->where('id', $msg_id)->find();
        if($message) {
            Db::startTrans();
            try{
                $result1 = Db::name('message')->where('id', $msg_id)->setField('status', 1);
                $result2 = Db::name('friend')->insert(
                    [
                        'uid'=>$message['from_uid'],
                        'friend_id'=>$message['to_uid'],
                        'create_time'=>time()
                    ]
                );
                if($result2 && $result1) {
                    Db::commit();
                    list($this->status, $this->message) =[1, '添加好友成功'];
                }else {
                    Db::rollback();
                    list($this->status, $this->message) =[0, '添加好友关系失败'];
                }
            }catch (\Exception $e) {
                Db::rollback();
                list($this->status, $this->message) =[0, '添加好友关系失败' . $e->getMessage()];
            }
        }else {
            list($this->status, $this->message) = [0, '消息未找到'];
        }
    }

    /**
     * @throws \think\exception\DbException
     */
    public function getChatRecord() {
        $from_uid = input('from_uid', 0, 'intval');
        $to_uid = input('to_uid', 0, 'intval');
        if(empty($from_uid) || empty($to_uid)) {
            list($this->status, $this->message) = [0, '参数错误'];
            return;
        }
        $data = $this->chatService->getChatRecord($from_uid, $to_uid);
        krsort($data['list']);
        $html = '';
        foreach ($data['list'] as $item) {
            $item['create_time'] = date('Y-m-d H:i:s');
            if($item['from_uid'] == $from_uid) {
                $html .= $this->chatService->formatFromMessageHtml($item);
            }else {
                $html.= $this->chatService->formatToMessageHtml($item);
            }
        }
        list($this->status, $this->message, $this->data) = [1, '获取成功', ['html'=>$html, 'lastPage'=>$data['lastPage']]];
    }
}
