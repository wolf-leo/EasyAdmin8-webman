<?php

namespace app\common\services;

use app\admin\model\SystemUploadfile;
use OSS\Core\OssException;
use OSS\OssClient;
use webman\Http\UploadFile;
use think\helper\Str;
use Qcloud\Cos\Client;
use Exception;
use Qiniu\Storage\UploadManager;
use Qiniu\Auth;

class UploadService
{
    public static ?UploadService $_instance = null;
    protected array              $options   = [];
    private array                $saveData;

    public static function instance(): ?UploadService
    {
        if (!static::$_instance) static::$_instance = new static();
        return static::$_instance;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setConfig(array $options = []): UploadService
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->options;
    }

    /**
     * @param UploadFile $file
     * @param string $base_path
     * @return string
     */
    protected function setFilePath(UploadFile $file, string $base_path = ''): string
    {
        $path = date('Ymd') . '/' . Str::random(3) . time() . Str::random() . '.' . $file->getUploadExtension();
        return $base_path . $path;
    }

    /**
     * @param UploadFile $file
     * @return UploadService
     */
    protected function setSaveData(UploadFile $file): static
    {
        $options        = $this->options;
        $data           = [
            'upload_type'   => $options['upload_type'],
            'original_name' => $file->getUploadName(),
            'mime_type'     => $file->getUploadMimeType(),
            'file_size'     => $file->getSize(),
            'file_ext'      => strtolower($file->getUploadExtension()),
            'create_time'   => time(),
        ];
        $this->saveData = $data;
        return $this;
    }

    /**
     * 本地存储
     *
     * @param UploadFile $file
     * @return array
     */
    public function local(UploadFile $file): array
    {
        if ($file->isValid()) {
            $base_path = '/storage/' . date('Ymd') . '/';
            // 上传文件的目标文件夹
            $destinationPath = public_path() . $base_path;
            !is_dir($destinationPath) && @mkdir($destinationPath);
            $this->setSaveData($file);
            // 将文件移动到目标文件夹中
            $move = $file->move($destinationPath . Str::random(3) . time() . Str::random() . session('admin.id') . '.' . $file->getUploadExtension());
            $url  = $base_path . $move->getFilename();
            $data = ['url' => $url];
            $this->save($url);
            return ['code' => 1, 'data' => $data];
        }
        $data = '上传失败';
        return ['code' => 0, 'data' => $data];
    }

    /**
     * 阿里云OSS
     *
     * @param UploadFile $file
     * @param string $type
     * @return array
     */
    public function oss(UploadFile $file, string $type = ''): array
    {
        $config          = $this->getConfig();
        $accessKeyId     = $config['oss_access_key_id'];
        $accessKeySecret = $config['oss_access_key_secret'];
        $endpoint        = $config['oss_endpoint'];
        $bucket          = $config['oss_bucket'];
        if ($file->isValid()) {
            $object = $this->setFilePath($file, env('EASYADMIN.OSS_STATIC_PREFIX', 'easyadmin8') . '/');
            try {
                $ossClient       = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
                $_rs             = $ossClient->putObject($bucket, $object, file_get_contents($file->getRealPath()));
                $oss_request_url = $_rs['oss-request-url'] ?? '';
                if (empty($oss_request_url)) return ['code' => 0, 'data' => '上传至OSS失败'];
                $oss_request_url = str_replace('http://', 'https://', $oss_request_url);
                $this->setSaveData($file);
            } catch (OssException $e) {
                return ['code' => 0, 'data' => $e->getMessage()];
            }
            $data = ['url' => $oss_request_url];
            $this->save($oss_request_url);
            return ['code' => 1, 'data' => $data];
        }
        $data = '上传失败';
        return ['code' => 0, 'data' => $data];
    }

    /**
     * 腾讯云cos
     *
     * @param UploadFile $file
     * @param string $type
     * @return array
     */
    public function cos(UploadFile $file, string $type = ''): array
    {
        $config    = $this->getConfig();
        $secretId  = $config['cos_secret_id'];              //替换为用户的 secretId，请登录访问管理控制台进行查看和管理，https://console.cloud.tencent.com/cam/capi
        $secretKey = $config['cos_secret_key'];             //替换为用户的 secretKey，请登录访问管理控制台进行查看和管理，https://console.cloud.tencent.com/cam/capi
        $region    = $config['cos_region'];                 //替换为用户的 region，已创建桶归属的region可以在控制台查看，https://console.cloud.tencent.com/cos5/bucket
        if ($file->isValid()) {
            $cosClient = new Client(
                [
                    'region'      => $region,
                    'schema'      => 'http',
                    'credentials' => ['secretId' => $secretId, 'secretKey' => $secretKey,
                    ],
                ]);
            try {
                $object   = $this->setFilePath($file, env('EASYADMIN.OSS_STATIC_PREFIX', 'easyadmin8') . '/');
                $result   = $cosClient->upload(
                    $config['cos_bucket'],         //存储桶名称，由BucketName-Appid 组成，可以在COS控制台查看 https://console.cloud.tencent.com/cos5/bucket
                    $object,                       //此处的 key 为对象键
                    file_get_contents($file->getRealPath())
                );
                $location = $result['Location'] ?? '';
                if (empty($location)) return ['code' => 0, 'data' => '上传至COS失败'];
                $location = 'https://' . $location;
                $this->setSaveData($file);
            } catch (Exception $e) {
                return ['code' => 0, 'data' => $e->getMessage()];
            }
            $data = ['url' => $location];
            $this->save($location);
            return ['code' => 1, 'data' => $data];
        }
        $data = '上传失败';
        return ['code' => 0, 'data' => $data];
    }

    /**
     * 七牛云
     *
     * @param UploadFile $file
     * @param string $type
     * @return array
     * @throws Exception
     */
    public function qnoss(UploadFile $file, string $type = ''): array
    {
        if (!$file->isValid()) return ['code' => 1, 'data' => '上传验证失败'];
        $uploadMgr = new UploadManager();
        $config    = $this->getConfig();
        $accessKey = $config['qnoss_access_key'];
        $secretKey = $config['qnoss_secret_key'];
        $bucket    = $config['qnoss_bucket'];
        $domain    = $config['qnoss_domain'];
        $auth      = new Auth($accessKey, $secretKey);
        $token     = $auth->uploadToken($bucket);
        $object    = $this->setFilePath($file, env('EASYADMIN.OSS_STATIC_PREFIX', 'easyadmin8') . '/');
        list($ret, $error) = $uploadMgr->putFile($token, $object, $file->getRealPath());
        if (empty($ret)) return ['code' => 0, 'data' => $error->getResponse()->error ?? '上传失败，请检查七牛云相关参数配置'];
        $url  = $domain . "/" . $ret['key'];
        $data = ['url' => $url];
        $this->setSaveData($file);
        $this->save($url);
        return ['code' => 1, 'data' => $data];
    }

    protected function save(string $url = ''): bool
    {
        $data                = $this->saveData;
        $data['url']         = $url;
        $data['upload_time'] = time();
        return (new SystemUploadfile())->insert($data);
    }
}
