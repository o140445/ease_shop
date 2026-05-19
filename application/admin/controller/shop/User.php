<?php

namespace app\admin\controller\shop;

use app\common\controller\Backend;
use fast\Random;
use think\Db;

/**
 * 商城-会员
 *
 * @icon fa fa-circle-o
 */
class User extends Backend
{
    protected $relationSearch = true;

    /**
     * User模型对象
     * @var \app\admin\model\shop\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\shop\User;
        $this->view->assign("statusList", $this->model->getStatusList());
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
        $list = $this->model
            ->with('level')
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
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
        $password = trim((string)($params['password'] ?? ''));
        if ($password === '') {
            $this->error(__('Password can not be empty'));
        }
        if (Db::name('shop_user')->where('username', trim((string)$params['username']))->whereNull('deletetime')->find()) {
            $this->error(__('Username already exists'));
        }

        $salt = Random::alnum();
        $params['password'] = md5(md5($password) . $salt);
        $params['salt'] = $salt;
        $params['pay_password'] = '';
        $params['pay_salt'] = '';

        Db::startTrans();
        try {
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
        $password = trim((string)($params['password'] ?? ''));
        if ($password !== '') {
            $salt = Random::alnum();
            $params['password'] = md5(md5($password) . $salt);
            $params['salt'] = $salt;
        } else {
            unset($params['password'], $params['salt']);
        }
        if (isset($params['username']) && Db::name('shop_user')->where('username', trim((string)$params['username']))->where('id', '<>', $row['id'])->whereNull('deletetime')->find()) {
            $this->error(__('Username already exists'));
        }

        Db::startTrans();
        try {
            $result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }

}
