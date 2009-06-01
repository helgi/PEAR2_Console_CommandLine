<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of the PEAR2_Console_CommandLine package.
 *
 * A full featured package for managing command-line options and arguments 
 * hightly inspired from python optparse module, it allows the developper to 
 * easily build complex command line interfaces.
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to the MIT license that is available
 * through the world-wide-web at the following URI:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category  Console 
 * @package   PEAR2_Console_CommandLine
 * @author    David JEAN LOUIS <izimobil@gmail.com>
 * @copyright 2007 David JEAN LOUIS
 * @license   http://opensource.org/licenses/mit-license.php MIT License 
 * @version   SVN: $Id$
 * @link      http://pear.php.net/package/Console_CommandLine
 * @since     Class available since release 0.1.0
 */

/**
 * Main class for parsing command line options and arguments.
 * 
 * There are three ways to create parsers with this class:
 * <code>
 * // direct usage
 * $parser = new PEAR2_Console_CommandLine();
 *
 * // with an xml definition file
 * $parser = PEAR2_Console_CommandLine::fromXmlFile('path/to/file.xml');
 *
 * // with an xml definition string
 * $validXmlString = '..your xml string...';
 * $parser = PEAR2_Console_CommandLine::fromXmlString($validXmlString);
 * </code>
 *
 * @category  Console
 * @package   PEAR2_Console_CommandLine
 * @author    David JEAN LOUIS <izimobil@gmail.com>
 * @copyright 2007 David JEAN LOUIS
 * @license   http://opensource.org/licenses/mit-license.php MIT License 
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/Console_CommandLine
 * @since     File available since release 0.1.0
 * @example   docs/examples/ex1.php
 * @example   docs/examples/ex2.php
 */
class PEAR2_Console_CommandLine
{
    // Public properties {{{

    /**
     * Error messages.
     *
     * @var    array $errors
     * @access public
     */
    public static $errors = array(
        'option_bad_name'                    => 'option name must be a valid php variable name (got: {$name})',
        'argument_bad_name'                  => 'argument name must be a valid php variable name (got: {$name})',
        'option_long_and_short_name_missing' => 'you must provide at least an option short name or long name for option "{$name}"',
        'option_bad_short_name'              => 'option "{$name}" short name must be a dash followed by a letter (got: "{$short_name}")',
        'option_bad_long_name'               => 'option "{$name}" long name must be 2 dashes followed by a word (got: "{$long_name}")',
        'option_unregistered_action'         => 'unregistered action "{$action}" for option "{$name}".',
        'option_bad_action'                  => 'invalid action for option "{$name}".',
        'option_invalid_callback'            => 'you must provide a valid callback for option "{$name}"',
        'action_class_does_not_exists'       => 'action "{$name}" class "{$class}" not found, make sure that your class is available before calling PEAR2_Console_CommandLine::registerAction()',
        'invalid_xml_file'                   => 'XML definition file "{$file}" does not exists or is not readable',
        'invalid_rng_file'                   => 'RNG file "{$file}" does not exists or is not readable'
    );

    /**
     * The name of the program, if not given it defaults to argv[0].
     *
     * @var    string $name
     * @access public
     */
    public $name;

    /**
     * A description text that will be displayed in the help message.
     *
     * @var    string $description
     * @access public
     */
    public $description = '';

    /**
     * A string that represents the version of the program, if this property is 
     * not empty and property add_version_option is not set to false, the
     * command line parser will add a --version option, that will display the
     * property content.
     *
     * @var    string $version
     * @access public
     */
    public $version = '';

    /**
     * Boolean that determine if the command line parser should add the help
     * (-h, --help) option automatically.
     *
     * @var    bool $add_help_option
     * @access public
     */
    public $add_help_option = true;

    /**
     * Boolean that determine if the command line parser should add the version
     * (-v, --version) option automatically.
     * Note that the version option is also generated only if the version 
     * property is not empty, it's up to you to provide a version string of 
     * course.
     *
     * @var    bool $add_version_option
     * @access public
     */
    public $add_version_option = true;

