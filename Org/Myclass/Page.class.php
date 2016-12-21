<?php
namespace Org\Myclass;
/**
 * 分页类库
 * @category    ORG
 * @package     ORG
 * @subpackage  Myclass
 * 调用方法(一下方法均可在php文件中单独调用)
 * $page=new Page('1003','15','1','','page','6');//实例化分页类
 * $page->getFirstPage('','color:#cccccc;float:left','2');//获取首页模块，并设置其样式
 * $page->getLastPage('','color:#cccccc;float:left','2'); //获取尾页模块，并设置其样式
 * $page->getPrePage('','color:#cccccc;float:left','2');  //获取上一页模块，并设置其样式
 * $page->getNextPage('','color:#cccccc;float:left','2'); //获取下一页模块，并设置其样式
 * $page->getPreBar('','color:#cccccc;float:left','2');   //获取上N页模块，并设置其样式
 * $page->getNextBar('','color:#cccccc;float:left','2');  //获取下N页模块，并设置其样式
 * $page->getListPage('color:blue;float:left;cursor:pointer','color:red;float:left;cursor:pointer','2');  //获取列表模块，并设置其样式
 * $page->getGoPage('width:20px;height:17px !important;height:18px;border:1px solid #cccccc;float:left','cursor:pointer;width:25px;height:21px !important;height:18px;border:1px solid #cccccc;float:left');  //获取跳转模块，并设置文本框和跳转按钮的样式(数组类型，[0]是文本框，[1]是按钮)
 * $page->getStart();  //获取当前页显示开始记录数，例如：当前页显示记录从102—112条，该值为102
 * $page->getEnd();    //获取当前页显示结束记录数，例如：当前页显示记录从102—112条，该值为112
 * $page->getNowRows();//获取当前页显示的记录条数
 * $page->totalRows;   //总记录数(获取和设置)
 * $page->listRows;    //每页最多显示条数(获取和设置)
 * $page->totalPages;  //总页数(获取和设置)
 * $page->nowPage;     //当前页数(获取和设置)
 * $page->limit;       //当前页limit所需检索条件，如  503,20
 * $page->setConfig('first','<');//设置上一页模块显示的文字（其他：head、last、prev、next、prebar、nextbar同理）
 * $page->show();输出分页，付给变量后，可以传给前台页面调用
 * model显示样式
 * 1：共120条纪录，当前第1/10页，每页10条纪录 首页 上一页 下一页 尾页 转到第【】页【button】(带背景)
 * 2: 共1000条记录 本页25条  本页从  1-25条    1/40页   |< |<< 1 2 3 4 5 6 >>|  >| 【】GO
 * 3：6/40页  首页  上一页  1 2 3 4 5 6 7 8 9 10 11 下一页  末页  共1000条记录 
 * 
 * @author      高祥栋 <gxd_dnjlw@163.com>
 */
class Page {

    public $totalRows;        //数据总行数
    public $listRows;         //每页默认显示的数据行数
    public $totalPages;       //总页数
    public $nowPage;          //当前页数
    public $limit;            //sql语句使用limit从句，限制获取记录个数
    public $config = array(
        'head' => '条记录',
        'first' => '首页',
        'last' => '尾页',
        'prev' => '上一页',
        'next' => '下一页',
        'prevbar' => '上N页',
        'nextbar' => '下N页',
        'go' => 'GO',
    );                        //分页显示定制
    private $pagebarNum;      //分页条显示的页数（比如  《<1 2 3 4 5>》 值为5）
    private $url;             //分页URL地址
    private $parameter;       //参数
    private $pageName;        //默认分页变量
    private $ord = true;      //默认显示页，true显示第一页，false显示最后一页
    private $model;           //分页样式0：自定义 1——N表示不同样式，默认为1

    public function __construct($totalRows, $listRows=20, $model='1', $parameter='', $pageName='p', $pagebarNum='8') {
        $this->model = $model;
        $this->totalRows = $totalRows;
        $this->listRows = $listRows;
        $this->totalPages = ceil($totalRows / $listRows);
        $this->pageName = 'p';
        $this->url = $this->__getUrl($parameter);
        if ($this->ord) {
            $defaultpage = 1;
        } else {
            $defaultpage = $this->totalPages;
        }
        $this->nowPage = !empty($_GET[$pageName]) ? $_GET[$pageName] : $defaultpage;
        if ($this->nowPage < 1) {
            $this->nowPage = 1;
        } else if ($this->nowPage > $this->totalPages) {
            $this->nowPage = $this->totalPages;
        }
        $this->pagebarNum = $pagebarNum;
        $this->limit = $this->__setLimit();
    }

