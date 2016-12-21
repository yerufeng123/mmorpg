<?php
namespace Org\Myclass;
/**
 * 文件上传类
 * 调用方法：
 * $config=array(
 *      maxSize='873000',
 *      savePath='./uploads/test';
 * );//配置不同参数，覆盖默认参数
 * $file=new UploadFile($config);
 * $file->upload('./uploads/test');//上传多个文件（多个文件名）
 * $file->uploadOne($_FILES['test'],'./uploads/test');//上传单个文件（一个文件名，可以是多张图片，但必须用一个名称）
 * $file->autoSub=true;//开启子目录存储模式
 * $file->subType='date';//设置子目录命名方式 hash、date、custom
 * $file->dateFormat='Ymd';//当subType为date时，设置日期模式
 * $file->thumb=true;//开启图片压缩模式
 * $file->savePath='./uploads/big';//设置大图上传路径
 * $file->thumbPath='./uploads/little';//设置缩略图上传路径
 * $file->thumbType='3';//设置缩略图压缩方式 1：等比例压缩 2：按指定大小裁剪压缩 3：按指定大小缩放（变形失真）
 * $file->thumbMaxWidth=200;//设置压缩图片宽度
 * $file->thumbMaxHeight=200;//设置压缩图片高度
 * $file->thumbFile='water';//设置压缩图片名称（不带jpg）
 * $file->water=true;//添加水印开启
 * $file->waterRemoveOrigin=true;//水印添加后删除原图
 * @category   ORG
 * @package  ORG
 * @subpackage  Myclass
 * @author    高祥栋 <gxd_dnjlw@163.com>
 */
class UploadFile {//类定义开始

    private $config = array(
        'maxSize' => -1, // 上传文件的最大值
        'supportMulti' => true, // 是否支持多文件上传
        'allowExts' => array(), // 允许上传的文件后缀 留空不作后缀检查（有时候不清楚文件类型，可以直接写后缀进行过滤）
        'allowTypes' => array(), // 允许上传的文件类型 留空不做检查
        'thumb' => false, // 是否对上传图片进行缩略图处理
        //'imageClassPath' => 'ORG.Myclass.Image', // 图库类包路径
        'thumbMaxWidth' => 100, // 缩略图最大宽度（可以是多个，用逗号连接）
        'thumbMaxHeight' => 100, // 缩略图最大高度（可以是多个，用逗号连接）
        'thumbPrefix' => 'thumb_', // 缩略图前缀（可以是多个，用逗号连接）
        'thumbSuffix' => '', //缩略图后缀（可以是多个，用逗号连接）
        'thumbPath' => './uploads/tempFile/', // 缩略图保存路径
        'thumbFile' => '', // 缩略图文件名（可以是多个，用逗号连接,不带后缀）
        'thumbExt' => '', // 缩略图扩展名 
        'thumbType' => 1, // 缩略图压缩方式 默认1：等比例压缩，图片大小自动调整到等比例模式 2：指定尺寸压缩，会裁掉图片不符合缩略图比例的部分 3：按指定尺寸压缩，不裁剪任何部分，但会变形失真
        'water' => false, //是否对上传图片打水印
        'waterImg' => './gaoImg/water.jpg', //水印图片
        'waterPos' => 3, //水印的（九宫格）位置，0：随机1：左上2：中上3：右上4：左中5：中中6：右中7：左下8：中下9：右下
        'waterAlpha' => 80, //水印透明度
        'waterPrefix' => 'water_', //水印图片前缀
        'thumbRemoveOrigin' => false, // 是否移除压缩原图
        'waterRemoveOrigin' => false, //是否移除水印原图
        'zipImages' => false, // 压缩图片文件上传
        'autoSub' => false, // 启用子目录保存文件
        'subType' => 'hash', // 子目录创建方式 可以使用hash date custom(子目录命名规则)
        'subDir' => '', // 子目录名称 subType为custom方式后有效
        'dateFormat' => 'Ymd',
        'hashLevel' => 1, // hash的目录层次,1表示根目录里的某个文件夹2：表示文件夹下的文件夹
        'savePath' => './uploads/tempFile/src/', // 上传文件保存路径
        'autoCheck' => true, // 是否自动检查附件
        'uploadReplace' => false, // 存在同名是否覆盖
        'renameFile' => true, //是否重名名文件,如果关闭重命名，最好开启覆盖
        'saveRule' => 'time', // 上传文件命名规则
        'hashType' => 'md5_file', // 上传文件Hash规则函数名
    );
    private $error = ''; // 错误信息
    private $uploadFileInfo; // 上传成功的文件信息