    /**
     * The command line parser renderer instance.
     *
     * @var    object that implements PEAR2_Console_CommandLine_Renderer interface
     * @access protected
     */
    public $renderer = false;

    /**
     * The command line parser outputter instance.
     *
     * @var    object that implements PEAR2_Console_CommandLine::Outputter interface
     * @access protected
     */
    public $outputter = false;

    /**
     * The command line message provider instance.
     *
     * @var    object an instance of PEAR2_Console_CommandLine::Message
     * @access protected
     */
    public $message_provider = false;

    /**
     * Boolean that tells the parser to be POSIX compliant, POSIX demands the 
     * following behavior: the first non-option stops option processing.
     *
     * @var    bool $force_posix
     * @access public
     */
    public $force_posix = false;

    /**
     * An array of PEAR2_Console_CommandLine_Option objects.
     *
     * @var    array $options
     * @access public
     */
    public $options = array();

    /**
     * An array of PEAR2_Console_CommandLine_Argument objects.
     *
     * @var    array $args
     * @access public
     */
    public $args = array();

    /**
     * An array of PEAR2_Console_CommandLine_Command objects (sub commands).
     *
     * @var    array $commands
     * @access public
     */
    public $commands = array();

    /**
     * Parent, only relevant in Command objects but left here for interface 
     * convenience.
     *
     * @var    object PEAR2_Console_CommandLine
     * @access public
     */
    public $parent = false;

    /**
     * Array of valid actions for an option, this array will also store user 
     * registered actions.
     * The array format is:
     * <pre>
     * array(
     *     <ActionName:string> => array(<ActionClass:string>, <builtin:bool>)
     * )
     *
     * @var    array $actions
     * @static
     * @access public
     */
    public static $actions = array(
        'StoreTrue'   => array('PEAR2_Console_CommandLine_Action_StoreTrue', true),
        'StoreFalse'  => array('PEAR2_Console_CommandLine_Action_StoreFalse', true),
        'StoreString' => array('PEAR2_Console_CommandLine_Action_StoreString', true),
        'StoreInt'    => array('PEAR2_Console_CommandLine_Action_StoreInt', true),
        'StoreFloat'  => array('PEAR2_Console_CommandLine_Action_StoreFloat', true),
        'StoreArray'  => array('PEAR2_Console_CommandLine_Action_StoreArray', true),
        'Callback'    => array('PEAR2_Console_CommandLine_Action_Callback', true),
        'Counter'     => array('PEAR2_Console_CommandLine_Action_Counter', true),
        'Help'        => array('PEAR2_Console_CommandLine_Action_Help', true),
        'Version'     => array('PEAR2_Console_CommandLine_Action_Version', true),
        'Password'    => array('PEAR2_Console_CommandLine_Action_Password', true)
    );

    /**
     * Array of options that must be dispatched at the end.
     *
     * @var    array $_dispatchLater
     * @access private
     */
    private $_dispatchLater = array();

    // }}}
    // __construct() {{{

    /**
     * Constructor.
     * Example:
     *
     * <code>
     * $parser = new PEAR2_Console_CommandLine(array(
     *     'name'               => 'yourprogram', // defaults to argv[0]
     *     'description'        => 'Description of your program',
     *     'version'            => '0.0.1', // your program version
     *     'add_help_option'    => true, // or false to disable --version option
     *     'add_version_option' => true, // or false to disable --help option
     *     'force_posix'        => false // or true to force posix compliance
     * ));
     * </code>
     *
     * @param array $params an optional array of parameters
     *
     * @access public
     */
    public function __construct(array $params=array()) 
    {
        if (isset($params['name'])) {
            $this->name = $params['name'];
        } else if (isset($argv) && count($argv) > 0) {
            $this->name = $argv[0];
        } else if (isset($_SERVER['argv']) && count($_SERVER['argv']) > 0) {
            $this->name = $_SERVER['argv'][0];
        } else if (isset($_SERVER['SCRIPT_NAME'])) {
            $this->name = basename($_SERVER['SCRIPT_NAME']);
        }
        if (isset($params['description'])) {
            $this->description = $params['description'];
        }
        if (isset($params['version'])) {
            $this->version = $params['version'];
        }
        if (isset($params['add_version_option'])) {
            $this->add_version_option = $params['add_version_option'];
        }
        if (isset($params['add_help_option'])) {
            $this->add_help_option = $params['add_help_option'];
        }
        if (isset($params['force_posix'])) {
            $this->force_posix = $params['force_posix'];
        } else if (getenv('POSIXLY_CORRECT')) {
            $this->force_posix = true;
        }
        // set default instances
        $this->renderer         = new PEAR2_Console_CommandLine_Renderer_Default($this);
        $this->outputter        = new PEAR2_Console_CommandLine_Outputter_Default();
        $this->message_provider = new PEAR2_Console_CommandLine_MessageProvider_Default();
    }

