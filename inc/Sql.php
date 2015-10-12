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
 * Sql class, required to run the framework
 */
class Sql
{
	/**
	 * Holds the PDO class (bridge)
	 * @var PDO
	 */
	static $bridge;

	/**
	 * Constructs the Sql class and sets the bridge
	 * @return void
	 */
	public function __construct()
	{
		global $config;

		try 
		{
			if ( !$config['sql']['custom'] )
			{
				if ( $config['sql']['driver'] == "mysql" )
					self::$bridge = new PDO('mysql:dbname='.$config['sql']['db'].';port='.$config['sql']['port'].';host='.$config['sql']['host'], $config['sql']['user'], $config['sql']['pswd'], $config['sql']['pdoOptions']);
				elseif ( $config['sql']['driver'] == "sqlite" )
				{
					if ( $config['sql']['memory'] )
						self::$bridge = new PDO($config['sql']['driver'].'::memory:');
					else
						self::$bridge = new PDO($config['sql']['driver'].':'.$config['sql']['SqliteFile']);
				}
				elseif ( $config['sql']['driver'] == "postgresql")
					self::$bridge = new PDO('pgsql:host='.$config['sql']['host'].';port='.$config['sql']['port'].';dbname='.$config['sql']['db'].';user='.$config['sql']['user'].';password='.$config['sql']['pswd'], null, null, $config['sql']['pdoOptions']);
				else
				{
					Kernel::Log('No pre-supported driver specified, driver requested: '.$config['sql']['driver'], 1);
					trigger_error('No pre-supported driver specified, driver requested: '.$config['sql']['driver'], E_USER_ERROR);
				}
			}
			else
				self::$bridge = new PDO($config['sql']['dsn'], $config['sql']['customUser'], $config['sql']['customPswd'], $config['sql']['pdoOptions']);
		} 
		catch ( PDOException $e ) 
		{
			Kernel::Log('Could not connect to server. Server returned "'.$e->getMessage().'"');
			trigger_error('Could not connect to server. Server returned "'.$e->getMessage().'"', E_USER_ERROR);

			return false;
		}

		self::$bridge->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		self::$bridge->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		if ( $config['sql']['driver'] == "sqlite" && $config['sql']['memory'] )
			self::$bridge->setAttribute(PDO::ATTR_PERSISTENT, true);

		return true;
	}

	/**
	 * Checks if a table exists in the database
	 * @param  string $table Name of the table to look for
	 * @return integer       Returns >1 if the table exists
	 */
	static function tableExists($table)
	{
		$stmt = self::$bridge->query('SHOW TABLES LIKE \''.$table.'\'');

		return $stmt->rowCount();
	}

	/**
	 * Checks if a table has a certain array of columns
	 * @param  string  $table   Name of the table to work with
	 * @param  array   $columns Columns to look for
	 * @return boolean          Returns true if the columns were all found
	 */
	static function hasColumns($table, $columns)
	{
		if ( !is_array($columns) )
		{
			Kernel::Log('Columns must be an array');
			return false;
		}
		else
		{
			try
			{
				$columnsnum = 0;

				foreach ( self::$bridge->query('SHOW COLUMNS FROM '.$table) as $row )
				{
					if ( in_array($row['Field'], $columns) )
						$columnsnum++;
				}

				if ( $columnsnum == count($columns) )
					return true;
				else
					return false;
			}
			catch ( PDOException $e ) 
			{
				Kernel::Log('Error. Could not execute query, message: "'.$e->getMessage().'"');
				return false;
			}
		}
	}

	/**
	 * Runs a fetchAll on the provided query
	 * @param  string $query Query to run
	 * @return array  		 Associative array holding the table registries
	 */
	static function fetch($query)
	{
	    $Sql = self::$bridge->prepare($query);

	    $Sql->execute();

	    $row = $Sql->fetchAll(PDO::FETCH_ASSOC);

	    return $row;		
	}

	/**
	 * Tests if a query is going to be successful but does not affect the table
	 * @param  string $query_t Query to test
	 * @return PDOStatement    Returns a PDOStatement with the fake query
	 */
	static function testQuery($query_t)
	{
		if ( substr($query_t, -1) !== ";" )
			$query_end = ';';
		else
			$query_end = '';

		return self::Query("BEGIN TRANSACTION ".$query_t.$query_end." ROLLBACK TRANSACTION");
	}