    /**
     * 架构函数
     * @access public
     * @param array $config  上传参数
     */

    public function __construct($config=array()) {
        if (is_array($config)) {
            $this->config = array_merge($this->config, $config);
        }
        //处理用户输入的路径为标准路径格式
        if (!empty($this->subDir)) {
            $this->subDir = rtrim($this->subDir, '/') . '/';
        }
        if (!empty($this->savePath)) {
            $this->savePath = rtrim($this->savePath, '/') . '/';
        }
    }

    /**
     * 上传单个上传字段中的文件 支持多附件
     * @access public
     * @param array $file  上传文件信息
     * @param string $savePath  上传文件保存路径
     * @return string
     */
    public function uploadOne($file, $savePath='') {
        //如果不指定保存文件名，则由系统默认
        if (empty($savePath))
            $savePath = $this->savePath;
        // 检查上传目录
        if (!is_dir($savePath)) {
            // 尝试创建目录
            if (!mkdir($savePath, 0777, true)) {
                $this->error = '上传目录' . $savePath . '不存在';
                return false;
            }
        } else {
            if (!is_writeable($savePath)) {
                $this->error = '上传目录' . $savePath . '不可写';
                return false;
            }
        }
        $savePath = rtrim($savePath, '/') . '/'; //处理用户输入为标准路径格式
        //过滤无效的上传
        if (!empty($file['name'])) {
            $fileArray = array();
            if (is_array($file['name'])) {
                $keys = array_keys($file);
                $count = count($file['name']);
                for ($i = 0; $i < $count; $i++) {
                    foreach ($keys as $key)
                        $fileArray[$i][$key] = $file[$key][$i];
                }
            } else {
                $fileArray[] = $file;
            }
            $info = array();
            foreach ($fileArray as $key => $file) {
                //登记上传文件的扩展信息
                $file['extension'] = $this->getExt($file['name']);
                $file['savepath'] = $savePath;
                $file['savename'] = $this->getSaveName($file);
                // 自动检查附件
                if ($this->autoCheck) {
                    if (!$this->check($file))
                        return false;
                }
                //保存上传文件
                if (!$this->save($file))
                    return false;
                if (function_exists($this->hashType)) {
                    $fun = $this->hashType;
                    $file['hash'] = $fun($this->autoCharset($file['savepath'] . $file['savename'], 'utf-8', 'gbk'));
                }
                unset($file['tmp_name'], $file['error']);
                $info[] = $file;
            }
            $this->uploadFileInfo = $info;
            // 返回上传的文件信息
            return $info;
        } else {
            $this->error = '没有选择上传文件';
            return false;
        }
    }