    /*     * *************************************************************************
     * @功能：生成图像验证码
     * @param string $mode 分页样式
     * @return string 
     * ************************************************************************ */

    public function show() {
        $str = ""; //用于存放返回字符串
        switch ($this->model) {
            case '1':
                $this->setConfig('go', '');
                $goPage = $this->getGoPage('height:15px;line-height:12px;width:25px;text-align:center;font-size:12px;font-family:黑体;float:left;cursor:pointer', 'width:37px;height:18px !important;height:15px;background:url(/gaoImg/go1.gif);text-align:center;font-size:12px;font-family:黑体;float:left;cursor:pointer','2');
                $str = '<div style="text-align:center;font-size:14px;font-family:黑体;float:left;">共' . $this->totalRows . $this->config['head'] . '，当前第' . $this->nowPage . '/' . $this->totalPages . '页，本页' . $this->getNowRows() . $this->config['head'] . '</div>' . '<div style="width:15px;height:15px;float:left;"></div>';
                if ($this->totalPages > 1) {
                    $str.=$this->getFirstPage("background:url(/gaoImg/background1_4.gif);width:37px;height:15px;text-align:center;font-size:12px;font-family:黑体;float:left;cursor:pointer", "background:url(/gaoImg/background1_4.gif);width:37px;height:15px;text-align:center;font-size:12px;font-family:黑体;float:left;color:#aaaaaa;cursor:pointer", '2') . '<div style="width:7px;height:15px;float:left"></div>' . $this->getPrePage("background:url(/gaoImg/background1_3.gif);width:43px;height:15px;text-align:center;font-size:12px;font-family:黑体;float:left;cursor:pointer", "background:url(/gaoImg/background1_3.gif);width:43px;height:15px;text-align:center;font-size:12px;font-family:黑体;float:left;color:#aaaaaa;cursor:pointer", '2') . '<div style="width:7px;height:15px;float:left"></div>' . $this->getNextPage("background:url(/gaoImg/background1_3.gif);width:43px;height:15px;text-align:center;font-size:12px;font-family:黑体;float:left;cursor:pointer", "background:url(/gaoImg/background1_3.gif);width:43px;height:15px;text-align:center;font-size:12px;font-family:黑体;float:left;color:#aaaaaa;cursor:pointer", '2') . '<div style="width:7px;height:15px;float:left"></div>' . $this->getLastPage("background:url(/gaoImg/background1_4.gif);width:37px;height:15px;text-align:center;font-size:12px;font-family:黑体;float:left;cursor:pointer", "background:url(/gaoImg/background1_4.gif);width:37px;height:15px;text-align:center;font-size:12px;font-family:黑体;float:left;color:#aaaaaa;cursor:pointer", '2') . '<div style="width:15px;height:15px;float:left"></div><div style="text-align:center;font-size:14px;font-family:黑体;float:left;">转到第</div>' . $goPage[0] . '<div style="text-align:center;font-size:14px;font-family:黑体;float:left;">页</div><div style="width:7px;height:15px;float:left"></div>' . $goPage[1];
                }
                break;
            case '2';
                $this->setConfig('first', '|<<');
                $this->setConfig('last', '>>|');
                $this->setConfig('prev', '|<');
                $this->setConfig('next', '>|');
                $goPage = $this->getGoPage();
                $str = '共' . $this->totalRows . $this->config['head'] . '  本页' . $this->getNowRows() . '条  本页从' . $this->getStart() . '-' . $this->getEnd() . '条  ' . $this->nowPage . '/' . $this->totalPages . '页  ' . $this->getFirstPage() . $this->getPrePage() . $this->getListPage() . $this->getNextPage() . $this->getLastPage() . $goPage[0] . $goPage[1];
                break;
            case '3':
                $str=$this->nowPage.'/'.$this->totalPages.'页  '.$this->getFirstPage().$this->getPrePage().$this->getListPage().$this->getNextPage().$this->getLastPage().'  共&nbsp;'.$this->totalRows.'&nbsp;'.$this->config['head'];
                break;
            default :
                $this->setConfig('go', '');
                $goPage = $this->getGoPage('height:15px;line-height:12px;width:25px;text-align:center;font-size:12px;font-family:黑体;float:left;cursor:pointer', 'width:37px;height:18px !important;height:15px;background:url(/gaoImg/go1.gif);text-align:center;font-size:12px;font-family:黑体;float:left;cursor:pointer','2');
                $str = '<div style="text-align:center;font-size:14px;font-family:黑体;float:left;">共' . $this->totalRows . $this->config['head'] . '，当前第' . $this->nowPage . '/' . $this->totalPages . '页，每页' . $this->getNowRows() . $this->config['head'] . '</div>' . '<div style="width:500px;height:15px;float:left;"></div>';
                if ($this->totalPages > 1) {
                    $str.=$this->getFirstPage("background:url(/gaoImg/background1_4.gif);width:37px;height:15px;text-align:center;font-size:12px;font-family:黑体;float:left;cursor:pointer", "background:url(/gaoImg/background1_4.gif);width:37px;height:15px;text-align:center;font-size:12px;font-family:黑体;float:left;color:#aaaaaa;cursor:pointer", '2') . '<div style="width:7px;height:15px;float:left"></div>' . $this->getPrePage("background:url(/gaoImg/background1_3.gif);width:43px;height:15px;text-align:center;font-size:12px;font-family:黑体;float:left;cursor:pointer", "background:url(/gaoImg/background1_3.gif);width:43px;height:15px;text-align:center;font-size:12px;font-family:黑体;float:left;color:#aaaaaa;cursor:pointer", '2') . '<div style="width:7px;height:15px;float:left"></div>' . $this->getNextPage("background:url(/gaoImg/background1_3.gif);width:43px;height:15px;text-align:center;font-size:12px;font-family:黑体;float:left;cursor:pointer", "background:url(/gaoImg/background1_3.gif);width:43px;height:15px;text-align:center;font-size:12px;font-family:黑体;float:left;color:#aaaaaa;cursor:pointer", '2') . '<div style="width:7px;height:15px;float:left"></div>' . $this->getLastPage("background:url(/gaoImg/background1_4.gif);width:37px;height:15px;text-align:center;font-size:12px;font-family:黑体;float:left;cursor:pointer", "background:url(/gaoImg/background1_4.gif);width:37px;height:15px;text-align:center;font-size:12px;font-family:黑体;float:left;color:#aaaaaa;cursor:pointer", '2') . '<div style="width:15px;height:15px;float:left"></div><div style="text-align:center;font-size:14px;font-family:黑体;float:left;">转到第</div>' . $goPage[0] . '<div style="text-align:center;font-size:14px;font-family:黑体;float:left;">页</div><div style="width:7px;height:15px;float:left"></div>' . $goPage[1];
                }
                break;
        }
        return $str;
    }

