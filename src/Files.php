<?php
namespace TJM\Files;

use Exception;

class Files{
	/*
	Method: rmdir
	Remove directory, recursively removing contents.
	Sources:
		-@ http://stackoverflow.com/a/11267139/1139122
	*/
	static public function rmdir($dir){
		if($dir && is_dir($dir)){
			foreach(glob($dir . '/{.??*,.[!.],*}', GLOB_BRACE) as $file){
				if(is_dir($file)){
					static::rmdir($file);
				}else{
					unlink($file);
				}
			}
			rmdir($dir);
		}else{
			throw new Exception("Files::mdir(): '{$dir}' is not a directory.");
		}
	}
	/*
	Method: symlink
	Simple passthrough to PHP symlink
	*/
	static public function symlink($at, $from){
		return symlink($from, $at);
	}

	/*
	Method: symlinkRelativelySafely
	Symlink using relative path.  Create parent folders if they don't exist.  Do not overwrite existing file unless it is a symlink.
	*/
	static public function symlinkRelativelySafely($at, $from){
		//-! dot should properly be handled in `getRelativePathTo()`
		if(substr($at, 0, 1) !== '.' && substr($at, 0, 1) !== '/'){
			$at = "./{$at}";
		}
		if(substr($from, 0, 1) !== '.' && substr($from, 0, 1) !== '/'){
			$from = "./{$from}";
		}
		if(substr($at, 0, 1) !== '.' && substr($from, 0, 1) !== false){
			$from = static::getRelativePathTo($from, $at);
		}

		if(is_link($at)){
			if(readlink($at) !== $from){
				unlink($at);
			}
		}elseif(is_dir($at)){
			$at .= '/' . basename($from);
		}
		if(!is_dir(dirname($at))){
			mkdir(dirname($at), 0755, true);
		}
		if(!file_exists($at)){
			return static::symlink($at, $from);
		}else{
			return false;
		}
	}


	/*
	Method: getRelativePathTo
	Get relative path from one location to another for symlinking purposes.
	Sources:
		-@ https://github.com/symfony/Routing/blob/master/Generator/UrlGenerator.php#L302
		-@ http://stackoverflow.com/questions/2637945/getting-relative-path-from-absolute-path-in-php
		-@ https://gist.github.com/ohaal/2936041
	*/
	static public function getRelativePathTo($to, $from){
		if(!(is_dir($from) && !is_link($from))){
			$from = dirname($from);
		}
		if(file_exists($to)){
			$to = realpath($to);
		}
		if(file_exists($from) && strpos($from, '../') !== false){
			$from = realpath($from);
		}
		$toDir = dirname($to);
		if($toDir === $from){
			return '.' . DIRECTORY_SEPARATOR. str_replace($toDir, '', $to);
		}else{
			$toParents = explode(DIRECTORY_SEPARATOR, substr($to, 1));
			$toEnd = array_pop($toParents);
			$fromParents = explode(DIRECTORY_SEPARATOR, substr($from, 1));
			foreach($fromParents as $i=> $fromParentsDir){
				if(isset($toParents[$i]) && $toParents[$i] === $fromParents[$i]){
					unset($toParents[$i], $fromParents[$i]);
				}else{
					break;
				}
			}
			return str_repeat('..' . DIRECTORY_SEPARATOR, count($fromParents)) . implode(DIRECTORY_SEPARATOR, $toParents) . DIRECTORY_SEPARATOR . $toEnd;
		}
	}
}