	/**
	 * Runs a query
	 * @param mixed   $query_t Query to run. If a an array is provided, it will generate a query using Sql::genQuery
	 * @param boolean $fetch   Should we fetch the query?
	 * @return PDOStatement    Returns the query statement
	 */
	static function Query($query_t, $fetch = false)
	{
		$bridge = self::$bridge;

		try
		{
			if ( !is_array($query_t) )
				$query = $bridge->query($query_t);
			else
				$query = $bridge->query(self::genQuery($query_t));

			if ( $fetch )
				return $query->fetch();
			else
				return $query;
		}
		catch ( PDOException $e )
		{
   			 Kernel::Log('Could not execute query. Server returned "'.$e->getMessage().'"');
   			 return false;
		}
	}

	/**
	 * Generates a query based on an array
	 * @param  array $array  Array to use
	 * @return string        Returns a string with the newly structured query
	 */
	static function genQuery($array)
	{
		$commands = array(
				"select"	=> "SELECT {x}",
				"table"		=> "FROM {x}",
				"where"		=> "WHERE {x} = '{y}'",
				"limit"		=> "LIMIT {x}, {y}",
				"between"	=> "WHERE {x} BETWEEN {y} AND {z}",
			);

		if ( !isset($array['table']) )
			return false;

		$query = array();

		if ( !isset($array['select']) )
			$query[] = "SELECT *";

		foreach ( $commands as $key => $i )
		{
			if ( in_array($key, array_keys($array)) )
			{
				$query[] = Text::bindParams($commands[$key], $array[$key]);
			}
		}

		return implode(" ", $query).";";
	}

	/**
	 * Runs a query and returns the number of rows affected
	 * @param  string $query Query to run
	 * @return integer       Number of rows affected
	 */
	static function numRows($query)
	{
		if ( !is_array($query) )
			$query2 = self::Query($query);
		else
			$query2 = self::Query(self::genQuery($query));

		try
		{
			$num = $query2->rowCount();
			return $num;
		}
		catch ( PDOException $e )
		{
   			 Kernel::Log('Could not execute query. Server returned "'.$e->getMessage().'"');
   			 return false;
		}
	}

	/**
	 * Escapes a string and adds single quotes
	 * @param string $string String to work with
	 * @return string        Returns the modified string
	 */
	static function String($string)
	{
		return "'".addslashes($string)."'";
	}

	/**
	 * Inserts information into a table
	 * @param string $table    Name of the table to work with
	 * @param array $array     Array to work with
	 * @param string $criteria Criteria to add at the end of the generated Sql query
	 * @return bool 	       Returns true if the query was successful
	 */
	static function Insert($table, $array, $criteria = null)
	{
		$bridge = self::$bridge;

		$array2 = array();

		foreach ( $array as $i )
		{
			$array2[] = htmlentities(addslashes($i));
		}

		$string = "INSERT INTO $table(".implode(', ' , array_keys($array)).") VALUES ('".implode('\', \'', $array2)."')";

		$stmt = self::Query($string);

		if ( !$stmt )
   			return false;			
		else
			return true;
	}

	/**
	 * Updates a table's row
	 * @param string $table    Table to work with
	 * @param array $array     Array to use
	 * @param string $criteria Criteria to add at the end of the query
	 * @return bool            Returns false if the query was not successful
	 */
	static function Update($table, $array, $criteria = null)
	{
		$bridge = self::$bridge;

		$sets = array();
		$values = array();

		$index = 0;
		foreach ( $array as $key => $value )
		{
			$values[$index] = $value;

			$sets[] = 'SET '.$key.' = :'.$index;

			$index++;
		}

		$stmt = $bridge->prepare("UPDATE $table ".implode(', ', $sets)." ".$criteria.";");
		
		foreach ( $values as $key => $i )
		{
			$stmt->bindParam(':'.$key, $i);
		}

		try
		{
			$stmt->execute();
		}
		catch ( PDOException $e )
		{
   			 Kernel::Log('Could not execute query. Server returned "'.$e->getMessage().'"');
   			 return false;
		}

		return true;
	}

	/**
	 * Destroys the class and nulls the bridge
	 * @return void
	 */
	public function __destroy()
	{
		self::$bridge = null;
	}
}

new Sql;