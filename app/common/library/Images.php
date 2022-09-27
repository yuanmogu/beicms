<?php
declare (strict_types = 1);

namespace app\common\library;

use think\Image;

/**
 * @mixin \think\Images
 */
class Images 
{
    /**
     * 水印函数
     * @access public
     * @param  string  $filename   文件路径	 
     * @param  array   $config     配置数组 
     * @return object
     */
	public function waterMark($filename,$config) {

		try {
			
			// 获取文件信息
			$Image = Image::open($filename);
			$ImageInfo = getimagesize($filename);
			
			// 判断水印类型
			if (isset($config['type']) && $config['type'] == 'text') { // 文字水印
				$size = $config['size'] ? $config['size'] : 15;
				$color = $config['color'] ?: '#000000';
				$ttf = public_path().DIRECTORY_SEPARATOR.'static'.DIRECTORY_SEPARATOR.'fonts'.DIRECTORY_SEPARATOR.'default.ttf';
				if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
					$color = '#000000';
				}
				
				// 设置透明度
				$transparency = intval((100 - $config['opacity']) * (127/100));
				$color .= dechex($transparency);	

				$reswater = $Image->text($config['text'],$ttf,$size,$color,$config['position'])->save($filename);
			}

			if (isset($config['type']) && $config['type'] == 'image'){
				

				$water_image = public_path().$config['image'];

				if (!file_exists($water_image)) {
					return false;
				}

				$ImageWaterInfo = getimagesize($water_image);

				// 对比图片大小
				if ($ImageWaterInfo[0] >= $ImageInfo[0] || $ImageWaterInfo[1] >= $ImageInfo[1]) {
					return false;
				}

				if ($ImageInfo[0] <= 800 && $ImageInfo[1] <= 600) {
					return false;
				}

				// 检查图片
				$reswater = $Image->water($water_image,$config['position'],$config['opacity'])->save($filename);
			}
			
			return $reswater ?? $filename;
		}
		catch(\Throwable $th){
			throw new \Exception($th->getMessage());
		}
	}
	
    /**
     * 微缩图函数
     * @access public
     * @param  string  $resource   文件
     * @param  array   $config     配置数组 
	 * @param  string  refresh     重新生成
     * @return object
     */
	public function thumb($resource, $width='300', $heigth='300', $type='3', $refresh = false) {
		try {
			
			$filehost = parse_url($resource, PHP_URL_HOST);
			$filepath = parse_url($resource, PHP_URL_PATH);
			$filename = basename($resource);

			if (!empty($filehost)) {
                $filehost = explode('.',$filehost);
                $count = count($filehost);
                $filehost  = $count > 1 ? $filehost[$count - 2] . '.' . $filehost[$count - 1] : $filehost[0];
            }

            // 过滤非本站链接
            $domain = request()->rootDomain();

			// 是否是本站链接
			if ($filehost && $filehost != $domain) {
				return $resource;
			}

			// 转为本地路径
			$resource = public_path().$filepath;

			// 拼接新缩略图的路径
			$path = strstr($filepath, $filename, TRUE);
			$savename =  public_path().$path.'thumb_'.$width.'_'.$heigth.'_'.$filename;

			// 是否已经存在缩略图，或者强制刷新缩略图
			if(!file_exists($savename) || $refresh){

				// 判断图片大小，原图尺寸不得小于微缩图
				$ImageInfo = getimagesize($resource);
				if ($ImageInfo[0] >= $width || $ImageInfo[1] >= $heigth) {
					$Image = Image::open($resource); 
					$Image->thumb($width,$heigth,$type)->save($savename,NULL, 80);
				}else{
					return $resource;
				}
			}

			return $path.'thumb_'.$width.'_'.$heigth.'_'.$filename;
		
		}
		catch(\Exception $e){
			return $resource;
		}
	}	

    /**
     * 图片压缩
     * @access public
     * @param  string  $resource   文件名	 
     * @param  array   $config     配置数组 
     * @return object
     */
	public function compress($resource,$config) {
		
		try {
			// 判断图片大小
			$ImageInfo = getimagesize($resource);
			
			$Image = Image::open($resource); 


			if ($ImageInfo[0] > $config['width'] || $ImageInfo[1] > $config['height']) {	  
				$resthumb = $Image->thumb($config['width'], $config['height'])->save($resource,NULL, $config['quality']);
			}else{
				$resthumb = $Image->save($resource,NULL, $config['quality']);
			}		

			return $resthumb;
			
		}
		catch(\Exception $e){
			return $resource;
		}
	}	
}
