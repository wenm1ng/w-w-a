<?php
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2021-07-20 23:51
 */
namespace App\HttpController;

use App\Utility\Code;
use EasySwoole\Http\AbstractInterface\Controller;
use Common\CodeKey;
use Common\Common;
use User\Service\LoginService;

class BaseController extends Controller
{

    protected function onRequest(?string $action): ?bool
    {
        $status = true;

        try {
            //验证token
            $authorization = $this->request()->getHeader('authorization');
            if (empty($authorization[0]))
                throw new \Exception('非法访问, 缺少Authorization.');

            $loginService = new LoginService();

            $userId = $loginService->checkToken($authorization[0]);
            //将用户id写进header头
            $this->request()->withAddedHeader('user_id', $userId);
            $body = json_decode($this->request()->getBody()->__toString(), true);
            $body['user_id'] = $userId;
            dump($userId);
            //将解析出来的user_id重新写进body
            $this->request()->withBody(\GuzzleHttp\Psr7\stream_for(json_encode($body)));
        } catch (\Exception $exception) {
            $status = false;
            $this->writeJson($exception->getCode() ?? CodeKey::EXPIRED_TOKEN, $exception->getMessage(), $exception->getMessage());

            Common::log('刊登BaseController Exception:' . $exception->getMessage(), 'BaseController');
        }

        return $status;
    }

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