    // }}}
    // accept() {{{

    /**
     * Method to allow PEAR2_Console_CommandLine to accept either:
     *  + a custom renderer, 
     *  + a custom outputter,
     *  + or a custom message provider
     *
     * @param mixed $instance the custom instance
     *
     * @access public
     * @return void
     * @throws PEAR2_Console_CommandLine_Exception if wrong argument passed
     */
    public function accept($instance) 
    {
        if ($instance instanceof PEAR2_Console_CommandLine_Renderer) {
            if (property_exists($instance, 'parser') && !$instance->parser) {
                $instance->parser = $this;
            }
            $this->renderer = $instance;
        } else if ($instance instanceof PEAR2_Console_CommandLine_Outputter) {
            $this->outputter = $instance;
        } else if ($instance instanceof PEAR2_Console_CommandLine_MessageProvider) {
            $this->message_provider = $instance;
        } else {
            throw PEAR2_Console_CommandLine_Exception::build(
                'INVALID_CUSTOM_INSTANCE',
                array(),
                $this
            );
        }
    }

    // }}}
    // fromXmlFile() {{{

    /**
     * Return a command line parser instance built from an xml file.
     *
     * Example:
     * <code>
     * $parser = PEAR2_Console_CommandLine::fromXmlFile('path/to/file.xml');
     * $result = $parser->parse();
     * </code>
     *
     * @param string $file path to the xml file
     *
     * @return object a PEAR2_Console_CommandLine instance
     * @access public
     * @static
     */
    public static function fromXmlFile($file) 
    {
        return PEAR2_Console_CommandLine_XmlParser::parse($file);
    }

    // }}}
    // fromXmlString() {{{

    /**
     * Return a command line parser instance built from an xml string.
     *
     * Example:
     * <code>
     * $xmldata = '<?xml version="1.0" encoding="utf-8" standalone="yes"?>
     * <command>
     *   <description>Compress files</description>
     *   <option name="quiet">
     *     <short_name>-q</short_name>
     *     <long_name>--quiet</long_name>
     *     <description>be quiet when run</description>
     *     <action>StoreTrue/action>
     *   </option>
     *   <argument name="files">
     *     <description>a list of files</description>
     *     <multiple>true</multiple>
     *   </argument>
     * </command>';
     * $parser = PEAR2_Console_CommandLine::fromXmlString($xmldata);
     * $result = $parser->parse();
     * </code>
     *
     * @param string $string the xml data
     *
     * @return object a PEAR2_Console_CommandLine instance
     * @access public
     * @static
     */
    public static function fromXmlString($string) 
    {
        return PEAR2_Console_CommandLine_XmlParser::parseString($string);
    }

    // }}}
    // addArgument() {{{

