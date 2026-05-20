<?php

namespace app\admin\controller\shop\user;

use app\common\controller\Backend;
use think\Db;

/**
 * 商城-用户银行卡
 *
 * @icon fa fa-circle-o
 */
class Bank extends Backend
{
    protected $relationSearch = true;

    /**
     * Bank模型对象
     * @var \app\admin\model\shop\user\Bank
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\shop\user\Bank;
        $this->view->assign("isDefaultList", $this->model->getIsDefaultList());
        $this->view->assign("statusList", $this->model->getStatusList());
        $userId = (int)$this->request->param('user_id', 0);
        $user = $userId ? Db::name('shop_user')->where('id', $userId)->field('id,username,nickname')->find() : null;
        $userName = $user ? ($user['nickname'] ?: $user['username']) : '';
        $this->view->assign('userId', $userId);
        $this->view->assign('bankUser', $user);
        $this->view->assign('bankUserName', $userName);
    }

    public function index()
    {
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }

        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $userId = (int)$this->request->param('user_id', 0);
        $query = $this->model
            ->with('user')
            ->where($where);
        if ($userId > 0) {
            $query->where('user_id', $userId);
        }
        $list = $query
            ->order($sort, $order)
            ->paginate($limit);

        return json(['total' => $list->total(), 'rows' => $list->items()]);
    }

    public function add()
    {
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $params['user_id'] = (int)($params['user_id'] ?? 0);
        $params = $this->normalizeBankParams($params);
        if (!$params['user_id'] || !Db::name('shop_user')->where('id', $params['user_id'])->whereNull('deletetime')->find()) {
            $this->error(__('User does not exist'));
        }

        Db::startTrans();
        try {
            if (!empty($params['is_default'])) {
                Db::name('shop_user_bank')->where('user_id', $params['user_id'])->update(['is_default' => 0]);
            }
            $result = $this->model->allowField(true)->save($params);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success();
    }

    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $params['user_id'] = (int)($params['user_id'] ?? 0);
        $params = $this->normalizeBankParams($params);
        if (!$params['user_id'] || !Db::name('shop_user')->where('id', $params['user_id'])->whereNull('deletetime')->find()) {
            $this->error(__('User does not exist'));
        }

        Db::startTrans();
        try {
            if (!empty($params['is_default'])) {
                Db::name('shop_user_bank')
                    ->where('user_id', $params['user_id'])
                    ->where('id', '<>', (int)$row['id'])
                    ->update(['is_default' => 0]);
            }
            $result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }

    protected function normalizeBankParams(array $params)
    {
        $params['card_no'] = trim((string)($params['card_no'] ?? ''));
        if ($params['card_no'] === '') {
            $this->error(__('Please enter account number'));
        }
        $params['realname'] = '';
        $params['bank_name'] = '';
        $params['bank_branch'] = '';
        $params['mobile'] = '';
        $params['id_card'] = '';
        $params['status'] = $params['status'] ?? 'normal';
        $params['is_default'] = (int)($params['is_default'] ?? 0);
        return $params;
    }
}
