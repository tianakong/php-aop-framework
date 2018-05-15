<?php
/**
 *
 * @link http://www.ketangshang.cn/
 * @author tiankong <tianakong@aliyun.com>
 * @version 1.0
 */
namespace app\Controller;

use app\Core\BaseController;
use app\Model\User;

class Index extends BaseController
{
    public function index($id)
    {
        //数据库操作
        $datas = User::find();
        //var_dump($datas);

        $this->pluginManager->trigger('index_end', $params = []); //预留钩子，应当把所有变量当做参数传入
        //显示模板文件
        $this->render('index', ['id' => $id]);
    }
}

