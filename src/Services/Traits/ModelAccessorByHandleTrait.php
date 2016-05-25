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

use Flipbox\Craft3\Spark\Exceptions\ModelNotFoundException;
use Flipbox\Craft3\Spark\Helpers\ModelHelper;
use Flipbox\Craft3\Spark\Helpers\RecordHelper;
use Flipbox\Craft3\Spark\Models\Interfaces\ModelInterface;
use Flipbox\Craft3\Spark\Models\Interfaces\ModelWithHandleInterface;
use Flipbox\Craft3\Spark\Records\Interfaces\RecordInterface;
use Flipbox\Craft3\Spark\Records\Interfaces\RecordWithHandleInterface;

trait ModelAccessorByHandleTrait
{

    /**
     * @var ModelWithHandleInterface[]
     */
    protected $_cacheByHandle = [];

    /*******************************************
     * ABSTRACTS
     *******************************************/

    /**
     * @param $condition
     * @param string $scenario
     * @return RecordWithHandleInterface|null
     */
    public abstract function findRecord($condition, $scenario = RecordHelper::SCENARIO_UPDATE);

    /**
     * @param RecordInterface $record
     * @param string $scenario
     * @return ModelWithHandleInterface|null
     */
    public abstract function findByRecord(RecordInterface $record, $scenario = ModelHelper::SCENARIO_SAVE);


    /**
     * @param array $config
     * @param string $scenario
     * @return ModelWithHandleInterface
     */
    public abstract function create($config = [], $scenario = ModelHelper::SCENARIO_SAVE);


    /*******************************************
     * RECORD
     *******************************************/

    /**
     * @param $handle
     * @return RecordWithHandleInterface|null
     */
    protected function findRecordByHandle($handle)
    {

        return $this->findRecord([
            'handle' => $handle
        ]);

    }

    /*******************************************
     * FRESH FIND
     *******************************************/

    /**
     * @param $handle
     * @param string $scenario
     * @return ModelWithHandleInterface|null
     */
    public function freshFindByHandle($handle, $scenario = ModelHelper::SCENARIO_SAVE)
    {

        // Find record in db
        if ($record = $this->findRecordByHandle($handle)) {

            // Create
            return $this->create($record, $scenario);

        }

        return null;

    }

    /**
     * @param $handle
     * @param string $scenario
     * @return ModelWithHandleInterface
     * @throws ModelNotFoundException
     */
    public function freshGetByHandle($handle, $scenario = ModelHelper::SCENARIO_SAVE)
    {

        if (!$model = $this->freshFindByHandle($handle, $scenario)) {

            $this->notFoundByHandleException($handle);

        }

        return $model;

    }


    /*******************************************
     * FIND
     *******************************************/

    /**
     * @param $handle
     * @param string $scenario
     * @return ModelWithHandleInterface|null
     */
    public function findByHandle($handle, $scenario = ModelHelper::SCENARIO_SAVE)
    {

        // Check cache
        if (!$model = $this->findCacheByHandle($handle)) {

            // Find record in db
            if ($record = $this->findRecordByHandle($handle)) {

                // Perhaps in cache
                $model = $this->findByRecord($record, $scenario);

            } else {

                $this->_cacheByHandle[$handle] = null;

                return null;

            }

        }

        return $model;

    }


    /*******************************************
     * GET
     *******************************************/

    /**
     * @param $handle
     * @param string $scenario
     * @return ModelWithHandleInterface|null
     * @throws ModelNotFoundException
     */
    public function getByHandle($handle, $scenario = ModelHelper::SCENARIO_SAVE)
    {

        if (!$model = $this->findByHandle($handle, $scenario)) {

            $this->notFoundByHandleException($handle);

        }

        return $model;

    }


    /*******************************************
     * CACHE
     *******************************************/

    /**
     * Find an existing cache by handle
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
     * Identify whether in cache by handle
     *
     * @param $handle
     * @return bool
     */
    private function isCachedByHandle($handle)
    {
        return array_key_exists($handle, $this->_cacheByHandle);
    }

    /**
     * @param ModelWithHandleInterface $model
     * @return $this
     */
    protected function cacheByHandle(ModelWithHandleInterface $model)
    {

        // Check if already in cache
        if (!$this->isCachedByHandle($model->getHandle())) {

            // Cache it
            $this->_cacheByHandle[$model->getHandle()] = $model;

        }

        return $this;

    }

    /**
     * @param RecordInterface $record
     * @return null
     */
    public function findCacheByRecord(RecordInterface $record)
    {

        if ($record instanceof RecordWithHandleInterface) {

            // Check if already in cache by id
            if (!$this->isCachedByHandle($record->getHandle())) {

                return $this->findCacheByHandle($record->getHandle());

            }

        }

        return null;

    }

    /*******************************************
     * EXCEPTIONS
     *******************************************/

    /**
     * @param null $handle
     * @throws ModelNotFoundException
     */
    protected function notFoundByHandleException($handle = null)
    {

        throw new ModelNotFoundException(
            sprintf(
                'Model does not exist with the handle "%s".',
                (string)$handle
            )
        );

    }

}