    /**
     * Add an argument with the given $name to the command line parser.
     *
     * Example:
     * <code>
     * $parser = new PEAR2_Console_CommandLine();
     * // add an array argument
     * $parser->addArgument('input_files', array('multiple'=>true));
     * // add a simple argument
     * $parser->addArgument('output_file');
     * $result = $parser->parse();
     * print_r($result->args['input_files']);
     * print_r($result->args['output_file']);
     * // will print:
     * // array('file1', 'file2')
     * // 'file3'
     * // if the command line was:
     * // myscript.php file1 file2 file3
     * </code>
     *
     * In a terminal, the help will be displayed like this:
     * <code>
     * $ myscript.php install -h
     * Usage: myscript.php <input_files...> <output_file>
     * </code>
     *
     * @param mixed $name   a string containing the argument name or an
     *                      instance of PEAR2_Console_CommandLine_Argument
     * @param array $params an array containing the argument attributes
     *
     * @return object PEAR2_Console_CommandLine_Argument
     * @access public
     * @see    PEAR2_Console_CommandLine_Command
     */
    public function addArgument($name, $params=array())
    {
        if ($name instanceof PEAR2_Console_CommandLine_Argument) {
            $argument = $name;
        } else {
            $argument = new PEAR2_Console_CommandLine_Argument($name, $params);
        }
        $argument->validate();
        $this->args[$argument->name] = $argument;
        return $argument;
    }

    // }}}
    // addCommand() {{{

    /**
     * Add a sub-command to the command line parser.
     *
     * Add a command with the given $name to the parser and return the 
     * PEAR2_Console_CommandLine_Command instance, you can then populate the command
     * with options, configure it, etc... like you would do for the main parser
     * because the class PEAR2_Console_CommandLine_Command inherits from
     * PEAR2_Console_CommandLine.
     *
     * An example:
     * <code>
     * $parser = new PEAR2_Console_CommandLine();
     * $install_cmd = $parser->addCommand('install');
     * $install_cmd->addOption(
     *     'verbose',
     *     array(
     *         'short_name'  => '-v',
     *         'long_name'   => '--verbose',
     *         'description' => 'be noisy when installing stuff',
     *         'action'      => 'StoreTrue'
     *      )
     * );
     * $parser->parse();
     * </code>
     * Then in a terminal:
     * <code>
     * $ myscript.php install -h
     * Usage: myscript.php install [options]
     *
     * Options:
     *   -h, --help     display this help message and exit
     *   -v, --verbose  be noisy when installing stuff
     *
     * $ myscript.php install --verbose
     * Installing whatever...
     * $
     * </code>
     *
     * @param mixed $name   a string containing the command name or an
     *                      instance of PEAR2_Console_CommandLine_Command
     * @param array $params an array containing the command attributes
     *
     * @return object PEAR2_Console_CommandLine_Command
     * @access public
     * @see    PEAR2_Console_CommandLine_Command
     */
    public function addCommand($name, $params=array())
    {
        if ($name instanceof PEAR2_Console_CommandLine_Command) {
            $command = $name;
        } else {
            $params['name'] = $name;
            $command        = new PEAR2_Console_CommandLine_Command($params);
        }
        $command->parent                = $this;
        $this->commands[$command->name] = $command;
        return $command;
    }

    // }}}
    // addOption() {{{

    /**
     * Add an option to the command line parser.
     *
     * Add an option with the name (variable name) $optname and set its 
     * attributes with the array $params, then return the
     * PEAR2_Console_CommandLine_Option instance created.
     * The method accepts another form: you can directly pass a 
     * PEAR2_Console_CommandLine_Option object as the sole argument, this allows to
     * contruct  the option separately, in order to reuse an option in
     * different command line parsers or commands for example.
     *
     * Example:
     * <code>
     * $parser = new PEAR2_Console_CommandLine();
     * $parser->addOption('path', array(
     *     'short_name'  => '-p',  // a short name
     *     'long_name'   => '--path', // a long name
     *     'description' => 'path to the dir', // a description msg
     *     'action'      => 'StoreString',
     *     'default'     => '/tmp' // a default value
     * ));
     * $parser->parse();
     * </code>
     *
     * In a terminal, the help will be displayed like this:
     * <code>
     * $ myscript.php --help
     * Usage: myscript.php [options]
     *
     * Options:
     *   -h, --help  display this help message and exit
     *   -p, --path  path to the dir
     *
     * </code>
     *
     * Various methods to specify an option, these 3 commands are equivalent:
     * <code>
     * $ myscript.php --path=some/path
     * $ myscript.php -p some/path
     * $ myscript.php -psome/path
     * </code>
     *
     * @param mixed $name   a string containing the option name or an
     *                      instance of PEAR2_Console_CommandLine_Option
     * @param array $params an array containing the option attributes
     *
     * @return object PEAR2_Console_CommandLine_Option
     * @access public
     * @see    PEAR2_Console_CommandLine_Option
     */
    public function addOption($name, $params=array())
    {
        if ($name instanceof PEAR2_Console_CommandLine_Option) {
            $opt = $name;
        } else {
            $opt = new PEAR2_Console_CommandLine_Option($name, $params);
        }
        $opt->validate();
        $this->options[$opt->name] = $opt;
        return $opt;
    }

