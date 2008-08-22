<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of the PEAR2_Console_CommandLine package.
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
 * @since     File available since release 0.1.0
 */

/**
 * Class for exceptions raised by the PEAR2_Console_CommandLine package.
 *
 * @category  Console
 * @package   PEAR2_Console_CommandLine
 * @author    David JEAN LOUIS <izimobil@gmail.com>
 * @copyright 2007 David JEAN LOUIS
 * @license   http://opensource.org/licenses/mit-license.php MIT License 
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/Console_CommandLine
 * @since     Class available since release 0.1.0
 */
class PEAR2_Console_CommandLine_Exception extends PEAR2_Exception
{
    // Codes constants {{{

    /**#@+
     * Exception code constants.
     */
    const OPTION_VALUE_REQUIRED   = 1;
    const OPTION_VALUE_UNEXPECTED = 2;
    const OPTION_VALUE_TYPE_ERROR = 3;
    const OPTION_UNKNOWN          = 4;
    const ARGUMENT_REQUIRED       = 5;
    /**#@-*/

    // }}}
    // build() {{{

    /**
     * Convenience method that builds the exception with the array of params by 
     * calling the message provider class.
     *
     * @param string $code   the string identifier of the exception
     * @param array  $params an array containing the vars of the message tpl
     * @param object $parser an instance of PEAR2_Console_CommandLine
     *
     * @return object an instance of PEAR2_Console_CommandLine_Exception
     * @access public
     * @static
     */
    public static function build($code, $params, $parser)
    {
        $msg   = $parser->message_provider->get($code, $params);
        $const = 'PEAR2_Console_CommandLine_Exception::' . $code;
        $code  = defined($const) ? constant($const) : 0;
        return new PEAR2_Console_CommandLine_Exception($msg, $code);
    }

    // }}}
}

?>
