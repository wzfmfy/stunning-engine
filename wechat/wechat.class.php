<?php

/**
 * @Author: jsy135135
 * @email:732677288@qq.com
 * @Date:   2017-10-31 16:01:00
 * @Last Modified by:   jsy135135
 * @Last Modified time: 2017-11-03 17:29:45
 */

// 引入配置文件
require './wechat.cfg.php';
class Wechat
{
    // 构造方法
    public function __construct()
    {
      $this->token = TOKEN;
      $this->appid = APPID;
      $this->appsecret = APPSECRET;
      $this->textTpl = "<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[%s]]></MsgType>
        <Content><![CDATA[%s]]></Content>
        <FuncFlag>0</FuncFlag>
        </xml>";
      $this->itemTpl = "<item>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
        <PicUrl><![CDATA[%s]]></PicUrl>
        <Url><![CDATA[%s]]></Url>
        </item>";
      $this->newsTpl = "<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[news]]></MsgType>
        <ArticleCount>%s</ArticleCount>
        <Articles>%s
        </Articles>
        </xml>";
      $this->imgTpl = "<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[image]]></MsgType>
        <Image>
        <MediaId><![CDATA[%s]]></MediaId>
        </Image>
        </xml>";
      $this->musicTpl = "<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[music]]></MsgType>
        <Music>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
        <MusicUrl><![CDATA[%s]]></MusicUrl>
        <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
        <ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
        </Music>
        </xml>";
    }
    // 验证方法
    public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if ($this->checkSignature()) {
            echo $echoStr;
            exit;
        }
    }
    // 消息管理
    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        //extract post data
        if (!empty($postStr)) {
            /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
              the best way is to check the validity of xml by yourself */
            libxml_disable_entity_loader(true);
            // file_put_contents('./text.txt', $postStr);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            // 根据不同的消息类型，分配到不同的处理方法
            switch ($postObj->MsgType) {
              // 文本消息处理
              case 'text':
                $this->doText($postObj);
                break;
              // 图片消息处理
              case 'image':
                $this->doImage($postObj);
                break;
              // 语音消息处理
              case 'voice':
                $this->doVoice($postObj);
                break;
              // 位置消息处理
              case 'location':
                $this->doLocation($postObj);
                break;
              // 事件消息处理
              case 'event':
                $this->doEvent($postObj);
                break;
              default:
                # code...
                break;
            }
        }
    }
    // 校验签名
    private function checkSignature()
    {
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }

        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = $this->token;
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }
    // 文本消息接收
    private function doText($postObj)
    {
      // 如果有识别结果，namekeyword就是识别结果
      if(empty($postObj->Recognition)){
        $keyword = trim($postObj->Content);
      }else{
        $keyword = $postObj->Recognition;
      }
      if (!empty($keyword)) {
        // 通过新闻关键字回复图文消息
        if(strpos($keyword, '新闻') !== false ){
          $this->sendNews($postObj);
          exit();
        }elseif ($keyword === '图片') {
          $this->sendPic($postObj);
          exit();
        }elseif ($keyword === '歌曲') {
          $this->sendMusic($postObj);
          exit();
        }
          // $contentStr = "Welcome to wechat world!";
          // $contentStr = "你好!我是php60的微信公众号";
          $url = 'http://api.qingyunke.com/api.php?key=free&appid=0&msg='.$keyword;
          $content = file_get_contents($url);
          $contentStr = json_decode($content)->content;
          $contentStr = str_replace("{br}", "\r", $contentStr);
          $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), 'text', $contentStr);
          file_put_contents('./debug.txt',$resultStr);
          echo $resultStr;
      }
    }
    // 图片消息接收
    private function doImage($postObj)
    {
      // 图片的url地址传回去
      // $contentStr = $postObj->PicUrl;
      // 拼接回复xml
      // $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), 'text', $contentStr);
      // echo $resultStr;
      // 回复图片方法
      $this->sendPic($postObj);
    }
    // 语音消息接收
    private function doVoice($postObj)
    {
      // 语音的mediaid传回
      // $contentStr = $postObj->MediaId;
      // 获取语音识别结果
      // $contentStr = $postObj->Recognition;
      // 拼接回复xml
      // $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), 'text', $contentStr);
      // echo $resultStr;
      // 调用到文本处理
      $this->doText($postObj);
    }
    // 地理位置消息处理方法
    private function doLocation($postObj)
    {
      // $contentStr = '您所在位置的经度：'.$postObj->Location_Y.',纬度：'.$postObj->Location_X;
      $location = $postObj->Location_Y.','.$postObj->Location_X;
      $contentStr = $this->amapLBS($location);
      // 拼接回复模板
      $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), 'text', $contentStr);
      echo $resultStr;
    }
    // 发送请求方法
    public function request($url,$https=true,$method='get',$data=null)
    {
      // 1.curl初始化
      $ch = curl_init($url);
      // 2.设置请求参数
      // 设置响应信息不直接输出，以文件流的返回
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      // 判断请求发送的协议
      if($https === true){
        // https
        // 绕过ssl证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
      }
      // 判断请求方式
      if($method === 'post'){
        // post
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
      }
      // 3.发送请求
      $content = curl_exec($ch);
      // 4.关闭资源
      curl_close($ch);
      // 返回响应数据
      return $content;
    }
    // 获取access_token
    public function getAccessToken()
    {
      // 判断是否有缓存
      $redis = new Redis();
      $redis->connect('127.0.0.1',6379);
      $access_token = $redis->get('access_token');
      if($access_token === false){
        // 1.url
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->appid.'&secret='.$this->appsecret;
        // 2.请求方式
        // 3.发送请求
        $content = $this->request($url);
        // 4.处理返回值
        $content = json_decode($content);
        $access_token = $content->access_token;
        $redis->set('access_token',$access_token);
        $redis->setTimeout('access_token',7000);
      }
      return $access_token;
    }
    // 获取access_token通过文件缓存
    public function getAccessTokenByFile()
    {
      // 判断是否有缓存
      $fileName = './access_token';
      // 没有缓存或者过期了
      if(!file_exists($fileName) || time()- filemtime($fileName) > 7000){
        // 1.url
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->appid.'&secret='.$this->appsecret;
        // 2.请求方式
        // 3.发送请求
        $content = $this->request($url);
        // 4.处理返回值
        $content = json_decode($content);
        $access_token = $content->access_token;
        // 缓存到文件
        file_put_contents($fileName, $access_token);
      }else{
        $access_token = file_get_contents($fileName);
      }
      echo $access_token;
    }
    // 获取二维码ticket
    public function getTicket($scene_id,$expire_seconds=604800,$tmp=true)
    {
      // 1.url
      $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$this->getAccessToken();
      // 2.请求方式
      if($tmp === true){
        // 临时
        $data = '{"expire_seconds": '.$expire_seconds.', "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": '.$scene_id.'}}}';
      }else{
        // 永久
        $data = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": '.$scene_id.'}}}';
      }
      // 3.发送请求
      // public function request($url,$https=true,$method='get',$data=null)
      $content = $this->request($url,true,'post',$data);
      // 4.处理返回值
      $ticket = json_decode($content)->ticket;
      return $ticket;
    }
    // 通过ticket获取二维码
    public function getQRCode($scene_id)
    {
      // 1.url
      $url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$this->getTicket($scene_id);
      // 2.请求方式
      // 3.发送请求
      $content = $this->request($url);
      // 4.处理返回值
      echo file_put_contents('./qrcode.jpg',$content);
    }
    // 事件消息处理方法
    public function doEvent($postObj)
    {
        // 不同的事件类型进行不同的方法处理
        switch ($postObj->Event) {
          // 关注事件处理 包含未关注扫描二维码事件
          case 'subscribe':
            $this->doSubscribe($postObj);
            break;
          // 未关注事件处理
          case 'unsubscribe':
            $this->doUnsubscribe($postObj);
            break;
          // 已关注扫描二维码事件处理
          case 'SCAN':
            $this->doscan($postObj);
            break;
          // 自定义菜单点击事件
          case 'CLICK':
            $this->doClick($postObj);
            break;
          default:
            # code...
            break;
        }
    }
    // 关注事件  未关注扫描二维码事件
    public function doSubscribe($postObj)
    {
      // 通过检测是否存在EventKey
      // 确实是关注事件，还是未关注扫描二维码事件
      if(!empty($postObj->EventKey)){
        // 未关注扫描二维码事件,存在EventKey 存在二维码的场景值id
        // 回复接收到的场景值id
        $contentStr = '感谢关注,您参加的活动id为:'.$postObj->EventKey;
      }else{
        $contentStr = '感谢关注!!!';
      }
      $resultStr = sprintf($this->textTpl,$postObj->FromUserName,$this->ToUserName,time(),'text',$contentStr);
      echo $resultStr;
    }
    // 已关注扫描二维码事件
    public function doscan($postObj)
    {
      $contentStr = '您已经是老用户了,参加的活动是'.$postObj->EventKey;
      $resultStr = sprintf($this->textTpl,$postObj->FromUserName,$postObj->ToUserName,time(),'text',$contentStr);
      echo $resultStr;
    }
    // 取消关注事件
    public function doUnsubscribe($postObj)
    {
      // 用来记录用户取消的相关信息
      // 解绑删除用户的相关信息
      $openID = $postObj->FromUserName;
      $postObj->CreateTime;
      file_put_contents('./unsubscribe.txt',date("Y-m-d H:i:s").'###'.$openID,FILE_APPEND);
    }
    // 获取用户openID列表
    public function getUserList()
    {
      // 1.url
      $url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$this->getAccessToken();
      // 2.请求方式
      // 3.发送请求
      $content = $this->request($url);
      // 4.处理返回值
      // {"total":2,"count":2,"data":{"openid":["","OPENID1","OPENID2"]},"next_openid":"NEXT_OPENID"}
      $content = json_decode($content);
      // echo '<pre>';
      // var_dump($content);
      header("Content-type:text/html;charset=utf-8");
      echo '用户关注数为:'.$content->total.'<br />';
      echo '本次获取数为:'.$content->count.'<br />';
      echo '用户列表<br />';
      foreach ($content->data->openid as $key => $value) {
        echo ($key+1).'###<a href="http://localhost/wechat60/do2.php?openid='.$value.'">'.$value.'</a><br />';
      }
    }
    // 通过openID获取用户基本信息
    public function getUserInfo($openid)
    {
      // 1.url
      $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->getAccessToken().'&openid='.$openid.'&lang=zh_CN ';
      // 2.请求方式
      // 3.发送请求
      $content = $this->request($url);
      // 4.处理返回值
      $content = json_decode($content);
      // 输出用户信息
      switch ($content->sex) {
        case '1':
          $sex = '男';
          break;
        case '2':
          $sex = '女';
          break;
        default:
          $sex = '未知';
          break;
      }
      header('Content-type:text/html;charset=utf-8');
      echo '昵称:'.$content->nickname.'<br />';
      echo '性别:'.$sex.'<br />';
      echo '省份:'.$content->province.'<br />';
      echo '<img src="'.$content->headimgurl.'" style="width:150px;" />';
    }
    // 通过media_id获取素材
    public function getFile($media_id)
    {
      // 1.url
      $url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$this->getAccessToken().'&media_id='.$media_id;
      // 2.请求方式
      // 3.发送请求
      $content = $this->request($url);
      // 4.处理返回值
      // echo file_put_contents('./1.amr',$content);
      echo file_put_contents('./1.jpg',$content);
    }
    // 上传素材
    public function upLoadFile()
    {
      // 1.url
      // $url = 'https://api.weixin.qq.com/cgi-bin/media/upload?access_token='.$this->getAccessToken().'&type=image';
      $url = 'https://api.weixin.qq.com/cgi-bin/media/upload?access_token='.$this->getAccessToken().'&type=video';
      // $path = urlencode('桌面');
      // 2.请求方式
      $data = array(
        // 'media' => '@D:\phpStudy\WWW\wechat60\qrcode.jpg'
        'media' => '@C:\Users\heart\Desktop\7827668989818c673b62b6cfb19b488a.mp4'
        // 'media' => '@C:\Users\heart\Desktop\\'.$path.'\images\007.jpg'
      );
      // 3.发送请求
      $content = $this->request($url,true,'post',$data);
      var_dump($content);
      // 4.处理返回值
      // $media_id = json_decode($content)->media_id;
      // echo $media_id;
    }
    // 创建自定义菜单
    public function createMenu()
    {
      // 1.url
      $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->getAccessToken();
      // 2.请求方式
      $data = '{
              "button":[
              {
                   "type":"click",
                   "name":"今日新闻",
                   "key":"news"
               },
               {
                    "name":"php60",
                    "sub_button":[
                    {
                        "type":"view",
                        "name":"百度",
                        "url":"http://www.baidu.com/"
                    },
                    {
                        "type":"view",
                        "name":"淘宝",
                        "url":"http://m.taobao.com/"
                    },
                     {
                        "type": "scancode_push",
                        "name": "扫码推事件",
                        "key": "rselfmenu_0_1",
                        "sub_button": [ ]
                    }]
                }]
          }';
      // 3.发送请求
      $content = $this->request($url,true,'post',$data);
      // 4.处理返回值
      $content = json_decode($content);
      // var_dump($content);
      if($content->errmsg === 'ok'){
        echo '创建自定义菜单成功!';
      }else{
        echo '创建失败!<br />';
        echo '错误码为'.$content->errcode;
      }
    }
    // 查看菜单
    public function showMenu()
    {
      // 1.url
      $url = 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token='.$this->getAccessToken();
      // 2.请求方式
      // 3.发送请求
      $content = $this->request($url);
      // 4.处理返回值
      var_dump($content);
    }
    // 删除菜单
    public function delMenu(){
      // 1.url
      $url = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token='.$this->getAccessToken();
      // 2.请求方式
      // 3.发送请求
      $content = $this->request($url);
      // 4.处理返回值
      $content = json_decode($content);
      if($content->errmsg === 'ok'){
        echo '删除自定义菜单成功!';
      }else{
        echo '删除失败!<br />';
        echo '错误码为'.$content->errcode;
      }
    }
    // 发送图文方法
    public function sendNews($postObj)
    {
      // 模拟数据  二维数据
      $data = array(
        array(
          'Title' => '中央军委举行晋升上将军衔仪式',
          'Description' => '（原标题： 中央军委举行晋升上将军衔仪式 习近平向晋升上将军衔的张升民颁发命令状并表示祝贺）',
          'PicUrl' =>'http://cms-bucket.nosdn.127.net/catchpic/1/14/14cdcfe53e5beec18cdc7ffd6afbd061.jpg?imageView&thumbnail=550x0',
          'Url' =>'http://news.163.com/17/1102/20/D28VKSVE000189FH.html'
        ),
        array(
          'Title' => '金领冠母爱计划：用"母爱"托起折翼的天使',
          'Description' => '今年5月20日，伊利集团金领冠联合春晖博爱儿童救助基金会发起“金领冠母爱计划”',
          'PicUrl' =>'http://imgsize.ph.126.net/?imgurl=http://img4.cache.netease.com/news/2017/7/17/201707171107301658a.png_250x166x1x85.jpg',
          'Url' =>'http://gongyi.163.com/17/0717/11/CPHSJEOQ009363EC.html'
        ),
        array(
          'Title' => '2千元买保温箱养蜗牛 家长吐槽亲子作业成负担',
          'Description' => '记者走访发现，现在很多中小学都会布置各种亲子作业，制作贺卡、手抄报之类算是简单的，养乌龟、养蚕、养蜗牛，还有手工制作保龄球、房子、汽车……花样百出，虽然名为“亲子作业”，实际大多数都是由家长完成，有家长表示已成下班后的负担',
          'PicUrl' =>'http://cms-bucket.nosdn.127.net/b48646c2d82142b5b174c5cadda7cc4920171103095801.png?imageView&thumbnail=140y88&quality=85',
          'Url' =>'http://news.163.com/17/1103/09/D2ACK7J7000187VE.html'
        ),
      );
      // 拼多条新闻
      $items = '';
      foreach ($data as $key => $value) {
        $items .= sprintf($this->itemTpl,$value['Title'],$value['Description'],$value['PicUrl'],$value['Url']);
      }
      // 拼接新闻模板
      // $key+1 代表新闻条数
      $resultStr = sprintf($this->newsTpl,$postObj->FromUserName,$postObj->ToUserName,time(),$key+1,$items);
      // file_put_contents('./news.xml',$resultStr);
      echo $resultStr;
    }
    // 自定菜单事件
    public function doClick($postObj)
    {
      // 根据不同的key值，对应返回不同操作
      switch ($postObj->EventKey) {
        case 'news':
          // 发送图文
          $this->sendNews($postObj);
          break;
        default:
          # code...
          break;
      }
    }
    // 发送图片方法
    public function sendPic($postObj)
    {
      $MediaId = $postObj->MediaId;
      // 如果没有$MediaId就使用下面这个
      if(empty($MediaId)){
        $MediaId = '6Yl8Wf08VoKgTqqYrLHAVwOgjeIGqVUDHsGE_l32ODw0TxWSCvc-Cex4vtbuPfn7';
      }
      echo sprintf($this->imgTpl,$postObj->FromUserName,$postObj->ToUserName,time(),$MediaId);
    }
    // 发送音乐消息
    public function sendMusic($postObj)
    {
      // 模拟数据
      $Title = '带你去旅行';
      $Description = '校长';
      $MusicUrl = 'http://47.88.217.149/wechat60/1.mp3';
      $HQMusicUrl = $MusicUrl;
      $ThumbMediaId = '6Yl8Wf08VoKgTqqYrLHAVwOgjeIGqVUDHsGE_l32ODw0TxWSCvc-Cex4vtbuPfn7';
      echo sprintf($this->musicTpl,$postObj->FromUserName,$postObj->ToUserName,time(),$Title,$Description,$MusicUrl,$HQMusicUrl,$ThumbMediaId);
    }
    // 高德地图周边搜索设施
    public function amapLBS($location=null)
    {
      if(empty($location)){
        $location = '116.628440,40.162825';
      }
      // file_put_contents('./amap.txt',$location);
      // 1.url
      $url = 'http://restapi.amap.com/v3/place/around?key=e7e04652fe31a5a8758735e521961550&location='.$location.'&output=xml&radius=10000&types=餐饮';
      // 2.请求方式
      // 3.发送请求
      $content = $this->request($url,false);
      // 4.处理返回值
      $content = simplexml_load_string($content);
      $content = $content->pois->poi;
      // file_put_contents('./lbs.txt',$content->name);
      $contentStr = "离您最近的餐饮:\r名称:$content->name\r类型:$content->type\r地址:$content->address\r电话:$content->tel";
      return $contentStr;
    }
    // 客服消息发送
    public function customSend()
    {
      // 1.url
      $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$this->getAccessToken();
      // 2.请求方式
      // $data = '{
      //     "touser":"oGMVlw2BFUYpQ6mHUQaD-ukJTVq4",
      //     "msgtype":"voice",
      //     "voice":
      //     {
      //       "media_id":"HHz-VMcH0lleQHSDgzaU6USgOIEEbcWbGy7ezVeEAm7XkMqWnAA90uEJim_RXTzl"
      //     }
      // }';
      // $data = '{
      //     "touser":"oGMVlw2BFUYpQ6mHUQaD-ukJTVq4",
      //     "msgtype":"video",
      //     "video":
      //     {
      //       "media_id":"yRJe5xqflj8PSPvTz2Q0JNiDkxROCKUjMc7v4i-4E7kZ5fZHcvTd0tPX6a0uX5eb",
      //       "thumb_media_id":"6Yl8Wf08VoKgTqqYrLHAVwOgjeIGqVUDHsGE_l32ODw0TxWSCvc-Cex4vtbuPfn7",
      //       "title":"php60上课了!",
      //       "description":"我们在上微信公众平台开发课"
      //     }
      // }';
      // $data = '{
      //     "touser":"oGMVlw2BFUYpQ6mHUQaD-ukJTVq4",
      //     "msgtype":"text",
      //     "text":
      //     {
      //          "content":"https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx4d9d3e8cdc505f85&redirect_uri=http://47.88.217.149/wechat60/userinfo.php&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect"
      //     }
      //    }';
      $data = '{
          "touser":"oGMVlw2BFUYpQ6mHUQaD-ukJTVq4",
          "msgtype":"text",
          "text":
          {
               "content":"http://47.88.217.149/wechat60/jssdk/sample.php"
          }
         }';
            // 3.发送请求
      $content = $this->request($url,true,'post',$data);
      // 4.处理返回值
      var_dump($content);
    }
    // 根据openID群发
    public function sendAll()
    {
      // 1.url
      $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token='.$this->getAccessToken();
      // 2.请求方式
      $data = '{
           "touser":["oGMVlw2DH2ffQGJBLMY4XreV00WM","oGMVlw0d1YcThU8ZeOF54pIS0d6c","oGMVlw2BFUYpQ6mHUQaD-ukJTVq4"],
           "msgtype": "text",
           "text": { "content": "你好，php60！"}
      }';
      // 3.发送请求
      $content = $this->request($url,true,'post',$data);
      // 4.处理返回值
      var_dump($content);
    }
}