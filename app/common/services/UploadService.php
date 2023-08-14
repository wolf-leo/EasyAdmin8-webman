<?php

namespace app\common\services;

use app\admin\model\SystemUploadfile;
use webman\Http\UploadFile;
use support\Db;
use Illuminate\Support\Str;

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
            $object = $this->setFilePath($file, 'blog -static/');
            try {
                $ossClient       = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
                $_rs             = $ossClient->putObject($bucket, $object, file_get_contents($file->getRealPath()));
                $oss_request_url = $_rs['oss - request - url'] ?? '';
                if (empty($oss_request_url)) return ['code' => 0, 'data' => '上传至OSS失败'];
                $oss_request_url = str_replace('http://', 'https://', $oss_request_url);
            } catch (OssException $e) {
                return ['code' => 0, 'data' => $e->getMessage()];
            }

            $data = $type == 'editor' ? ['state' => 'success', 'msg' => $oss_request_url, 'name' => ''] : ['url' => $oss_request_url];
            return ['code' => 1, 'data' => $data];
        }
        $data = '上传失败';
        return ['code' => 0, 'data' => $data];
    }


    protected function save(string $url = ''): bool
    {
        $data        = $this->saveData;
        $data['url'] = $url;
        return DB::table((new SystemUploadfile())->getTable())->insert($data);
    }
}
