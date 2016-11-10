<?php

/**
 * @package    Spark
 * @author     Flipbox Factory <hello@flipboxfactory.com>
 * @copyright  2010-2016 Flipbox Digital Limited
 * @license    https://github.com/FlipboxFactory/Craft3-Spark/blob/master/LICENSE
 * @link       https://github.com/FlipboxFactory/Craft3-Spark
 * @since      Class available since Release 1.1.0
 */

namespace Flipbox\Craft3\Spark\Exceptions;

use craft\app\errors\ErrorException as Exception;

class ElementNotFoundException extends Exception
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Element Not Found Exception';
    }

}