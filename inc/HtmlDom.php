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
 * HTML class, usage is optional. Due to complications, not all functions were made using DomDocument. This class may only create documents and HTML elements, file reading and parsing requires a module extension.
 */
class HtmlDom
{
	/**
	 * Doctype that the HTML document will have
	 * @var string
	 */
	public $doctype;

	/**
	 * Holds all the DOM elements the document has. You can access elements like this: $mydocument->element->property
	 * @var array
	 */
	public $elements = array();

	/**
	 * Constructs the HTMLDom class and prepares the document
	 * @return void
	 */
	public function __construct()
	{
		$this->doctype = '<!doctype html>';

		$this->createElement('head');
		$this->createElement('body');
	}

	/**
	 * Creates a DOM element in the document and adds it to the elements array
	 * @param  string $element  Name of the HTML XML tag
	 * @param  array  $attr_arr Array of attributes the element will have
	 * @return void          
	 */
	public function createElement($element, $attr_arr = null)
	{
		if ( !in_array('body', array_keys($this->elements)) )
		{
			$this->elements[$element] = new HTMLObj($element);

			if ( !is_null($attr_arr) )
			{
				foreach ( $attr_arr as $key => $i )
				{
					$this->elements[$element]->$key = $i;
				}
			}
		}
		else
		{
			$this->elements[$element] = $this->elements['body']->addChild($element);
			
			if ( !is_null($attr_arr) )
			{
				foreach ( $attr_arr as $key => $i )
				{
					$this->elements[$element]->$key = $i;
				}
			}
		}
	}

	/**
	 * Retrieves a certain element in the elements array
	 * @param  string $element Name of the elements
	 * @return HTMLObj         Returns HTMLObj of the object called if it exists
	 */
	public function getElementHTML($element)
	{
		return $this->elements[$element]->execute();
	}

	/**
	 * Runs an execute on all the elements and returns a string with the HTML document
	 * @return string Returns an HTML document
	 */
	public function execute()
	{
		$html_doc[] = $this->doctype;
		$html_doc[] = '<html>';

		foreach ( $this->elements as $i )
		{
			$html_doc[] = $i->execute();
		}

		$html_doc[] = '</html>';

		return implode("\n", $html_doc);
	}

	/**
	 * Displays the raw HTML (Meaning, replaces the greater than and less than symbols for HTML codes) of the current document
	 * @return string Returns the raw HTML code of the current document
	 */
	public function displayRawHTML()
	{
		$find = array('<', '>');
		$replace = array('&lt;', '&gt;');

		return str_replace($find, $replace, $this->execute());
	}

	/**
	 * Returns a certain element unless the user requests doctype or elements, otherwise it returns that property
	 * @param  string $name Name of the property requested
	 * @return mixed        Returns the class' property or the document's element
	 */
	public function __get($name)
	{
		$special = array('doctype', 'elements');

		if ( !in_array($name, $special) )
		{
			return $this->elements[$name];
		}
	}
}