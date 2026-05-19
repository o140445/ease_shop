<?php

namespace app\index\controller;

use think\Db;

class Product extends Index
{
    public function index()
    {
        $this->view->assign('title', __('Products'));
        $categoryId = (int)$this->request->param('category_id', 0);
        $keyword = trim($this->request->param('keyword', ''));
        $sort = $this->request->param('sort', 'default');
        $order = strtolower($this->request->param('order', 'desc'));
        $sortOptions = [
            'default' => __('Comprehensive'),
            'new'     => __('Latest'),
            'price'   => __('Price'),
            'sales'   => __('Sales'),
        ];
        if (!isset($sortOptions[$sort])) {
            $sort = 'default';
        }
        if (!in_array($order, ['asc', 'desc'])) {
            $order = 'desc';
        }

        $query = Db::name('shop_product')->where('status', 'normal');
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        if ($keyword !== '') {
            $query->where('title|subtitle|keywords', 'like', '%' . $keyword . '%');
        }

        switch ($sort) {
            case 'new':
                $query->order('id ' . $order);
                break;
            case 'price':
                $query->order('price ' . $order . ',id desc');
                break;
            case 'sales':
                $query->order('sales ' . $order . ',id desc');
                break;
            default:
                $query->order('weigh ' . $order . ',id desc');
                break;
        }

        $products = $query->paginate(12, false, [
            'query' => ['category_id' => $categoryId, 'keyword' => $keyword, 'sort' => $sort, 'order' => $order, 'lang' => $this->shopLang],
        ]);
        $categories = Db::name('shop_category')->where('status', 'normal')->order('weigh desc,id desc')->select();

        $this->view->assign(compact('products', 'categories', 'categoryId', 'keyword', 'sort', 'order', 'sortOptions'));
        return $this->view->fetch();
    }

    public function detail()
    {
        $id = (int)$this->request->param('id');
        $product = Db::name('shop_product')->where('id', $id)->where('status', 'normal')->find();
        if (!$product) {
            $this->error(__('Product not found'));
        }
        $this->view->assign('title', $product['title']);
        $images = Db::name('shop_product_image')->where('product_id', $id)->order('weigh desc,id asc')->select();
        $skus = Db::name('shop_product_sku')->where('product_id', $id)->where('status', 'normal')->order('weigh desc,id asc')->select();
        $related = Db::name('shop_product')
            ->where('status', 'normal')
            ->where('id', '<>', $id)
            ->where('category_id', $product['category_id'])
            ->order('weigh desc,id desc')
            ->limit(4)
            ->select();

        $product['display_price'] = number_format((float)$product['price'], 2, '.', '');
        $product['display_market_price'] = (float)$product['market_price'] > 0 ? number_format((float)$product['market_price'], 2, '.', '') : '';
        $galleryImages = [];
        if ($product['main_image']) {
            $galleryImages[] = ['image' => $product['main_image']];
        }
        foreach ($images as $image) {
            foreach (explode(',', (string)$image['image']) as $imageUrl) {
                $imageUrl = trim($imageUrl);
                if ($imageUrl && $imageUrl !== $product['main_image']) {
                    $galleryImages[] = ['image' => $imageUrl];
                }
            }
        }
        if (!$galleryImages) {
            $galleryImages[] = ['image' => '/assets/img/logo.png'];
        }
        foreach ($related as &$item) {
            $item['display_price'] = number_format((float)$item['price'], 2, '.', '');
            $item['display_market_price'] = (float)$item['market_price'] > 0 ? number_format((float)$item['market_price'], 2, '.', '') : '';
        }
        unset($item);

        $this->view->assign(compact('product', 'images', 'galleryImages', 'skus', 'related'));
        return $this->view->fetch();
    }
}
