<?php
namespace Org\Myclass;
/**
 * 图像操作类库
 * import('ORG.Myclass.Image');//导入类库
 * $image=new Image($_FILES['test']);//实例化，可传入文件数组，如果不传，需要在thumb方法中传入image路径
 * $image->path='./uploads/wwwww';//设置图片保存路径
 * $image->type='2';//设置图片的压缩模式
 * $image->thumbname='nihaoa.png';//设置压缩后图片、裁剪后图片、添加水印后图片名称
 * $image->thumb(null,100,100,'haha_');//调用缩略图生成方法
 * $path=$image->cut(null,500,300,500,300);//调用裁剪图方法
 * $path=$image->water('',30,4);调用填加水印的方法
 * @category   ORG
 * @package  ORG
 * @subpackage  Util
 * @author    liu21st <liu21st@gmail.com>
 */
class Image {

    private $file;     //包含原始图片全部上传信息的文件数组
    private $path;     //图像保存路径
    private $thumbname; //缩略图或者剪切图或者水印图名称
    private $type;     //缩略图压缩方式 默认1：等比例压缩，图片大小自动调整到等比例模式 2：指定尺寸压缩，会裁掉图片不符合缩略图比例的部分 3：按指定尺寸压缩，不裁剪任何部分，但会变形失真
    private $error;    //错误信息

    /**
     * 实例图像对象时传递图像的一个路径，默认是当前目录
     * @param array $file         //上传的包含原图像全部信息的文件数组$_FILES['NAME']
     * @param type $path          //生成图像的保存路径
     * @param type $thumbname     //生成的压缩图片名称
     */

    public function __construct($file=null, $path='./', $thumbname='', $type='1') {
        $this->file = $file;
        $this->path = rtrim($path, '/') . '/';
        $this->thumbname = $thumbname;
        $this->type = $type;
    }

    /**
     * 功能：生成缩略图
     * @param string $image      //需要生成缩略图的图片
     * @param int $maxWidth      //缩略图的宽度
     * @param int $maxHeight     //缩略图的高度
     * @param string $thumbname  //缩略图前缀
     * @param boolean $interlace //启用隔行扫描
     * @return mixed             //成功返回压缩图路径名称，失败发挥false
     */
    public function thumb($image, $maxWidth=200, $maxHeight=50, $qz='th_', $interlace=true) {
        $flag = false;
        if (empty($image)) {
            $image = $this->file['tmp_name'];
            $flag = true;
        }
        /* 获取原图信息 */
        if ($info = $this->getImageInfo($image)) {
            /* 初始化备用数据 */
            $interlace = $interlace ? 1 : 0;
            /* 获取缩略图保存路径 */
            $thumbname = $this->getThumbName($info, $qz, $flag);
            /* 载入原图 */
            if ($srcImg = $this->getImg($image, $info)) {
                /* 获取新图尺寸 */
                $size = $this->getNewSize($maxWidth, $maxHeight, $info);
                /* 创建缩略图同时处理透明度问题 */
                $thumbImg = $this->kidOfImage($srcImg, $size, $info);
                /* 对jpeg图形设置隔行扫描 */
                if ('jpg' == $info['type'] || 'jpeg' == $info['type'])
                    imageinterlace($thumbImg, $interlace);
                /* 生成图片 */
                $thumbname = $this->createNewImage($thumbImg, $thumbname, $info);
                return $thumbname;
            }
            return false;
        }
        $this->error = '图像不存在';
        return false;
    }

