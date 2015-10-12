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
 * Text class, optional but recommended
 */
class Text
{
	/**
	 * Removes the comments and new lines of a parsed string and counts the real lines of a file
	 * @param  string $file Name of the file
	 * @return integer      Number of lines
	 */
	static function countRealLines($file)
	{
		$fileStr = file_get_contents($file);
		$newStr  = '';

		$commentTokens = array(T_COMMENT);

		if ( defined('T_DOC_COMMENT') )
		    $commentTokens[] = T_DOC_COMMENT;

		if ( defined('T_ML_COMMENT') )
		    $commentTokens[] = T_ML_COMMENT;

		$tokens = token_get_all($fileStr);

		foreach ($tokens as $token) 
		{    
		    if ( is_array($token) ) 
		    {
		        if ( in_array($token[0], $commentTokens) )
		            continue;

		        $token = $token[1];
		    }

		    $newStr .= $token;
		}

		$replace = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $newStr);

		return substr_count($replace, "\n");
	}

	/**
	 * Trims and lowers a string
	 * @param  string $string String to work with
	 * @return string         Returns the modified string
	 */
	static function fixString($string)
	{
		return strtolower(trim($string));
	}

	/**
	 * Verifies if a variable is formatted in a certain way or is a certain type of variable
	 * @param  mixed  $string Variable to work with
	 * @param  string $type   Type of variable to verify (email, alpha, alnum, num)
	 * @return bool           Returns false if the variable wasn't valid
	 */
	static function validate($string, $type)
	{
		switch ( $type )
		{
			case 'email':
				return filter_var($string, FILTER_VALIDATE_EMAIL);
				break;
			case 'alpha':
				return ctype_alpha($string);
				break;
			case 'alnum':
				return ctype_alnum($string);
				break;
			case 'num':
				return is_numeric($string);
				break;
		}
	}

	/**
	 * Encrypts a string
	 * @param  string  $string String to encrypt
	 * @param  integer $level  Level of encryption (1, 2, 3, password)
	 * @param  string  $salt   Salt to use if the level is password
	 * @return string          Returns the encrypted string
	 */
	static function encrypt($string, $level = 1, $salt = null)
	{
		switch ( $level )
		{
			case 1:
				return base64_encode($string);
				break;
			case 2:
				return md5(base64_encode($string));
				break;
			case 3:
				return sha1(md5(base64_encode($string)));
				break;
			case 'password':
				if ( !is_null($salt) )
					return crypt($string, $salt);
				else
					return crypt($string);
				break;
		}
	}

	/**
	 * Parses a text for a special kind of variable (placeholder) using regex
	 * @param  string $string     String to work with
	 * @param  string $identifier Placeholder boundaries
	 * @return array              Returns an array with the matches
	 */
	static function parseStringVar($string, $identifier = '%')
	{
		preg_match_all('/'.$identifier.'(.*)'.$identifier.'/U', $string, $matches);

		return $matches[1];
	}

	/**
	 * Binds parameters to a string
	 * @param  string $string Template string to work with
	 * @param  string $string String to replace ocurrences with
	 * @return string         Returns the binded string
	 */
	static function bindParams($string, $string2, $identifier = array('{', '}'))
	{
		$temp_escape = '__|||....=====_+++++++__'.rand().uniqid().'temp_comma'.'__|||....=====_+++++++__';
		$dynamite = '__|||....=====_+++++++__'.rand().uniqid().'dynamite__|||....=====_+++++++__';

		$string_escape = str_replace('/,', $temp_escape, $string2);

		$string_escape = str_replace(',', $dynamite, $string_escape);

		$args_buf = explode($dynamite, $string_escape);

		foreach ( $args_buf as $i )
		{
			$args[] = str_replace($temp_escape, ',', $i);
		}

		preg_match_all("/".$identifier[0]."(.*).".$identifier[1]."/U", $string, $matches, PREG_PATTERN_ORDER);

		if ( count($matches[0]) !== count($args) )
			return false;

		foreach ( $matches[0] as $key => $i )
		{
			$string = str_replace($i, trim($args[$key]), $string);
		}

		return $string;
	}

	/**
	 * Escapes and applies htmlentities to a string
	 * @param  string $string String to work with
	 * @return string         Returns a newly formatted string
	 */
	static function htmlEscape($string)
	{
		return htmlentities(addslashes($string));
	}

	/**
	 * Counts all the lines of a file
	 * @param  string $file File to work with
	 * @return integer      Counts the amount of lines
	 */
	static function countLines($file)
	{
		return count(file($file));
	}
}