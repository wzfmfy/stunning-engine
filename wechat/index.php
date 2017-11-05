<?php

/**
 * @Author: jsy135135
 * @email:732677288@qq.com
 * @Date:   2017-10-31 16:01:29
 * @Last Modified by:   jsy135135
 * @Last Modified time: 2017-10-31 16:15:57
 */
// 引入类文件
require './wechat.class.php';
$wechat = new Wechat();
// 校验操作
if($_GET['echostr']){
  $wechat->valid();
}else{
  // 调用消息管理方法
  $wechat->responseMsg();
}
