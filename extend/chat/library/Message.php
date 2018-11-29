<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-11-28
 * Time: 11:17
 */

namespace chat\library;

/**
 * 消息
 * Class Message
 * @package chat\library
 */
class Message
{

    protected $host = '127.0.0.1';

    protected $port = '9501';

    public function __construct($host = '127.0.0.1', $port = '9501')
    {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * @param $sendData array 需要发送的数组消息
     * @return bool
     */
    public function sendMessage($sendData) {
        $client = new WebSocketClient($this->host,  $this->port);
        $data = $client->connect();
        if ($data) {
            $result = $client->send(json_encode($sendData));
            $client->recv();
            sleep(1);
            return $result;
        }else {
            return false;
        }
    }
}