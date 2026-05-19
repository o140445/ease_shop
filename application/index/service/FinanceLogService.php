<?php

namespace app\index\service;

use think\Db;
use think\Log;

class FinanceLogService
{
    public function balanceChange(array $data)
    {
        Db::name('shop_balance_log')->insert([
            'user_id'     => (int)($data['user_id'] ?? 0),
            'type'        => $data['type'] ?? 'adjust',
            'order_id'    => (int)($data['order_id'] ?? 0),
            'recharge_id' => (int)($data['recharge_id'] ?? 0),
            'withdraw_id' => (int)($data['withdraw_id'] ?? 0),
            'refund_id'   => (int)($data['refund_id'] ?? 0),
            'money'       => number_format((float)($data['money'] ?? 0), 2, '.', ''),
            'before'      => number_format((float)($data['before'] ?? 0), 2, '.', ''),
            'after'       => number_format((float)($data['after'] ?? 0), 2, '.', ''),
            'memo'        => $data['memo'] ?? '',
            'createtime'  => time(),
        ]);
    }

    public function operation($action, $status, array $context = [])
    {
        Log::record('[shop-money] ' . $action . ' ' . $status . ' ' . json_encode($context, JSON_UNESCAPED_UNICODE), $status === 'fail' ? 'error' : 'info');
    }

    public function transactionFail($action, \Exception $e, array $context = [])
    {
        $context['error'] = $e->getMessage();
        $this->operation($action, 'fail', $context);
    }
}
