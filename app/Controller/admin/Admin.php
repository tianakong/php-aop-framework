<?php
/**
 *
 * @link http://www.ketangshang.cn/
 * @author tiankong <tianakong@aliyun.com>
 * @version 1.0
 */
namespace app\Controller\admin;

use app\Core\BaseController;

class Admin extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->theme = 'admin';
    }
}