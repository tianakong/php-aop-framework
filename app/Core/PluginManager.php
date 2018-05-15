<?php

namespace app\Core;

/**
 * 插件管理器
 * @link http://www.ketangshang.cn/
 * @author tiankong <tianakong@aliyun.com>
 * @version 1.0
 */
class PluginManager
{
    /**
     * 监听已注册的插件
     *
     * @access private
     * @var array
     */
    private $_listeners = array();

    /**
     * 构造函数
     * @access public
     */
    public function __construct()
    {
        #这里$plugin数组包含我们获取已经由用户激活的插件信息
        #为演示方便，我们假定$plugin中至少包含
        #$plugin = array(
        #  'name' => '插件名称',
        #  'directory'=>'插件安装目录'
        #);
        $plugins = $this->get_active_plugins();

        if ($plugins) {
            foreach ($plugins as $plugin) {
                if (@file_exists($plugin['directory'] . '/actions.php')) {
                    include_once($plugin['directory'] . '/actions.php');
                    $class = $plugin['name'] . '_actions';
                    if (class_exists($class)) {
                        //初始化所有插件
                        new $class($this);
                    }
                }
            }
        }
        #此处做些日志记录方面的东西
    }

    /**
     * 注册需要监听的插件方法（钩子）
     *
     * @param string $hook
     * @param object $reference
     * @param string $method
     */
    public function register($hook, &$reference, $method)
    {
        //获取插件要实现的方法
        $key = get_class($reference) . '->' . $method;
        //将插件的引用连同方法push进监听数组中
        $this->_listeners[$hook][$key] = array(&$reference, $method);
        #此处做些日志记录方面的东西
    }

    /**
     * 触发一个钩子
     *
     * @param string $hook 钩子的名称
     * @param mixed $data 钩子的入参
     * @return mixed
     */
    public function trigger($hook, $data = '')
    {
        $result = '';
        //查看要实现的钩子，是否在监听数组之中
        if (isset($this->_listeners[$hook]) && is_array($this->_listeners[$hook]) && count($this->_listeners[$hook]) > 0) {
            // 循环调用开始
            foreach ($this->_listeners[$hook] as $listener) {
                // 取出插件对象的引用和方法
                $class =& $listener[0];
                $method = $listener[1];
                if (method_exists($class, $method)) {
                    // 动态调用插件的方法
                    $result .= $class->$method($data);
                }
            }
        }
        #此处做些日志记录方面的东西
        return $result;
    }

    /**
     * 获取已安装并启用的插件
     * @return array
     */
    private function get_active_plugins()
    {
        $plugins = null;
        $plugin_paths = glob(APP_PATH . '../plugins/*', GLOB_ONLYDIR);
        if (is_array($plugin_paths)) {
            foreach ($plugin_paths as $key => $path) {
                $conffile = $path . "/config.php";
                if (!is_file($conffile)) continue;
                $conArr = include $conffile;
                if (empty($conArr)) continue;
                if ($conArr['install'] == 1 && $conArr['enable'] == 1) {
                    $plugins[$key] = $conArr;
                    $plugins[$key]['directory'] = $path;
                }
            }
        }
        return $plugins;
    }

    public function _include($srcfile)
    {
        // 合并插件，存入 tmp_path
        $len = strlen(APP_PATH);
        $tmpfile = APP_PATH . '../tmp/' . substr(str_replace(['/', '..'], ['_', ''], $srcfile), $len);
        if (!is_file($tmpfile) or DEBUG > 1) {
            // 开始编译
            $s = $this->plugin_compile_srcfile($srcfile);

            $this->file_put_contents_try($tmpfile, $s);
        }
        return $tmpfile;
    }

    // 编译源文件，把插件合并到该文件，不需要递归，执行的过程中 include _include() 自动会递归。
    private function plugin_compile_srcfile($srcfile)
    {
        // 如果有 overwrite，则用 overwrite 替换掉 todo
        //$srcfile = $this->plugin_find_overwrite($srcfile);
        $s = file_get_contents($srcfile);

        // 最多支持 10 层
        for ($i = 0; $i < 10; $i++) {
            if (strpos($s, '<!--{hook') !== FALSE || strpos($s, '// hook') !== FALSE) {
                $s = preg_replace('#<!--{hook\s+(.*?)}-->#', '// hook \\1', $s);
                $s = preg_replace_callback('#//\s*hook\s+(\S+)#is', [$this, 'plugin_compile_srcfile_callback'], $s);
            } else {
                break;
            }
        }
        return $s;
    }

    private function plugin_compile_srcfile_callback($m)
    {
        static $hooks;
        if (empty($hooks)) {
            $hooks = array();
            $plugin_paths = $this->get_active_plugins();

            foreach ($plugin_paths as $path => $pconf) {
                $hookpaths = glob($pconf['directory'] . "/theme/*.html"); // path
                if (is_array($hookpaths)) {
                    foreach ($hookpaths as $hookpath) {
                        $hookname = pathinfo($hookpath)['basename'];
                        $rank = isset($pconf['theme_rank']["$hookname"]) ? $pconf['theme_rank']["$hookname"] : 0;
                        $hooks[$hookname][] = array('hookpath' => $hookpath, 'rank' => $rank);
                    }
                }
            }
            foreach ($hooks as $hookname => $arrlist) {
                $arrlist = $this->arrlist_multisort($arrlist, 'rank', FALSE);
                $hooks[$hookname] = $this->arrlist_values($arrlist, 'hookpath');
            }
        }
        $s = '';
        $hookname = $m[1];
        if (!empty($hooks[$hookname])) {
            foreach ($hooks[$hookname] as $path) {
                $t = file_get_contents($path);
                $s .= $t;
            }
        }
        return $s;
    }

    private function file_put_contents_try($file, $s, $times = 3)
    {
        while ($times-- > 0) {
            $fp = fopen($file, 'wb');
            if ($fp AND flock($fp, LOCK_EX)) {
                $n = fwrite($fp, $s);
                version_compare(PHP_VERSION, '5.3.2', '>=') AND flock($fp, LOCK_UN);
                fclose($fp);
                clearstatcache();
                return $n;
            } else {
                sleep(1);
            }
        }
        return FALSE;
    }

    // 对多维数组排序
    private function arrlist_multisort($arrlist, $col, $asc = TRUE)
    {
        $colarr = array();
        foreach ($arrlist as $k => $arr) {
            $colarr[$k] = $arr[$col];
        }
        $asc = $asc ? SORT_ASC : SORT_DESC;
        array_multisort($colarr, $asc, $arrlist);
        return $arrlist;
    }

    // 从一个二维数组中取出一个 values() 格式的一维数组，某一列key
    private function arrlist_values($arrlist, $key)
    {
        if (!$arrlist) return array();
        $return = array();
        foreach ($arrlist as &$arr) {
            $return[] = $arr[$key];
        }
        return $return;
    }
}