    /**
     * 上传所有文件
     * @access public
     * @param string $savePath  上传文件保存路径(不支持多级目录)
     * @return string
     */
    public function upload($savePath ='') {
        //如果不指定保存文件名，则由系统默认
        if (empty($savePath))
            $savePath = $this->savePath;
        // 检查上传目录
        if (!is_dir($savePath)) {
            // 检查目录是否编码后的
            if (is_dir(base64_decode($savePath))) {
                $savePath = base64_decode($savePath);
            } else {
                // 尝试创建目录
                if (!mkdir($savePath, 0777, true)) {
                    $this->error = '上传目录' . $savePath . '不存在';
                    return false;
                }
            }
        } else {
            if (!is_writeable($savePath)) {
                $this->error = '上传目录' . $savePath . '不可写';
                return false;
            }
        }
        $savePath = rtrim($savePath, '/') . '/'; //处理用户输入为标准路径格式
        $fileInfo = array();
        $isUpload = false;

        // 获取上传的文件信息
        // 对$_FILES数组信息处理
        $files = $this->dealFiles($_FILES);
        foreach ($files as $key => $file) {
            //过滤无效的上传
            if (!empty($file['name'])) {
                //登记上传文件的扩展信息
                if (!isset($file['key']))
                    $file['key'] = $key;
                $file['extension'] = $this->getExt($file['name']);
                $file['savepath'] = $savePath;
                $file['savename'] = $this->getSaveName($file);

                // 自动检查附件
                if ($this->autoCheck) {
                    if (!$this->check($file))
                        return false;
                }

                //保存上传文件
                if (!$this->save($file))
                    return false;
                if (function_exists($this->hashType)) {
                    $fun = $this->hashType;
                    $file['hash'] = $fun($this->autoCharset($file['savepath'] . $file['savename'], 'utf-8', 'gbk'));
                }
                //上传成功后保存文件信息，供其他地方调用
                unset($file['tmp_name'], $file['error']);
                $fileInfo[] = $file;
                $isUpload = true;
            }
        }
        if ($isUpload) {
            $this->uploadFileInfo = $fileInfo;
            return true;
        } else {
            $this->error = '没有选择上传文件';
            return false;
        }
    }

    /**
     * 取得上传文件的信息
     * @access public
     * @return array
     */
    public function getUploadFileInfo() {
        return $this->uploadFileInfo;
    }

    /**
     * 取得最后一次错误信息
     * @access public
     * @return string
     */
    public function getErrorMsg() {
        return $this->error;
    }

    public function __get($name) {
        if (isset($this->config[$name])) {
            return $this->config[$name];
        }
        return null;
    }

    public function __set($name, $value) {
        if (isset($this->config[$name])) {
            $this->config[$name] = $value;
        }
    }

    public function __isset($name) {
        return isset($this->config[$name]);
    }

    /* 内部使用的私有方法，用于上传保存一个文件 */

    private function save($file) {
        $file['savepath'] = rtrim($file['savepath'], '/') . '/';
        $filename = $file['savepath'] . $file['savename'];
        if (!$this->uploadReplace && is_file($filename)) {
            // 不覆盖同名文件
            $this->error = '文件已经存在！' . $filename;
            return false;
        }
        /* 如果是图像文件 检测文件格式 */
        if (in_array(strtolower($file['extension']), array('gif', 'jpg', 'jpeg', 'bmp', 'png', 'swf'))) {
            $info = getimagesize($file['tmp_name']);
            if (false === $info || ('gif' == strtolower($file['extension']) && empty($info['bits']))) {
                $this->error = '非法图像文件';
                return false;
            }
        }
        if (!move_uploaded_file($file['tmp_name'], $this->autoCharset($filename, 'utf-8', 'gbk'))) {
            $this->error = '文件上传保存错误！';
            return false;
        }
        if ($this->thumb && in_array(strtolower($file['extension']), array('gif', 'jpg', 'jpeg', 'bmp', 'png'))) {
            /* 压缩图像 */
            if (!$this->thumbPic($filename, $file)) {
                return false;
            }
        }
        if (!$this->thumbRemoveOrigin && $this->water && in_array(strtolower($file['extension']), array('gif', 'jpg', 'jpeg', 'bmp', 'png'))) {
            /* 添加水印 */
            if (!$this->waterPic($filename, $file)) {
                return false;
            }
        }
        if ($this->zipImags) {
            // TODO 对图片压缩包在线解压
        }
        return true;
    }

    /* 内部使用的私有方法，用于转换上传文件数组变量为正确的方式 */

    private function dealFiles($files) {
        $fileArray = array();
        $n = 0;
        foreach ($files as $key => $file) {
            if (is_array($file['name'])) {
                $keys = array_keys($file);
                $count = count($file['name']);
                for ($i = 0; $i < $count; $i++) {
                    $fileArray[$n]['key'] = $key;
                    foreach ($keys as $_key) {
                        $fileArray[$n][$_key] = $file[$_key][$i];
                    }
                    $n++;
                }
            } else {
                $fileArray[$key] = $file;
            }
        }
        return $fileArray;
    }

