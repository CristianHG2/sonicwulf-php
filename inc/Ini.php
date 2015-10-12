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
 * Ini class, usage is optional. Inherited from SonicWulf (Beta) 2
 */
class IniDat {

	/**
	 * Variable kept for the filename
	 * @var string
	 */
	private $file;

	/**
	 * Variable that stores the lines of the file
	 * @var array
	 */
	private $line;

	/**
	 * Separator that the ini file will use
	 * @var string
	 */
	private $separator;

	/**
	 * Constructs the class and verifies the file exists and is parseable
	 * @param string $file File to be parsed
	 * @return bool        Returns true if the parsing was successful
	 */
	public function __construct($file, $separator = " = ")
	{
		if ( file_exists($file) )
		{
			$this->line = file($file);
			$this->file = $file;
			$this->separator = $separator;

			if ( !parse_ini_file($file) )
				return false;
			else
				return true;
		}
		else
			return false;
	}

	/**
	 * Changes the file we're working with
	 * @param  string $newfile File to be parsed
	 * @return boolean         Returns true if the file was changed correctly
	 */
	public function changeFile($newfile)
	{
		$this->file = $newfile;

		if ( $this->file == $newfile )
			return true;
		else
			return false;
	}

	/**
	 * Checks if the file we're working with will return a multidimensional array
	 * @return boolean Returns true if the file will return multiple dimensions
	 */
	public function isMultidimensional()
	{
		$index = 0;
		$lines = array();
		$line_count = count($this->line) - 1;

		for ( $i = 0; $i <= $line_count; $i++ )
		{
			if ( strpos($this->line[$i], '[') !== false)
			{
				$line = $index;
			}

			$index++;
		}

		$split = str_split($this->line[$line]);

		foreach ( $split as $i2 )
		{
			if ( $i2 == "]" )
			{
				return true;
			}
		}
	}

	/**
	 * Returns the number of dimentions of the array
	 * @return integer Number of dimentions
	 */
	public function getDimensions($array)
	{
		$return = Kernel::getArrayDimensions($array);

	    return $return;
	}

	/**
	 * Parses the file
	 * @return array Returns the array keys
	 */
	public function parse()
	{
		if ( self::isMultidimensional() )
			return parse_ini_file($this->file, true);
		else
			return parse_ini_file($this->file);
	}

	/**
	 * Gets the definition of a key
	 * @param  string  $key       Name of the key
	 * @param  boolean $dimension Dimension to be used
	 * @return string             Returns the key definition
	 */
	public function getKeyDef($key, $dimension = false)
	{
		$parse = self::parse();

		if ( !$dimension )
		{
			if ( self::KeyExists($key) )
				return $parse[$key];
		}
		else
		{
			if ( self::KeyExists($key) )
				return $parse[$dimension][$key];
		}
	}

	/**
	 * Returns the array keys of a multidimensional ini file
	 * @return array       Array keys of the multidimensional array
	 */
	public function getKeys()
	{
		$parse = self::parse();

		return array_keys($parse);
	}

	/**
	 * Returns the keys that match a certain value
	 * @param boolean $value  Value to look for
	 * @param boolean $exact  If set to false, we will attempt a raw search and approximate possible keys
	 * @return array Returns an array of keys that contain the value, will return an empty array if there's no keys
	 */
	public function matchValue($value, $exact = true)
	{
		$parse = self::parse();

		$keys = array();

		if ( self::isMultidimensional() )
		{
			foreach ( $parse as $key => $i )
			{
				foreach ( $i as $key2 => $i2 )
				{
					if ( $exact )
					{
						if ( $i2 == $value )
						{
							array_push($keys, $key2);
							$find = true;
						}
					}
					else
					{
						if ( strpos($i2, $value) !== false )
						{
							array_push($keys, $key);
						}
					}
				}

				if ( $find )
				{
					$keys['dimensions'][] = $key;
					$find = false;
				}
			}
		}
		else
		{
			foreach ( $parse as $key => $i )
			{
				if ( $exact )
				{
					if ( $i == $value )
					{
						array_push($keys, $key);
					}
				}
				else
				{
					if ( strpos($i, $search) !== false )
					{
						array_push($keys, $key);
					}
				}
			}
		}

		return $keys;
	}

	/**
	 * Verifies if a key exists
	 * @param  string $key Key to look for
	 * @return bool        Returns true if the key exists
	 */
	public function keyExists($key)
	{
		if ( !self::isMultidimensional() )
		{
			foreach ( getKeys() as $i )
			{
				if ( trim($i) === trim($key) )
					return true;
			}
		}
		else
		{
			foreach ( self::parse() as $i )
			{
				foreach ( $i as $key2 => $i2 )
				{
					if ( trim($key) === trim($key2) )
						return true;
				}
			}
		}

		return false;
	}

	/**
	 * Returns the key in a line
	 * @param  integer $line Line to be inspected
	 * @return boolean       Returns the key on the line
	 */
	public function getKeyLine($line)
	{
		if ( strpos($this->line[$line], $this->separator) !== false )
		{
			$exp = explode($this->separator, $this->line[$line]);
			return trim($exp[0]);
		}
	}

	/**
	 * Matches the dimensions that contains a certain value 
	 * @param string  $value Value to look for
	 * @param boolean $exact If set to false, we will attempt a raw search and approximate possible keys
	 * @return array Returns an array of keys that contain the value, will return an empty array if there's no keys
	 */
	public function matchDimension($value, $exact = true)
	{
		$parse = self::matchValue($value, $exact);

		return $parse['dimensions'];
	}

	/**
	 * Returns the keys of a dimension
	 * @param  string $dimension Dimension to check
	 * @return array             Returns the array keys
	 */
	public function getDimensionKeys($dimension)
	{
		$parse = self::parse();

		return array_keys($parse[$dimension]);
	}

	/**
	 * Modifies the value of a key
	 * @param  string  $key       Key to change
	 * @param  string  $value     New value
	 * @param  string  $dimension If the variable is set on another dimension, you can specify that here, if there's more than two 
	 * @return boolean            Returns true if the value was successfully changed, and zero if the pre-modified value was the same as the requested change
	 */
	public function modifyKey($key, $value, $dimension = false)
	{
		if ( !$dimension )
		{
			$get = new files;
			$num = $get->findLineNumber($this->file, $key.$this->separator);

			$newfile = array();
			foreach ( $this->line as $key => $i )
			{
				$newfile[] = trim($i).PHP_EOL;

				foreach ( $num as $i )
				{
					if ( $key == $i )
					{
						$exp = explode($this->separator, $this->line[$i]);

						if ( trim($exp[1]) === trim($value) )
							return 0;

						$newfile[$i] = self::getKeyLine($i).$this->separator.trim($value).PHP_EOL;
					}					
				}
			}

			$newfile[count($newfile) - 1] = trim($this->line[count($this->line) - 1]);

			$data = implode($newfile);

			foreach ( $newfile as $i )
			{
				$if = file_put_contents($this->file, $data);
			}

			return $if;
		}
	}

	/**
	 * Modifies various keys in the file based on an array, format to follow is key => value
	 * @param  array $array Array to be used
	 * @return boolean        Returns true if the keys were successfully modified, zero if the file was partially modified and false if it wasn't modified at all
	 */
	public function modifyKeysArray($array)
	{
		foreach ( $array as $key => $i )
		{
			self::modifyKey($key, $i);
		}
	}
}