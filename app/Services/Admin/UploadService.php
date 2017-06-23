<?php
namespace App\Services\Admin;

use Illuminate\Http\Request;
use Config;
use Illuminate\Support\Str;
use File;
use App\Exceptions\Admin\UploadException;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Validator;
use Symfony\Component\HttpFoundation\File\File as RequestFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;

/**
 * 文件上传处理
 *
 * @author  AaronLiu <liukan0926@stnts.com>
 * @package App\Services
 */
class UploadService
{

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $tempUploadRelativePath;

    /**
     * @var string
     */
    protected $savedUploadRelativePath;

    /**
     * 资源目录（相对于public/）
     * 最终文件保存在：
     * public/ . $resourcePath . 临时/永久上传文件保存目录 . 子目录 . 文件名
     *
     * @var string
     */
    protected $resourceRelativePath;

    /**
     * 默认的上传配置
     *
     * @var array
     */
    protected $defaultOptions = [
        'allowedMimeExtensions' => [],
        'alsoAllowedMimeTypes'  => [], //当文件MIME类型不在内置中时，就检查这些MIME类型
        'maxFileSize'           => '2048', //KB
    ];


    /**
     * @param Request $request
     * @param string  $resourceRelativePath
     * @param string  $savedUploadRelativePath
     * @param string  $tempUploadRelativePath
     * @throws UploadException
     */
    public function __construct(Request $request,
                                $resourceRelativePath,
                                $savedUploadRelativePath,
                                $tempUploadRelativePath)
    {
        $this->request = $request;

        $this->resourceRelativePath    = $resourceRelativePath;
        $this->savedUploadRelativePath = $savedUploadRelativePath;
        $this->tempUploadRelativePath  = $tempUploadRelativePath;

        $this->failWhenConfigureIllegal();
    }

    protected function failWhenConfigureIllegal()
    {
        if ($this->savedUploadRelativePath == '') {
            throw new UploadException('没有配置文件上传保存目录路径（UPLOAD_PATH）');
        }
        if ($this->tempUploadRelativePath == '') {
            throw new UploadException('没有配置文件上传临时保存目录路径（TEMP_UPLOAD_PATH）');
        }
    }

    /**
     * 保存上传的文件并返回路径（从temp目录移到正式目录），如果文件已上传则直接返回路径
     *
     * @param string $inputFileName
     * @param array  $options
     * @return string
     * @throws UploadException
     */
    public function upload($inputFileName = 'filedata', array $options = [])
    {
        $options = array_merge($this->defaultOptions, $options);

        /**
         * 上传文件验证（类型、大小等）
         */
        $this->failWhenUploadedInvalid(
            $inputFileName,
            $options
        );

        $file = $this->request->file($inputFileName);

        //如果上传失败
        if ( ! $file->isValid()) {
            throw new UploadException($file->getErrorMessage());
        }

        //得到临时上传文件夹的相对保存目录
        $tempUploadDirPath = $this->getRandomTempUploadDirectoryPath();

        //保存文件
        return $this->saveFile($file, $tempUploadDirPath);
    }

    /**
     * 得到上传文件临时保存目录路径（会生成一层以日期命名的子目录）
     * 临时文件过一段时间会清空一次
     *
     * @return string
     * @throws UploadException
     */
    protected function getRandomTempUploadDirectoryPath()
    {
        return $this->appendRandomSubDirectory($this->tempUploadRelativePath);
    }

    /**
     * 得到正式上传文件保存目录名
     *
     * @return string
     * @throws UploadException
     */
    protected function getRandomSavedUploadDirectoryPath()
    {
        return $this->appendRandomSubDirectory($this->savedUploadRelativePath);
    }

