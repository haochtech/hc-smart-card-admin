<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2017 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: zhangyajun <448901948@qq.com>
// +----------------------------------------------------------------------
namespace think\paginator\driver;
use think\Cache;
use think\Paginator;
class Bootstrap extends Paginator
{
    public $rollPage=5;//分页栏每页显示的页数
    public $showPage=1;//总页数超过多少条时显示的首页末页
    /**
     * 上一页按钮
     * @param string $text
     * @return string
     */
    protected function getPreviousButton($text = "上一页")
    {
        if ($this->currentPage() <= 1) {
            return $this->getDisabledTextWrapper($text);
        }
        $url = $this->url(
            $this->currentPage() - 1
        );
        return $this->getPageLinkWrapper($url, $text);
    }
    /**
     * 下一页按钮
     * @param string $text
     * @return string
     */
    protected function getNextButton($text = '下一页')
    {
        if (!$this->hasMore) {
            return $this->getDisabledTextWrapper($text);
        }
        $url = $this->url($this->currentPage() + 1);
        return $this->getPageLinkWrapper($url, $text);
    }
    /**
     * 首页按钮
     * @param string $text
     * @return string
     */
    protected function getFirstButton($text = '首页')
    {
            if ($this->currentPage == 1) {
                return $this->getDisabledTextWrapper($text);
            }
            $url = $this->url(1);
            return $this->getPageLinkWrapper($url, $text);
    }
    /**
     * 末页按钮
     * @param string $text
     * @return string
     */
    protected function getLastButton($text = '末页')
    {
            //dump($this->currentPage);
            //dump($this->lastPage);
            if ($this->currentPage == $this->lastPage) {
                return $this->getDisabledTextWrapper($text);
            }
            $url = $this->url($this->lastPage);
            return $this->getPageLinkWrapper($url, $text);
    }
    /**
     * 页码按钮
     * @return string
     */
    protected function getLinks()
    {
        if ($this->simple)
            return '';
        $block = [
            'first'  => null,
            'slider' => null,
            'last'   => null
        ];
        $side   = 2;
        $window = $side * 2;
        if ($this->lastPage < $window + 5) {
            $block['first'] = $this->getUrlRange(1, $this->lastPage);
        } elseif ($this->currentPage <= $window) {
            $block['first'] = $this->getUrlRange(1, $window + 2);
            $block['last']  = $this->getUrlRange($this->lastPage - 1, $this->lastPage);
        } elseif ($this->currentPage > ($this->lastPage - $window)) {
            $block['first'] = $this->getUrlRange(1, 2);
            $block['last']  = $this->getUrlRange($this->lastPage - $window-1, $this->lastPage);
        } else {
            $block['first']  = $this->getUrlRange(1, 2);
            $block['slider'] = $this->getUrlRange($this->currentPage - $side, $this->currentPage + $side);
            $block['last']   = $this->getUrlRange($this->lastPage , $this->lastPage);
        }
        $html = '';
        if (is_array($block['first'])) {
            $html .= $this->getUrlLinks($block['first']);
        }
        if (is_array($block['slider'])) {
            $html="";
//            $html .= $this->getDots();
            $html .= $this->getUrlLinks($block['slider']);
        }
        if (is_array($block['last'])) {
            $html .= $this->getDots();
            $html .= $this->getUrlLinks($block['last']);
        }
        return $html;
    }
    /**
     * 渲染分页html
     * @return mixed
     */
    public function render()
    {
            if ($this->simple) {
                return sprintf(
                    '<ul class="pager">%s %s</ul>',
                    $this->getPreviousButton(),
                    $this->getLinks(),
                    $this->getNextButton()
                );
            } else {
                return sprintf(
                    '<ul class="pagination">
<span style="margin-right:15px;">共'.$this->total().'条信息</span>
%s %s %s %s %s</ul>',
                    $this->getFirstButton(),
                    $this->getPreviousButton(),
                    $this->getLinks(),
                    $this->getNextButton(),
                    $this->getLastButton()
                );
            }
    }
    /**
     * 生成一个可点击的按钮
     *
     * @param  string $url
     * @param  int    $page
     * @return string
     */
    protected function getAvailablePageWrapper($url, $page)
    {
        $load=Cache::get("is_load");
//        var_dump($load);
        if (empty($load) || !isset($load) || $load<1) {
            return '<a class="n_page_button" href="javascript:;" onclick="load_page(\'' . htmlentities($url) . '\')">' . $page . '</a>';
        }else{
            return '<a class="n_page_button" href="' . htmlentities($url) . '">' . $page . '</a>';
        }
    }
    /**
     * 生成一个禁用的按钮
     *
     * @param  string $text
     * @return string
     */
    protected function getDisabledTextWrapper($text)
    {
        return '<a href="javascript:;" class="n_page_button page_disable">' . $text . '</a>';
    }
    /**
     * 生成一个激活的按钮
     *
     * @param  string $text
     * @return string
     */
    protected function getActivePageWrapper($text)
    {
        return '<a href="javascript:;" class="n_page_button current">' . $text . '</a>';
    }
    /**
     * 生成省略号按钮
     *
     * @return string
     */
    protected function getDots()
    {
        return $this->getDisabledTextWrapper('...');
    }
    /**
     * 批量生成页码按钮.
     *
     * @param  array $urls
     * @return string
     */
    protected function getUrlLinks(array $urls)
    {
        $html = '';
        foreach ($urls as $page => $url) {
            $html .= $this->getPageLinkWrapper($url, $page);
        }
        return $html;
    }
    /**
     * 生成普通页码按钮
     *
     * @param  string $url
     * @param  int    $page
     * @return string
     */
    protected function getPageLinkWrapper($url, $page)
    {
        if ($page == $this->currentPage()) {
            return $this->getActivePageWrapper($page);
        }
        return $this->getAvailablePageWrapper($url, $page);
    }
}
