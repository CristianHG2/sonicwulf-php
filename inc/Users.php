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
 * Accounts class, optional use
 */
class Users
{
	/**
	 * Column to identify the user through
	 * @var string
	 */
	static $id_field;

	/**
	 * Constructs the class and verifies if the requirements have been met
	 * @param string $id_field Column to identify the user through
	 */
	public function __construct($id_field = 'id')
	{
		self::$id_field = $id_field;

		$req_columns = array('id', 'username', 'email', 'password', 'lastactive');

		if ( Sql::tableExists('accounts') < 1 )
		{
			Kernel::Log('The accounts table doesn\'t exist');
			return false;
		}

		if ( Sql::hasColumns('accounts', $req_columns) !== true )
		{
			Kernel::Log('The accounts table does not have the required columns');
			return false;
		}

		return true;
	}

	/**
	 * Adds an user to the database
	 * @param string $username     Username of the new user
	 * @param string $email        Email of the new user
	 * @param string $password     Password of the new user
	 * @param array  $extra_fields Array of extra fields (column => value)
	 * @return bool                Returns true if the user was registered
	 */
	static function addUser($username, $email, $password, $extra_fields = null)
	{
		$escaped_user = Text::htmlEscape($username);

		if ( !Text::validate($email, 'email') )
			return false;

		if ( !is_null($extra_fields) )
		{
			if ( !Sql::hasColumns('accounts', array_keys($extra_fields)) )
				return false;
		}
		else
			$extra_fields = array();

		$pswd = Text::encrypt($password, 'password', $escaped_user);

		$fields_buf = array(
				"username"	=> $escaped_user,
				"email"		=> $email,
				"password"	=> $pswd,
				"lastactive"=> time()
			);

		$fields = array_merge($fields_buf, $extra_fields);

		foreach ( $fields as $key => $i )
		{
			if ( $key == self::$id_field )
			{
				if ( self::userExists($i) )
					return false;
			}
		}

		$q = Sql::Insert('accounts', $fields);

		return $q;
	}

	/**
	 * Logs an user out
	 * @param  string $id_field Column to identify the user with
	 * @return void
	 */
	static function logOut($id_field, $forced = false, $redirect = false)
	{
		if ( !$forced )
			self::modifyUser($id_field, 'lastactive', time());

		if ( $redirect !== false )
			header('Location: '.$redirect);

		unset($_SESSION['user_id']);
	}

	/**
	 * Logs an user in
	 * @param  string $id_field Column to identify the user with
	 * @param  string $password Password that the user is giving
	 * @return bool             Returns true if the user was logged in successfully
	 */
	static function logIn($id_field, $password)
	{
		if ( self::userExists($id_field) < 1 )
			return 0;

		if ( self::getUserInfo($id_field, 'password') !== Text::encrypt($password, 'password', self::getUserInfo($id_field, 'username')) )
			return false;

		self::modifyUser($id_field, 'lastactive', time());

		$_SESSION['user_id'] = self::getUserInfo($id_field, 'id');
		return true;
	}

	/**
	 * Retrieves an user's certain info
	 * @param  string $id_field Name of the identification column
	 * @param  string $field    Field we'll be fetching
	 * @return mixed            Returns the value of the column, returns false otherwise
	 */
	static function getUserInfo($id_field, $field)
	{
		if ( self::userExists($id_field) < 1 )
			return false;

		if ( !Sql::hasColumns('accounts', array($field)) )
			return false;

		$data = Sql::Query(array("table" => "accounts", "select" => $field, "where" => self::$id_field.",".$id_field), true);
		
		return $data[$field];		
	}

	/**
	 * Modifies an user's information
	 * @param  string $id_field     Field to identify the user with
	 * @param  mixed  $fields_mixed Fields to modify. If the value is an array, it must be formatted column => value
	 * @param  string $newvalue     New value of the column
	 * @return bool                 Returns true if the query was successful
	 */
	static function modifyUser($id_field, $fields_mixed, $newvalue = null)
	{
		if ( is_array($fields_mixed) )
		{
			if ( !Sql::hasColumns('accounts', array_keys($fields_mixed)) )
				return false;
		}
		else
		{
			if ( !Sql::hasColumns('accounts', array($fields_mixed)) )
				return false;
		}

		$q = Sql::Update('accounts', array($fields_mixed => $newvalue), 'WHERE '.self::$id_field.' = \''.$id_field.'\'');

		return $q;
	}

	/**
	 * Checks if an user exists
	 * @param  string $id_field Field to identify the user with
	 * @return bool             Returns true if the query was successful
	 */
	static function userExists($id_field)
	{
		if ( !Text::validate($id_field, 'num') )
			$quote = "'";
		else
			$quote = '';
		
		if ( Sql::numRows("SELECT ".self::$id_field." FROM accounts WHERE ".self::$id_field." = ".$quote.$id_field.$quote) < 1 )
			return false;
		else
			return true;		
	}
}

new Users;