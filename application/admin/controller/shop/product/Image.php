<?php

namespace app\admin\controller\shop\product;

use app\common\controller\Backend;
use think\Db;

/**
 * 商城-商品图片
 *
 * @icon fa fa-circle-o
 */
class Image extends Backend
{

    /**
     * Image模型对象
     * @var \app\admin\model\shop\product\Image
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\shop\product\Image;

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
        $productId = (int)$this->request->get('product_id', 0);
        $list = $this->model
            ->where($where)
            ->where(function ($query) use ($productId) {
                if ($productId) {
                    $query->where('product_id', $productId);
                }
            })
            ->order($sort, $order)
            ->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }

    public function add()
    {
        if (false === $this->request->isPost()) {
            $this->view->assign('productId', (int)$this->request->get('product_id', 0));
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (!empty($params['image']) && strpos($params['image'], ',') !== false) {
            $images = array_values(array_filter(array_map('trim', explode(',', $params['image']))));
            Db::startTrans();
            try {
                foreach ($images as $index => $image) {
                    $this->model->isUpdate(false)->allowField(true)->save([
                        'product_id' => (int)$params['product_id'],
                        'image'      => $image,
                        'weigh'      => count($images) - $index,
                    ]);
                    $this->model = new \app\admin\model\shop\product\Image;
                }
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            $this->success();
        }
        return parent::add();
    }

}