    /**
     * 增加一层子文件夹并返回
     *
     * @param $uploadDirectory
     * @return string
     */
    protected function appendRandomSubDirectory($uploadDirectory)
    {
        $subDirectory = date('Ymd');

        return rtrim($uploadDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $subDirectory;
    }

    /**
     * 将相对路径转换为绝对路径
     *
     * @param $relativePath
     * @return string
     */
    public function getFullPathFromRelative($relativePath)
    {
        return base_path($this->resourceRelativePath . $relativePath);
    }

    /**
     * 如果文件夹不存在则递归创建，返回文件夹路径
     *
     * @param $directoryPath
     * @return string
     */
    protected function makeDirectoryIfNotExists($directoryPath)
    {
        $fullPath = $this->getFullPathFromRelative($directoryPath);

        if ( ! File::exists($fullPath) || ! File::isDirectory($fullPath)) {
            File::makeDirectory($fullPath, 493, true);
        }

        return $fullPath;
    }

    /**
     * 返回一个随机文件名
     *
     * @param string $extension
     * @return string
     */
    protected function getRandomFilename($extension)
    {
        $suffix = $extension ? ('.' . $extension) : '';

        return Str::random('20') . $suffix;
    }

    /**
     * 返回文件的类型后缀
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $extension
     * @return string
     */
    protected function getFileExt($extension)
    {
        $suffix = $extension ? ('.' . $extension) : '';

        return $suffix;
    }

    /**
     * 保存上传文件到临时目录，并返回相对public/的路径
     *
     * @param UploadedFile $file
     * @param              $uploadDirectoryRelativePath
     * @return string
     */
    protected function saveFile(UploadedFile $file, $uploadDirectoryRelativePath)
    {
        //优先使用根据MIME算出的扩展名，如果不存在则使用原始上传的扩展名
        //$fileExtension = $file->guessExtension() ?: $file->getClientOriginalExtension();
        //$this->getRandomFilename($fileExtension);//获得一个随机名称

        $filename=$this->getFileRealName($file,$uploadDirectoryRelativePath);

        $filePath = $file->move($this->makeDirectoryIfNotExists($uploadDirectoryRelativePath), $filename);
        return $uploadDirectoryRelativePath . '/' . $filename;
    }

    /**
     * 将已上传的文件移动到永久存储的目录并返回路径
     * 会判断是否为上传的临时文件
     *
     * @param $fileRelativePath
     * @return string
     */
    public function saveUploaded($fileRelativePath)
    {
        if ($this->isInTemp($fileRelativePath)) {
            $tempFullPath = $this->getFullPathFromRelative($fileRelativePath);

            if ( ! File::exists($tempFullPath)) {
                throw new \InvalidArgumentException('临时上传的文件不存在！' . $fileRelativePath . '[' . $tempFullPath . ']');
            }

            $uploadDirectoryPath = $this->getRandomSavedUploadDirectoryPath();

            //$filename   = $this->getRandomFilename(File::extension($tempFullPath));
            $filename   = File::name($tempFullPath).$this->getFileExt(File::extension($tempFullPath));

            $toFilePath = $this->makeDirectoryIfNotExists($uploadDirectoryPath) . DIRECTORY_SEPARATOR . $filename;

            File::copy($tempFullPath, $toFilePath);

            return $uploadDirectoryPath . '/' . $filename;
        }

        return $fileRelativePath;
    }

    /**
     * 删除 已经存在正式目录的文件
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $fileRandomPath
     * @return bool
     */
    public function deleteUploadFile($fileRandomPath){
        $fullPath=base_path($this->resourceRelativePath ). $fileRandomPath;

        if ( ! File::exists($fullPath) ) {
            return false;
        }else{
            File::delete($fullPath);
        }
        return true;
    }

    /**
     * 获得文件的真实名称
     * 如果存在重名 则在后面加上"_His"（时间）
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function getFileRealName(UploadedFile $file,$fileRelativePath){

        if ($this->isInTemp($fileRelativePath)) {
            $tempFullPath = $this->getFullPathFromRelative($fileRelativePath);

            $filepath=$tempFullPath. '/' .$file->getClientOriginalName();

            if ( File::exists($filepath)) {//如果文件已经存在 则加上后缀
                $timeStr='_'.date('His',time());
                $fileRealName=$file->getClientOriginalName();
                $extIndex=strripos($fileRealName,'.');
                $fileRealName_fir=substr($fileRealName,0,$extIndex);
                $fileRealName_end=substr($fileRealName,$extIndex);
                return $fileRealName_fir.$timeStr.$fileRealName_end;

            }else{//如果文件不存在则返回文件真实的名称
                return $file->getClientOriginalName();
            }
        }
    }

    /**
     * 判断文件是否在临时上传文件目录，且文件存在
     *
     * @param $path
     * @return bool
     */
    protected function isInTemp($path)
    {
        $fullPath = $this->getFullPathFromRelative($path);

        $tempDirectoryFullPath = $this->getFullPathFromRelative(
            $this->getRandomTempUploadDirectoryPath()
        );

        //如果文件路径和临时文件夹路径一致，并且文件存在
        if (
            substr_compare($fullPath, $tempDirectoryFullPath, 0, mb_strlen($tempDirectoryFullPath)) == 0
        ) {
            return true;
        }

        return false;
    }

    /**
     * 验证上传文件，如果上传文件验证失败则抛出异常
     *
     * @param       $filename
     * @param array $options
     * @throws UploadException
     */
    protected function failWhenUploadedInvalid($filename, array $options)
    {
        $this->validateUploadedForBasics($filename, $options);
        $this->validateUploadedForMimeType($filename, $options);
    }

    /**
     * 验证上传文件的基本信息（必选、文件大小）
     *
     * @param       $filename
     * @param array $options
     * @throws UploadException
     */
    protected function validateUploadedForBasics($filename, array $options)
    {
        $fileSize = $options['maxFileSize'];

        $toBeValidated = $this->request->only($filename);

        /**
         * 验证文件存在和文件大小
         */
        $rules   = [];
        $rules[] = 'required';
        $rules[] = 'max:' . $fileSize;

        $validator = Validator::make($toBeValidated, [
            $filename => $rules,
        ]);
        if ($validator->fails()) {
            throw new UploadException($validator->messages()->first());
        }
    }

    /**
     * 验证上传文件的MIME类型
     *
     * @param       $filename
     * @param array $options
     * @throws UploadException
     */
    protected function validateUploadedForMimeType($filename, array $options)
    {
        $mimeExtensions = $options['allowedMimeExtensions'];
        $extraMimeTypes = $options['alsoAllowedMimeTypes'];

        $toBeValidated = $this->request->only($filename);

        $rules   = [];
        $rules[] = 'required';
        $rules[] = 'mimes:' . implode(',', $mimeExtensions);

        $validator = Validator::make($toBeValidated, [
            $filename => $rules,
        ]);
        if ($validator->fails()) {
            if (empty($extraMimeTypes)) {
                throw new UploadException($validator->messages()->first());
            }

            $fileMimeType = $this->request->file($filename)->getMimeType();
            if ( ! in_array($fileMimeType, $extraMimeTypes)) {
                throw new UploadException('上传文件格式错误');
            }
        }
    }

}