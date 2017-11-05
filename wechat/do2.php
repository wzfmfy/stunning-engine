<?php

/**
 * @Author: jsy135135
 * @email:732677288@qq.com
 * @Date:   2017-11-01 16:11:08
 * @Last Modified by:   jsy135135
 * @Last Modified time: 2017-11-01 16:11:38
 */
require './wechat.class.php';
$wechat = new Wechat();
// 获取access_token
// $wechat->getAccessToken();
// $wechat->getAccessTokenByFile();
// 获取ticket
// $wechat->getTicket(258);
// 获取二维码
// $wechat->getQRCode(888);
// 获取用户列表
$wechat->getUserInfo($_GET['openid']);