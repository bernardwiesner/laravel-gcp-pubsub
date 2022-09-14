<?php

namespace BernardWiesner\PubSub;

trait Formatter
{

    public function formatMessage(array $message, $delaySeconds): array
    {
        if(isset($message['data'])) {
            $message['data'] = json_encode($message['data']);
        }

        if ($delaySeconds > 0) {
            if (!isset($message['attributes'])) {
                $message['attributes'] = [];
            }
            $message['attributes'] += ['available_at' => time() + $delaySeconds];
        }
        if (isset($message['attributes'])) {
            $message['attributes'] = array_map('strval', $message['attributes']);
        }
        return $message;
    }
}