    //首页
    /**
     * @param type $style1 //当前页非第一页时的样式
     * @param type $style2 //当前页是第一页时的样式（消失，禁用等）
     * @param type $mod//有无样式，默认1无样式，2有样式
     */
    public function getFirstPage($style1="", $style2="", $mod="1") {
        if ($mod == '2') {
            if ($this->nowPage == '1') {
                return $this->__setStyle($this->config['first'], $style2);
            } else {
                return $this->__setStyle($this->__setUrl($this->config['first'], 1), $style1);
            }
        } else {
            if ($this->nowPage == '1') {
                return;
            } else {
                return $this->__setUrl($this->config['first'], 1);
            }
        }
    }

    //尾页
    /**
     * @param type $style1 //当前页非最后一页时的样式
     * @param type $style2 //当前页是最后一页时的样式（消失，禁用等）
     * @param type $mod//有无样式，默认1无样式，2有样式
     */
    public function getLastPage($style1="", $style2="", $mod="1") {
        if ($mod == '2') {
            if ($this->nowPage == $this->totalPages) {
                return $this->__setStyle($this->config['last'], $style2);
            } else {
                return $this->__setStyle($this->__setUrl($this->config['last'], $this->totalPages), $style1);
            }
        } else {
            if ($this->nowPage == $this->totalPages) {
                return;
            } else {
                return $this->__setUrl($this->config['last'], $this->totalPages);
            }
        }
    }

    //上一页
    /**
     * @param type $style1 //当前页非第一页时的样式
     * @param type $style2 //当前页是第一页时的样式（消失，禁用等）
     * @param type $mod//有无样式，默认1无样式，2有样式
     */
    public function getPrePage($style1="", $style2="", $mod="1") {
        $pre = $this->nowPage - 1;
        if ($mod == '2') {
            if ($pre >= 1) {
                return $this->__setStyle($this->__setUrl($this->config['prev'], $pre), $style1);
            } else {
                return $this->__setStyle($this->config['prev'], $style2);
            }
        } else {
            if ($pre >= 1) {
                return $this->__setUrl($this->config['prev'], $pre);
            } else {
                return;
            }
        }
    }

