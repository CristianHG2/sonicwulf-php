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
 * User class, optional use
 */
class User
{
	/**
	 * Column that we'll use to identify the user
	 * @var string
	 */
	public $id_field;

	/**
	 * Value of the identification column we'll look for to identify the user
	 * @var string
	 */
	public $user_id;

	/**
	 * Variable that tell us if we finished the first caching
	 * @var boolean
	 */
	public $boot = false;

	/**
	 * Constructs the class and sets class properties
	 * @param mixed  $field_id Value of the identification column
	 * @param string $id_field Identification column
	 */
	public function __construct($field_id, $id_field = 'id')
	{
		$this->id_field = $id_field;
		$this->user_id = $field_id;

		Users::$id_field = $id_field;

		if ( !Users::userExists($field_id) )
		{
			//Kernel::Log('User with '.$id_field.' '.$field_id.' does not exist');
			return false;
		}

		$this->boot = true;

		$this->refreshCache($id_field, $field_id);
	}

	/**
	 * Logs a user in
	 * @param  string $password Password
	 * @return bool             Returns true if we were able to log the user in
	 */
	public function logIn($password)
	{
		return Users::logIn($this->user_id, $password);
	}

	/**
	 * Logs a user out
	 * @return void
	 */
	public function logOut()
	{
		return Users::logOut($this->user_id);
	}

	/**
	 * Refreshes the properties of the class
	 * @param  string $id_field Column to identify the user with
	 * @param  string $field_id Value of the identification column
	 * @return void
	 */
	public function refreshCache($id_field, $field_id)
	{
		foreach ( Sql::Query("SELECT * FROM accounts WHERE $id_field = '".$field_id."'") as $i )
		{
			foreach ( $i as $key => $i2 )
			{
				if ( Text::validate($key, 'num') )
				{
					continue;
				}

				$this->$key = $i2;
			}
		}	
	}

	/**
	 * Modifies an user's database information if we're modifying a cached column. Otherwise it just sets a property.
	 * @param string $name  Name of the property
	 * @param string $value New value of the property
	 * @return void
	*/
	public function __set($name, $value)
	{
		if ( $this->boot )
		{
			Users::$id_field = $this->id_field;

			$disallowedKeys = array('id_field', 'user_id', 'boot');

			if ( !in_array($name, $disallowedKeys) )
				$q = Users::modifyUser($this->user_id, $name, $value);

			if ( !$q )
			{
				Kernel::Log('User with '.$this->id_field." ".$this->user_id." could not be modified (".$name.")");
				return false;
			}

			$this->$name = $value;
		}
	}

	/**
	 * Recaches the user information
	 * @return void
	 */
	public function __wakeup()
	{
		$this->refreshCache($this->id_field, $this->user_id);
	}
}