# yii1.x-wechat-pay

yii1公众号微信支付，2014写过一版，发现不能用了，现在更新为最新版
只保留最核心的公众号支付，扫码支付等等已经移除。
注意*HTTP_RAW_POST_DATA在php7无法获取post数据，改成php://input*

使用说明。
在支付模块引入

```php
$this->setImport([
  'application.extensions.pay.*'
]);
```

# One
import pay extension
# two
create pay module
# three
send pay request


/views/weixin/index.php is the send request page
about specific please to consult 
https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=7_1
