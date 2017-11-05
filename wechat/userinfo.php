<?php

/**
 * @Author: jsy135135
 * @email:732677288@qq.com
 * @Date:   2017-11-03 16:55:16
 * @Last Modified by:   jsy135135
 * @Last Modified time: 2017-11-03 17:17:16
 */
// 引入类
require './wechat.class.php';
// 1.通过用户点击确认登录，获取到临时code值
// echo $_GET['code'];
$wechat = new Wechat();
// 2.获取access_token及其openid
$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$wechat->appid.'&secret='.$wechat->appsecret.'&code='.$_GET['code'].'&grant_type=authorization_code';
$content = $wechat->request($url);
// var_dump($content);die();
$access_token = json_decode($content)->access_token;
$openid = json_decode($content)->openid;
//3.通过openid获取基本信息
$url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
$content = $wechat->request($url);
$content = json_decode($content,true);
echo '<pre>';
var_dump($content);