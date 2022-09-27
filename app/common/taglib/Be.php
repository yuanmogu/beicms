<?php
/**
 * +----------------------------------------------------------------------
 * | 自定义标签
 * +----------------------------------------------------------------------
 */
namespace app\common\taglib;

use think\template\TagLib;

class Be extends TagLib {

    protected $tags = array(
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'close'     => ['attr' => 'time,format', 'close' => 0],			// 闭合标签，默认为不闭合
        'open'      => ['attr' => 'name,type', 'close' => 1],
        
		'breadcrumb'  => ['attr'=>'symbol,class', 'close'=>0],// 面包屑导航

        'cate'      => ['attr' => 'id,name','close' => 0],				// 通用栏目信息

		'slider'	=> ['attr' => 'name,type,limit', 'close' => 1],		// 通用幻灯信息
        'link'      => ['attr' => 'name,limit,type','close' => 1],		// 获取友情链接
		
		'catelist'		=> ['attr' => 'name,cid,mid,limit,order,current,empty', 'close' => 1],		// 通用频道信息
		'arclist'		=> ['attr' => 'name,cid,mid,sid,field,where,pagesize,limit,order,model,flag,tags,empty', 'close' => 1],
		'speclist'		=> ['attr' => 'name,limit,order,type,pagesize,empty', 'close' => 1],


       
    );

    // 这是一个闭合标签的简单演示
    public function tagClose($tag)
    {
        $format = empty($tag['format']) ? 'Y-m-d H:i:s' : $tag['format'];
        $time   = empty($tag['time'])   ? time()        : $tag['time'];
        $parse  = '<?php ';
        $parse .= 'echo date("' . $format . '",' . $time . ');';
        $parse .= ' ?>';
        return $parse;
    }

    // 这是一个非闭合标签的简单演示
    public function tagOpen($tag, $content)
    {
        $type   = empty($tag['type']) ? 0 : 1; // 这个type目的是为了区分类型，一般来源是数据库
        $name   = $tag['name'];                // name是必填项，这里不做判断了
        $parse  = '<?php ';
        $parse .= '$test_arr=[[1,3,5,7,9],[2,4,6,8,10]];'; // 这里是模拟数据
        $parse .= '$__LIST__ = $test_arr[' . $type . '];';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    // 通用导航信息
    Public function tagChannel($tag, $content)
    {
		$name			= $tag['name'] ?? 'item';
        $style			= $tag['style'] ?? '';
        $type			= $tag['type'] ?? '';
        
        $parse  = '<?php ';
        $parse .= '$__CHANNEL__ = \app\common\model\CmsChannel::getTaglib(\'' . $style . '\', \'' . $type . '\');';
        $parse .= ' ?>';
        $parse .= '{volist name="__CHANNEL__" id="' . $name . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }



    // 通用栏目信息
    Public function tagCate($tag)
    {
        $id     = $tag['id']     ?? get_cateid();
        $name   = $tag['name']   ?? 'title';
       

        $str  = '<?php ';
        $str .= '$__CATE__ = \app\common\model\CmsCategory::where("id",' . $id . ')->find();';

        $str .= 'echo $__CATE__[\'' . $name . '\'];';
        $str .= '}';
        $str .= '?>';
        return $str;
    }

    // 通用位置信息
    Public function tagBreadcrumb($tag, $content)
    {
        $symbol = !empty($tag['symbol']) ? $tag['symbol']: ' ';
        $class	= !empty($tag['class']) ? $tag['class']: ' ';

        $parseStr = '<?php'."\r\n";
        $parseStr .= 'echo \app\common\model\CmsCategory::getBreadcrumb(\'' . $symbol . '\', \'' . $class . '\');'."\r\n";
        $parseStr .= '?>';
        return $parseStr;
    }


    // 获取TAGS信息
    Public function tagSlider($tag, $content)
    {
        $name	= $tag['name'] ?? 'item';
		$type	= $tag['type'] ?? ''; 
		$limit	= $tag['limit'] ?? '8'; 
        $parse = '<?php ';       
        $parse .= '$__LIST__ = \app\common\model\SiteSlider::getTaglib(\'' . $type . '\', \'' . $limit . '\');';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

	// 获取链接信息
    Public function tagLink($tag, $content)
    {
        $name	= $tag['name'] ?? 'item';
		$type	= $tag['type'] ?? ''; 
		$limit	= $tag['limit'] ?? '8'; 
        $parse = '<?php ';       
        $parse .= '$__LIST__ = \app\common\model\SiteLinks::getTaglib(\'' . $type . '\', \'' . $limit . '\');';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }



	// 获取单页信息
    Public function tagCatelist($tag, $content)
    {
        $name	= $tag['name'] ?? 'list';  
		$empty		= isset($tag['empty']) ? $tag['empty'] : '';

        $params = [];
        foreach ($tag as $k => & $v) {
            $params[] = '"' . $k . '"=>"' . $v . '"';
        }

        $parse = '<?php ';
        $parse .= '$__CATELIST__ = \app\common\model\CmsCategory::getTaglib([' . implode(',', $params) . ']);';
        $parse .= ' ?>';
        $parse .= '{volist name="__CATELIST__" id="' . $name . '" empty="' . $empty . '"  }';
        $parse .= $content;
        $parse .= '{/volist}';
		
        return $parse;
    }

	
	// 获取专题信息
    Public function tagSpeclist($tag, $content)
    {
        $name	= $tag['name'] ?? 'list';  
		$empty		= isset($tag['empty']) ? $tag['empty'] : '';
		$pagesize	= isset($tag['pagesize']) ? (int)$tag['pagesize'] : 0;

        $params = [];
        foreach ($tag as $k => & $v) {
            $params[] = '"' . $k . '"=>"' . $v . '"';
        }

        $parse = '<?php ';
        $parse .= '$__SPECLIST__ = \app\common\model\CmsSpecial::getTaglib([' . implode(',', $params) . ']);';
        $parse .= ' ?>';
        $parse .= '{volist name="__SPECLIST__" id="' . $name . '" empty="' . $empty . '"  }';
        $parse .= $content;
        $parse .= '{/volist}';

		if ($pagesize > 0) {
            $parse .= '{php}$' . $name . '_page = $__SPECLIST__->render();{/php}';
        }
		
        return $parse;
    }

	// 通用列表
    public function tagArclist($tag, $content)
    {
        $name		= $tag['name'] ?? 'item';  
        $empty		= isset($tag['empty']) ? $tag['empty'] : '';
		$pagesize	= isset($tag['pagesize']) ? (int)$tag['pagesize'] : 0;

        $params = [];
        foreach ($tag as $k => & $v) {
            $params[] = '"' . $k . '"=>"' . $v . '"';
        }

        $parse = '<?php ';
        $parse .= '$__ARCLIST__ = \app\common\model\CmsArchives::getTaglib([' . implode(',', $params) . ']);';
        $parse .= ' ?>';
        $parse .= '{volist name="__ARCLIST__" id="' . $name . '" empty="' . $empty . '" }';
        $parse .= $content;
        $parse .= '{/volist}';

		if ($pagesize > 0) {
            $parse .= '{php}$' . $name . '_page = $__ARCLIST__->render();{/php}';
        }
        return $parse;
    }




}