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
 * HTMLObj class, hosts information about HTML DOM objects and can be used for single DOM objects
 */
class HTMLObj
{
	/**
	 * Holds the attributes of the element
	 * @var array
	 */
	public $attr;

	/**
	 * Name of the DOM element tag
	 * @var string
	 */
	public $elname;

	/**
	 * Content of the DOM element
	 * @var array
	 */
	public $content;

	/**
	 * Children of the DOM element
	 * @var array
	 */
	public $children;

	/**
	 * Order in which the children and content will be parsed
	 * @var array
	 */
	public $order = null;

	/**
	 * Holds a boolean that is true if the DOM element is child of another element
	 * @var bool
	 */
	public $isChild;

	/**
	 * Holds the child level of a DOM element (How deep in the hierarchy it is)
	 * @var integer
	 */
	public $childLevel;

	/**
	 * Constructs the HTMLObj class and sets the elname property
	 * @param string $element Name of the DOM element tag
	 * @return void
	 */	
	public function __construct($element)
	{
		$this->elname = $element;
	}

	/**
	 * Returns the variables that can be used on the order property of the class
	 * @return array Returns an array with all the order variables
	 */
	public function getOrderVars()
	{
		$array_vars = array();

		$array_vars[] = '%main_tag%';

		if ( count($this->children) > 0 )
		{
			$array_vars[] = '%children_all%';

			foreach ( $this->children as $key => $i )
			{
				$array_vars[] = '%children'.$i.'%';
			}			
		}

		if ( count($this->content) )
		{
			foreach ( $this->content as $key => $i )
			{
				$array_vars[] = '%content'.$i.'%';
			}

			$array_vars[] = '%content_all%';
		}

		$array_vars[] = '%end_tag%';

		return $array_vars;
	}

	/**
	 * Adds a pre-constructed DOM element to the children hierarchy and makes it a child
	 * @param  HTMLObj $child HTMLObj that will be merged
	 * @return mixed          Returns the HTMLObj if it was successful, otherwise it returns false
	 */
	public function mergeChild($child)
	{
		if ( $child instanceof HTMLObj )
		{
			$this->children[$child->elname][$num] = $child;
			$this->children[$child->elname][$num]->isChild = true;
			$this->children[$child->elname][$num]->childLevel = $this->childLevel + 1;

			return $this->children[$child->elname];
		}
		else
		{
			Kernel::Log('All children must be an instance of HTMLObj');
			return false;
		}
	}

	/**
	 * Constructs and adds a child to the DOM Element
	 * @param string $name Name of the DOM element tag of the child
	 * @return HTMLObj     Returns the HTMLObj of the child
	 */
	public function addChild($name)
	{
		$num = count($this->children[$name]);

		$this->children[$name][$num] = new HTMLObj($name);

		$this->children[$name][$num]->isChild = true;
		$this->children[$name][$num]->childLevel = $this->childLevel + 1;

		return $this->children[$name][$num];
	}

	/**
	 * Returns the last child that was added to the hierarchy
	 * @return HTMLObj Last HTMLObj of children
	 */
	public function getLastChild()
	{
		return end(array_values($this->children));
	}

	/**
	 * Adds content to the content array of the HTMLObj
	 * @param string $content Content that will be added
	 * @return void
	 */
	public function addContent($content)
	{
		$this->content[] = $content;
	}

	/**
	 * Executes the HTMLObj but removes all new lines and tabbing
	 * @return string Returns the HTML of the HTMLObj
	 */
	public function executeNoFormat()
	{
		$find = array("\t", "\n");

		return str_replace($find, "", $this->execute());
	}

	/**
	 * Executes the HTMLObj with formatting
	 * @return string Returns the HTML interpretation of the HTMLObj
	 */
	public function execute()
	{
		$attr = array();

		$html['children_all'] = '';
		$html['content_all'] = '';

		if ( $this->order == null )
			$this->genOrder();

		if ( count($this->attr) > 0 )
		{
			$attr[] = ' ';
			foreach ( $this->attr as $key => $i )
			{
				$attr[] = $key.'="'.addslashes($i).'"';
			}
		}

		if ( count($this->children) > 0 && is_array($this->children) )
		{
			foreach ( $this->children as $key => $i )
			{
				if ( is_array($i) )
				{
					foreach ( $i as $i2 )
					{
						$html['children'.$key] = $i2->execute();
						$html['children_all'] .= $i2->execute();						
					}
				}
				else
				{
					$html['children'.$key] = $i->execute();
					$html['children_all'] .= $i->execute();
				}
			}
		}

		if ( is_string($this->content) )
			$this->content = array($this->content);

		if ( count($this->content) > 0 )
		{
			foreach ( $this->content as $key => $i )
			{
				$html['content'.$key] = $i;
				$html['content_all'] .= $i;
			}
		}

		$html['main_tag'] = '<'.$this->elname.implode(' ', $attr).'>';
		$html['end_tag'] = '</'.$this->elname.'>';

		$ordertags = Text::parseStringVar($this->order);

		$string = $this->order;

		foreach ( $ordertags as $i )
		{
			if ( isset($html[$i]) )
				$string = str_replace('%'.$i.'%', $html[$i], $string);
		}

		return $string;
	}

	/**
	 * Generates a default order of appareance in the DOM element and sets it as a property
	 * @return void
	 */
	public function genOrder()
	{
		$order = '';
		$tabs = '';

		for ( $i = 1; $i <= $this->childLevel; $i++ )
		{
			$tabs .= "\t";
		}

		if ( $this->isChild )
		{
			$order .= "\n".$tabs."%main_tag%\n";

			if ( count($this->content) > 0 )
				$order .= $tabs."\t%content_all%\n";

			if ( count($this->children) > 0 )
				$order .= $tabs."\t%children_all%";

			$order .= $tabs."%end_tag%\n";
		}
		else
		{
			$order .= "%main_tag%";
			$order .= "%content_all%";
			$order .= "%children_all%";
			$order .= "%end_tag%";			
		}

		$this->order = $order;
	}

	/**
	 * Displays the literal HTML code of the HTMLObj
	 * @return string HTML code of the HTMLObj
	 */
	public function displayRawHTML()
	{
		$find = array('<', '>');
		$replace = array('&lt;', '&gt;');

		return str_replace($find, $replace, $this->execute());
	}

	/**
	 * The class will return either a child or an attribute if it is not a special class property
	 * @param  string $name Name of the property requested
	 * @return mixed        May return HTMLObj or a string
	 */
	public function __get($name)
	{
		$special = array('attr', 'elname', 'content', 'children', 'html');

		if ( in_array($name, $special) )
			return $this->$name;
		elseif ( in_array($name, array_keys($this->children)) )
			return $this->children[$name];
		else
			return $this->attr[$name];
	}

	/**
	 * Sets content or attributes of an HTMLObj
	 * @param string $name  Name of the property
	 * @param string $value New value of the property
	 * @return void
	 */
	public function __set($name, $value)
	{
		$disallowed = array('attr', 'elname', 'content', 'children', 'html');

		if ( in_array($name, $disallowed) )
		{
			Kernel::Log('Could not modify attribute of element '.$this->elname);
			return false;
		}
		else
		{
			if ( $name == "content" )
				$this->content = array($value);
			else
				$this->attr[$name] = $value;
		}
	}
}