<?php

/**
 * @package    Spark
 * @author     Flipbox Factory <hello@flipboxfactory.com>
 * @copyright  2010-2016 Flipbox Digital Limited
 * @license    https://github.com/FlipboxFactory/Craft3-Spark/blob/master/LICENSE
 * @version    Release: 1.1.0
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

trait ObjectAccessorByIdTrait
{

    /**
     * @var array of all objects indexed by Id
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
     * @param $id
     * @return ModelWithIdInterface|null
     */
    public function freshFindById($id)
    {

        // Find record in db
        if ($record = $this->findRecord($id)) {

            // New object
            $object = $this->create($record);

            // Cache it
            $this->cacheById($id, $object);

            return $object;

        }

        return null;

    }

    /**
     * @param $id
     * @return ModelWithIdInterface|null
     * @throws ObjectNotFoundException
     */
    public function freshGetById($id)
    {

        if (!$object = $this->freshFindById($id)) {

            $this->notFoundByIdException($id);

        }

        return $object;

    }


    /*******************************************
     * FIND
     *******************************************/

    /**
     * @param $id
     * @return ModelWithIdInterface|null
     */
    public function findById($id)
    {

        // Check addToCache
        if (!$object = $this->findCacheById($id)) {

            // Find record in db
            if ($record = $this->findRecord([
                'id' => $id
            ])
            ) {

                // Perhaps in cache
                $object = $this->findByRecord($record);

            } else {

                // Cache null (for subsequent requests)
                $this->cacheById($id);

                $object = null;

            }

        }

        return $object;

    }


    /*******************************************
     * GET
     *******************************************/

    /**
     * @param $id
     * @return ModelWithIdInterface|null
     * @throws ObjectNotFoundException
     */
    public function getById($id)
    {

        // Find object by Id
        if (!$object = $this->findById($id)) {

            $this->notFoundByIdException($id);

        }

        return $object;

    }


    /*******************************************
     * CACHE
     *******************************************/

    /**
     * Find an existing cached object by Id
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
     * Identify whether an object is cached by Id
     *
     * @param $id
     * @return bool
     */
    protected function isCachedById($id)
    {
        return array_key_exists($id, $this->_cacheById);
    }

    /**
     * @param $id
     * @param ObjectInterface $object
     * @return $this
     */
    protected function cacheById($id, ObjectInterface $object = null)
    {

        // Check if already in cache
        if (!$this->isCachedById($id)) {

            // Cache it
            $this->_cacheById[$id] = $object;

        }

        return $this;

    }

    /*******************************************
     * EXCEPTIONS
     *******************************************/

    /**
     * @param null $id
     * @throws ObjectNotFoundException
     */
    protected function notFoundByIdException($id = null)
    {

        throw new ObjectNotFoundException(
            sprintf(
                'Object does not exist with the id "%s".',
                (string)$id
            )
        );

    }

}
