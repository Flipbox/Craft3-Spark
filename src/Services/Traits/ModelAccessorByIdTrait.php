<?php

/**
 * @package    Spark
 * @author     Flipbox Factory <hello@flipboxfactory.com>
 * @copyright  2010-2016 Flipbox Digital Limited
 * @license    https://github.com/FlipboxFactory/Craft3-Spark/blob/master/LICENSE
 * @link       https://github.com/FlipboxFactory/Craft3-Spark
 * @since      Class available since Release 1.0.0
 */

namespace Flipbox\Craft3\Spark\Services\Traits;

use Flipbox\Craft3\Spark\Exceptions\ModelNotFoundException;
use Flipbox\Craft3\Spark\Helpers\ModelHelper;
use Flipbox\Craft3\Spark\Helpers\RecordHelper;
use Flipbox\Craft3\Spark\Models\Interfaces\ModelInterface;
use Flipbox\Craft3\Spark\Models\Interfaces\ModelWithIdInterface;
use Flipbox\Craft3\Spark\Records\Interfaces\RecordInterface;
use Flipbox\Craft3\Spark\Records\Interfaces\RecordWithIdInterface;

trait ModelAccessorByIdTrait
{

    /**
     * @var ModelWithIdInterface[]
     */
    protected $_cacheById = [];

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
     * @param string $scenario
     * @return ModelInterface
     */
    public abstract function findByRecord(RecordInterface $record, $scenario = ModelHelper::SCENARIO_SAVE);

    /**
     * @param array $config
     * @param string $scenario
     * @return ModelWithIdInterface
     */
    public abstract function create($config = [], $scenario = ModelHelper::SCENARIO_SAVE);


    /*******************************************
     * RECORD
     *******************************************/

    /**
     * @param $id
     * @return RecordWithIdInterface|null
     */
    protected function findRecordById($id)
    {

        return $this->findRecord([
            'id' => $id
        ]);

    }

    /*******************************************
     * FRESH FIND
     *******************************************/

    /**
     * @param $id
     * @param string $scenario
     * @return ModelWithIdInterface|null
     */
    public function freshFindById($id, $scenario = ModelHelper::SCENARIO_SAVE)
    {

        // Find record in db
        if ($record = $this->findRecordById($id)) {

            // Create
            return $this->create($record, $scenario);

        }

        return null;

    }

    /**
     * @param $id
     * @param string $scenario
     * @return ModelWithIdInterface
     * @throws ModelNotFoundException
     */
    public function freshGetByString($id, $scenario = ModelHelper::SCENARIO_SAVE)
    {

        if (!$model = $this->freshFindById($id, $scenario)) {

            $this->notFoundByIdException($id);

        }

        return $model;

    }


    /*******************************************
     * FIND
     *******************************************/

    /**
     * @param $id
     * @param string $scenario
     * @return ModelWithIdInterface|null
     */
    public function findById($id, $scenario = ModelHelper::SCENARIO_SAVE)
    {

        // Check cache
        if (!$model = $this->findCacheById($id)) {

            // Find record in db
            if ($record = $this->findRecordById($id)) {

                // Perhaps in cache
                $model = $this->findByRecord($record, $scenario);

            } else {

                $this->_cacheById[$id] = null;

                return null;

            }

        }

        return $model;

    }


    /*******************************************
     * GET
     *******************************************/

    /**
     * @param $id
     * @param string $scenario
     * @return ModelWithIdInterface
     * @throws ModelNotFoundException
     */
    public function getById($id, $scenario = ModelHelper::SCENARIO_SAVE)
    {

        // Find by ID
        if (!$model = $this->findById($id, $scenario)) {

            $this->notFoundByIdException($id);

        }

        return $model;

    }


    /*******************************************
     * CACHE
     *******************************************/

    /**
     * Find an existing cache by ID
     *
     * @param $id
     * @return null
     */
    public function findCacheById($id)
    {

        // Check if already in addToCache
        if ($this->isCachedById($id)) {

            return $this->_cacheById[$id];

        }

        return null;

    }

    /**
     * Identify whether in cache by ID
     *
     * @param $id
     * @return bool
     */
    protected function isCachedById($id)
    {
        return array_key_exists($id, $this->_cacheById);
    }

    /**
     * @param ModelWithIdInterface $model
     * @return $this
     */
    protected function cacheById(ModelWithIdInterface $model)
    {

        // Check if already in cache
        if (!$this->isCachedById($model->getId())) {

            // Cache it
            $this->_cacheById[$model->getId()] = $model;

        }

        return $this;

    }

    /**
     * @param RecordInterface $record
     * @return null
     */
    public function findCacheByRecord(RecordInterface $record)
    {

        if ($record instanceof RecordWithIdInterface) {

            // Check if already in addToCache by id
            if (!$this->isCachedById($record->getId())) {

                return $this->findCacheById($record->getId());

            }

        }

        return null;

    }

    /*******************************************
     * EXCEPTIONS
     *******************************************/

    /**
     * @param null $id
     * @throws ModelNotFoundException
     */
    protected function notFoundByIdException($id = null)
    {

        throw new ModelNotFoundException(
            sprintf(
                'Model does not exist with the id "%s".',
                (string)$id
            )
        );

    }

}
