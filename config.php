<?

/**
 * Framework configuration files
 *
 * Contains all the configuration required for the framework to run smoothly
 */

// Sql configuration

// Let's stick with MySql, Sqlite, Sqlite2 and PostgreSql for now shall we?
// Also, lowercase please!

$config['sql']['driver'] = 'mysql';

// This one is used for Sqlite only :P
// Here, we're asking if we should use memory instead of a file

$config['sql']['memory'] = false;

// This one's obvious, but if it isn't for you, it's the database file
// Of course, don't use this one if the variable above ($config['sql']['memory']) is equal to true

$config['sql']['SqliteFile'] = null;

// Everything below here is useless if you're using Sqlite

$config['sql']['host']  = 'localhost';
$config['sql']['port']	= 3306;

// Let's keep it alphanumerical shall we?

$config['sql']['user']  = 'root';
$config['sql']['pswd']  = 'usbw';

// Nope, this one is useless as well if you're using Sqlite

$config['sql']['db']	= 'mydb';

// A few options, keep in mind that these are just passed directly to the PDO constructor
// If you're not familiar with PDO attributes, check the docs first for advice
// http://dev.studiowolfree.com/docs/pdoOptions

$config['sql']['pdoOptions'] = null;

// And if none of these satisfy your needs, you can always use a custom DSN
// Set the CUSTOM element to true if you want to use a custom DSN

$config['sql']['custom'] = false;

// Here's the DSN

$config['sql']['dsn'] = false;

// Now for the options

// If these are set to null, they're just not passed, remember that if you're using these both of them must be filled
// Also, these are passed as arguments for the PDO constructor and are not part of the DSN, so if you're looking to place them
// in the DSN element and leave these null

$config['sql']['customUser'] = null;
$config['sql']['customPswd'] = null;

// Framework configuration

// 1. Standard logging
// 2. Log lines
// 3. Dump debug_backtrace on a separate file
// 4. Email fatal errors and exceptions to developer
// 5. Email everything to developer (Warnings, notices, errors and exceptions )

$config['kernel']['errorLevel'] = 2;

// Logs directory!

$config['kernel']['logDir'] = 'logs';

// An empty variable for debugging purposes

$config['kernel']['currentSource'] = null;

// Some replacement variables if sitevars are not available

$config['kernel']['sysResources'] = "http://$_SERVER[HTTP_HOST]/swphp/resources/system";

// Should we print a warning if something has been logged?

$config['kernel']['showLog'] = true;

// Mail configuration

// The spokesperson of your server. This is basically the default sender's email

$config['mail']['postmaster'] = "postmaster@$_SERVER[HTTP_HOST]";

// This right here is the sendmail path, keep in mind that this will override php.ini directives

$config['mail']['sendmail_path'] = 'my_sendmail_path';

// Same thing as the sendmail path, except these are the arguments that will be passed.

$config['mail']['sendmail_args'] = '-t -f'.$config['mail']['postmaster'];

// Should we enable the emailExists function?
// If you want to enable this, you must register and use your username and password provided
// by the folks at http://www.verify-email.org

$config['mail']['emailExists'] = false;

// Access details for verify-mail.org

$config['mail']['ver_user'] = 'myuser';
$config['mail']['ver_pswd'] = 'mypassword';

$sitevars = array();

global $sql, $sitevars;