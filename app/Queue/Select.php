<?php

namespace App\Queue;

use App\Utils\Queue;
use Ruesin\Utils\Redis;

class Select extends Base
{
    public function handle()
    {
        $info = Queue::getInfo($this->queue_name);

        if (empty($info)) {
            return self::response(400, ['messageId' => '', 'content' => ''], 'Queue does not exist!');
        }

        if (isset($info['config']['host'])) {
            $message = Redis::createInstance($info['list_name'], $info['config'])
                ->lpop($info['list_name']);
            return self::response(200, ['messageId' => 'custom-active-queue', 'content' => $message]);
        }

        $this->connection->multi();
        $messageId = $this->connection->lpop($this->activeName);
        if (!$messageId) {
            $this->connection->discard();
            return self::response(200, ['messageId' => '', 'content' => ''], 'Message is empty!');
        }

        $message = $this->connection->hget($this->messageName, $messageId);
        if (!$message) {
            $this->connection->discard();
            return self::response(400, ['messageId' => $messageId], 'Message body does not exist!');
        }

        $this->connection->zadd($this->readName, date('YmdHis', time() + $info['hide_time']), $messageId);
        $this->connection->exec();
        return self::response(200, ['messageId' => $messageId, 'content' => $message]);
    }

}