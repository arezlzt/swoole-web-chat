<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-11-26
 * Time: 14:03
 */

namespace app\push\controller;


use chat\service\Chat as ChatService;
use think\Controller;
use think\Request;

/**
 * 聊天服务器
 * Class ChatServer
 * @package app\push\controller
 */
class ChatServer extends Controller
{

    public $chatService = null;

    /**
     * ChatServer constructor.
     * @param Request|null $request
     */
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->chatService = new ChatService();
    }

    public  function getService() {
        return $this->chatService;
    }

    public function index() {

        $server = new \swoole_websocket_server("0.0.0.0", 9501);
        $server->on('open', function (\swoole_websocket_server $server, $request) {
            echo "server: handshake success with fd{$request->fd}\n";
        });

        $server->on('message', function (\swoole_websocket_server $server, $frame) {
            $user_message = $frame->data;
            echo 'fd:' .$frame->fd . '   message:' . $user_message . PHP_EOL;
            $user_message = json_decode($user_message, true);
            if($user_message) {
                if(isset($user_message['type'])) {
                    switch ($user_message['type']) {
                        case 0://上线
                            echo static::getService()->online($user_message['uid'], $frame->fd);
                            break;
                        case 1: //单条信息发送（聊天新消息通知）
                        case 2:
                            //单条信息发送(聊天记录)
                            $server->push($user_message['to_fd'], json_encode($user_message));
                            break;
                        case 3://处理网页端发送过来的消息
                            $list = static::getService()->sendChatMessage($user_message['from_uid'], $user_message['to_uid'], $user_message['message']);
                            foreach ($list as $item) {
                                $server->push($item['to_fd'], json_encode($item));
                            }
                            break;
                    }
                }
            }else {
                echo 'json 解析错误';
            }
          /*  foreach($server->connections as $key => $fd) {
                $server->push($fd, $user_message);
            }*/

        });

        $server->on('close', function ($ser, $fd) {
            echo "client {$fd} sclosed\n";
            //下线操作
           echo static::getService()->offline($fd);
        });

        $server->start();
    }

}