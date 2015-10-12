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
 */

/**
 * Kernel class, required to run the framework
 */
class Kernel
{
	/**
	 * Holds an array with all the used modules of the framework
	 * @var array
	 */
	static $modList;

	/**
	 * Constructs the Kernel class and sets the error handlers, shutdown functions, autoload functions and exception handlers
	 * @return void
	 */
	public function __construct()
	{
		global $config;

		self::$modList = array('Kernel');

		set_error_handler('Kernel::Error', E_ALL);
		set_exception_handler('Kernel::Exception');

		spl_autoload_register('Kernel::LoadModule');
		register_shutdown_function('Kernel::Shutdown');
	}

	/**
	 * SonicWulf's uncaught exception handler
	 * @param Exception $ex Exception provided by the PHP exception handler
	 * @return void
	 */
	static function Exception($ex)
	{
    	echo Tpl::includeSystem();
        echo "<div class=\"sonicwulf_overlay\"></div>";
        echo "<div class=\"sonicwulf_fatal animated fadeInDown\">";
        echo "<div class=\"info\"><h1>Uncaught exception!</h1>";
        echo "<b>Dump:</b><br><pre>Exception caught on line ".$ex->getLine()." in file ".$ex->getFile()."<br><br>";
        echo "PHP said: ".$ex->getMessage()."";
        echo "<br><br>PHP " . PHPV . " (" . OS . ")<br /><br>";
        echo "Host: ".HOST."<br>Requested from: ".REF;
        echo "</pre></div>";
        exit(1);
	}

	/**
	 * Returns the total amount of dimensions an array has
	 * @param  array $array  Array to count
	 * @return int           Number of dimensions
	 */
	static function getArrayDimensions($array)
	{
	    if ( is_array(reset($array)) )
	        $return = self::getArrayDimensions(reset($array)) + 1;
	    else
	        $return = 1;

	    return $return;
	}

	/**
	 * SonicWulf's custom error handler
	 * @param integer $errno   Error number provided by PHP
	 * @param string  $errstr  Error message provided by PHP
	 * @param string  $errfile Error causing file provided by PHP
	 * @param integer $errline Line where the error was caused
	 * @return void
	 */
	static function Error($errno, $errstr, $errfile, $errline)
	{
		global $config;

		$backtrace = debug_backtrace();

		if ( strpos($errfile, 'eval') !== false )
			$newfile = $config['kernel']['currentSource'];
		else
			$newfile = $errfile;

	    switch ($errno) 
	    {
		    case E_ERROR:
		    	echo Tpl::includeSystem();
		    	echo "<title>Fatal error!</title>";
		        echo "<div class=\"sonicwulf_overlay\"></div>";
		        echo "<div class=\"sonicwulf_fatal animated fadeInDown\">";
		        echo "<div class=\"info\"><h1>FATAL EHRROHR!!!!11 [".$errno."]</h1>";
		        echo "<b>Dump:</b><br><pre>Fatal error near line $errline in file $newfile<br><br>";
		        echo "PHP said: ".$errstr."";
		        echo "<br><br>PHP " . PHPV . " (" . OS . ")<br /><br>";
		        echo "Host: ".HOST."<br>Requested from: ".REF;
		        echo "</pre>Powered by <b>".SWVER."</b></div>";
		        exit(1);
		        break;

		    case E_WARNING:
		        echo "<div class=\"sonicwulf_msg\"><b>Warning! [$errno]</b> $errstr on line $errline in file $newfile</div>";
		        break;

		    case E_NOTICE:
				echo "<div class=\"sonicwulf_msg\"><b>Notice! [$errno]</b> $errstr on line $errline in file $newfile</div>";
		        break;

		    case E_USER_ERROR:
		    	echo Tpl::includeSystem();
		    	echo "<title>Fatal error!</title>";
		        echo "<div class=\"sonicwulf_overlay\"></div>";
		        echo "<div class=\"sonicwulf_fatal animated fadeInDown\">";
		        echo "<div class=\"info\"><h1>FATAL EHRROHR!!!!11 [".$errno."]</h1>";
		        echo "<b>Dump:</b><br><pre>Fatal error near line $errline in file $newfile<br><br>";
		        echo "PHP said: ".$errstr."";
		        echo "<br><br>PHP " . PHPV . " (" . OS . ")<br /><br>";
		        echo "Host: ".HOST."<br>Requested from: ".REF;
		        echo "</pre>Powered by <b>".SWVER."</b></div>";
		        exit(1);
		        break;

		    case E_USER_WARNING:
		        echo "<div class=\"sonicwulf_msg\"><b>Warning! [$errno]</b> $errstr on line $errline in file $newfile</div>";
		        break;

		    case E_USER_NOTICE:
				echo "<div class=\"sonicwulf_msg\"><b>Notice! [$errno]</b> $errstr on line $errline in file $newfile</div>";
		        break;

		    default:
		        echo "<div class=\"sonicwulf_msg\"><b>Unknown error type $errno:</b> $errstr [$errno] on line $errline in file $newfile</div>";
		        break;
    	}

	    return true;
	}

