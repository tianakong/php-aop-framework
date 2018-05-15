<?php
/**
 *
 * @link http://www.ketangshang.cn/
 * @author tiankong <tianakong@aliyun.com>
 * @version 1.0
 */
class chajian1_actions
{
    public function __construct($pluginManager)
    {
        $pluginManager->register('index_end', $this, 'say');
    }


    public function say()
    {
        echo '我是插件1';
    }
}