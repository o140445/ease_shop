<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\index\service\ShopAuthService;
use think\Cookie;
use think\Db;
use think\Lang;

class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';
    protected $shopLang = 'ar';
    protected $shopLangs = ['ar', 'en', 'zh-cn', 'ja', 'es'];

    public function _initialize()
    {
        parent::_initialize();

        $lang = $this->request->param('lang', 'ar');
        $lang = in_array($lang, $this->shopLangs) ? $lang : 'ar';
        $this->shopLang = $lang;
        Cookie::set('shop_lang', $lang);
        Lang::load(APP_PATH . 'index' . DS . 'lang' . DS . $lang . DS . 'shop.php');

        $shopUser = $this->getOptionalShopUser();
        $cartCount = $shopUser ? (int)Db::name('shop_cart')->where('user_id', $shopUser['id'])->sum('quantity') : 0;
        $favoriteCount = $shopUser ? (int)Db::name('shop_favorite')->where('user_id', $shopUser['id'])->count() : 0;
        $shopCategories = Db::name('shop_category')
            ->where('status', 'normal')
            ->order('weigh desc,id desc')
            ->select();

        $this->view->assign([
            'shopLang'  => $lang,
            'shopLangs' => $this->getLangOptions(),
            'langLinks' => $this->getLangLinks(),
            'navs'      => $this->getNavs(),
            'shopCategories' => $shopCategories,
            'shopTitle' => $this->getConfig('site_name', $this->site['name'] ?? 'Shop'),
            'shopUser'  => $shopUser,
            'cartCount' => $cartCount,
            'favoriteCount' => $favoriteCount,
        ]);
    }

    public function index()
    {
        $this->view->assign('title', __('Home'));
        $now = time();
        $banners = Db::name('shop_banner')
            ->where('position', 'home')
            ->where('status', 'normal')
            ->order('weigh desc,id desc')
            ->select();
        foreach ($banners as $key => &$banner) {
            if (($banner['starttime'] && $banner['starttime'] > $now) || ($banner['endtime'] && $banner['endtime'] < $now)) {
                unset($banners[$key]);
                continue;
            }
            $banner['href'] = $this->getBannerHref($banner);
        }
        unset($banner);
        $banners = array_values($banners);

        $products = Db::name('shop_product')->alias('product')
            ->join('__SHOP_CATEGORY__ category', 'category.id=product.category_id AND category.status="normal"', 'LEFT')
            ->where('product.status', 'normal')
            ->field('product.*,category.name as category_name,category.weigh as category_weigh')
            ->order('category.weigh desc,category.id asc,product.is_hot desc,product.is_recommend desc,product.weigh desc,product.id desc')
            ->select();

        $categoryProducts = [];
        foreach ($products as &$product) {
            $price = (float)$product['price'];
            $marketPrice = (float)$product['market_price'];
            $product['display_price'] = number_format($price, 2, '.', '');
            $product['display_market_price'] = $marketPrice > 0 ? number_format($marketPrice, 2, '.', '') : '';
            $product['discount_text'] = '';
            if ($marketPrice > $price && $marketPrice > 0) {
                $product['discount_text'] = max(1, (int)round((($marketPrice - $price) / $marketPrice) * 100)) . '% off';
            }

            $categoryId = (int)$product['category_id'];
            if (!isset($categoryProducts[$categoryId])) {
                $categoryProducts[$categoryId] = [
                    'id'       => $categoryId,
                    'name'     => $product['category_name'] ?: __('Uncategorized'),
                    'products' => [],
                ];
            }
            $categoryProducts[$categoryId]['products'][] = $product;
        }
        unset($product);

        $this->view->assign(compact('banners', 'categoryProducts'));
        return $this->view->fetch();
    }

    protected function getBannerHref($banner)
    {
        switch ($banner['link_type']) {
            case 'url':
                return $banner['link_url'] ?: url('product/index', ['lang' => $this->shopLang]);
            case 'product':
                return $banner['link_id'] ? url('product/detail', ['id' => $banner['link_id'], 'lang' => $this->shopLang]) : url('product/index', ['lang' => $this->shopLang]);
            case 'category':
                return $banner['link_id'] ? url('product/index', ['category_id' => $banner['link_id'], 'lang' => $this->shopLang]) : url('product/index', ['lang' => $this->shopLang]);
            case 'notice':
                return $banner['link_id'] ? url('index/notice', ['id' => $banner['link_id'], 'lang' => $this->shopLang]) : url('product/index', ['lang' => $this->shopLang]);
            default:
                return url('product/index', ['lang' => $this->shopLang]);
        }
    }

    public function notice()
    {
        $id = (int)$this->request->param('id');
        $notice = Db::name('shop_notice')->where('id', $id)->where('status', 'normal')->find();
        if (!$notice) {
            $this->error(__('Notice not found'));
        }
        $this->view->assign('title', $notice['title']);
        $this->view->assign(compact('notice'));
        return $this->view->fetch();
    }

    protected function getNavs()
    {
        $navs = Db::name('shop_nav')
            ->where('position', 'home')
            ->where('status', 'normal')
            ->order('weigh desc,id asc')
            ->limit(8)
            ->select();
        if ($navs) {
            foreach ($navs as &$nav) {
                if ($nav['link_url'] === '') {
                    $map = [
                        'Home'          => 'index/index',
                        'Products'      => 'product/index',
                        'Cart'          => 'cart/index',
                        'Member center' => 'center/index',
                    ];
                    $nav['link_url'] = isset($map[$nav['title']]) ? url($map[$nav['title']], ['lang' => $this->shopLang]) : '#';
                }
            }
            unset($nav);
            return $navs;
        }
        return [
            ['title' => __('Home'), 'link_url' => url('index/index', ['lang' => $this->shopLang]), 'link_type' => 'url'],
            ['title' => __('Products'), 'link_url' => url('product/index', ['lang' => $this->shopLang]), 'link_type' => 'url'],
            ['title' => __('Cart'), 'link_url' => url('cart/index', ['lang' => $this->shopLang]), 'link_type' => 'url'],
            ['title' => __('Member center'), 'link_url' => url('center/index', ['lang' => $this->shopLang]), 'link_type' => 'url'],
        ];
    }

    protected function getCurrentShopUser()
    {
        $user = $this->getOptionalShopUser();
        if (!$user) {
            $this->redirect($this->request->domain() . url('user/login', ['lang' => $this->shopLang]) . '?url=' . urlencode($this->request->url(true)));
        }
        return $user;
    }

    protected function getOptionalShopUser()
    {
        return (new ShopAuthService())->currentUser();
    }

    protected function requireShopLogin()
    {
        $user = $this->getOptionalShopUser();
        if (!$user) {
            $this->redirect($this->request->domain() . url('user/login', ['lang' => $this->shopLang]) . '?url=' . urlencode($this->request->url(true)));
        }
        return $user;
    }

    protected function assignCenterBase($user, $active)
    {
        $level = $user['level_id'] ? Db::name('shop_user_level')->where('id', $user['level_id'])->find() : null;
        $centerMenu = [
            ['key' => 'center', 'title' => __('Member center'), 'icon' => 'fa-user-o', 'url' => url('center/index', ['lang' => $this->shopLang])],
            ['key' => 'centerorders', 'title' => __('Order list'), 'icon' => 'fa-list-alt', 'url' => url('center/orders', ['lang' => $this->shopLang])],
            ['key' => 'centeraccount', 'title' => __('Account settings'), 'icon' => 'fa-cog', 'url' => url('center/account', ['lang' => $this->shopLang])],
            ['key' => 'centeraddress', 'title' => __('Shipping address'), 'icon' => 'fa-map-marker', 'url' => url('center/address', ['lang' => $this->shopLang])],
            ['key' => 'centerbank', 'title' => __('Bank card information'), 'icon' => 'fa-credit-card', 'url' => url('center/bank', ['lang' => $this->shopLang])],
            ['key' => 'centerprofile', 'title' => __('Profile information'), 'icon' => 'fa-id-card-o', 'url' => url('center/profile', ['lang' => $this->shopLang])],
            ['key' => 'centerpassword', 'title' => __('Change password'), 'icon' => 'fa-lock', 'url' => url('center/password', ['lang' => $this->shopLang])],
            ['key' => 'centerwallet', 'title' => __('Recharge and withdraw'), 'icon' => 'fa-credit-card', 'url' => url('center/wallet', ['lang' => $this->shopLang])],
        ];
        $centerMenuGroups = [
            [
                'key'   => 'account',
                'title' => __('Account settings'),
                'icon'  => 'fa-cog',
                'keys'  => ['centerprofile', 'centerpassword', 'centerpaypassword', 'centeraddress'],
                'items' => [
                    ['key' => 'centerprofile', 'title' => __('Edit profile'), 'icon' => 'fa-id-card-o', 'url' => url('center/profile', ['lang' => $this->shopLang])],
                    ['key' => 'centerpassword', 'title' => __('Login password'), 'icon' => 'fa-lock', 'url' => url('center/password', ['lang' => $this->shopLang])],
                    ['key' => 'centerpaypassword', 'title' => __('Payment password'), 'icon' => 'fa-key', 'url' => url('center/paypassword', ['lang' => $this->shopLang])],
                    ['key' => 'centeraddress', 'title' => __('Address information'), 'icon' => 'fa-map-marker', 'url' => url('center/address', ['lang' => $this->shopLang])],
                ],
            ],
            [
                'key'   => 'finance',
                'title' => __('Finance settings'),
                'icon'  => 'fa-credit-card',
                'keys'  => ['centerbank', 'centerrecharge', 'centerwithdraw'],
                'items' => [
                    ['key' => 'centerbank', 'title' => __('Bank cards'), 'icon' => 'fa-credit-card', 'url' => url('center/bank', ['lang' => $this->shopLang])],
                    ['key' => 'centerrecharge', 'title' => __('Recharge'), 'icon' => 'fa-plus-circle', 'url' => url('center/wallet', ['wallet_tab' => 'recharge', 'lang' => $this->shopLang])],
                    ['key' => 'centerwithdraw', 'title' => __('Withdraw'), 'icon' => 'fa-arrow-circle-down', 'url' => url('center/wallet', ['wallet_tab' => 'withdraw', 'lang' => $this->shopLang])],
                ],
            ],
        ];
        $this->view->assign([
            'user'             => $user,
            'level'            => $level,
            'centerMenu'       => $centerMenu,
            'centerMenuGroups' => $centerMenuGroups,
            'centerActive'     => $active,
        ]);
    }

    protected function getOrderStats($userId)
    {
        return [
            'unpaid'    => (int)Db::name('shop_order')->where('user_id', $userId)->where('status', 'unpaid')->count(),
            'paid'      => (int)Db::name('shop_order')->where('user_id', $userId)->where('status', 'paid')->count(),
            'shipped'   => (int)Db::name('shop_order')->where('user_id', $userId)->where('status', 'shipped')->count(),
            'completed' => (int)Db::name('shop_order')->where('user_id', $userId)->where('status', 'completed')->count(),
            'returned'  => (int)Db::name('shop_order')->where('user_id', $userId)->where('status', 'returned')->count(),
            'recycled'  => (int)Db::name('shop_order')->where('user_id', $userId)->where('status', 'recycled')->count(),
            'refund'    => (int)Db::name('shop_order')->where('user_id', $userId)->where('status', 'in', ['refunding', 'refunded'])->count(),
        ];
    }

    protected function getConfig($name, $default = '')
    {
        $row = Db::name('shop_config')->where('group', 'basic')->where('name', $name)->where('status', 'normal')->find();
        return $row && $row['value'] !== '' ? $row['value'] : $default;
    }

    protected function getLangOptions()
    {
        return [
            'ar'    => 'العربية',
            'en'    => 'English',
            'zh-cn' => '中文',
            'ja'    => '日本語',
            'es'    => 'Español',
        ];
    }

    protected function getLangLinks()
    {
        $params = $this->request->param();
        $links = [];
        foreach ($this->shopLangs as $lang) {
            $params['lang'] = $lang;
            $links[$lang] = url($this->request->controller() . '/' . $this->request->action(), $params);
        }
        return $links;
    }

}
