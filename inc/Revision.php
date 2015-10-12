<?

/**
 *
 * SonicWulf v1.0.0 Gold release PHP Framework
 * 
 * @package SonicWulf
 * @version v1.0.0
 * @author  Cristian Herrera <cristian.herrera@studiowolfree.com>
 * @copyright  Copyright 2015 (c) Studio Wolfree. Coral Springs, FL.
 * @link http://dev.studiowolfree.net
 *
 * The revision system allows you to archive and manage versions of frontend files. Aside from intelligent file optimization.
 * 
 */

/**
 * Revision class, optional
 */
class Revision
{
	static $minify = true;
	static $compressImg = true;

	static $archivePath;
	static $resourcePath;
	
	static function areEqual($filename)
	{
		self::$archivePath = PATH.'/archive';
		self::$resourcePath = PATH.'/resources';

		if ( self::GetFile($filename) == false )
			return 0;
		else
		{
			if ( md5_file(self::$archivePath.'/'.str_replace('/', '__', $filename)) == md5_file(self::$resourcePath.'/'.self::getFileDir($filename).'/'.self::GetFile($filename)) )
				return true;
			else
				return false;
		}
	}

	static function getFileDir($filename)
	{
		self::$archivePath = PATH.'/archive';
		self::$resourcePath = PATH.'/resources';

		$files = file(self::$archivePath.'/cacheTable');

		$array = array();

		$index = 0;
		foreach ( $files as $line )
		{
			preg_match_all('/(.*):::!@#!@\$!\$\$\$%\^\^:::/U', $line, $matches, PREG_SET_ORDER);
			$array[$index] = $matches;

			$index++;
		}
		
		foreach ( $array as $element )
		{
			if ( trim($element[0][1]) == trim($filename) )
			{
				return $element[2][1];
			}
		}

		return false;	
	}

	static function GetFile($filename)
	{
		self::$archivePath = PATH.'/archive';
		self::$resourcePath = PATH.'/resources';

		$files = file(self::$archivePath.'/cacheTable');

		$array = array();

		$index = 0;
		foreach ( $files as $line )
		{
			preg_match_all('/(.*):::!@#!@\$!\$\$\$%\^\^:::/U', $line, $matches, PREG_SET_ORDER);
			$array[$index] = $matches;

			$index++;
		}

		foreach ( $array as $key => $element )
		{
			if ( trim($element[0][1]) == trim($filename) )
			{
				if ( !file_exists(self::$archivePath.'/'.str_replace('/', '__', $element[0][1])) )
				{
					unset($files[$key]);

					$files = array_values($files);

					unlink(self::$resourcePath.'/'.self::getFileDir($filename).'/'.$element[1][1]);

					file_put_contents(self::$archivePath.'/cacheTable', implode($files, "\n"));

					return false;
				}

				return $element[1][1];
			}
		}

		return false;
	}

	/*static function archivedExists($filename)
	{
		self::$archivePath = PATH.'/archive';
		self::$resourcePath = PATH.'/resources';

		$files = file(self::$archivePath.'/cacheTable');

		$array = array();

		$index = 0;
		foreach ( $files as $line )
		{
			preg_match_all('/(.*):::!@#!@\$!\$\$\$%\^\^:::/U', $line, $matches, PREG_SET_ORDER);
			$array[$index] = $matches;

			$index++;
		}

		foreach ( $array as $key => $element )
		{
			if ( trim($element[1][1]) == trim($filename) )
			{
				if ( file_exists(self::$archivePath.'/'.str_replace('/', '__', $element[0][1]) ) )
					return true;
				else
					return $key;
			}
		}

		return false;		
	}

	static function minifiedExists($filename)
	{
		self::$archivePath = PATH.'/archive';
		self::$resourcePath = PATH.'/resources';

		$files = file(self::$archivePath.'/cacheTable');

		$array = array();

		$index = 0;
		foreach ( $files as $line )
		{
			preg_match_all('/(.*):::!@#!@\$!\$\$\$%\^\^:::/U', $line, $matches, PREG_SET_ORDER);
			$array[$index] = $matches;

			$index++;
		}

		foreach ( $array as $key => $element )
		{
			if ( trim($element[0][1]) == trim($filename) )
			{
				if ( file_exists(self::$resourcePath.'/'.$element[2][1].'/'.$element[1][1]) )
					return true;
				else
					return $key;
			}
		}

		return false;		
	}

	static function deleteEntry($key)
	{
		self::$archivePath = PATH.'/archive';
		self::$resourcePath = PATH.'/resources';

		$files = file(self::$archivePath.'/cacheTable');

		unset($files[$key]);

		$files = array_values($files);

		return file_put_contents(self::$archivePath.'/cacheTable', implode($files, "\n"));
	}*/

