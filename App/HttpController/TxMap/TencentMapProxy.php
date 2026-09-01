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
    // 已关闭腾讯控制台签名校验，SK不再使用，注释
    //const TENCENT_MAP_SK  = 'OGp7Q4EKvP0BfAd0A0Ikf6ON0qHlSfyT';
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
     * 统一入口，访问路由：/wx/enter/xxx
     */
    public function enter()
    {
        // 获取swoole底层原始请求uri，不需要nginx
        $swooleReq = $this->request()->getSwooleRequest();
        $rawRequestUri = $swooleReq->server['request_uri'];
        $pathInfo = explode('?', $rawRequestUri)[0];

        $prefix = '/wx/enter/';
        if (strpos($pathInfo, $prefix) === false) {
            return $this->jsons([
                'status' => 404,
                'message' => 'api not found, pathInfo:'.$pathInfo
            ], 404);
        }
        $apiPath = ltrim(substr($pathInfo, strlen($prefix)), '/');

        if(trim($apiPath) === ''){
            return $this->jsons([
                'status' =>400,
                'message'=>'缺少地图接口子路径'
            ],400);
        }

        $clientQuery = $this->request()->getQueryParams();
        unset($clientQuery['key']);

        $tencentQuery = $clientQuery;
        $tencentQuery['key'] = self::TENCENT_MAP_KEY;

        $trimApiPath = rtrim($apiPath, '/');
        $tencentUrl = self::TENCENT_API_HOST . '/' . $trimApiPath;

        var_dump('rawRequestUri:'.$rawRequestUri);
        var_dump('pathInfo:'.$pathInfo);
        var_dump('apiPath:'.$apiPath);
        var_dump('trimApiPath:'.$trimApiPath);
        var_dump('tencentQuery',$tencentQuery);
        var_dump('tencentUrl:'.$tencentUrl);

        $httpClient = new HttpClient($tencentUrl);
        $httpClient->setTimeout(10);
        // =========关键修复：用setQuery设置get查询参数，不要给get()传数组=========
        $httpClient->setQuery($tencentQuery);
        $resp = $httpClient->get();

        if ($resp === false) {
            return $this->jsons([
                'status' => 600,
                'message' => 'request tencent map api timeout or fail'
            ], 500);
        }

        $body = $resp->getBody();
        $statusCode = $resp->getStatusCode();

        $this->response()->withStatus($statusCode);
        $this->response()->withHeader('Content-Type', 'application/json;charset=utf-8');
        $this->response()->write($body);
    }




    /**
     * 【现在已经不用，保留代码备查，不再调用】
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
        $this->jsons(['status' =>404, 'message'=>'action not found'],404);
    }

    private function jsons(array $data, int $httpCode = 200)
    {
        $this->response()->withStatus($httpCode);
        $this->response()->withHeader('Content-Type', 'application/json;charset=utf-8');
        $this->response()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}
