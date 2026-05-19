<?php

namespace app\index\service;

use fast\Random;
use think\Cookie;
use think\Db;

class ShopAuthService
{
    public function currentUser()
    {
        $uid = (int)Cookie::get('shop_uid');
        $token = (string)Cookie::get('shop_token');
        if (!$uid || $token === '') {
            return null;
        }

        $user = Db::name('shop_user')->where('id', $uid)->where('status', 'normal')->find();
        if (!$user || !hash_equals((string)$user['token'], $token)) {
            $this->logout();
            return null;
        }

        return $user;
    }

    public function register($username, $password, $joinip = '')
    {
        $username = trim((string)$username);
        if (Db::name('shop_user')->where('username', $username)->whereNull('deletetime')->find()) {
            throw new \Exception(__('Username already exists'));
        }

        $now = time();
        $salt = Random::alnum();
        $token = Random::uuid();
        $id = Db::name('shop_user')->insertGetId([
            'level_id'     => $this->getDefaultLevelId(),
            'username'     => $username,
            'nickname'     => $username,
            'password'     => $this->encryptPassword($password, $salt),
            'salt'         => $salt,
            'email'        => '',
            'mobile'       => '',
            'avatar'       => '/assets/img/avatar.png',
            'money'        => '0.00',
            'frozen_money' => '0.00',
            'score'        => 0,
            'pay_password' => '',
            'pay_salt'     => '',
            'joinip'       => $joinip,
            'loginip'      => $joinip,
            'jointime'     => $now,
            'logintime'    => $now,
            'token'        => $token,
            'status'       => 'normal',
            'createtime'   => $now,
            'updatetime'   => $now,
        ]);

        $user = Db::name('shop_user')->where('id', $id)->find();
        $this->setLoginCookie($user);
        return $user;
    }

    public function login($account, $password, $keeplogin = false, $loginip = '')
    {
        $account = trim((string)$account);
        $user = Db::name('shop_user')
            ->where('status', 'normal')
            ->whereNull('deletetime')
            ->where(function ($query) use ($account) {
                $query->where('username', $account)
                    ->whereOr('email', $account)
                    ->whereOr('mobile', $account);
            })
            ->find();
        if (!$user || $user['password'] !== $this->encryptPassword($password, $user['salt'])) {
            throw new \Exception(__('Account or password is incorrect'));
        }

        $now = time();
        $token = Random::uuid();
        Db::name('shop_user')->where('id', $user['id'])->update([
            'prevtime'     => $user['logintime'],
            'logintime'    => $now,
            'loginip'      => $loginip,
            'loginfailure' => 0,
            'token'        => $token,
            'updatetime'   => $now,
        ]);

        $user = Db::name('shop_user')->where('id', $user['id'])->find();
        $this->setLoginCookie($user, $keeplogin ? 30 * 86400 : 0);
        return $user;
    }

    public function changePassword($userId, $oldpassword, $newpassword)
    {
        $user = Db::name('shop_user')->where('id', $userId)->where('status', 'normal')->find();
        if (!$user) {
            throw new \Exception(__('User does not exist'));
        }
        if ($user['password'] !== $this->encryptPassword($oldpassword, $user['salt'])) {
            throw new \Exception(__('Old password is incorrect'));
        }

        $salt = Random::alnum();
        Db::name('shop_user')->where('id', $user['id'])->update([
            'password'   => $this->encryptPassword($newpassword, $salt),
            'salt'       => $salt,
            'token'      => '',
            'updatetime' => time(),
        ]);
        $this->logout();
    }

    public function logout()
    {
        $uid = (int)Cookie::get('shop_uid');
        if ($uid > 0) {
            Db::name('shop_user')->where('id', $uid)->update([
                'token'      => '',
                'updatetime' => time(),
            ]);
        }

        Cookie::delete('shop_uid');
        Cookie::delete('shop_token');
        foreach (['shop_uid', 'shop_token'] as $name) {
            setcookie($name, '', time() - 3600, '/');
            setcookie($name, '', time() - 3600, '/index');
            setcookie($name, '', time() - 3600, '/admin');
        }
    }

    protected function setLoginCookie($user, $expire = 0)
    {
        Cookie::set('shop_uid', $user['id'], $expire);
        Cookie::set('shop_token', $user['token'], $expire);
    }

    protected function encryptPassword($password, $salt)
    {
        return md5(md5($password) . $salt);
    }

    protected function getDefaultLevelId()
    {
        $level = Db::name('shop_user_level')->where('is_default', 1)->where('status', 'normal')->order('level asc,id asc')->find();
        return $level ? (int)$level['id'] : 1;
    }
}
