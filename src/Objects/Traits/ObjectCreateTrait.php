<?php

/**
 * @package    Spark
 * @author     Flipbox Factory <hello@flipboxfactory.com>
 * @copyright  2010-2016 Flipbox Digital Limited
 * @license    https://github.com/FlipboxFactory/Craft3-Spark/blob/master/LICENSE
 * @link       https://github.com/FlipboxFactory/Craft3-Spark
 * @since      Class available since Release 1.2.12
 */

namespace Flipbox\Craft3\Spark\Objects\Traits;

use Flipbox\Craft3\Spark\Helpers\ArrayHelper;
use Flipbox\Craft3\Spark\Helpers\ObjectHelper;

trait ObjectCreateTrait
{

    /**
     * Returns the fully qualified name of this class.
     * @return string the fully qualified name of this class.
     */
    public abstract static function className();

    /**
     * @inheritdoc
     */
    public static function create($config = [])
    {

        // Force array
        if (!is_array($config)) {
            $config = ArrayHelper::toArray($config, [], false);
        }

        // Get object class
        $config['class'] = static::className();

        return ObjectHelper::create($config);

    }

}