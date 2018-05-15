<?php
/**
 * 这是一个Hello World简单插件的实现
 * @link http://www.ketangshang.cn/
 * @author tiankong <tianakong@aliyun.com>
 * @version 1.0
 */
include 'Test.php';

/**
 *  需要注意的几个默认规则：
 *  1. 本插件类的文件名必须是action
 *  2. 插件类的名称必须是{插件名_actions}
 */
class DEMO_actions
{
    //解析函数的参数是pluginManager的引用
    public function __construct(&$pluginManager)
    {
        //注册这个插件
        //第一个参数是钩子的名称
        //第二个参数是pluginManager的引用
        //第三个是插件所执行的方法
        $pluginManager->register('index_end', $this, 'say_hello');
        $pluginManager->register('index_end', $this, 'say_hello2');
    }

    public function say_hello()
    {
        echo 'Hello World';
    }

    public function say_hello2()
    {
        $obj = new Test();
        $obj->index();
    }
}