<?php

//微信支付逻辑
class WeixinController extends Controller{

    public function actionCall(){
        /* 验权代码 */
        $url_base = 'https://open.weixin.qq.com/connect/oauth2/authorize?';
        $urlObj["appId"] = WxPayConfig::APPID;
        $urlObj["redirect_uri"] = '';
        $urlObj["response_type"] = "code";
        $urlObj["scope"] = "snsapi_base";
        $urlObj["state"] = $_GET['sn']."#wechat_redirec";

        $buff = "";
        foreach ($urlObj as $k => $v)
        {
            if($k != "sign"){
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");

        $this->redirect($url_base.$buff);
    }

    public function actionPay(){
        $order_sn = Yii::app()->request->getParam('state');
        $code = Yii::app()->request->getParam('code');

        /* 验权代码 */
        $urlObj["appId"] = WxPayConfig::APPID;
        $urlObj["secret"] = WxPayConfig::APPSECRET;
        $urlObj["code"] = $code;
        $urlObj["grant_type"] = "authorization_code";
        $bizString = $this->formatBizQueryParaMap($urlObj, false);

        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;

        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 6000);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //运行curl，结果以jason形式返回
        $res = curl_exec($ch);
        if($ch != null)
            curl_close($ch);
        //取出openid
        $data = json_decode($res,true);
        $openid = $data['openid'];
        $access_token = $data['access_token'];


        $tools = new WxJsApiPay();

        $time = time();
        $unifiedOrder = new WxPayUnifiedOrder(); //统一支付接口中，trade_type为JSAPI时，openid为必填参数！
        $unifiedOrder->SetAppid(''); //商户 App ID
        $unifiedOrder->SetMch_id('');
        $unifiedOrder->SetDevice_info('');
        $unifiedOrder->SetNonce_str('');
        $unifiedOrder->SetOpenid($openid);//用户标识
        $unifiedOrder->SetBody('');//商品简要描述
        $unifiedOrder->SetDetail('');//商品描述
        $unifiedOrder->SetOut_trade_no($order_sn);//商品订单编号，不可重复
        $unifiedOrder->SetFee_type('CNY');//商品支付类型
        $unifiedOrder->SetTotal_fee('');
        //商品总金额,交易金额默认为人民币交易，接口中参数支付金额单位为【分】，参数值不能带小数。对账单中的交易金额单位为【元】。
        $unifiedOrder->SetSpbill_create_ip('');//用户创建订单的IP
        $unifiedOrder->SetTime_start(date('YmdHis',$time));//交易起始时间
        $unifiedOrder->SetTime_expire(date('YmdHis',$time + 30*60));//交易结束时间
        $unifiedOrder->SetNotify_url(''); //接收微信支付异步通知回调地址
        $unifiedOrder->SetTrade_type('JSAPI');//交易类型，取值如下：JSAPI，NATIVE，APP

        $result = WxPayApi::unifiedOrder($unifiedOrder);

        if($result['return_code']=='SUCCESS'){
            if($result['result_code']=='SUCCESS'){
                /* 订单创建成功代码 */
            }
        }

        $jsApiParameters = $tools->GetJsApiParameters($result);

        //获取共享收货地址js函数参数
        $editAddress = $tools->GetEditAddressParameters();


        return $this->render('index',[
            'jsApiParameters' => $jsApiParameters, // 传递这些参数到显示页面 View 层
        ]);
    }

    /**
     * 	作用：格式化参数，签名过程需要使用
     */
    function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v)
        {
            if($urlencode)
            {
                $v = urlencode($v);
            }
            //$buff .= strtolower($k) . "=" . $v . "&";
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = '';
        if (strlen($buff) > 0)
        {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }
}


