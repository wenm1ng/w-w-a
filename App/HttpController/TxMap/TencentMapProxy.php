<?php
/**
 * @desc
 * @author     文明<736038880@qq.com>
 * @date       2026-08-26 19:46
 */
namespace App\HttpController\TxMap;

use App\HttpController\CommonController;
use Common\Common;
use Common\CodeKey;
use EasySwoole\HttpClient\HttpClient;

class TencentMapProxy extends CommonController
{
    // ========== 配置项，建议放到.env环境变量，不要硬编码 ==========
    const TENCENT_MAP_KEY = 'RNJBZ-BU3ET-TXQXR-VBCJT-WD3ME-D6F3B';
    const TENCENT_MAP_SK  = 'OGp7Q4EKvP0BfAd0A0Ikf6ON0qHlSfyT';
    const TENCENT_API_HOST = 'https://apis.map.qq.com/ws';

    /**
     * @desc        获取用户信息
     * @example
     * @return bool
     */
    public function indexs()
    {
        $params = Common::getHttpParams($this->request());
        if ($this->checkSignature($params)) {
            $this->response()->write($params['echostr']);
        } else {
            $this->response()->write('error');
        }
    }


    /**
     * 统一入口，匹配 /maps-api/ws/{path}
     */
    public function enter()
    {
        // 1. 获取请求路径，截取 /maps-api/ws/ 后面的部分
        $pathInfo = $this->request()->getUri()->getPath();
        $prefix = '/TencentMapProxy/enter';
        if (strpos($pathInfo, $prefix) === false) {
            return $this->jsons([
                'status' => 404,
                'message' => 'api not found'
            ], 404);
        }
        $apiPath = ltrim(substr($pathInfo, strlen($prefix)), '/');
        // 2. 获取前端GET全部query参数
        $clientQuery = $this->request()->getQueryParams();

        // --- 生产环境：这里校验前端传入的key作为租户token，示例跳过鉴权 ---
        $clientToken = $clientQuery['key'] ?? '';
        unset($clientQuery['key']); // 删除前端传的key，不能传给腾讯！

        // 3. 组装给腾讯的请求参数：加入服务端真实key
        $tencentQuery = $clientQuery;
        $tencentQuery['key'] = self::TENCENT_MAP_KEY;

        // 4. 按照腾讯规则计算 sig 签名
        $sig = $this->calcSig($apiPath, $tencentQuery, self::TENCENT_MAP_SK);
        $tencentQuery['sig'] = $sig;

        // 5. 拼接腾讯完整URL
        $tencentUrl = self::TENCENT_API_HOST . '/' . $apiPath;

        // 6. 协程HTTP客户端请求腾讯接口
        $httpClient = new HttpClient($tencentUrl);
        $httpClient->setTimeout(10);
        $resp = $httpClient->get($tencentQuery);

        if ($resp === false) {
            return $this->jsons([
                'status' => 600,
                'message' => 'request tencent map api timeout or fail'
            ], 500);
        }

        $body = $resp->getBody();
        $statusCode = $resp->getStatusCode();

        // 原样透传腾讯返回给小程序，不修改数据结构，兼容SDK handleData解析
        $this->response()->withStatus($statusCode);
        $this->response()->withHeader('Content-Type', 'application/json;charset=utf-8');
        $this->response()->write($body);
    }

    /**
     * 按照腾讯WebService规则计算sig签名
     * 参考官方文档：参数key=xxx&a=xxx 排序后拼接路径?k=v&k=v + sk，再md5
     * @param string $apiPath 接口路径，如 place/v1/search
     * @param array $params query数组（已经带上腾讯key）
     * @param string $sk secret key
     * @return string 小写md5 sig
     */
    protected function calcSig(string $apiPath, array $params, string $sk): string
    {
        ksort($params); // 参数key字典升序排序
        $arr = [];
        foreach ($params as $k => $v) {
            $arr[] = sprintf("%s=%s", $k, $v);
        }
        $queryStr = implode('&', $arr);
        // 拼接格式：/ws/{apiPath}?queryStr + sk
        $sigRaw = sprintf("/ws/%s?%s%s", $apiPath, $queryStr, $sk);
        return strtolower(md5($sigRaw));
    }

    protected function actionNotFound(?string $action): void
    {
        $this->jsonss(['status' =>404, 'message'=>'action not found'],404);
    }

    private function jsons(array $data, int $httpCode = 200)
    {
        $this->response()->withStatus($httpCode);
        $this->response()->withHeader('Content-Type', 'application/json;charset=utf-8');
        $this->response()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}