    /* 内部使用的私有方法，用于取得上传文件的后缀 */

    private function getExt($filename) {
        $pathinfo = pathinfo($filename);
        return $pathinfo['extension'];
    }

    /* 内部使用的私有方法，用于根据上传文件命名规则取得保存文件名,规则：如果给定的规则是个函数，就用函数结果+后缀做新文件名，如果不是函数，就用规则+后缀做新文件名 */

    private function getSaveName($filename) {
        $rule = $this->saveRule;
        if (!($this->renameFile) || empty($rule)) {//没有定义命名规则，则保持文件名不变
            $saveName = $filename['name'];
        } else {
            if (function_exists($rule)) {
                /* 使用函数生成一个唯一文件标识号（新文件名唯一） */
                $saveName = $rule() . '_' . mt_rand(1000, 9999) . "." . $filename['extension'];
            } else {
                /* 使用给定的文件名作为标识号（新文件名都相同，不支持覆盖，第二次上传报错） */
                $saveName = $rule . '_' . mt_rand(1000, 9999) . "." . $filename['extension'];
            }
        }
        if ($this->autoSub) {
            // 使用子目录保存文件
            $filename['savename'] = $saveName;
            $saveName = $this->getSubName($filename) . $saveName;
        }
        return $saveName;
    }

    /* 内部使用的私有方法，用于获取子目录的名称 */

    private function getSubName($file) {
        switch ($this->subType) {
            case 'custom':
                $this->subDir = rtrim($this->subDir, '/') . '/';
                $dir = $this->subDir;
                break;
            case 'date':
                $dir = date($this->dateFormat, time()) . '/';
                break;
            case 'hash':
            default:
                $name = md5($file['savename']);
                $dir = '';
                for ($i = 0; $i < $this->hashLevel; $i++) {
                    $dir .= $name{$i} . '/';
                }
                break;
        }
        if (!is_dir($file['savepath'] . $dir)) {
            mkdir($file['savepath'] . $dir, 0777, true);
        }
        return $dir;
    }

    /* 内部使用的私有方法，用于检查上传的文件 */

    private function check($file) {
        if ($file['error'] !== 0) {
            /* 文件上传失败 */
            /* 捕获错误代码 */
            $this->error($file['error']);
            return false;
        }
        /* 文件上传成功，进行自定义规则检查 */
        /* 检查文件大小 */
        if (!$this->checkSize($file['size'])) {
            $this->error = '上传文件大小不符！';
            return false;
        }

        /* 检查文件Mime类型 */
        if (!$this->checkType($file['type'])) {
            $this->error = '上传文件MIME类型不允许！';
            return false;
        }
        /* 检查文件类型 */
        if (!$this->checkExt($file['extension'])) {
            $this->error = '上传文件类型不允许';
            return false;
        }
        /* 检查是否合法上传 */
        if (!$this->checkUpload($file['tmp_name'])) {
            $this->error = '非法上传文件！';
            return false;
        }
        return true;
    }

    /* 内部使用的私有方法，用于获取错误代码信息 */

    protected function error($errorNo) {
        switch ($errorNo) {
            case 1:
                $this->error = '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值';
                break;
            case 2:
                $this->error = '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值';
                break;
            case 3:
                $this->error = '文件只有部分被上传';
                break;
            case 4:
                $this->error = '没有文件被上传';
                break;
            case 6:
                $this->error = '找不到临时文件夹';
                break;
            case 7:
                $this->error = '文件写入失败';
                break;
            default:
                $this->error = '未知上传错误！';
        }
        return;
    }

    /* 内部使用的私有方法，用于检查文件大小是否合法 */

    private function checkSize($size) {
        return (-1 == $this->maxSize) || !($size > $this->maxSize);
    }

    /* 内部使用的私有方法，用于检查上传的文件类型是否合法 */

