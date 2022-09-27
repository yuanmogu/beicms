<?php
// 这是系统自动生成的公共文件

if (!function_exists('get_api_method')) {
    /**
     * 获取API访问模式
     */
    function get_api_method(int $type = 0)
    {
        $method_array = ['GET','POST','ALL','Del'];
        if (isset($method_array[$type])) {
            return $method_array[$type];
        }

        return $method_array[0];
    }
}

if (!function_exists('get_api_url')) {
    /**
     * 获取API访问地址
     */
    function get_api_url(array $array)
    {
        $url = null;
        $url = $array['model'] ? '/'.$array['hash'] : $array['class'];
        return (string)url($url,[],false,'api');
    }
}
