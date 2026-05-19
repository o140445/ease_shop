<?php

namespace app\admin\controller\shop;

use app\common\controller\Backend;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 商城-商品
 *
 * @icon fa fa-circle-o
 */
class Product extends Backend
{
    protected $relationSearch = true;

    /**
     * Product模型对象
     * @var \app\admin\model\shop\Product
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\shop\Product;
        $this->view->assign("isSkuList", $this->model->getIsSkuList());
        $this->view->assign("isRecommendList", $this->model->getIsRecommendList());
        $this->view->assign("isNewList", $this->model->getIsNewList());
        $this->view->assign("isHotList", $this->model->getIsHotList());
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
            ->with('category')
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
        $galleryImages = $params['gallery_images'] ?? '';
        unset($params['gallery_images']);
        $params = $this->preExcludeFields($params);

        Db::startTrans();
        try {
            $result = $this->model->allowField(true)->save($params);
            if ($result === false) {
                throw new \Exception(__('No rows were inserted'));
            }
            $this->syncGalleryImages((int)$this->model->id, $galleryImages);
            Db::commit();
        } catch (ValidateException|PDOException|\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
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
            $galleryImages = Db::name('shop_product_image')
                ->where('product_id', (int)$row['id'])
                ->order('weigh desc,id asc')
                ->column('image');
            $row['gallery_images'] = implode(',', $galleryImages);
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }

        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $galleryImages = $params['gallery_images'] ?? '';
        unset($params['gallery_images']);
        $params = $this->preExcludeFields($params);

        Db::startTrans();
        try {
            $result = $row->allowField(true)->save($params);
            if ($result === false) {
                throw new \Exception(__('No rows were updated'));
            }
            $this->syncGalleryImages((int)$row['id'], $galleryImages);
            Db::commit();
        } catch (ValidateException|PDOException|\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success();
    }

    protected function syncGalleryImages($productId, $images)
    {
        $images = is_array($images) ? $images : explode(',', (string)$images);
        $images = array_values(array_unique(array_filter(array_map('trim', $images))));
        Db::name('shop_product_image')->where('product_id', $productId)->delete();
        foreach ($images as $index => $image) {
            Db::name('shop_product_image')->insert([
                'product_id'  => $productId,
                'image'       => $image,
                'weigh'       => count($images) - $index,
                'createtime'  => time(),
            ]);
        }
    }

}
