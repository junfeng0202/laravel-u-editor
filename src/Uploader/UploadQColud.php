<?php
namespace Stevenyangecho\UEditor\Uploader;

use Qcloud\Cos\Client;
/**
 *
 *
 * trait UploadQiniu
 *
 * 七牛 上传 类
 *
 * @package Stevenyangecho\UEditor\Uploader
 */
trait UploadQColud
{

    public function uploadQcloud($key, $content, $type = '')
    {

	    $cosClient = new Client(
		    array(
			    'region' => config('UEditorUpload.core.qcloud.region'),
			    'credentials'=> array(
				    'secretId'    => config('UEditorUpload.core.qcloud.secretId'),
				    'secretKey' => config('UEditorUpload.core.qcloud.secretKey')
			    )
		    )
	    );
	    #上传
	    /*if($type == 'remote'){
		    $body=$content;
	    }else{
		    $body=fopen($content,'rb+');
	    }*/
	    try {
		    $result = $cosClient->upload(
		    //bucket的命名规则为{name}-{appid} ，此处填写的存储桶名称必须为此格式
			    $bucket=config('UEditorUpload.core.qcloud.bucket'),
			    $key,
			    $body=$content

		    );
//			print_r($result);
		    $url=rtrim(strtolower(config('UEditorUpload.core.qcloud.url')),'/');
		    $fullName = ltrim($this->fullName, '/');
		    $this->fullName=$url.'/'.$fullName;
		    $this->stateInfo = $this->stateMap[0];

	    } catch (\Exception $e) {
		    $this->stateInfo= $e->getMessage();
	    }
        return true;
    }
}