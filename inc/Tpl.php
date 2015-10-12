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
 * Tpl class, required to run the framework
 */
class Tpl
{
	/**
	 * Holds all the sitevars (%var%) that'll be used in parsing
	 * @var array
	 */
	static $sitevars;

	/**
	 * Holds all the scripts that'll be included in the head or are called from the parser
	 * @var array
	 */
	static $scripts = array();

	/**
	 * Holds all the styles that'll be included in the head or are called from the parser
	 * @var array
	 */
	static $styles = array();

	/**
	 * Holds all the META tags that'll be included in the head or are called from the parser
	 * @var array
	 */
	static $meta = array();

	/**
	 * Holds the name of the files that have been parsed
	 * @var array
	 */
	static $models = array();

	/**
	 * Forces inclusion of the system files
	 * @return void
	 */
	static function includeSystem()
	{
		global $config;

		$headPiece = "
			<link href=\"".$config['kernel']['sysResources']."/css/style.css\" rel=\"stylesheet\" type=\"text/css\">
			<link href=\"".$config['kernel']['sysResources']."/css/animate.css\" rel=\"stylesheet\" type=\"text/css\">
			<script src=\"".$config['kernel']['sysResources']."/js/jquery.js\" type=\"text/javascript\"></script>
			<script src=\"".$config['kernel']['sysResources']."/js/jquery-ui.js\" type=\"text/javascript\"></script>
		";

		return $headPiece;
	}

	/**
	 * Adds a style to the style array which will be added to the head
	 * @return string Returns the HTML code of the style
	 */
	static function addStyle()
	{
		$args = func_get_args();

		$headPiece = '';

		foreach ( $args as $i )
		{
			if ( !in_array(trim($i), self::$styles) )
				self::$styles[] = trim($i);
		}

		foreach ( self::$styles as $i )
		{
			$link = Revision::minifyCSS(trim('css/'.$i));

			$headPiece .= "<link href=\"".self::$sitevars['site_resources'].'/css/'.$link."\" rel=\"stylesheet\" type=\"text/css\">";
		}

		return $headPiece;
	}

	/**
	 * Adds a style to the style array which will be added to the head
	 * @return string Returns the HTML code of the style
	*/

	static function addImg()
	{
		$args = func_get_args();

		$link = Revision::minifyImg(trim('img/'.$args[0]));

		$headPiece = "<img src=\"".self::$sitevars['site_resources'].'/img/'.$link."\"/>";

		return $headPiece;
	}

	/**
	 * Adds a style to the style array which will be added to the head
	 * @return string Returns the HTML code of the style
	*/

	static function addImgArgs($img, $options = null)
	{
		$link = Revision::minifyImg(trim('img/'.$img));

		$headPiece = "<img src=\"".self::$sitevars['site_resources'].'/img/'.$link."\" ".$options."/>";

		return $headPiece;
	}

	/**
	 * Adds a script to the script array which will be added to the head
	 * @return string Returns the HTML code of the script inclusion
	 */
	static function addScript()
	{
		$args = func_get_args();

		$headPiece = '';

		foreach ( $args as $i )
		{
			if ( !in_array(trim($i), self::$scripts) )
				self::$scripts[] = trim($i);
		}

		foreach ( self::$scripts as $i )
		{
			$link = Revision::minifyJS(trim('js/'.$i));

			$headPiece .= "<script src=\"".self::$sitevars['site_resources'].'/js/'.$link."\" type=\"text/javascript\"></script>";
		}

		return $headPiece;
	}

	/** 
	 * Adds a meta tag to the meta array which will be added to the head
	 * @return string Returns the HTML code of the tag
	 */
	static function addMeta($name, $value)
	{
		$headPiece = '';

		self::$meta[] = array(trim($name), trim($value));

		foreach ( self::$meta as $i )
		{
			$headPiece .= "<meta name=\"".$i[0]."\" content=\"".$i[1]."\">";
		} 

		return $headPiece;
	}