    // }}}
    // displayError() {{{

    /**
     * Display an error to the user and exit with $exitCode.
     *
     * @param string $error    the error message
     * @param int    $exitCode the exit code number
     *
     * @return void
     * @access public
     */
    public function displayError($error, $exitCode = 1)
    {
        $this->outputter->stderr($this->renderer->error($error));
        exit($exitCode);
    }

    // }}}
    // displayUsage() {{{

    /**
     * Display the usage help message to the user and exit with $exitCode
     *
     * @param int $exitCode the exit code number
     *
     * @return void
     * @access public
     */
    public function displayUsage($exitCode = 1)
    {
        $this->outputter->stderr($this->renderer->usage());
        exit($exitCode);
    }

    // }}}
    // displayVersion() {{{

    /**
     * Display the program version to the user
     *
     * @return void
     * @access public
     */
    public function displayVersion()
    {
        $this->outputter->stdout($this->renderer->version());
        exit(0);
    }

    // }}}
    // findOption() {{{

    /**
     * Find the option that matches the given short_name (ex: -v), long_name
     * (ex: --verbose) or name (ex: verbose).
     *
     * @param string $str the option identifier
     *
     * @return mixed a PEAR2_Console_CommandLine_Option instance or false
     * @access public
     */
    public function findOption($str)
    {
        $str = trim($str);
        if ($str === '') {
            return false;
        }
        $matches = array();
        foreach ($this->options as $opt) {
            if ($opt->short_name == $str || $opt->long_name == $str ||
                $opt->name == $str) {
                // exact match
                return $opt;
            }
            if (substr($opt->long_name, 0, strlen($str)) === $str) {
                // abbreviated long option
                $matches[] = $opt;
            }
        }
        if ($count = count($matches)) {
            if ($count > 1) {
                $matches_str = '';
                $padding     = '';
                foreach ($matches as $opt) {
                    $matches_str .= $padding . $opt->long_name;
                    $padding      = ', ';
                }
                throw PEAR2_Console_CommandLine_Exception::build(
                    'OPTION_AMBIGUOUS',
                    array('name' => $str, 'matches' => $matches_str),
                    $this
                );
            }
            return $matches[0];
        }
        return false;
    }
    // }}}
    // registerAction() {{{

    /**
     * Register a custom action for the parser, an example:
     *
     * <code>
     * <?php
     *
     * // in this example we create a "range" action:
     * // the user will be able to enter something like:
     * // $ <program> -r 1,5
     * // and in the result we will have:
     * // $result->options['range']: array(1, 5)
     *
     * class ActionRange extends PEAR2_Console_CommandLine_Action
     * {
     *     public function execute($value=false, $params=array())
     *     {
     *         $range = explode(',', str_replace(' ', '', $value));
     *         if (count($range) != 2) {
     *             throw new Exception(sprintf(
     *                 'Option "%s" must be 2 integers separated by a comma',
     *                 $this->option->name
     *             ));
     *         }
     *         $this->setResult($range);
     *     }
     * }
     * // then we can register our action
     * PEAR2_Console_CommandLine::registerAction('Range', 'ActionRange');
     * // and now our action is available !
     * $parser = new PEAR2_Console_CommandLine();
     * $parser->addOption('range', array(
     *     'short_name'  => '-r',
     *     'long_name'   => '--range',
     *     'action'      => 'Range', // note our custom action
     *     'description' => 'A range of two integers separated by a comma'
     * ));
     * // etc...
     *
     * ?>
     * </code>
     *
     * @param string $name  the name of the custom action
     * @param string $class the class name of the custom action
     *
     * @return void
     * @access public
     * @static
     */
    public static function registerAction($name, $class) 
    {
        if (!isset(self::$actions[$name])) {
            if (!class_exists($class)) {
                self::triggerError('action_class_does_not_exists',
                    E_USER_ERROR,
                    array('{$name}' => $name, '{$class}' => $class));
            }
            self::$actions[$name] = array($class, false);
        }
    }