    /**
     * 功能：在一个大的背景图片中剪切出指定区域的图片
     * @param string $image  //需要剪切的背景图片
     * @param type $cutX     //剪切图片左边开始的位置
     * @param type $cutY     //剪切图片顶部开始的位置
     * @param type $cWidth   //图片裁切的宽度
     * @param type $cHeight  //图片裁切的高度
     * @param type $qz       //新图片的前缀名
     * @return mined         //成功返回新图片的名称，失败返回false
     */
    public function cut($image, $cutX=0, $cutY=0, $cWidth=100, $cHeight=100, $qz='cu_') {
        $flag = false;
        if (empty($image)) {
            $image = $this->file['tmp_name'];
            $flag = true;
        }
        /* 获取原图信息 */
        if ($info = $this->getImageInfo($image)) {
            if ((($cutX + $cWidth) > $info['width']) || (($cutY + $cHeight) > $info['height'])) {
                $this->error = '裁剪的位置超出了背景图片范围！';
                return false;
            }
            /* 获取剪切图保存路径 */
            $cutname = $this->getThumbName($info, $qz, $flag);
            /* 载入原图 */
            if ($srcImg = $this->getImg($image, $info)) {
                /* 创建一个可以保存裁剪后的图片的资源 */
                if ($info['type'] != 'gif' && function_exists('imagecreatetruecolor'))
                    $cutImg = imagecreatetruecolor($cWidth, $cHeight);
                else
                    $cutImg = imagecreate($cWidth, $cHeight);

                /* png和gif的透明处理 by luofei614 */
                if ('png' == $info['type']) {
                    imagealphablending($cutImg, false); //取消默认的混色模式（为解决阴影为绿色的问题）
                    imagesavealpha($cutImg, true); //设定保存完整的 alpha 通道信息（为解决阴影为绿色的问题）    
                } elseif ('gif' == $info['type']) {
                    $trnprt_indx = imagecolortransparent($srcImg);
                    if ($trnprt_indx >= 0) {
                        /* its transparent */
                        $trnprt_color = imagecolorsforindex($srcImg, $trnprt_indx);
                        $trnprt_indx = imagecolorallocate($cutImg, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
                        imagefill($cutImg, 0, 0, $trnprt_indx);
                        imagecolortransparent($cutImg, $trnprt_indx);
                    }
                }
                /* 复制图像 */
                if (function_exists("ImageCopyResampled"))
                    imagecopyresampled($cutImg, $srcImg, 0, 0, $cutX, $cutY, $cWidth, $cHeight, $cWidth, $cHeight);
                else
                    imagecopyresized($cutImg, $srcImg, 0, 0, $cutX, $cutY, $cWidth, $cHeight, $cWidth, $cHeight);
                imagedestroy($srcImg);
                /* 生成图片 */
                $cutname = $this->createNewImage($cutImg, $cutname, $info);
                return $cutname;
            }
            return false;
        }
        $this->error = '图像不存在';
        return false;
    }

    /**
     * 为图片添加水印
     * @static public
     * @param string $source     //原文件名
     * @param string $water      //水印图片
     * @param string $qz         //添加水印后的图片名前缀
     * @param inter $alpha       //水印的透明度
     * @param inter $waterpos    //水印的（九宫格）位置，0：随机1：左上2：中上3：右上4：左中5：中中6：右中7：左下8：中下9：右下
     * @return void
     */
    public function water($source, $alpha=80, $waterpos=0, $water='./gaoImg/water.jpg', $qz='wa_') {
        $flag = false;
        if (empty($source)) {
            $source = $this->file['tmp_name'];
            $flag = true;
        }
        /* 检查文件是否存在 */
        /* if (!file_exists($source) || !file_exists($water)) {
          $this->error = '图片或水印图片不存在';
          return false;
          } */

        /* 图片信息 */
        $sInfo = $this->getImageInfo($source);
        $wInfo = $this->getImageInfo($water);

        /* 获取水印图保存路径 */
        $watname = $this->getThumbName($sInfo, $qz, $flag);

        if (($sInfo !== false) && ($wInfo !== false)) {
            /* 获取水印位置 */
            if ($pos = $this->position($sInfo, $wInfo, $waterpos)) {
                /* 建立图像 */
                $sImage = $this->getImg($source, $sInfo);
                $wImage = $this->getImg($water, $wInfo);
                if ($sImage !== false && $wImage !== false) {
                    /* png和gif的透明处理 by luofei614 */
                    if ('png' == $sInfo['type']) {
                        imagealphablending($sImage, false); //取消默认的混色模式（为解决阴影为绿色的问题）
                        imagesavealpha($sImage, true); //设定保存完整的 alpha 通道信息（为解决阴影为绿色的问题）    
                    } elseif ('gif' == $sInfo['type']) {
                        $trnprt_indx = imagecolortransparent($wImage);
                        if ($trnprt_indx >= 0) {
                            /* its transparent */
                            $trnprt_color = imagecolorsforindex($wImage, $trnprt_indx);
                            $trnprt_indx = imagecolorallocate($sImage, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
                            imagefill($sImage, 0, 0, $trnprt_indx);
                            imagecolortransparent($sImage, $trnprt_indx);
                        }
                    }

                    /* 生成混合图像 */
                    $sImage = $this->copyImage($sImage, $wImage, $pos, $wInfo, $alpha);

                    /* 输出图像 */
                    $watname = $this->createNewImage($sImage, $watname, $sInfo);
                    return $watname;
                }
                return false;
            }
            return false;
        }
        $this->error = '图片或水印图片不存在';
        return false;
    }

    /**
     * 旋转一张图片
     * @param string $image 需要旋转的图片路径
     * @param type $angle 旋转的角度
     * @param type $qz 新的旋转图的前缀
     * @return mixed 成功返回新图路径，失败返回false
     */
    public function rotate($image, $angle, $qz) {
        $flag = false;
        if (empty($image)) {
            $image = $this->file['tmp_name'];
            $flag = true;
        }

        /* 获取原图信息 */
        if ($info = $this->getImageInfo($image)) {
            /* 获取旋转图保存路径 */
            $rotatename = $this->getThumbName($info, $qz, $flag);
            /* 载入原图 */
            if ($srcImg = $this->getImg($image, $info)) {
                $white = imagecolorallocate($srcImg, 255, 255, 255);
                /* 旋转图像 */
                $rotateImg = imagerotate($srcImg, $angle, $white, 0);
                imagedestroy($srcImg);
                /* 生成图片 */
                $rotatename = $this->createNewImage($rotateImg, $rotatename, $info);
                return $rotatename;
            }
            return false;
        }
        return false;
    }

    /**
     * 翻转图像
     * @param string $image 需要翻转的图像路径
     * @param string $type 翻转类型，x:按x轴进行翻转 y:按y轴进行翻转
     * @param type $qz 翻转图像前缀
     * @return type 
     */
    public function turnover($image, $type='y', $qz='turn_') {
        $flag = false;
        if (empty($image)) {
            $image = $this->file['tmp_name'];
            $flag = true;
        }
        /* 获取原图信息 */
        if ($info = $this->getImageInfo($image)) {
            /* 获取缩略图保存路径 */
            $turnname = $this->getThumbName($info, $qz, $flag);
            /* 载入原图 */
            if ($srcImg = $this->getImg($image, $info)) {
                $turnImg = imagecreatetruecolor($info['width'], $info['height']);
                switch (strtolower($type)) {
                    case 'x':
                        for ($i = 0; $i < $info['height']; $i++) {
                            imagecopy($turnImg, $srcImg, 0, $info['height'] - $i - 1, 0, $i, $info['width'], 1);
                        }
                        break;
                    case 'y':
                    default :
                        for ($j = 0; $j < $info['width']; $j++) {
                            imagecopy($turnImg, $srcImg, $info['width'] - $j - 1, 0, $j, 0, 1, $info['height']);
                        }
                }
                imagedestroy($srcImg);
                /* 输出图像 */
                $turnname = $this->createNewImage($turnImg, $turnname, $info);
                return $turnname;
            }
            return false;
        }
        return false;
    }

    /**
     * 取得最后一次错误信息
     * @return string    //错误信息
     */
    public function getErrorMsg() {
        return $this->error;
    }

    public function __set($name, $value) {
        if (isset($this->$name)) {
            $this->$name = $value;
        }
    }

    /* 内部使用的私有方法，用于获取图像的信息 */

    private function getImageInfo($img) {
        $imageInfo = getimagesize($img);
        $imgPath = basename($img);
        if ($imageInfo !== false) {
            $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]), 1));
            $imageSize = filesize($img);
            $info = array(
                "width" => $imageInfo[0],
                "height" => $imageInfo[1],
                "type" => $imageType,
                "size" => $imageSize,
                "mime" => $imageInfo['mime'],
                "name" => $imgPath
            );
            return $info;
        } else {
            $this->error = '获取图像信息错误！';
            return false;
        }
    }

    /* 内部使用的私有方法，用于创建支持各种图片格式（jpg,gif,png）资源 */

    private function getThumbName($info, $qz, $flag) {
        $this->path = rtrim($this->path, '/') . '/';
        if ($flag) {
            $info['name'] = $this->file['name'];
        }
        /* 检查路径 */
        $this->checkPath($this->path);
        $thumbname = empty($this->thumbname) ? $this->path . $qz . $info['name'] : $this->path . $this->thumbname;
        return $thumbname;
    }

    /* 内部使用的私有方法，用于创建支持各种图片格式（jpg,gif,png）资源 */

    private function getImg($image, $info) {
        $createFun = 'ImageCreateFrom' . ($info['type'] == 'jpg' ? 'jpeg' : $info['type']);
        if (!function_exists($createFun)) {
            $this->error = '加载原图像出错！';
            return false;
        }
        $srcImg = $createFun($image);
        return $srcImg;
    }

    /* 内部使用的私有方法，返回等比例缩放的图片宽度和高度，如果原图比缩放后的还小，保持不变 */

    private function getNewSize($maxWidth, $maxHeight, $info) {
        $srcWidth = $info['width'];
        $srcHeight = $info['height'];
        switch ($this->type) {
            case '2':
                $scale = max($maxWidth / $srcWidth, $maxHeight / $srcHeight); // 计算缩放比例
                /* 判断原图和缩略图比例 如原图宽于缩略图则裁掉两边 反之.. */
                if ($maxWidth / $srcWidth > $maxHeight / $srcHeight) {
                    /* 高于 */
                    $size['srcX'] = 0;
                    $size['srcY'] = ($srcHeight - $maxHeight / $scale) / 2;
                    $size['width'] = $srcWidth;
                    $size['height'] = $maxHeight / $scale;
                    $size['maxWidth'] = $maxWidth;
                    $size['maxHeight'] = $maxHeight;
                } else {
                    /* 宽于 */
                    $size['srcX'] = ($srcWidth - $maxWidth / $scale) / 2;
                    $size['srcY'] = 0;
                    $size['width'] = $maxWidth / $scale;
                    $size['height'] = $srcHeight;
                    $size['maxWidth'] = $maxWidth;
                    $size['maxHeight'] = $maxHeight;
                }
                break;
            case '3':
                $size['width'] = $maxWidth;
                $size['height'] = $maxHeight;
                break;
            case '1':
            default:
                $scale = min($maxWidth / $srcWidth, $maxHeight / $srcHeight); // 计算缩放比例
                if ($scale >= 1) {
                    /* 超过原图大小不再缩略 */
                    $size['width'] = $srcWidth;
                    $size['height'] = $srcHeight;
                } else {
                    /* 缩略图尺寸 */
                    $size['width'] = (int) ($srcWidth * $scale);
                    $size['height'] = (int) ($srcHeight * $scale);
                }
        }
        return $size;
    }

    /* 内部使用的私有方法，处理带有透明度的图片保持原样 */

    private function kidOfImage($srcImg, $size, $info) {
        if ($this->type == '2') {
            if ($info['type'] != 'gif' && function_exists('imagecreatetruecolor'))
                $thumbImg = imagecreatetruecolor($size['maxWidth'], $size['maxHeight']);
            else
                $thumbImg = imagecreate($size['maxWidth'], $size['maxHeight']);
        }else {
            if ($info['type'] != 'gif' && function_exists('imagecreatetruecolor'))
                $thumbImg = imagecreatetruecolor($size['width'], $size['height']);
            else
                $thumbImg = imagecreate($size['width'], $size['height']);
        }

        /* png和gif的透明处理 by luofei614 */
        if ('png' == $info['type']) {
            imagealphablending($thumbImg, false); //取消默认的混色模式（为解决阴影为绿色的问题）
            imagesavealpha($thumbImg, true); //设定保存完整的 alpha 通道信息（为解决阴影为绿色的问题）    
        } elseif ('gif' == $info['type']) {
            $trnprt_indx = imagecolortransparent($srcImg);
            if ($trnprt_indx >= 0) {
                /* its transparent */
                $trnprt_color = imagecolorsforindex($srcImg, $trnprt_indx);
                $trnprt_indx = imagecolorallocate($thumbImg, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
                imagefill($thumbImg, 0, 0, $trnprt_indx);
                imagecolortransparent($thumbImg, $trnprt_indx);
            }
        }
        /* 复制图片 */
        if ($this->type == '2') {
            if (function_exists("ImageCopyResampled"))
                imagecopyresampled($thumbImg, $srcImg, 0, 0, $size['srcX'], $size['srcY'], $size['maxWidth'], $size['maxHeight'], $size['width'], $size['height']);
            else
                imagecopyresized($thumbImg, $srcImg, 0, 0, $size['srcX'], $size['srcY'], $size['maxWidth'], $size['maxHeight'], $size['width'], $size['height']);
        }else {
            if (function_exists("ImageCopyResampled"))
                imagecopyresampled($thumbImg, $srcImg, 0, 0, 0, 0, $size['width'], $size['height'], $info['width'], $info['height']);
            else
                imagecopyresized($thumbImg, $srcImg, 0, 0, 0, 0, $size['width'], $size['height'], $info['width'], $info['height']);
        }
        imagedestroy($srcImg);
        return $thumbImg;
    }

    /* 内部使用的私有方法，用于加水印时复制图像 */

    private function copyImage($groundImg, $waterImg, $pos, $wInfo, $alpha) {
        imagecopymerge($groundImg, $waterImg, $pos['posX'], $pos['posY'], 0, 0, $wInfo['width'], $wInfo['height'], $alpha);
        imagedestroy($waterImg);
        return $groundImg;
    }

    /* 内部使用的私有方法，用于获取水印位置的x,y坐标 */

    private function position($sInfo, $wInfo, $waterpos) {
        /* 如果图片小于水印图片，不生成图片 */
        if ($sInfo["width"] < $wInfo["width"] || $sInfo['height'] < $wInfo['height']) {
            $this->error = '水印图片不能大于背景图片';
            return false;
        }
        switch ($waterpos) {
            case 1:
                $posX = 0;
                $posY = 0;
                break;
            case 2:
                $posX = ($sInfo['width'] - $wInfo['width']) / 2;
                $posY = 0;
                break;
            case 3:
                $posX = $sInfo['width'] - $wInfo['width'];
                $posY = 0;
                break;
            case 4:
                $posX = 0;
                $posY = ($sInfo['height'] - $wInfo['height']) / 2;
                break;
            case 5:
                $posX = ($sInfo['width'] - $wInfo['width']) / 2;
                $posY = ($sInfo['height'] - $wInfo['height']) / 2;
                break;
            case 6:
                $posX = $sInfo['width'] - $wInfo['width'];
                $posY = ($sInfo['height'] - $wInfo['height']) / 2;
                break;
            case 7:
                $posX = 0;
                $posY = $sInfo['height'] - $wInfo['height'];
                break;
            case 8:
                $posX = ($sInfo['width'] - $wInfo['width']) / 2;
                $posY = $sInfo['height'] - $wInfo['height'];
                break;
            case 9:
                $posX = $sInfo['width'] - $wInfo['width'];
                $posY = $sInfo['height'] - $wInfo['height'];
                break;
            case 0:
            default :
                $posX = rand(0, ($sInfo['width'] - $wInfo['width']));
                $posY = rand(0, ($sInfo['height'] - $wInfo['height']));
                break;
        }
        return array('posX' => $posX, 'posY' => $posY);
    }

    /* 内部使用的私有方法，用于保存图像，并保留原有图片格式 */

    private function createNewImage($newImg, $newName, $info) {
        $imageFun = 'image' . ($info['type'] == 'jpg' ? 'jpeg' : $info['type']);
        $imageFun($newImg, $this->autoCharset($newName, 'utf-8', 'gbk'));
        imagedestroy($newImg);
        return $newName;
    }

    /* 内部使用的私有方法,用于检查路径是否存在 */

    private function checkPath($savePath) {
        /* 检查上传目录 */
        if (!is_dir($savePath)) {
            /* 尝试创建目录 */
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
    }

    /* 内部使用的私有方法，用于自动转换字符集 支持数组转换 */

    private function autoCharset($fContents, $from='gbk', $to='utf-8') {
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

}