    //下一页
    /**
     * @param type $style1 //当前页非最后一页时的样式
     * @param type $style2 //当前页是最后一页时的样式（消失，禁用等）
     * @param type $mod//有无样式，默认1无样式，2有样式
     */
    public function getNextPage($style1="", $style2="", $mod="1") {
        $next = $this->nowPage + 1;
        if ($mod == '2') {
            if ($next <= $this->totalPages) {
                return $this->__setStyle($this->__setUrl($this->config['next'], $next), $style1);
            } else {
                return $this->__setStyle($this->config['next'], $style2);
            }
        } else {
            if ($next <= $this->totalPages) {
                return $this->__setUrl($this->config['next'], $next);
            } else {
                return;
            }
        }
    }

    //上N页
    /**
     * @param type $style1 //当前页够上翻N页时的样式
     * @param type $style2 //当前页不够上翻N页时的样式（消失，禁用等）
     * @param type $mod//有无样式，默认1无样式，2有样式
     */
    public function getPreBar($style1="", $style2="", $mod="1") {
        $pren = $this->nowPage - $this->pagebarNum;
        if ($mod == '2') {
            if ($pren >= 1) {
                return $this->__setStyle($this->__setUrl($this->config['prevbar'], $pren), $style1);
            } else {
                return $this->__setStyle($this->config['prevbar'], $style2);
            }
        } else {
            if ($pren >= 1) {
                return $this->__setUrl($this->config['prevbar'], $pren);
            } else {
                return;
            }
        }
    }

    //下N页
    /**
     * @param type $style1 //当前页够下翻N页时的样式
     * @param type $style2 //当前页不够下翻N页时的样式（消失，禁用等）
     * @param type $mod//有无样式，默认1无样式，2有样式
     */
    public function getNextBar($style1="", $style2="", $mod="1") {
        $nextn = $this->nowPage + $this->pagebarNum;
        if ($mod == '2') {
            if ($nextn <= $this->totalPages) {
                return $this->__setStyle($this->__setUrl($this->config['nextbar'], $nextn), $style1);
            } else {
                return $this->__setStyle($this->config['nextbar'], $style2);
            }
        } else {
            if ($nextn <= $this->totalPages) {
                return $this->__setUrl($this->config['nextbar'], $nextn);
            } else {
                return;
            }
        }
    }

