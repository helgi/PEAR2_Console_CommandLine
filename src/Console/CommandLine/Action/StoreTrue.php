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
 * Class that represent the StoreTrue action.
 *
 * The execute method store the boolean 'true' in the corrsponding result
 * option array entry (the value is false if the option is not present in the 
 * command line entered by the user).
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
class PEAR2_Console_CommandLine_Action_StoreTrue extends PEAR2_Console_CommandLine_Action
{
    // execute() {{{

    /**
     * Execute the action with the value entered by the user.
     *
     * @param mixed $value  the option value
     * @param array $params an array of optional parameters
     *
     * @return string
     * @access public
     */
    public function execute($value=false, $params=array())
    {
        $this->setResult(true);
    }
    // }}}
}

?>