    // }}}
    // triggerError() {{{

    /**
     * A wrapper for programming errors triggering.
     *
     * @param string $msgId  identifier of the message
     * @param int    $level  the php error level
     * @param array  $params an array of search=>replaces entries
     *
     * @return void
     * @access public
     * @static
     */
    public static function triggerError($msgId, $level, $params=array()) 
    {
        if (isset(self::$errors[$msgId])) {
            $msg = str_replace(array_keys($params),
                array_values($params), self::$errors[$msgId]); 
            trigger_error($msg, $level);
        } else {
            trigger_error('unknown error', $level);
        }
    }

    // }}}
    // parse() {{{

    /**
     * Parse the command line arguments and return a PEAR2_Console_CommandLine_Result 
     * object.
     *
     * @param integer $userArgc number of arguments (optional)
     * @param array   $userArgv array containing arguments (optional)
     * @param integer $beginAt  beginning index of the argv array (optional)
     *
     * @return object PEAR2_Console_CommandLine_Result
     * @access public
     * @throws Exception on user errors
     */
    public function parse($userArgc=null, $userArgv=null)
    {
        $this->addBuiltinOptions();
        if ($userArgc !== null && $userArgv !== null) {
            $argc = $userArgc;
            $argv = $userArgv;
        } else {
            list($argc, $argv) = $this->getArgcArgv();
        }
        // build an empty result
        $result = new PEAR2_Console_CommandLine_Result();
        if (!$argc || empty($argv)) {
            return $result;
        }
        if (!($this instanceof PEAR2_Console_CommandLine_Command)) {
            // remove script name if we're not in a subcommand
            array_shift($argv);
            $argc--;
        }
        // will contain aruments
        $args = array();
        foreach ($this->options as $name=>$option) {
            $result->options[$name] = $option->default;
        }
        // parse command line tokens
        while ($argc--) {
            $token = array_shift($argv);
            try {
                if (isset($this->commands[$token])) {
                    $result->command_name = $token;
                    $result->command      = $this->commands[$token]->parse($argc,
                        $argv);
                    break;
                } else {
                    $this->parseToken($token, $result, $args, $argc);
                }
            } catch (Exception $exc) {
                throw $exc;
            }
        }
        // minimum argument number check
        $argnum = count($this->args);
        if (count($args) < $argnum) {
            throw PEAR2_Console_CommandLine_Exception::build(
                'ARGUMENT_REQUIRED',
                array('argnum' => $argnum, 'plural' => $argnum>1 ? 's': ''),
                $this
            );
        }
        // handle arguments
        $c = count($this->args);
        foreach ($this->args as $name=>$arg) {
            $c--;
            if ($arg->multiple) {
                $result->args[$name] = $c ? array_splice($args, 0, -$c) : $args;
            } else {
                $result->args[$name] = array_shift($args);
            }
        }
        // dispatch deferred options
        foreach ($this->_dispatchLater as $optArray) {
            $optArray[0]->dispatchAction($optArray[1], $optArray[2], $this);
        }
        return $result;
    }

    // }}}
    // parseToken() {{{