    //列表页 1 2 3 4,保持长度为$pagebarNum
    /**
     * @param type $style1 //非当前页的列表数字的样式（颜色，字体大小、粗细）
     * @param type $style2 //当前页的列表数字的样式（颜色，字体大小、粗细）
     * @param type $mod//有无样式，默认1无样式，2有样式
     */
    public function getListPage($style1="", $style2="", $mod="1") {
        $num = $this->pagebarNum / 2 + 1; //分页条总长度的一半,用于判断是奇数还是偶数页数
        $list = "";
        if ($mod == '2') {
            if ($this->nowPage < $num) {
                //当前页没有过一半时，分页条不更新，一直是从1开始
                for ($i = 1; $i < $this->nowPage; $i++) {
                    $list.=$this->__setStyle($this->__setUrl($i, $i), $style1);
                }
                $list.=$this->__setStyle($this->__setUrl($this->nowPage, $this->nowPage), $style2);
                for ($j = $this->nowPage + 1; $j <= $this->pagebarNum; $j++) {
                    if ($j <= $this->totalPages) {
                        $list.=$this->__setStyle($this->__setUrl($j, $j), $style1);
                    } else {
                        break;
                    }
                }
            } else {
                //当前页过一半时，需要动态更新分页条，保证当前页在中间位置
                if (is_int($num)) {
                    for ($i = $num - 2; $i > 0; $i--) {
                        if ($this->nowPage - $i >= 1) {
                            $list.=$this->__setStyle($this->__setUrl($this->nowPage - $i, $this->nowPage - $i), $style1);
                        } else {
                            break;
                        }
                    }
                    $list.=$this->__setStyle($this->__setUrl($this->nowPage, $this->nowPage), $style2);
                    for ($j = 1; $j < $num; $j++) {
                        if ($this->nowPage + $j <= $this->totalPages) {
                            $list.=$this->__setStyle($this->__setUrl($this->nowPage + $j, $this->nowPage + $j), $style1);
                        } else {
                            break;
                        }
                    }
                } else {
                    for ($i = floor($num - 1); $i > 0; $i--) {
                        if ($this->nowPage - $i >= 1) {
                            $list.=$this->__setStyle($this->__setUrl($this->nowPage - $i, $this->nowPage - $i), $style1);
                        } else {
                            break;
                        }
                    }
                    $list.=$this->__setStyle($this->__setUrl($this->nowPage, $this->nowPage), $style2);
                    for ($j = 1; $j < floor($num); $j++) {
                        if ($this->nowPage + $j <= $this->totalPages) {
                            $list.=$this->__setStyle($this->__setUrl($this->nowPage + $j, $this->nowPage + $j), $style1);
                        } else {
                            break;
                        }
                    }
                }
            }
        } else {
            if ($this->nowPage < $num) {
                //当前页没有过一半时，分页条不更新，一直是从1开始
                for ($i = 1; $i < $this->nowPage; $i++) {
                    $list.=$this->__setUrl($i, $i);
                }
                $list.='<span style="font-weight:bold;background:#cccccc">' . $this->__setUrl($this->nowPage, $this->nowPage) . '</span>';
                for ($j = $this->nowPage + 1; $j <= $this->pagebarNum; $j++) {
                    if ($j <= $this->totalPages) {
                        $list.=$this->__setUrl($j, $j);
                    } else {
                        break;
                    }
                }
            } else {
                //当前页过一半时，需要动态更新分页条，保证当前页在中间位置
                if (is_int($num)) {
                    for ($i = $num - 2; $i > 0; $i--) {
                        if ($this->nowPage - $i >= 1) {
                            $list.=$this->__setUrl($this->nowPage - $i, $this->nowPage - $i);
                        } else {
                            break;
                        }
                    }
                    $list.='<span style="font-weight:bold;background:#cccccc">' . $this->__setUrl($this->nowPage, $this->nowPage) . '</span>';
                    for ($j = 1; $j < $num; $j++) {
                        if ($this->nowPage + $j <= $this->totalPages) {
                            $list.=$this->__setUrl($this->nowPage + $j, $this->nowPage + $j);
                        } else {
                            break;
                        }
                    }
                } else {
                    for ($i = floor($num - 1); $i > 0; $i--) {
                        if ($this->nowPage - $i >= 1) {
                            $list.=$this->__setUrl($this->nowPage - $i, $this->nowPage - $i);
                        } else {
                            break;
                        }
                    }
                    $list.='<span style="font-weight:bold;background:#cccccc">' . $this->__setUrl($this->nowPage, $this->nowPage) . '</span>';
                    for ($j = 1; $j < floor($num); $j++) {
                        if ($this->nowPage + $j <= $this->totalPages) {
                            $list.=$this->__setUrl($this->nowPage + $j, $this->nowPage + $j);
                        } else {
                            break;
                        }
                    }
                }
            }
        }

        return $list;
    }