	/**
	 * Prints an error dialogue
	 * @param  string  $msg  Message that'll be displayed
	 * @param  integer $line Line number of the original error
	 * @param  string  $file File where the error originated
	 * @param  string  $type Type of error we should display (May be block (small) or full (full screen))
	 * @return void
	 */
	static function printError($msg, $line, $file, $type = 'block')
	{
		switch( $type )
		{
		    case 'full':
		    	echo Tpl::includeSystem();
		    	echo "<title>Fatal error!</title>";
		        echo "<div class=\"sonicwulf_overlay\"></div>";
		        echo "<div class=\"sonicwulf_fatal animated fadeInDown\">";
		        echo "<div class=\"info\"><h1>FATAL EHRROHR!!!!11</h1>";
		        echo "<b>Dump:</b><br><pre>Fatal error near line $line in file $file<br><br>";
		        echo "SonicWulf said: ".$msg."";
		        echo "<br><br>PHP " . PHPV . " (" . OS . ")<br /><br>";
		        echo "Host: ".HOST."<br>Requested from: ".REF;
		        echo "</pre>Powered by <b>".SWVER."</b></div>";
		        exit(1);
		        break;

		    case 'block':
		        echo "<div class=\"sonicwulf_msg\"><b>Error!</b> $msg on line $line in file $file</div>";
		        break;		
		}
	}

	/**
	 * Logs to the main log file
	 * @param string $message Message to log
	 * @return bool           Returns true if the log was successful
	 */
	static function Log($message)
	{
		global $config;

		$dump = debug_backtrace();

		if ( isset($dump[1]['class']) && in_array($dump[1]['class'], self::$modList) )
			$type = $dump[1]['class'];
		else
			$type = 'Unknown';

		$backtrace = $dump;

		$req_key = 0;

		foreach ( $backtrace as $key => $i )
		{
			if ( isset($i['file']) )
			{
				if ( strpos($i['file'], 'sources') )
				{
					$req_key = $key;
				}
			}
		}

		if ( !is_array($dump) && $config['kernel']['errorLevel'] > 1 )
		{
			$write = file_put_contents(PATH.'/'.LOGFILE, '['.time().'][0] The $dump argument must be provided'."\n", FILE_APPEND);
			return false;			
		}

		if ( $config['kernel']['errorLevel'] == 1 )
		{
			$write = file_put_contents(PATH.'/'.LOGFILE, '['.time().']['.$type.'] '.$message."\n", FILE_APPEND);			
		}
		elseif ( $config['kernel']['errorLevel'] == 2 )
		{
			$caller = array_shift($dump);
			$write = file_put_contents(PATH.'/'.LOGFILE, '['.time().']['.$type.'] '.$message." on file ".$backtrace[$req_key]['file']." on line ".$backtrace[$req_key]['line']."\n", FILE_APPEND);
		}
		elseif ( $config['kernel']['errorLevel'] == 3 )
		{
			preg_match('/(.*){x}(.*)/', DUMPFILE, $matches);

			$filename = $matches[1].time().$matches[2];

			$caller = array_shift($dump);

			$write = file_put_contents(PATH.'/'.LOGFILE, '['.time().']['.$type.'] '.$message." on file ".$backtrace[$req_key]['file']." on line ".$backtrace[$req_key]['line'].". A dump has been made on ".$filename."\n", FILE_APPEND);
		
			file_put_contents(PATH.'/'.$filename, var_export(debug_backtrace(), true));
		}

		if ( $config['kernel']['showLog'] )
			self::printError($message, $backtrace[$req_key]['line'], $backtrace[$req_key]['file']);

		if ( !$write )
		{
			echo "<center><h3>Could not write to log, please check write permissions.</h3></center>";
			return false;
		}
		else
			return true;
	}

	/**
	 * Attemps to find a lost file
	 * @param  string $file Filename to look for
	 * @return mixed        Returns a bool if the file was not found or it may return a string with the new path
	 */
	static function findFile($file)
	{
		if ( !file_exists($file) )
		{
			for ( $i = 0; $i <= 2; $i++ )
			{
				if ( !isset($fakeFile) )
				{
					switch ( $i )
					{
						case 0:
							$fileSplit = str_split($file);
							$fileSplit[0] = strtoupper($file[0]);

							$fileImplode = implode('', $fileSplit);

							if ( file_exists($fileImplode) )
								$fakeFile = $fileImplode;
						break;
						case 1:
							if ( file_exists(strtoupper($file)) )
								$fakeFile = strtoupper($file);
						break;
						case 2:
							if ( file_exists(strtolower($file)) )
								$fakeFile = strtolower($file);
						break;
					}
				}
				else
					break;
			}

			if ( !isset($fakeFile) )
				return false;
			else
				return $fakeFile;
		}
		else
			return $file;	
	}

