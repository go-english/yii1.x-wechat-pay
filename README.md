# yii1.x-webchat-pay

yii1公众号微信支付，2014写过一般，发现不能用了，现在更新为最新版
只保留最核心的公众号支付，扫码支付等等已经移除。
注意HTTP_RAW_POST_DATA在php7无法获取post数据，改成php://input

使用说明。
在支付模块引入
$this->setImport([
  'application.extensions.pay.*'
]);

