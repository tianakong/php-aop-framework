<?php
/**
 *
 * @link http://www.ketangshang.cn/
 * @author tiankong <tianakong@aliyun.com>
 * @version 1.0
 */
namespace app\Controller\admin;

class Index extends Admin
{
    public function index()
    {
        $this->render('index');
    }

    public function main()
    {
        $this->render('main');
    }
}