	/**
	 * Loads and parses a source file
	 * @param string $source   Name of the source file
	 * @param string $pagename Name of the page
	 */
	static function LoadTpl($source, $pagename, $header, $footer)
	{
		global $page, $config;

		self::$sitevars = self::getVars();
		self::$sitevars['page_name'] = $pagename;
		self::$sitevars['source_name'] = $source;

		if ( !isset($header) || empty($header) )
			$header = 'header.php';

		if ( !isset($footer) || empty($footer) )
			$footer = 'footer.php';

		$required = array(PATH.'/sources/headers/head.php', PATH.'/sources/headers/'.$header, PATH.'/sources/'.$source.'.php', PATH.'/sources/headers/'.$footer);

		foreach ( $required as $i )
		{
			$config['kernel']['currentSource'] = $i;

			ob_start("self::parseString");
			include $i;
			ob_flush();

			self::$models[] = $i;
		}
	}

	/**
	 * Parses a string
	 * @param  string $string String to parse
	 * @return string         Parsed string
	 */
	static function parseString($string)
	{
		$contents = $string;

		preg_match_all('/%(.*)%/U', $contents, $matches, PREG_SET_ORDER);

		foreach ( $matches as $i )
		{
			if ( strpos($i[1], "func::") !== false )
			{
				preg_match('/func::(.*)\((.*)\)/U', $i[1], $matches2);

				if ( strpos($matches2[1], '::') )
				{
					$explode = explode('::', $matches2[1]);

					if ( in_array($explode[0], Kernel::$modList) )
					{
						if ( $explode[0] == "Tpl" )
							$explode[0] = "self";

						$explode2 = explode(',', $matches2[2]);

						if ( count($explode2) > 1 )
						{
							$args = $explode2;
							$call = call_user_func_array($explode[0].'::'.$explode[1], $args);
						}
						else
						{
							$args = $matches2[2];
							$call = call_user_func($explode[0].'::'.$explode[1], $args);
						}

						$contents = str_replace("%".$i[1]."%", $call, $contents);
					}
				}
				else
					Kernel::Log('Functions called using the file parser must be part of a registered module/class');
			}
			elseif ( strpos($i[1], "const::") !== false )
			{
				preg_match('/const::(.*)/', $i[1], $matches2);

				if ( defined($matches2[1]) )
				{
					$contents = str_replace("%const::".$matches2[1]."%", constant($matches2[1]), $contents);
				}
				else
					Kernel::Log('Constant '.$matches2[1].' not found');
			}
			elseif ( strpos($i[1], 'img::') !== false )
			{
				preg_match('/img::\((.*), (.*)\)/', $i[1], $matches2);
				preg_match('/img::(.*)/', $i[1], $matches3);

				if ( count($matches3) > count($matches2) )
				{
					$contents = str_replace("%img::".$matches3[1]."%", self::addImgArgs($matches3[1]), $contents);
				}
				elseif ( count($matches2) > 1 )
				{
					$contents = str_replace("%img::(".$matches2[1].", ".$matches2[2].")%", self::addImgArgs($matches2[1], $matches2[2]), $contents);
				}	
			}

			if ( isset(self::$sitevars[$i[1]]) )
			{
				$contents = str_replace("%".$i[1]."%", htmlentities(self::$sitevars[$i[1]]), $contents);
			}
		}

		return $contents;
	}

	/**
	 * Returns all the sitevars on the database
	 * @return array Sitevars
	 */
	static function getVars()
	{
		$query = Sql::Query("SELECT * FROM sitevars");

		if ( !$query )
		{
			Kernel::Log('Could not retrieve sitevars.');
			trigger_error('Could not retrieve sitevars.', E_USER_ERROR);
			return false;
		}

		foreach ( $query as $reg )
		{
			$sitevars[$reg['name']] = $reg['value'];
		}

		return $sitevars;
	}
}

new Tpl;