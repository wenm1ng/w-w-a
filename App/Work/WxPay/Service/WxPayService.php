<?php
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2022-09-03 11:59
 */
namespace App\Work\WxPay\Service;

use Common\Common;
use Wa\Models\WowWaTabTitleModel;
use WeChatPay\Builder;
use WeChatPay\Crypto\Rsa;
use WeChatPay\Util\PemUtil;
use EasySwoole\EasySwoole\Config;
use WeChatPay\Formatter;

class WxPayService{
    //商户号
    protected static $merchantId;
    //商户API私钥文件路径
    protected static $merchantPrivateKeyFilePath;
    //商户API证书序列号
    protected static $merchantCertificateSerial;
    //微信支付平台证书签名路径
    protected static $platformCertificateFilePath;
    //请求实例
    protected static $instance;
//    protected static \WeChatPay\BuilderChainable $instance;
    //appid
    protected static $appId;
    protected static $logName = 'wxPay';
    //支付回调链接
    protected static $callbackUrl;
    protected static $returnData = [
        'code' => 0,
        'message' => '',
        'data' => []
    ];

    public function __construct()
    {
        self::$merchantId = Config::getInstance()->getConf('app.MERCHANT_ID');
        self::$merchantPrivateKeyFilePath = Config::getInstance()->getConf('app.MERCHANT_PRIVATE_KEY_FILE_PATH');
        self::$merchantCertificateSerial = Config::getInstance()->getConf('app.MERCHANT_CERTIFICATE_SERIAL');
        self::$platformCertificateFilePath = Config::getInstance()->getConf('app.PLATFORM_CERTIFICATE_FILE_PATH');
        self::$appId = Config::getInstance()->getConf('app.APP_KEY');
        self::$callbackUrl = Config::getInstance()->getConf('app.MERCHANT_PAY_CALLBACK_URL');
        self::init();
    }

    public static function init(){
        dump(self::$merchantId);
        dump(self::$merchantPrivateKeyFilePath);
        dump(self::$merchantCertificateSerial);
        dump(self::$platformCertificateFilePath);
        $merchantPrivateKeyInstance = Rsa::from('file://'.self::$merchantPrivateKeyFilePath, Rsa::KEY_TYPE_PRIVATE);

        $platformPublicKeyInstance = Rsa::from('file://'.self::$platformCertificateFilePath, Rsa::KEY_TYPE_PUBLIC);

        $platformCertificateSerial = PemUtil::parseCertificateSerialNo('file://'.self::$platformCertificateFilePath);

        // 构造一个 APIv3 客户端实例
        self::$instance = Builder::factory([
            'mchid'      => self::$merchantId,
            'serial'     => self::$merchantCertificateSerial,
            'privateKey' => $merchantPrivateKeyInstance,
            'certs'      => [
                $platformCertificateSerial => $platformPublicKeyInstance,
            ],
        ]);
    }

    /**
     * @desc        添加wx预支付订单
     * @example
     * @param int    $money
     * @param string $openId
     *
     * @return mixed
     */
    public function wxAddOrder(int $money, string $openId, string $orderNo){

        try {
            $data = [
                'json' => [
                    'mchid'        => self::$merchantId,
                    'out_trade_no' => $orderNo,
                    'appid'        => self::$appId,
                    'description'  => 'WOW WA仓库-帮币',
                    'notify_url'   => self::$callbackUrl,
                    'amount'       => [
                        'total'    => $money, //单位分
                        'currency' => 'CNY'
                    ],
                    'payer' =>[
                        'openid' => $openId
                    ],
                ],
//                    'debug' => true
            ];
            Common::log('requestData:'.json_encode($data, JSON_UNESCAPED_UNICODE), self::$logName);

            $resp = self::$instance
                ->chain('v3/pay/transactions/jsapi')
                ->post($data);

            self::$returnData['code'] = $resp->getStatusCode();
            self::$returnData['data'] = json_decode($resp->getBody(), true);

        } catch (\Exception $e) {
            // 进行错误处理
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $r = $e->getResponse();
                Common::log('code:'.$r->getStatusCode(). ';body:'.$r->getReasonPhrase(), self::$logName);
            }
            Common::log('errorMsg:'.$e->getMessage(), self::$logName);
            self::$returnData['code'] = $r->getStatusCode();
            self::$returnData['message'] = $e->getMessage();
        }
        return self::$returnData;
    }

    /**
     * @desc        获取支付签名相关信息
     * @example
     * @param string $prepayId
     *
     * @return array
     */
    public static function getSign(string $prepayId){
        $merchantPrivateKeyFilePath = 'file://'.self::$merchantPrivateKeyFilePath;
        $merchantPrivateKeyInstance = Rsa::from($merchantPrivateKeyFilePath);

        $params = [
            'appId'     => self::$appId,
            'timeStamp' => (string)Formatter::timestamp(),
            'nonceStr'  => Formatter::nonce(),
            'package'   => 'prepay_id='.$prepayId,
        ];
        $params += ['paySign' => Rsa::sign(
            Formatter::joinedByLineFeed(...array_values($params)),
            $merchantPrivateKeyInstance
        ), 'signType' => 'RSA'];

        return $params;
    }
}