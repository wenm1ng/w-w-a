<?php
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2021-09-04 11:15
 */
namespace App\HttpController\Api\V1\Talent;

use App\HttpController\LoginController;
use Common\Common;
use Common\CodeKey;
use Talent\Service\TalentService;

class Talent extends LoginController
{
    /**
     * @desc        获取用户信息
     * @example
     * @return bool
     */
    public function getTalentList(){
        $rs = CodeKey::result();
        try {
            $version = Common::getHttpParams($this->request(), 'version');
            $talentService = new TalentService();
            $result = $talentService->getTalentList($version);
            $rs[CodeKey::STATE] = CodeKey::SUCCESS;
            $rs[CodeKey::DATA] = $result;
        } catch (\Exception $e) {
            $rs[CodeKey::MSG] = $e->getMessage();
        }

        return $this->writeResultJson($rs);
    }
}