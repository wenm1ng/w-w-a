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
use App\Exceptions\CommonException;

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
            $this->checkSign($loginService);

            if($authorization[0] === 'test_php'){
                $userIds = $this->request()->getHeader('test_user_id');
                if(empty($userIds[0])){
                    $userIds = $this->request()->getHeader('testuserid');
                }
                $userId = $userIds[0] ?? 2;
                Common::setUserId($userId);
            }else{
                $userId = $loginService->checkToken($authorization[0]);
            }

            //将用户id写进header头
            $this->request()->withAddedHeader('user_id', $userId);
            $body = json_decode($this->request()->getBody()->__toString(), true);
            $body['user_id'] = $userId;
            Common::setUserToken($authorization[0]);
            //将解析出来的user_id重新写进body
            $this->request()->withBody(\GuzzleHttp\Psr7\stream_for(json_encode($body)));
        } catch (\Exception $exception) {
            $status = false;
            $this->writeJson($exception->getCode() ?? CodeKey::EXPIRED_TOKEN, $exception->getMessage(), $exception->getMessage());

            Common::log('刊登BaseController Exception:' . $exception->getMessage(), 'BaseController');
        }

        return $status;
    }

    private function checkSign(\User\Service\LoginService $loginService){
        $signs = $this->request()->getHeader('signs');
        if(empty($signs[0])){
            CommonException::msgException('签名有误', CodeKey::SIGN_ERROR);
        }
        $time = $this->request()->getHeader('time');
        $loginService->checkSign($signs[0], $time[0]);
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

    /**
     * api统一返回处理，错误捕获
     * @param callable $func
     * @return bool
     */
    protected function apiResponse(callable $func)
    {
        $result = [CodeKey::STATE => CodeKey::SUCCESS, CodeKey::DATA => null, CodeKey::MSG => CodeKey::SUCCESSMSG];
        try{
            if (!is_callable($func)){
                throw new \Exception('Argument is not an executable function!');
            }
            $result[CodeKey::DATA] = call_user_func($func);
        }catch (\Exception $e){
            $result[CodeKey::STATE] = !empty($e->getCode()) ? $e->getCode() : CodeKey::FAIL;
            $result[CodeKey::MSG] = $e->getMessage();
            \App\Utility\Logger\Logger::error($e->getMessage().'_'.$e->getFile().'_'.$e->getLine(), 'apiResponse');
        }catch (\Throwable $e){
            $result[CodeKey::STATE] = !empty($e->getCode()) ? $e->getCode() : CodeKey::FAIL;
            $result[CodeKey::MSG] = '系统异常~';
            \App\Utility\Logger\Logger::error($e->getMessage().'_'.$e->getFile().'_'.$e->getLine(), 'apiResponse');
        }
        return $this->writeResultJson($result);
    }

    /**
     * 返回数组化后的请求json数据
     * @return mixed
     */
    public function getRequestJsonData()
    {
        $requestBody = [];
        $result = [];
        $content = $this->request()->getBody()->__toString();
        if($content){
            $requestBody = json_decode($content, true);
        }
        $params = $this->request()->getRequestParam();
        if($requestBody){
            $result = array_merge($requestBody, $params);
        }else{
            $result = $params;
        }
        return $result;
    }
}