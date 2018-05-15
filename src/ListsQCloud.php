<?php namespace Stevenyangecho\UEditor;

use Qcloud\Cos\Client;
use \Qiniu\Storage\BucketManager;
use \Qiniu\Auth;

/**
 * 列表文件 for 七牛
 * Class ListsQiniu
 * @package Stevenyangecho\UEditor
 */
class ListsQCloud
{
    public function __construct($allowFiles, $listSize, $path, $request)
    {
        $this->allowFiles = substr(str_replace(".", "|", join("", $allowFiles)), 1);
        $this->listSize = $listSize;
        $this->path = ltrim($path,'/');
        $this->request = $request;
    }

    public function getList()
    {
	    $cosClient = new Client(
		    array(
			    'region' =>config('UEditorUpload.core.qcloud.region'),
			    'credentials'=> array(
				    'secretId'    => config('UEditorUpload.core.qcloud.secretId'),
				    'secretKey' => config('UEditorUpload.core.qcloud.secretKey')
			    )
		    )
	    );
	    $items = $cosClient->listObjects(array('Bucket' =>config('UEditorUpload.core.qcloud.bucket'),'Prefix'=>$this->path));

        if(empty($items['Contents'])){
            return [
                "state" => "no match file",
                "list" => array(),
                "start" => $start,
                "total" => 0
            ];
        }

        $files=[];
        foreach ($items['Contents'] as  $v) {
            if (preg_match("/\.(" . $this->allowFiles . ")$/i", $v['Key'])) {
                $files[] = array(
                    'url' =>rtrim(config('UEditorUpload.core.qcloud.url'),'/').'/'.$this->path.$v['Key'],
                    'mtime' => $v['LastModified'],
                );
            }
        }
        if(empty($files)){
            return [
                "state" => "no match file",
                "list" => array(),
                "start" => $start,
                "total" => 0
            ];
        }
        /* 返回数据 */
        $result = [
            "state" => "SUCCESS",
            "list" => $files,
            "start" => $start,
            "total" => count($files)
        ];

        return $result;
    }

    /**
     * 遍历获取目录下的指定类型的文件
     * @param $path
     * @param array $files
     * @return array
     */
    protected function  getfiles($path, $allowFiles, &$files = array())
    {

        if (!is_dir($path)) return null;
        if (substr($path, strlen($path) - 1) != '/') $path .= '/';
        $handle = opendir($path);
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..') {
                $path2 = $path . $file;
                if (is_dir($path2)) {
                    $this->getfiles($path2, $allowFiles, $files);
                } else {
                    if (preg_match("/\.(" . $allowFiles . ")$/i", $file)) {
                        $files[] = array(
                            'url' => substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
                            'mtime' => filemtime($path2)
                        );
                    }
                }
            }
        }
        return $files;
    }

}
