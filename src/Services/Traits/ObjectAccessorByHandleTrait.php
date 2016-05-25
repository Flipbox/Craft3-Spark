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

namespace Flipbox\Craft3\Spark\Services\Traits;

use Flipbox\Craft3\Spark\Exceptions\ObjectNotFoundException;
use Flipbox\Craft3\Spark\Helpers\RecordHelper;
use Flipbox\Craft3\Spark\Models\Interfaces\ModelWithIdInterface;
use Flipbox\Craft3\Spark\Objects\Interfaces\ObjectInterface;
use Flipbox\Craft3\Spark\Records\Interfaces\RecordInterface;
use Flipbox\Craft3\Spark\Records\Interfaces\RecordWithIdInterface;

trait ObjectAccessorByHandleTrait
{

    /**
     * @var array of all objects indexed by Handle
     */
    protected $_cacheByHandle = [];

    /*******************************************
     * ABSTRACTS
     *******************************************/

    /**
     * @param $condition
     * @param string $scenario
     * @return RecordWithIdInterface|null
     */
    public abstract function findRecord($condition, $scenario = RecordHelper::SCENARIO_SAVE);

    /**
     * @param RecordInterface $record
     * @return ObjectInterface
     */
    public abstract function findByRecord(RecordInterface $record);

    /**
     * @param array $config
     * @return ObjectInterface
     */
    public abstract function create($config = []);

    /*******************************************
     * FRESH FIND
     *******************************************/

    /**
     * @param $handle
     * @return ModelWithIdInterface|null
     */
    public function freshFindByHandle($handle)
    {

        // Find record in db
        if ($record = $this->findRecord($handle)) {

            // New object
            $object = $this->create($record);

            // Cache it
            $this->cacheByHandle($handle, $object);

            return $object;

        }

        return null;

    }

    /**
     * @param $handle
     * @return ModelWithIdInterface|null
     * @throws ObjectNotFoundException
     */
    public function freshGetByHandle($handle)
    {

        if (!$object = $this->freshFindByHandle($handle)) {

            $this->notFoundByHandleException($handle);

        }

        return $object;

    }


    /*******************************************
     * FIND
     *******************************************/

    /**
     * @param $handle
     * @return ModelWithIdInterface|null
     */
    public function findByHandle($handle)
    {

        // Check addToCache
        if (!$object = $this->findCacheByHandle($handle)) {

            // Find record in db
            if ($record = $this->findRecord([
                'handle' => $handle
            ])
            ) {

                // Perhaps in cache
                $object = $this->findByRecord($record);

            } else {

                // Cache null (for subsequent requests)
                $this->cacheByHandle($handle);

                $object = null;

            }

        }

        return $object;

    }


    /*******************************************
     * GET
     *******************************************/

    /**
     * @param $handle
     * @return ModelWithIdInterface|null
     * @throws ObjectNotFoundException
     */
    public function getByHandle($handle)
    {

        // Find object by Handle
        if (!$object = $this->findByHandle($handle)) {

            $this->notFoundByHandleException($handle);

        }

        return $object;

    }


    /*******************************************
     * CACHE
     *******************************************/

    /**
     * Find an existing cached object by Handle
     *
     * @param $handle
     * @return null
     */
    public function findCacheByHandle($handle)
    {

        // Check if already in addToCache
        if ($this->isCachedByHandle($handle)) {

            return $this->_cacheByHandle[$handle];

        }

        return null;

    }

    /**
     * Identify whether an object is cached by Handle
     *
     * @param $handle
     * @return bool
     */
    protected function isCachedByHandle($handle)
    {
        return array_key_exists($handle, $this->_cacheByHandle);
    }

    /**
     * @param $handle
     * @param ObjectInterface $object
     * @return $this
     */
    protected function cacheByHandle($handle, ObjectInterface $object = null)
    {

        // Check if already in cache
        if (!$this->isCachedByHandle($handle)) {

            // Cache it
            $this->_cacheByHandle[$handle] = $object;

        }

        return $this;

    }

    /*******************************************
     * EXCEPTIONS
     *******************************************/

    /**
     * @param null $handle
     * @throws ObjectNotFoundException
     */
    protected function notFoundByHandleException($handle = null)
    {

        throw new ObjectNotFoundException(
            sprintf(
                'Object does not exist with the handle "%s".',
                (string)$handle
            )
        );

    }

}
