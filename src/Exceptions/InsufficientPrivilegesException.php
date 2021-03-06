<?php

/**
 * @package    Spark
 * @author     Flipbox Factory <hello@flipboxfactory.com>
 * @copyright  2010-2016 Flipbox Digital Limited
 * @license    https://github.com/FlipboxFactory/Craft3-Spark/blob/master/LICENSE
 * @link       https://github.com/FlipboxFactory/Craft3-Spark
 * @since      Class available since Release 1.0.0
 */

namespace Flipbox\Craft3\Spark\Exceptions;

use craft\app\errors\ErrorException as Exception;

class InsufficientPrivilegesException extends Exception
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Insufficient Privileges Exception';
    }

}