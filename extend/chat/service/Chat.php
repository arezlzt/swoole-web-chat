<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-11-28
 * Time: 10:11
 */

namespace chat\service;

use think\Db;
use chat\library\Message as ClientMessage;

/**
 * 相关逻辑
 * Class Chat
 * @package chat\service
 */
class Chat
{
    /**
     * 上线
     * @param $uid
     * @param $fid
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function online($uid, $fid)
    {
        $result = Db::name('user')->where('id', $uid)->update(['online' => 1, 'fid' => $fid]);
        return $result ? $fid . ' online success ' . PHP_EOL : $fid . ' online failed ' . PHP_EOL;
    }

    /**
     * 下线
     * @param $fid
     * @return string
     */
    public function offline($fid)
    {
        $result = Db::name('user')->where('fid', $fid)->setField('online', 0);
        return $result ? $fid . ' offline success ' . PHP_EOL : $fid . ' offline failed ' . PHP_EOL;
    }

    /**
     * 发送消息通知
     * @param $uid integer 发送用户
     * @param $to_uid integer 发送给谁
     * @return string
     * @throws \think\Exception
     */
    public function sendMessageNotice($uid, $to_uid)
    {
        $to_user = Db::name('user')->where('id', $to_uid)->find();
        if ($to_user && $to_user['online'] == 1) {
            $count = $this->getUserUnreadMessageCount($to_uid);
            $clientMessage = new ClientMessage();
            $data = [
                'from_uid' => $uid,
                'type' => 1,
                'to_fd' => $to_user['fid'],
                'data' => [
                    'count' => $count
                ]
            ];
            $result = $clientMessage->sendMessage($data);
            return $result ? $to_uid . ' send message notice success' : $to_uid . ' send message notice failed';
        } else {
            return $to_uid . ' Not online';
        }
    }

    /**
     * 获取用户未读消息数量
     * @param $uid
     * @return int|string
     * @throws \think\Exception
     */
    public function getUserUnreadMessageCount($uid)
    {
        $count = Db::name('message')->where('to_uid', $uid)->where('status', 0)->count();
        return $count;
    }

    /**
     * 获取好友列表
     * @param $uid integer 用户uid
     *
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\Exception
     */
    public function getFriendList($uid)
    {
        $list = Db::name('friend')
            ->alias('t1')
            ->field('t2.id, t2.nickname, t2.avatar')
            ->join('user t2', 't2.id = t1.friend_id')
            ->where('uid', $uid)
            ->union(
                Db::name('friend')
                    ->alias('t1')
                    ->field('t2.id, t2.nickname, t2.avatar')
                    ->join('user t2', 't2.id = t1.uid')
                    ->where('friend_id', $uid)
                    ->buildSql()
            )
            ->select();
        return $list;
    }

    /**
     * 获取聊天记录
     * @param $from_uid
     * @param $to_uid
     * @return array|\think\Paginator
     * @throws \think\exception\DbException
     */
    public function getChatRecord($from_uid, $to_uid)
    {
        $list = Db::name('record')->alias('t1')
            ->field('t1.content,t1.create_time, t2.nickname, t2.avatar,t1.from_uid')
            ->join('user t2', 't2.id = t1.from_uid')
            ->where("(t1.from_uid = :from_uid1 AND to_uid = :to_uid1) OR (t1.from_uid = :to_uid2 and to_uid = :from_uid2)")
            ->bind(['from_uid1' => [$from_uid, \PDO::PARAM_INT], 'to_uid1' => [$to_uid, \PDO::PARAM_INT], 'from_uid2' => [$from_uid, \PDO::PARAM_INT], 'to_uid2' => [$to_uid, \PDO::PARAM_INT]])
            ->order('t1.create_time desc')->paginate(10);
        return ['lastPage' => $list->lastPage(), 'list' => collection($list->items())->toArray()];
    }

    /**
     * 发送一条聊天消息
     * @param $from_uid integer 发送者
     * @param $to_uid integer 接收者
     * @param $message string 消息内容
     * @return array
     * @throws \think\Exception
     */
    public function sendChatMessage($from_uid, $to_uid, $message)
    {
        $user = Db::name('user')->field('online, fid')->where('id', $to_uid)->find();
        if ($user) {
            $data = [
                'from_uid' => $from_uid,
                'to_uid' => $to_uid,
                'content' => $message,
                'create_time' => time()
            ];
            $result = Db::name('record')->insert($data);
            if ($result) {
                $fromUser = Db::name('user')->field('nickname,avatar,fid')->where('id', $from_uid)->find();
                list($fromUser['content'], $fromUser['create_time']) = [$message, date('Y-m-d H:i:s', $data['create_time'])];
                $fromHtml = $this->formatFromMessageHtml($fromUser);
                $list = [
                    ['type' => 2, 'to_fd' => $fromUser['fid'], 'data' => ['html' => $fromHtml]],
                ];
                if ($user['online'] == 1) {
                    list($user['nickname'],$user['avatar'], $user['content'], $user['create_time']) = [$fromUser['nickname'],$fromUser['avatar'], $message, date('Y-m-d H:i:s', $data['create_time'])];
                    $toHtml = $this->formatToMessageHtml($user);
                    $list[] = ['type' => 2, 'to_fd' => $user['fid'], 'data' => ['html' => $toHtml]];
                }
                return $list;
            } else {
                //echo 'from_uid:' . $from_uid . ' message save failed' . PHP_EOL;
                return [];
            }
        } else {
            //  echo 'from_uid:' . $from_uid . ' this user not register' . PHP_EOL;
            return [];
        }
    }

    /**
     * 格式化发送者消息显示html
     * @param $user
     * @return string
     */
    public function formatFromMessageHtml($user)
    {
        return <<<EOF
                <li class="dialog-chat-mine">
                    <div class="dialog-chat-user">
                        <cite><i>{$user['create_time']}</i>{$user['nickname']}</cite>
                        <img src="{$user['avatar']}">
                    </div>
                    <div class="dialog-chat-text">
                        <div class="dialog-chat-triangle"></div>
                        <div class="dialog-chat-message">
                            {$user['content']}
                        </div>
                    </div>
                </li>
EOF;
    }

    /**
     * 格式化接受者消息显示html
     * @param $user
     * @return string
     */
    public function formatToMessageHtml($user)
    {
        return <<<EOF
                <li>
                    <div class="dialog-chat-user">
                        <img src="{$user['avatar']}">
                        <cite>{$user['nickname']}<i>{$user['create_time']}</i></cite>
                    </div>
                    <div class="dialog-chat-text">
                        <div class="dialog-chat-triangle"></div>
                        <div class="dialog-chat-message">{$user['content']}</div>
                    </div>
                </li>
EOF;
    }
}