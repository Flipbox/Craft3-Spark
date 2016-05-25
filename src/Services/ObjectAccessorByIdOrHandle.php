<?php

/**
 * @package    Spark
 * @author     Flipbox Factory <hello@flipboxfactory.com>
 * @copyright  2010-2016 Flipbox Digital Limited
 * @license    https://github.com/FlipboxFactory/Craft3-Spark/blob/master/LICENSE
 * @version    Release: 1.0.0
 * @link       https://github.com/FlipboxFactory/Craft3-Spark
 * @since      Class available since Release 1.0.0
 */

namespace Flipbox\Craft3\Spark\Services;

use Flipbox\Craft3\Spark\Objects\Interfaces\ObjectInterface;

abstract class ObjectAccessorByIdOrHandle extends ObjectAccessor
{

    use Traits\ObjectAccessorByIdTrait,
        Traits\ObjectAccessorByHandleTrait;

    /**
     * @param $identifier
     * @return ObjectInterface|null
     */
    public function find($identifier)
    {

        if (is_numeric($identifier)) {

            return $this->findById($identifier);

        } elseif (is_string($identifier)) {

            return $this->findByHandle($identifier);

        }

        return parent::find($identifier);

    }

    /*******************************************
     * CACHE
     *******************************************/

    /**
     * @param $identifier
     * @return null
     */
    public function findCache($identifier)
    {

        if (is_numeric($identifier)) {

            return $this->findCacheById($identifier);

        } elseif (is_string($identifier)) {

            return $this->findCacheByHandle($identifier);

        }

        return parent::findCache($identifier);

    }

}