    private function checkType($type) {
        if (!empty($this->allowTypes))
            return in_array(strtolower($type), $this->allowTypes);
        return true;
    }

    /* 内部使用的私有方法，用于检查上传的文件后缀是否合法 */

    private function checkExt($ext) {
        if (!empty($this->allowExts))
            return in_array(strtolower($ext), $this->allowExts, true);
        return true;
    }

    /* 内部使用的私有方法，用于检查文件是否非法提交 */

    private function checkUpload($filename) {
        return is_uploaded_file($filename);
    }

    /* 内部使用的私有方法，用于自动转换字符集 支持数组转换 */

    public function autoCharset($fContents, $from='gbk', $to='utf-8') {
        $from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
        $to = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
        if (strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents))) {
            //如果编码相同或者非字符串标量则不转换
            return $fContents;
        }
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($fContents, $to, $from);
        } elseif (function_exists('iconv')) {
            return iconv($from, $to, $fContents);
        } else {
            return $fContents;
        }
    }

    /* 内部使用的私有方法，用于压缩图片 */

    private function thumbPic($filename, $file) {
        if (!$this->renameFile) {
            $this->thumbPrefix = '';
            $this->thumbSuffix = '';
        }
        $image = getimagesize($filename);
        if (false !== $image) {
            /* 是图像文件生成缩略图 */
            $thumbWidth = explode(',', $this->thumbMaxWidth);
            $thumbHeight = explode(',', $this->thumbMaxHeight);
            $thumbPrefix = explode(',', $this->thumbPrefix);
            $thumbSuffix = explode(',', $this->thumbSuffix);
            $thumbFile = explode(',', $this->thumbFile);
            $thumbPath = $this->thumbPath ? $this->thumbPath : dirname($filename) . '/';
            $thumbExt = $this->thumbExt ? $this->thumbExt : $file['extension']; //自定义缩略图扩展名
            /* 生成图像缩略图 */
            //import($this->imageClassPath);
            for ($i = 0, $len = count($thumbWidth); $i < $len; $i++) {
                if (!empty($thumbFile[$i])) {
                    $thumbname = $thumbFile[$i];
                } else {
                    $prefix = isset($thumbPrefix[$i]) ? $thumbPrefix[$i] : $thumbPrefix[0];
                    $suffix = isset($thumbSuffix[$i]) ? $thumbSuffix[$i] : $thumbSuffix[0];
                    if ($this->renameFile) {
                        $thumbname = $prefix . basename($filename, '.' . $file['extension']) . mt_rand(1000, 9999) . $suffix;
                    }else{
                        $thumbname = $prefix . basename($filename, '.' . $file['extension']) . $suffix;
                    }
                }
                $image = new \Org\Myclass\Image();
                $image->thumbname = $thumbname . '.' . $thumbExt;
                $image->path = $thumbPath;
                $image->type = $this->thumbType;
                $thumbnames[$i] = $image->thumb($filename, $thumbWidth[$i], $thumbHeight[$i], '', true);
                if (!$thumbnames[$i]) {
                    return false;
                }
                if ($this->water) {
                    if (!$this->waterPic($thumbnames[$i])) {
                        return false;
                    }
                }
            }
            if ($this->thumbRemoveOrigin) {
                // 生成缩略图之后删除原图
                unlink($filename);
            }
            $this->thumbFile = implode(",", $thumbnames);
            return true;
        }
    }

    /* 内部使用的私有方法，用于压缩图片 */

    private function waterPic($filename) {
        if (!$this->renameFile) {
            $this->waterPrefix = '';
        }
        //import($this->imageClassPath);
        $image1 = new \Org\Myclass\Image();
        $image1->path = dirname($filename);
        if (!$image1->water($filename, $this->waterAlpha, $this->waterPos, $this->waterImg, $this->waterPrefix)) {
            $this->error = $image1->getErrorMsg();
            return false;
        }
        if ($this->waterRemoveOrigin) {
            // 生成缩略图之后删除原图
            unlink($filename);
        }
        return true;
    }

}