    /**
     * Parse the command line token and modify *by reference* the $options and 
     * $args arrays.
     *
     * @param string $token  the command line token to parse
     * @param object $result the PEAR2_Console_CommandLine_Result instance
     * @param array  &$args  the argv array
     * @param int    $argc   number of lasting args
     *
     * @return void
     * @access protected
     * @throws Exception on user errors
     */
    protected function parseToken($token, $result, &$args, $argc)
    {
        static $lastopt  = false;
        static $stopflag = false;
        $last  = $argc === 0;
        $token = trim($token);
        if (!$stopflag && $lastopt) {
            if (substr($token, 0, 1) == '-') {
                if ($lastopt->argument_optional) {
                    $this->_dispatchAction($lastopt, '', $result);
                    if ($lastopt->action != 'StoreArray') {
                        $lastopt = false;
                    }
                } else if (isset($result->options[$lastopt->name])) {
                    // case of an option that expect a list of args
                    $lastopt = false;
                } else {
                    throw PEAR2_Console_CommandLine_Exception::build(
                        'OPTION_VALUE_REQUIRED',
                        array('name' => $lastopt->name),
                        $this
                    );
                }
            } else {
                // when a StoreArray option is positioned last, the behavior
                // is to consider that if there's already an element in the
                // array, and the commandline expects one or more args, we
                // leave last tokens to arguments
                if ($lastopt->action == 'StoreArray' && 
                    !empty($result->options[$lastopt->name]) &&
                    count($this->args) > ($argc + count($args))) {
                    $args[] = $token;
                    return;
                }
                $this->_dispatchAction($lastopt, $token, $result);
                if ($lastopt->action != 'StoreArray') {
                    $lastopt = false;
                }
                return;
            }
        }
        if (!$stopflag && substr($token, 0, 2) == '--') {
            // a long option
            $optkv = explode('=', $token, 2);
            if (trim($optkv[0]) == '--') {
                // the special argument "--" forces in all cases the end of 
                // option scanning.
                $stopflag = true;
                return;
            }
            $opt = $this->findOption($optkv[0]);
            if (!$opt) {
                throw PEAR2_Console_CommandLine_Exception::build(
                    'OPTION_UNKNOWN',
                    array('name' => $optkv[0]),
                    $this
                );
            }
            $value = isset($optkv[1]) ? $optkv[1] : false;
            if (!$opt->expectsArgument() && $value !== false) {
                throw PEAR2_Console_CommandLine_Exception::build(
                    'OPTION_VALUE_UNEXPECTED',
                    array('name' => $opt->name, 'value' => $value),
                    $this
                );
            }
            if ($opt->expectsArgument() && $value === false) {
                // maybe the long option argument is separated by a space, if 
                // this is the case it will be the next arg
                if ($last && !$opt->argument_optional) {
                    throw PEAR2_Console_CommandLine_Exception::build(
                        'OPTION_VALUE_REQUIRED',
                        array('name' => $opt->name),
                        $this
                    );
                }
                // we will have a value next time
                $lastopt = $opt;
                return;
            }
            if ($opt->action == 'StoreArray') {
                $lastopt = $opt;
            }
            $this->_dispatchAction($opt, $value, $result);
        } else if (!$stopflag && substr($token, 0, 1) == '-') {
            // a short option
            $optname = substr($token, 0, 2);
            if ($optname == '-') {
                // special case of "-" passed on the command line, it should be 
                // treated as an argument
                $args[] = $optname;
                return;
            }
            $opt = $this->findOption($optname);
            if (!$opt) {
                throw PEAR2_Console_CommandLine_Exception::build(
                    'OPTION_UNKNOWN',
                    array('name' => $optname),
                    $this
                );
            }
            // parse other options or set the value
            // in short: handle -f<value> and -f <value>
            $next = substr($token, 2, 1);
            // check if we must wait for a value
            if ($next === false) {
                if ($opt->expectsArgument()) {
                    if ($last && !$opt->argument_optional) {
                        throw PEAR2_Console_CommandLine_Exception::build(
                            'OPTION_VALUE_REQUIRED',
                            array('name' => $opt->name),
                            $this
                        );
                    }
                    // we will have a value next time
                    $lastopt = $opt;
                    return;
                }
                $value = false;
            } else {
                if (!$opt->expectsArgument()) { 
                    if ($nextopt = $this->findOption('-' . $next)) {
                        $this->_dispatchAction($opt, false, $result);
                        $this->parseToken('-' . substr($token, 2), $result,
                            $args, $last);
                        return;
                    } else {
                        throw PEAR2_Console_CommandLine_Exception::build(
                            'OPTION_UNKNOWN',
                            array('name' => $next),
                            $this
                        );
                    }
                }
                if ($opt->action == 'StoreArray') {
                    $lastopt = $opt;
                }
                $value = substr($token, 2);
            }
            $this->_dispatchAction($opt, $value, $result);
        } else {
            // We have an argument.
            // if we are in POSIX compliant mode, we must set the stop flag to 
            // true in order to stop option parsing.
            if (!$stopflag && $this->force_posix) {
                $stopflag = true;
            }
            $args[] = $token;
        }
    }