	/**
	 * SonicWulf's autoload function
	 * @param string $module Name of the module to load
	 * @return void
	 */
	static function LoadModule($module)
	{
		if ( !file_exists(PATH.'/inc/'.$module.'.php') )
		{
			if ( !$file = self::findFile(PATH.'/inc/'.$module.'.php') )
				self::Log('Could not load class '.$module);
			else
			{
				self::$modList[] = $module;
				include(PATH.'/inc/'.$modulefile.'.php');
			}
		}
		else
		{
			self::$modList[] = $module;
			include(PATH.'/inc/'.$module.'.php');
		}
	}
	
	/**
	 * Sets a sitevar from the database
	 * @param string $var   Name of the variable to work with
	 * @param string $value New value of the variable
	 * @return bool         Returns true if the query was successful
	 */
	static function setSitevar($var, $value)
	{
		if ( Sql::numRows("SELECT name FROM sitevars WHERE name = '".$var."'") > 0 )
			return Sql::Update('sitevars', array("value" => $value), "WHERE name = '".$var."'");
		else
			return Sql::Insert('sitevars', array("name"	=> $var, "value" => $value));
	}

	/**
	 * Returns the number of real lines (No comments or line breaks) of the whole SonicWulf Kernel
	 * @return integer Amount of lines
	 */
	static function getCoreLines()
	{
		$files['Init.php'] = PATH.'/Init.php';

		$mods = scandir(PATH.'/inc/');

		unset($mods[0]);
		unset($mods[1]);

		$mods = array_values($mods);

		foreach ( $mods as $i )
		{
			$files[$i] = PATH.'/inc/'.$i;
		}

		$lines = 0;
		$array = array();
		$array['totalLines'] = 0;

		foreach ( $files as $key => $i )
		{
			$lineTemp = Text::countRealLines($i);

			$array[$key] = $lineTemp;

			$array['totalLines'] += $lineTemp;
		}

		return $array;		
	}

	/**
	 * Returns an HTTP header based on a code
	 * @param  integer $code Code to fetch
	 * @return string        Returns the HTTP header text
	 */
	static function getHTTP($code)
	{
		switch ( $code )
		{
			case 401:
				return $_SERVER['SERVER_PROTOCOL'] . " 401 Unauthorized";
			case 403:
				return $_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden";
			case 404:
				return $_SERVER['SERVER_PROTOCOL'] . " 404 Not Found";
			case 500:
				return $_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error';
			default:
				return false;
		}
	}

	/**
	 * Checks if a request was made through Ajax
	 * @return boolean Returns true if it was made through Ajax
	 */
	static function isAjax()
	{
		if ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' )
			return true;
		else
			return false;
	}

	/**
	 * Returns the first X words of a string
	 * @param  string  $string String to work with
	 * @param  integer $words  Number of words
	 * @return string          Sliced string
	 */
	static function getWords($string, $words = 5)
	{
		return implode(' ', array_slice(str_word_count($string, 2), 0, $words));
	}

	/**
	 * Shutdown function called by PHP at the end of a script. Handles MVC.
	 * @return void
	 */
	static function Shutdown()
	{
		global $page;

		if ( !isset($page['header']) )
			$header = 'header';
		else
			$header = $page['header'];

		if ( !isset($page['footer']) )
			$footer = 'footer';
		else
			$footer = $page['footer'];

		if ( isset($page) && !empty($page['id']) )
			echo Tpl::LoadTpl($page['id'], $page['name'], $header.'.php', $footer.'.php');
	}

	static function areEmpty()
	{
		$args = func_get_args();

		foreach ( $args as $i )
		{
			if ( empty($_POST[$i]) OR !isset($_POST[$i]) )
				return $i;
		}

		return false;
	}

	static function isFileSet($key, $formats = array('gif', 'png', 'jpg'))
	{
		$allowed =  $formats;
		$filename = $_FILES[$key]['name'];

		$ext = pathinfo($filename, PATHINFO_EXTENSION);

		if( !in_array($ext, $allowed) )
			return false;
		else
			return true;
	}

	static function uploadFile($index, $sizelimit = 2000000, $minify = true, $formats = array('gif', 'png', 'jpg'))
	{
		file_put_contents('test', var_export($_FILES, true));
		
		if ( $_FILES[$index]['error'] == 0 )
		{
			if ( $_FILES[$index]['size'] < $sizelimit )
			{
				if ( self::isFileSet($index, $formats) )
				{
					$uniq = uniqid().rand().uniqid();
					if ( copy($_FILES[$index]['tmp_name'], PATH.'/resources/img/userimg/'.$uniq.$_FILES['pic']['name']) )
					{
						$url = Revision::minifyImg('img/userimg/'.$uniq.$_FILES[$index]['name']);

						if ( !$url )
						{
							unlink(PATH.'/resources/img/userimg/'.$uniq.$_FILES[$index]['name']);
							return 'minify';
						}

						return $url;
					}
					else
						return 'copy';
				}
				else
					return 'format';
			}
			else
				return 'oversize';
		}
		else
			return 'copy';
	}
}