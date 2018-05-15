<?php
/**
 *
 * @link http://www.ketangshang.cn/
 * @author tiankong <tianakong@aliyun.com>
 * @version 1.0
 */
namespace app\Core;

class BaseController
{
    /**
     * 当前使用主题
     * @var string
     */
    protected $theme;

    /**
     * 插件管理器
     * @var PluginManager
     */
    public $pluginManager;

    public function __construct()
    {
        $this->pluginManager = new PluginManager();
        $this->theme = 'default';
    }

    /**
     * 渲染模板文件
     * @param $template
     * @param $params
     */
    protected function render($template, $params = [])
    {
        extract($params);
        include $this->pluginManager->_include(APP_PATH . '../theme/' . $this->theme . '/' . $template . '.html');
    }



}