    // }}}
    // addBuiltinOptions() {{{

    /**
     * Add the builtin "Help" and "Version" options if needed.
     *
     * @return void
     * @access protected 
     */
    public function addBuiltinOptions()
    {
        if ($this->add_help_option) {
            $helpOptionParams = array(
                'long_name'   => '--help',
                'description' => 'show this help message and exit',
                'action'      => 'Help'   
            );
            if (!$this->findOption('-h')) {
                // short name is available, take it
                $helpOptionParams['short_name'] = '-h';
            }
            $this->addOption('help', $helpOptionParams);
        }
        if ($this->add_version_option && !empty($this->version)) {
            $versionOptionParams = array(
                'long_name'   => '--version',
                'description' => 'show the program version and exit',
                'action'      => 'Version'   
            );
            if (!$this->findOption('-v')) {
                // short name is available, take it
                $versionOptionParams['short_name'] = '-v';
            }
            $this->addOption('version', $versionOptionParams);
        }
    } 

    // }}}
    // getArgcArgv() {{{

    /**
     * Try to return an array containing argc and argv, or trigger an error
     * if it fails to get them.
     *
     * @return array
     * @access protected
     * @throws Exception 
     */
    protected function getArgcArgv()
    {
        if (php_sapi_name() != 'cli') {
            // we have a web request
            $argv = array($this->name);
            if (isset($_REQUEST)) {
                foreach ($_REQUEST as $key => $value) {
                    if (!is_array($value)) {
                        $value = array($value);
                    }
                    $opt = $this->findOption($key);
                    if ($opt instanceof PEAR2_Console_CommandLine_Option) {
                        // match a configured option
                        $argv[] = $opt->short_name ? 
                            $opt->short_name : $opt->long_name;
                        foreach($value as $v) {
                            if ($opt->expectsArgument()) {
                                $argv[] = isset($_GET[$key]) ? urldecode($v) : $v;
                            } else if ($v == '0' || $v == 'false') {
                                array_pop($argv);
                            }
                        }
                    } else if (isset($this->args[$key])) {
                        // match a configured argument
                        foreach($value as $v) {
                            $argv[] = isset($_GET[$key]) ? urldecode($v) : $v;
                        }
                    }
                }
            }
            return array(count($argv), $argv);
        }
        if (isset($argc) && isset($argv)) {
            // case of register_argv_argc = 1
            return array($argc, $argv);
        }
        if (isset($_SERVER['argc']) && isset($_SERVER['argv'])) {
            return array($_SERVER['argc'], $_SERVER['argv']);
        }
        return array(0, array());
    }

    // }}}
    // _dispatchAction() {{{

    /**
     * Dispatch the given option or store the option to dispatch it later.
     *
     * @param object $option an instance of PEAR2_Console_CommandLine_Option
     * @param string $token  the command line token to parse
     * @param object $result the PEAR2_Console_CommandLine_Result instance
     *
     * @return void
     * @access protected
     * @throws Exception on user errors
     */
    private function _dispatchAction($option, $token, $result)
    {
        if ($option->action == 'Password') {
            $this->_dispatchLater[] = array($option, $token, $result);
        } else {
            $option->dispatchAction($token, $result, $this);
        }
    }
    // }}}
}

?>
