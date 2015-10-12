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
 * Listings class, usage is optional
 */
class Listing
{
	/**
	 * Template that the class will use
	 * @var string
	 */
	public $template;

	/**
	 * Database table that we will use to create the list
	 * @var string
	 */
	public $table;

	/**
	 * Query the class will run when retreiving the information
	 * @var string
	 */
	public $query;

	/**
	 * Holds all the variables that will be parsed
	 * @var array
	 */
	public $vars = array();

	/**
	 * Options the listing will take
	 * @var array
	 */
	public $options;

	/**
	 * Constructs the listing class and sets the table
	 * @param string $table Name of the table to work with
	 * @return void
	 */
	public function __construct($table)
	{
		if ( !Sql::tableExists($table) )
		{
			Kernel::Log($table.' does not exist');
			return false;
		}
		else
			$this->table = $table;
	}

	/**
	 * Executes a fetch and retrieves the table information
	 * @param  array   $columns   Columns to fetch
	 * @param  integer $rows_page Maximum amount of registries per page
	 * @param  integer $index     Index we'll use to see what page we are on
	 * @return PDOStatement       Returns a fetch PDOStatement
	 */
	public function getQuery($columns, $rows_page = null, $index = null)
	{
		if ( isset($this->options['criteria']) )
			$this->options['criteria'] = ' '.$this->options['criteria'];
		else
			$this->options['criteria'] = '';

		if ( $rows_page !== null OR $index !== null )
		{
			$limit_text = ' LIMIT '.(($rows_page) * $index).','.($index + $rows_page);
		}
		else
			$limit_text = '';

		return Sql::fetch('SELECT '.implode(', ', $columns).' FROM '.$this->table.$this->options['criteria'].$limit_text);
	}

	/**
	 * Sets an extra/non-Sql variable
	 * @param string $name  Name of the variable
	 * @param string $value Value of the variable
	 * @return bool         Returns false if the value is an array
	 */
	public function setVar($name, $value)
	{
		if ( is_array($value) )
		{
			Kernel::Log('The value of a variable set through setVar cannot be an array, please use setVarSql');
			return false;
		}
		else
			$this->vars[$name] = $value;
	}

	/**
	 * Sets a variable that will be fetched from other table
	 * @param string $name     Name of the variable on the template
	 * @param string $column   Name of the column we'll be fetching
	 * @param string $table    Name of the table we'll be fetching from
	 * @param string $criteria Criteria the query will use (Will force a LIMIT 1)
	 * @return void
	 */
	public function setVarSql($name, $column, $table, $criteria = null)
	{
		if ( !is_null($criteria) )
			$criteria = ' LIMIT 1';
		else
		{
			if ( strpos($criteria, 'LIMIT 1') === false )
				$criteria = ' '.$criteria.' LIMIT 1';
			else
				$criteria = ' '.$criteria;
		}

		$this->vars[$name] = array($column, $table, $criteria);
	}

	/**
	 * Parses the template and executes the listing
	 * @return void Prints the listing
	 */
	public function execute()
	{
		if ( $this->template == null )
		{
			Kernel::Log('There\'s no template, cannot generate listing');
			return false;
		}
		else
		{
			$columns = Text::parseStringVar($this->template);

			foreach ( $columns as $key => $i )
			{
				if ( in_array($i, array_keys($this->vars)) )
					unset($columns[$key]);
			}

			if ( isset($this->options['max_regs']) && isset($this->options['page_index']) )
			{
				if ( $this->options['max_regs'] < 2 )
				{
					Kernel::Log('The max_regs option must be higher than 1');
					return false;
				}

				$rows_page = $this->options['max_regs'];
				$index = $this->options['page_index'];

				if ( isset($this->options['criteria']) )
					$this->options['criteria'] = ' '.$this->options['criteria'];
				else
					$this->options['criteria'] = '';

				$rows = Sql::numRows('SELECT '.implode(', ', $columns).' FROM '.$this->table.$this->options['criteria']);

				if ( !isset($this->options['template_pag']) )
				{
					$pag = new HTMLObj('div');
					$pag->class = "sonicwulf_pages";
				}
				else
					$pag_html = '';

				for ( $i = 0; $i <= ceil($rows / $this->options['max_regs']) - 1; $i++ )
				{
					if ( !isset($this->options['template_pag']) )
					{
						$div = $pag->addChild('a');
						$div->addContent($i);
						$div->href = $_SERVER['PHP_SELF'].'?'.$this->options['get_name'].'='.$i;
						$div->class = 'sonicwulf_pagenum';
					}
					else
					{
						$parse = Text::parseStringVar($this->options['template_pag']);

						if ( in_array('href', $parse) )
						{
							$pag_html .= str_replace('%page_link%', $_SERVER['PHP_SELF'].'?'.$this->options['get_name'].'='.$i, $this->options['template_pag']);
						}
						else
						{
							Kernel::Log('There\'s no HREF attribute on the template, please add the atrribute');
							return false;
						}
					}
				}

				if ( !isset($this->options['template_pag']) )
					$pag_html = $pag->executeNoFormat();
			}
			else
			{
				$rows_page = null;
				$index = null;

				$pag_html = null;
			}

			foreach ( $this->getQuery($columns, $rows_page, $index) as $reg )
			{
				$string = $this->template;

				foreach ( $columns as $i )
				{
					if ( isset($reg[$i]) )
						$string = str_replace("%".$i."%", $reg[$i], $string);
				}

				foreach ( $this->vars as $key => $i )
				{
					if ( is_array($i) )
					{
						$data = Sql::fetch("SELECT ".$i[0]." FROM ".$i[1].$i[2]);

						$string = str_replace("%".$key."%", $data[0][$i[0]], $string);
					}
					else
						$string = str_replace("%".$key."%", $i, $string);
				}

				echo $string;
			}

			echo $pag_html;
		}
	}

	/**
	 * Overwatches template and query configuration, if it's not either of those, sets it as an option
	 * @param string $name  Name of the property
	 * @param string $value New value of the property
	 * @return bool         May return false if the template or query configuration weren't successful
	 */
	public function __set($name, $value)
	{
		if ( $name == 'template' )
		{
			$columns = Text::parseStringVar($value);

			foreach ( $columns as $key => $i )
			{
				if ( in_array($i, array_keys($this->vars)) )
					unset($columns[$key]);
			}

			if ( !Sql::hasColumns($this->table, $value) )
			{
				Kernel::Log('The template uses columns that do not exist on the '.$this->table.' table');
				return false;
			}
		}
		elseif ( $name == 'query' )
		{
			if ( !Sql::testQuery($value) )
			{
				Kernel::Log('The desired query did not run successfully. Please try another one. Your database wasn\'t affected.');
				return false;
			}
		}
		else
			$this->options[$name] = $value;
	}
}