	static function GetFileAndUpdate($filename, $type)
	{
		if ( self::GetFile($filename) !== false )
		{
			if ( self::areEqual($filename) === false )
			{
				$fileC = self::$resourcePath.'/'.self::getFileDir($filename).'/'.self::GetFile($filename);
				$sourceFile = self::$archivePath.'/'.str_replace('/', '__', $filename);

				if ( $type == 'js' )
				{
					$minified = JSMin::minify(file_get_contents($sourceFile));

					file_put_contents($fileC, $minified);

					return self::GetFile($filename);
				}
				elseif ( $type == 'css' )
				{
					$buffer = "";
					
					$buffer .= file_get_contents($sourceFile);

					$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
					$buffer = str_replace(': ', ':', $buffer);
					$buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);

					file_put_contents($fileC, $buffer);

					return self::GetFile($filename);
				}
				elseif ( $type == 'img' )
				{
					/*$info = getimagesize($sourceFile);
					$quality = 60;

					$destination_url = uniqid().rand().uniqid().rand().md5(uniqid()).base64_encode(uniqid());
				 
					if ($info['mime'] == 'image/jpeg') 
					{ 
						$image = imagecreatefromjpeg($sourceFile); 

						$destination_url .= '.jpg';

						imagejpeg($image, $destination_url, $quality); 
					}
					elseif ($info['mime'] == 'image/gif') 
					{
						$image = imagecreatefromgif($sourceFile); 

						$destination_url .= '.gif';

						imagejpeg($image, $destination_url, $quality); 
					}
					elseif ($info['mime'] == 'image/png') 
					{ 
						$image = imagecreatefrompng($sourceFile); 

						$destination_url .= '.png';

						imagepng($image, $destination_url, round(($quality / 10))); 
					}

					$cont = file_get_contents($destination_url);

					unlink($cont);

					file_put_contents($fileC, $cont);*/

					return self::GetFile($filename);			
				}
				else
					return false;
			}
			else
				return self::GetFile($filename);
		}
	}

	static function minifyJS($origin, $take = false)
	{
		self::$archivePath = PATH.'/archive';
		self::$resourcePath = PATH.'/resources';
		
		if ( file_exists(self::$resourcePath.'/'.$origin) === true )
		{
			$minified = JSMin::minify(file_get_contents(self::$resourcePath.'/'.$origin));

			$name = self::addToTable($origin, $minified);

			return $name;
		}
		else
			return self::GetFileAndUpdate($origin, 'js');
	}

	static function addToTable($file, $content)
	{
		self::$archivePath = PATH.'/archive';
		self::$resourcePath = PATH.'/resources';
		$archive_name = uniqid().'__'.rand().'__swr__'.str_replace('/', '', $file);

		file_put_contents(self::$resourcePath.'/'.dirname($file).'/'.$archive_name, $content);

		file_put_contents(self::$archivePath.'/cacheTable', $file.':::!@#!@$!$$$%^^:::'.$archive_name.':::!@#!@$!$$$%^^:::'.dirname($file).':::!@#!@$!$$$%^^:::'."\n", FILE_APPEND);

		if ( copy(self::$resourcePath.'/'.$file, self::$archivePath.'/'.str_replace('/', '__', $file)) )
			unlink(self::$resourcePath.'/'.$file);

		return $archive_name;
	}

	static function minifyCSS($origin, $take = false)
	{
		self::$archivePath = PATH.'/archive';
		self::$resourcePath = PATH.'/resources';

		if ( file_exists(self::$resourcePath.'/'.$origin) === true )
		{
			$buffer = "";

			$buffer .= file_get_contents(self::$resourcePath.'/'.$origin);

			$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
			$buffer = str_replace(': ', ':', $buffer);
			$buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
		
			return self::addToTable($origin, $buffer);
		}
		else
			return self::GetFileAndUpdate($origin, 'css');
	}

	static function minifyImg($origin) 
	{
		self::$archivePath = PATH.'/archive';
		self::$resourcePath = PATH.'/resources';

		if ( file_exists(self::$resourcePath.'/'.$origin) === true )
		{
			$info = getimagesize(self::$resourcePath.'/'.$origin);
			$quality = 60;

			$destination_url = self::$resourcePath.'/temp/'.uniqid().rand().uniqid().rand().md5(uniqid()).base64_encode(uniqid());
		 
			if ($info['mime'] == 'image/jpeg') 
			{ 
				$image = imagecreatefromjpeg(self::$resourcePath.'/'.$origin); 

				$destination_url .= '.jpg';

				$do = imagejpeg($image, $destination_url, $quality);

				if ( !$do )
					return false;
			}
			elseif ($info['mime'] == 'image/gif') 
			{
				$image = imagecreatefromgif(self::$resourcePath.'/'.$origin); 

				$destination_url .= '.gif';

				$do = imagejpeg($image, $destination_url, $quality);

				if ( !$do )
					return false;
			}
			elseif ($info['mime'] == 'image/png') 
			{ 
				$image = imagecreatefrompng(self::$resourcePath.'/'.$origin); 

				$destination_url .= '.png';

				imagealphablending($image, false);
				imagesavealpha($image, true);

				$do = imagepng($image, $destination_url, round(($quality / 10)));

				if ( !$do )
					return false;
			}
			else
				return false;

			$cont = file_get_contents($destination_url);

			unlink($destination_url);

			return self::addToTable($origin, $cont);
		}
		else
		{
			return self::GetFileAndUpdate($origin, 'img');
		}
	}
}