<?php
header('Content-type:text/html;charset=utf-8');
/**
 * @Author: jsy135135
 * @email:732677288@qq.com
 * @Date:   2017-10-31 16:01:23
 * @Last Modified by:   jsy135135
 * @Last Modified time: 2017-11-03 16:53:14
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
// $wechat->getUserList();
// 获取素材
// $wechat->getFile('HHz-VMcH0lleQHSDgzaU6USgOIEEbcWbGy7ezVeEAm7XkMqWnAA90uEJim_RXTzl');
// $wechat->getFile('6Yl8Wf08VoKgTqqYrLHAVwOgjeIGqVUDHsGE_l32ODw0TxWSCvc-Cex4vtbuPfn7');
// 上传素材
// $wechat->upLoadFile();
// 创建菜单
// $wechat->createMenu();
// 查看菜单
// $wechat->showMenu();
// 删除菜单
// $wechat->delMenu();
// lbs
// $wechat->amapLBS();
// 客服发送接口
$wechat->customSend();
// openID列表群发接口
// $wechat->sendAll();