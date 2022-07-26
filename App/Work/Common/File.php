<?php
/*
 * @desc       
 * @author     文明<736038880@qq.com>
 * @date       2022-07-19 10:29
 */
namespace App\Work\Common;

use EasySwoole\HttpClient\HttpClient;

class File{
    protected $dir = '/data/www/image';

    public function uploadImageToBlog(string $url){
        $return = $this->fileRequest('http://www.wenming.online/admin/file/uploadWa.html', $url);
        return json_decode($return, true);
    }

    public function fileRequest(string $url, string $fileUrl)
    {
//        dump($fileName);
//        $localUrl = download($fileUrl);
        $localUrl = saveInterImage($fileUrl);
        $data = [
            'download' => new \CURLFile($localUrl)
        ];
        $client = new HttpClient();
        $client->setContentTypeJson();
        $client->setTimeout(20);
        $newHeader = [
            'Accept:' => '*/*'
        ];
        $client->setHeaders($newHeader, false, false);
        $client->setUrl($url);
        $rs = $client->post($data);

        $output = strval($rs->getBody());
        dump($output);
        $errNo = $rs->getErrCode();
        $errMsg = $rs->getErrMsg();
        //删除临时文件
        @unlink($localUrl);
        return $output;
    }

    /**
     * @desc       下载文件
     * @author     文明<736038880@qq.com>
     * @date       2022-07-26 16:40
     * @param array $params
     *
     * @return array
     */
    public function uploadImage(array $params){
        $path = '/wa';
        $trim = '/data/www/image';
        $replace = 'http://119.29.1.85:83';
        $return = [];
        foreach ($params['url'] as $url) {
           $imageUrl = saveInterImage($url, $path);
           $imageUrl = str_replace($trim, $replace, $imageUrl);
           $return[$url] = $imageUrl;
           file_put_contents($this->dir.$path.'/image.txt', $url.'----'.$imageUrl."\n");
           \Co::sleep(0.2);
        }
        return $return;
    }
}
