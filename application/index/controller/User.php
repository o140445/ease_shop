<?php

namespace app\index\controller;

use addons\wechat\model\WechatCaptcha;
use app\common\controller\Frontend;
use app\common\library\Ems;
use app\common\library\Sms;
use app\common\model\Attachment;
use app\index\service\ShopAuthService;
use think\Config;
use think\Cookie;
use think\Db;
use think\Lang;
use think\Validate;

/**
 * User center
 */
class User extends Frontend
{
    protected $layout = 'default';
    protected $noNeedLogin = ['login', 'register', 'third', 'logout'];
    protected $noNeedRight = ['*'];
    protected $shopLang = 'ar';

    public function _initialize()
    {
        parent::_initialize();

        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'), '/');
        }

        $shopLangs = ['ar', 'en', 'zh-cn', 'ja', 'es'];
        $shopLang = $this->request->param('lang', 'ar');
        $shopLang = in_array($shopLang, $shopLangs) ? $shopLang : 'ar';
        $this->shopLang = $shopLang;
        Cookie::set('shop_lang', $shopLang);
        Lang::load(APP_PATH . 'index' . DS . 'lang' . DS . $shopLang . DS . 'shop.php');
        $shopUser = (new ShopAuthService())->currentUser();
        $cartCount = $shopUser ? (int)Db::name('shop_cart')->where('user_id', $shopUser['id'])->sum('quantity') : 0;
        $this->view->assign([
            'shopLang' => $shopLang,
            'cartCount' => $cartCount,
            'shopUser' => $shopUser,
        ]);
    }

    /**
     * User center
     */
    public function index()
    {
        $this->redirect('center/index', ['lang' => $this->shopLang]);
    }

    /**
     * Register user
     */
    public function register()
    {
        $url = $this->request->request('url', '', 'url_clean');
        if ((new ShopAuthService())->currentUser()) {
            $this->success(__('You\'ve logged in, do not login again'), $url ? $url : url('center/index', ['lang' => $this->shopLang]));
        }
        if ($this->request->isPost()) {
            $username = trim($this->request->post('username', ''));
            $password = $this->request->post('password', '', null);
            $email = '';
            $mobile = '';
            $captcha = $this->request->post('captcha');
            $token = $this->request->post('__token__');
            $rule = [
                'username'  => 'require|length:3,30',
                'password'  => 'require|length:6,30',
                '__token__' => 'require|token',
            ];

            $msg = [
                'username.require' => 'Username can not be empty',
                'username.length'  => 'Username must be 3 to 30 characters',
                'password.require' => 'Password can not be empty',
                'password.length'  => 'Password must be 6 to 30 characters',
            ];
            $data = [
                'username'  => $username,
                'password'  => $password,
                '__token__' => $token,
            ];
            // Captcha
            $captchaResult = true;
            $captchaType = $this->getRegisterCaptchaType();
            if ($captchaType) {
                if ($captchaType == 'mobile') {
                    $captchaResult = Sms::check($mobile, $captcha, 'register');
                } elseif ($captchaType == 'email') {
                    $captchaResult = Ems::check($email, $captcha, 'register');
                } elseif ($captchaType == 'wechat') {
                    $captchaResult = WechatCaptcha::check($captcha, 'register');
                } elseif ($captchaType == 'text') {
                    $captchaResult = \think\Validate::is($captcha, 'captcha');
                }
            }
            if (!$captchaResult) {
                $this->error(__('Captcha is incorrect'));
            }
            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null, ['token' => $this->request->token()]);
            }
            try {
                (new ShopAuthService())->register($username, $password, $this->request->ip());
                $this->success(__('Sign up successful'), $url ? $url : url('center/index', ['lang' => $this->shopLang]));
            } catch (\Exception $e) {
                $this->error(__($e->getMessage()), null, ['token' => $this->request->token()]);
            }
        }
        // Check request source.
        $referer = $this->request->server('HTTP_REFERER', '', 'url_clean');
        if (!$url && $referer && !preg_match("/(user\/login|user\/register|user\/logout)/i", $referer)) {
            $url = $referer;
        }
        $this->view->assign('captchaType', $this->getRegisterCaptchaType());
        $this->view->assign('url', $url);
        $this->view->assign('title', __('Register'));
        return $this->view->fetch();
    }

    protected function getRegisterCaptchaType()
    {
        if (!Config::get('fastadmin.login_captcha')) {
            return false;
        }
        $captchaType = Config::get('fastadmin.user_register_captcha');
        if ($captchaType === false || $captchaType === 0 || $captchaType === '0' || strtolower((string)$captchaType) === 'false') {
            return false;
        }
        return $captchaType;
    }

    /**
     * User login
     */
    public function login()
    {
        $url = $this->request->request('url', '', 'url_clean');
        if ((new ShopAuthService())->currentUser()) {
            $this->success(__('You\'ve logged in, do not login again'), $url ?: url('center/index', ['lang' => $this->shopLang]));
        }
        if ($this->request->isPost()) {
            $account = $this->request->post('account');
            $password = $this->request->post('password', '', null);
            $keeplogin = (int)$this->request->post('keeplogin');
            $captcha = $this->request->post('captcha');
            $token = $this->request->post('__token__');
            $rule = [
                'account'   => 'require|length:3,50',
                'password'  => 'require|length:6,30',
                '__token__' => 'require|token',
            ];

            $msg = [
                'account.require'  => 'Account can not be empty',
                'account.length'   => 'Account must be 3 to 50 characters',
                'password.require' => 'Password can not be empty',
                'password.length'  => 'Password must be 6 to 30 characters',
                'captcha.require'  => 'Captcha can not be empty',
                'captcha.captcha'  => 'Captcha is incorrect',
            ];
            $data = [
                'account'   => $account,
                'password'  => $password,
                '__token__' => $token,
            ];
            if (Config::get('fastadmin.login_captcha')) {
                $rule['captcha'] = 'require|captcha';
                $data['captcha'] = $captcha;
            }
            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null, ['token' => $this->request->token()]);
            }
            try {
                (new ShopAuthService())->login($account, $password, (bool)$keeplogin, $this->request->ip());
                $this->success(__('Logged in successful'), $url ? $url : url('center/index', ['lang' => $this->shopLang]));
            } catch (\Exception $e) {
                $this->error(__($e->getMessage()), null, ['token' => $this->request->token()]);
            }
        }
        // Check request source.
        $referer = $this->request->server('HTTP_REFERER', '', 'url_clean');
        if (!$url && $referer && !preg_match("/(user\/login|user\/register|user\/logout)/i", $referer)) {
            $url = $referer;
        }
        $this->view->assign('url', $url);
        $this->view->assign('loginCaptcha', Config::get('fastadmin.login_captcha'));
        $this->view->assign('title', __('Login'));
        return $this->view->fetch();
    }

    /**
     * Logout
     */
    public function logout()
    {
        $auth = new ShopAuthService();
        if ($this->request->isPost()) {
            $this->token();
            $auth->logout();
            $this->success(__('Logout successful'), url('user/login', ['lang' => $this->shopLang]));
        }

        $auth->logout();
        $this->redirect('user/login', ['lang' => $this->shopLang]);
    }

    /**
     * Profile
     */
    public function profile()
    {
        $this->view->assign('title', __('Profile'));
        return $this->view->fetch();
    }

    /**
     * Change password
     */
    public function changepwd()
    {
        if ($this->request->isPost()) {
            $oldpassword = $this->request->post("oldpassword", '', null);
            $newpassword = $this->request->post("newpassword", '', null);
            $renewpassword = $this->request->post("renewpassword", '', null);
            $token = $this->request->post('__token__');
            $rule = [
                'oldpassword'   => 'require|regex:\S{6,30}',
                'newpassword'   => 'require|regex:\S{6,30}',
                'renewpassword' => 'require|regex:\S{6,30}|confirm:newpassword',
                '__token__'     => 'token',
            ];

            $msg = [
                'renewpassword.confirm' => __('Password and confirm password don\'t match')
            ];
            $data = [
                'oldpassword'   => $oldpassword,
                'newpassword'   => $newpassword,
                'renewpassword' => $renewpassword,
                '__token__'     => $token,
            ];
            $field = [
                'oldpassword'   => __('Old password'),
                'newpassword'   => __('New password'),
                'renewpassword' => __('Renew password')
            ];
            $validate = new Validate($rule, $msg, $field);
            $result = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null, ['token' => $this->request->token()]);
            }

            $user = (new ShopAuthService())->currentUser();
            if (!$user) {
                $this->error(__('Please login first'), url('user/login', ['lang' => $this->shopLang]));
            }
            try {
                (new ShopAuthService())->changePassword($user['id'], $oldpassword, $newpassword);
                $this->success(__('Reset password successful'), url('user/login'));
            } catch (\Exception $e) {
                $this->error(__($e->getMessage()), null, ['token' => $this->request->token()]);
            }
        }
        $this->view->assign('title', __('Change password'));
        return $this->view->fetch();
    }

    public function attachment()
    {
            // Set input filter.
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            $shopUser = (new ShopAuthService())->currentUser();
            if (!$shopUser) {
                $this->error(__('Please login first'));
            }
            $mimetypeQuery = [];
            $where = [];
            $filter = $this->request->request('filter');
            $filterArr = (array)json_decode($filter, true);
            if (isset($filterArr['mimetype']) && preg_match("/(\/|\,|\*)/", $filterArr['mimetype'])) {
                $this->request->get(['filter' => json_encode(array_diff_key($filterArr, ['mimetype' => '']))]);
                $mimetypeQuery = function ($query) use ($filterArr) {
                    $mimetypeArr = array_filter(explode(',', $filterArr['mimetype']));
                    foreach ($mimetypeArr as $index => $item) {
                        $query->whereOr('mimetype', 'like', '%' . str_replace("/*", "/", $item) . '%');
                    }
                };
            } elseif (isset($filterArr['mimetype'])) {
                $where['mimetype'] = ['like', '%' . $filterArr['mimetype'] . '%'];
            }

            if (isset($filterArr['filename'])) {
                $where['filename'] = ['like', '%' . $filterArr['filename'] . '%'];
            }

            if (isset($filterArr['createtime'])) {
                $timeArr = explode(' - ', $filterArr['createtime']);
                $where['createtime'] = ['between', [strtotime($timeArr[0]), strtotime($timeArr[1])]];
            }
            $search = $this->request->get('search');
            if ($search) {
                $where['filename'] = ['like', '%' . $search . '%'];
            }

            $model = new Attachment();
            $offset = $this->request->get("offset", 0);
            $limit = $this->request->get("limit", 0);
            $total = $model
                ->where($where)
                ->where($mimetypeQuery)
                ->where('user_id', $shopUser['id'])
                ->order("id", "DESC")
                ->count();

            $list = $model
                ->where($where)
                ->where($mimetypeQuery)
                ->where('user_id', $shopUser['id'])
                ->order("id", "DESC")
                ->limit($offset, $limit)
                ->select();
            $cdnurl = preg_replace("/\/(\w+)\.php$/i", '', $this->request->root());
            foreach ($list as $k => &$v) {
                $v['fullurl'] = ($v['storage'] == 'local' ? $cdnurl : $this->view->config['upload']['cdnurl']) . $v['url'];
            }
            unset($v);
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        $mimetype = $this->request->get('mimetype', '');
        $mimetype = substr($mimetype, -1) === '/' ? $mimetype . '*' : $mimetype;
        $this->view->assign('mimetype', $mimetype);
        $this->view->assign("mimetypeList", \app\common\model\Attachment::getMimetypeList());
        return $this->view->fetch();
    }
}