    //
    //
    //跳转到第几页
    /**
     * @param type $style1 //文本框的样式
     * @param type $style2 //按钮的样式
     * * @param type $mod//有无样式，默认1无样式，2有样式
     */
    public function getGoPage($style1, $style2, $mod='1') {
        if ($mod == '2') {
            if ($this->totalPages >= 1) {
                // return '&nbsp;<input style="' . $style1 . '" type="text" onkeydown="javascript:if(event.keyCode==13){var page=(this.value>' . $this->totalPages . ') ? ' . $this->totalPages . ' : this.value;location=\'' . $this->url . $this->pageName . '=\'+page+\'\'}" value="' . $this->nowPage . '" /><input style="' . $style2 . '" type="button" value="GO" onclick="javascript:var page=(this.previousSibling.value >' . $this->totalPages . ') ? ' . $this->totalPages . ' : this.previousSibling.value;location=\'' . $this->url . $this->pageName . '=\'+page+\'\'">&nbsp;';
                $goPage[0] = '&nbsp;<input style="' . $style1 . '" type="text" id="jumpText" onkeydown="javascript:if(event.keyCode==13){var page=(this.value>' . $this->totalPages . ') ? ' . $this->totalPages . ' : this.value;location=\'' . $this->url . $this->pageName . '=\'+page+\'\'}" value="' . $this->nowPage . '" />'; //文本框
                $goPage[1] = '<input style="' . $style2 . '" type="button" value="' . $this->config['go'] . '" onclick="javascript:var page=(document.getElementById(\'jumpText\').value >' . $this->totalPages . ') ? ' . $this->totalPages . ' : document.getElementById(\'jumpText\').value;location=\'' . $this->url . $this->pageName . '=\'+page+\'\'">&nbsp;';
                return $goPage;
            }
        } else {
            if ($this->totalPages >= 1) {
                // return '&nbsp;<input style="' . $style1 . '" type="text" onkeydown="javascript:if(event.keyCode==13){var page=(this.value>' . $this->totalPages . ') ? ' . $this->totalPages . ' : this.value;location=\'' . $this->url . $this->pageName . '=\'+page+\'\'}" value="' . $this->nowPage . '" /><input style="' . $style2 . '" type="button" value="GO" onclick="javascript:var page=(this.previousSibling.value >' . $this->totalPages . ') ? ' . $this->totalPages . ' : this.previousSibling.value;location=\'' . $this->url . $this->pageName . '=\'+page+\'\'">&nbsp;';
                $goPage[0] = '&nbsp;<input style="width:25px;text-align:center;cursor:pointer" type="text" id="jumpText" onkeydown="javascript:if(event.keyCode==13){var page=(this.value>' . $this->totalPages . ') ? ' . $this->totalPages . ' : this.value;location=\'' . $this->url . $this->pageName . '=\'+page+\'\'}" value="' . $this->nowPage . '" />'; //文本框
                $goPage[1] = '<input style="width:37px;text-align:center;cursor:pointer" type="button" value="' . $this->config['go'] . '" onclick="javascript:var page=(document.getElementById(\'jumpText\').value >' . $this->totalPages . ') ? ' . $this->totalPages . ' : document.getElementById(\'jumpText\').value;location=\'' . $this->url . $this->pageName . '=\'+page+\'\'">&nbsp;';
                return $goPage;
            }
        }
    }

    //显示开始记录数
    public function getStart() {
        if ($this->totalRows == 0) {
            return 0;
        } else {
            return ($this->nowPage - 1) * $this->listRows + 1;
        }
    }

    //显示结束记录数
    public function getEnd() {
        return min($this->nowPage * $this->listRows,$this->totalRows);
    }

    //本页显示条数
    public function getNowRows() {
        if ($this->totalRows > 0) {
            return $this->getEnd() - $this->getStart() + 1;
        } else {
            return 0;
        }
    }

    //设置config中的样式
    public function setConfig($param, $value) {
        if (array_key_exists($param, $this->config)) {
            $this->config[$param] = $value;
        }
        return $this;
    }

    //获取URL
    private function __getUrl($parameter) {
        if (empty($parameter) && !empty($_POST)) {
            $parameter = $_POST;
        }
        //获得链接
        $request_uri = $_SERVER['REQUEST_URI'];
        //如果链接中带问号，不处理，如果不带问号，末尾添加问号
        $request_uri = strstr($request_uri, '?') ? $request_uri : $request_uri . '?';
        $url = parse_url($request_uri);
        //去除参数中的页数，只保留其余参数
        if (is_array($parameter)) {
            unset($parameter[$this->pageName]);
            if ($parameter) {
                $request_uri = $url['path'] . '?' . http_build_query($parameter) . '&';
            } else {
                $request_uri = $url['path'] . '?';
            }
        } else if ($parameter != '') {
            $request_uri = $url['path'] . '?' . $parameter . '&';
        } else {
            if (!empty($url['query'])) {
                parse_str($url['query'], $arr);
                unset($arr[$this->pageName]);
                if ($arr) {
                    $request_uri = $url['path'] . '?' . http_build_query($arr) . '&';
                } else {
                    $request_uri = $url['path'] . '?';
                }
            }
        }
        return $request_uri;
    }

    //给分页模块设置样式
    private function __setStyle($str, $style) {
        $style = (!empty($style)) ? $style : 'float:left;cursor:pointer';

        return "<div style='{$style}'>{$str}</div>";
    }

    //给分页模块设置链接
    private function __setUrl($str, $page) {
        return "<span onclick=\"javascript:window.location.href='{$this->url}{$this->pageName}={$page}'\" style='margin-right:5px;'>{$str}</span>";
    }

    //设置分页限制条数和偏移量
    private function __setLimit() {
        return ($this->nowPage - 1) * $this->listRows . ',$this->listRows';
    }

}

?>
