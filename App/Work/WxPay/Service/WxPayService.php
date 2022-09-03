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
use App\Work\WxPay\Models\WowOrderModel;

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
            $resp = self::$instance
                ->chain('v3/pay/transactions/jsapi')
                ->post([
                    'json' => [
                        'mchid'        => self::$merchantId,
                        'out_trade_no' => $orderNo,
                        'appid'        => self::$appId,
                        'description'  => 'WOW WA仓库-帮币',
                        'notify_url'   => 'https://mingtongct.com/api/v1/wx-pay/order-notify',
                        'amount'       => [
                            'total'    => $money, //单位分
                            'currency' => 'CNY'
                        ],
                        'payer' =>[
                            'openid' => $openId
                        ],
                    ],
                    'debug' => true
                ]);

//            echo $resp->getStatusCode(), PHP_EOL;
//            echo $resp->getBody(), PHP_EOL;
            self::$returnData['code'] = $resp->getStatusCode();
            self::$returnData['data'] = json_decode($resp->getBody(), true);

        } catch (\Exception $e) {
            // 进行错误处理
//            echo $e->getMessage(), PHP_EOL;
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $r = $e->getResponse();
                Common::log('code:'.$r->getStatusCode(). ';body:'.$r->getReasonPhrase(), self::$logName);
//                echo $r->getBody(), PHP_EOL, PHP_EOL, PHP_EOL;
            }
//            echo $e->getTraceAsString(), PHP_EOL;
            Common::log('errorMsg:'.$e->getMessage(), self::$logName);
            self::$returnData['code'] = $r->getStatusCode();
            self::$returnData['message'] = $e->getMessage();
        }
        return self::$returnData;
    }
}