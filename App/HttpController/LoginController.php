<?php
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2021-07-20 23:51
 */
namespace App\HttpController;
use EasySwoole\Http\AbstractInterface\Controller;
use Common\CodeKey;

Class LoginController extends Controller{
    /**
     * 解析数组返回值
     * @param array $rs
     * @return bool
     */
    public function writeResultJson(array $rs, $httpCode = null)
    {
        if(isset($rs['status'])) {
            return $this->writeJson($rs['status'], $rs[CodeKey::DATA], $rs[CodeKey::MSG], $httpCode);
        } else {
            return $this->writeJson($rs[CodeKey::STATE], $rs[CodeKey::DATA], $rs[CodeKey::MSG], $httpCode);
        }
    }

    public function writeJson($statusCode = 200, $result = null, $msg = null, $httpCode = null)
    {
        if (!$this->response()->isEndResponse()) {
            $data = Array(
                "code" => $statusCode,
                "data" => $result,
                "msg" => $msg
            );
            $this->response()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->response()->withHeader('Content-type', 'application/json;charset=utf-8');
            $this->response()->withStatus(is_null($httpCode) ? ($this->statusCode[$statusCode] ?? $statusCode) : $httpCode);
            return true;
        } else {
            return false;
        }
    }
}