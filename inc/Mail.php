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
 * Mail class, optional
 */
class Mail
{
	/**
	 * Body of the message
	 * @var array
	 */
	public $message_body;

	/**
	 * Headers that the email will contain
	 * @var array
	 */
	public $headers;

	/**
	 * Subject of the email
	 * @var string
	 */
	public $subject;

	/**
	 * Recipients the email will have
	 * @var array
	 */
	public $recipients;

	/**
	 * Will the email contain HTML?
	 * @var bool
	 */
	public $isHTML;

	/**
	 * Constructs the class and sets the subject and sender
	 * @param string $subject Subject of the email
	 * @param array  $sender  Information about the email sender (name, mail)
	 * @return void
	 */
	public function __construct($subject, $sender = array())
	{
		if ( function_exists('mail') )
		{
			$this->message_body = array();
			$this->headers = array();
			$this->recipients = array();

			if ( !is_array($sender) )
			{
				Kernel::Log('The sender argument must be an array with the name and email of the sender');
				return false;
			}
			else
			{
				global $config;

				if ( count($sender) < 2 )
				{
					$sender[1] = $config['mail']['postmaster'];
					$sender[0] = Tpl::$sitevars['site_name'];
				}

				ini_set('sendmail_from', $sender[1]);
				ini_set('sendmail_path', $config['mail']['sendmail_path']);

				$this->headers['From'] = $sender[0].' <'.$sender[1].'>';
				$this->subject = $subject;
			
				return true;
			}
		}
		else
		{
			Kernel::Log('The mail function does not exist.');
			return false;
		}
	}

	/**
	 * Uses the verify-email.org RESTful API to check if an email actually exists
	 * @param  string $email Email to verify
	 * @return bool          Returns true if the email is valid
	 */
	static function emailExists($email)
	{
		global $config;

		if ( $config['mail']['emailExists'] == false )
		{
			Kernel::Log('Cannot execute Mail::emailExists as it is disabled');
			return 0;
		}

		$username = $config['mail']['ver_user'];
		$password = $config['mail']['ver_pswd'];

		$url      = 'http://api.verify-email.org/api.php?usr='.$username.'&pwd='.$password.'&check='.$email;
		$object	  = json_decode(file_get_contents($url));
				
		if ( $object->verify_status )
			return true;
		else
			return false;	
	}

	/**
	 * Verifies if an email is correctly formatted (Alias of Text::validate)
	 * @param  string  $email Email to check
	 * @return boolean        Returns true if the email is valid
	 */
	static function isValid($email)
	{
		return Text::validate($email, 'email');
	}

	/**
	 * Adds recipients in bulk using a multidimensional arrays
	 * @param array $array Array of recipients ([] => [email, name, type])
	 * @return bool        Returns true if the recipients were added successfully
	 */
	public function addRecipientBulk($array)
	{
		if ( !is_array($array) )
		{
			Kernel::Log('The only argument for addRecipientBulk must be an array composed of the name, address and type of the recipient');
			return false;
		}
		else
		{
			foreach ( $array as $i )
			{
				$this->recipients[] = $i[0];

				switch ( $i[2] )
				{
					case 'normal':
						$this->headers['To'][] = $i[1].' <'.$i[0].'>';
					break;
					case 'cc':
						$this->headers['Cc'][] = $i[0];
					break;
					case 'bcc':
						$this->headers['Bcc'][] = $i[0];
					break;
				}
			}

			return true;
		}
	}

	/**
	 * Adds a recipient to the recipients array
	 * @param string $email Email of the recipient
	 * @param string $name  Name of the recipient
	 * @param string $type  Type of recipient (normal, cc, bcc)
	 * @return void
	 */
	public function addRecipient($email, $name, $type = 'normal')
	{
		$this->recipients[] = $email;

		switch ( $type )
		{
			case 'normal':
				$this->headers['To'][] = $name.' <'.$email.'>';
			break;
			case 'cc':
				$this->headers['Cc'][] = $email;
			break;
			case 'bcc':
				$this->headers['Bcc'][] = $email;
			break;
		}
	}

	/**
	 * Adds the necessary message body and headers for an HTML message
	 * @return void
	 */
	public function makeHTML()
	{
		$this->message_body[] = '
		<html>
		<head>
		  <title>'.$this->subject.'</title>
		</head>
		<body>
		';

		$this->isHTML = true;

		$this->headers['MIME-Version'] = '1.0';
		$this->headers['Content-type'] = 'text/html; charset=iso-8859-1';

		return true;
	}

	/**
	 * Implodes the message, headers and recipients and sends the email
	 * @return bool Returns true if the message was successfully sent
	 */
	public function sendMail()
	{
		global $config;

		$headers = '';
		$message = '';

		foreach ( $this->headers as $key => $i )
		{
			if ( is_array($i) )
			{
				$string = $key.': ';

				foreach ( $i as $i2 )
				{
					$string_arr[] = $i2;
				}

				$string .= implode(', ', $i2);

				$headers .= $string."\r\n";
			}
			else
				$headers .= $key.': '.$i."\r\n";
		}

		foreach ( $this->message_body as $i )
		{
			$message .= $i;
		}

		if ( $this->isHTML )
		{
			$message .= '
			</body>
			</html>';
		}

		return mail($this->recipients, $this->subject, $message, $headers, $config['mail']['sendmail_args']);
	}

	/**
	 * If the property that is being set is called content, we will simply add it to the message_body array
	 * @param string $name  Name of the property
	 * @param string $value New value of the property
	 * @return void
	 */
	public function __set($name, $value)
	{
		if ( $name == 'content' )
			$this->message_body[] = $value;
	}
}