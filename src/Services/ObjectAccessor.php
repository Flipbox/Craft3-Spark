<?php

/**
 * @package    Spark
 * @author     Flipbox Factory <hello@flipboxfactory.com>
 * @copyright  2010-2016 Flipbox Digital Limited
 * @license    https://github.com/FlipboxFactory/Craft3-Spark/blob/master/LICENSE
 * @link       https://github.com/FlipboxFactory/Craft3-Spark
 * @since      Class available since Release 1.0.0
 */

namespace Flipbox\Craft3\Spark\Services;

use Flipbox\Craft3\Spark\Exceptions\InvalidObjectException;
use Flipbox\Craft3\Spark\Exceptions\InvalidRecordException;
use Flipbox\Craft3\Spark\Helpers\ArrayHelper;
use Flipbox\Craft3\Spark\Helpers\JsonHelper;
use Flipbox\Craft3\Spark\Helpers\ObjectHelper;
use Flipbox\Craft3\Spark\Objects\Interfaces\ObjectInterface;
use Flipbox\Craft3\Spark\Records\Interfaces\RecordInterface;

abstract class ObjectAccessor extends RecordAccessor
{

    /**
     * @var ObjectInterface[] indexed by Id
     */
    protected $_cacheAll;

    /**
     * The object instance that this class interacts with
     */
    const OBJECT_CLASS_INSTANCE = 'Flipbox\Craft3\Spark\Objects\Interfaces\ObjectInterface';

    /**
     * @var string the associated object class
     */
    public $object;


    /**
     * @throws InvalidObjectException
     * @throws InvalidRecordException
     */
    public function init()
    {

        parent::init();

        // todo - support multiple instances
        if ($this->object && !is_subclass_of($this->object, static::OBJECT_CLASS_INSTANCE)) {

            throw new InvalidObjectException(
                sprintf(
                    "The class '%s' requires an object class that is an instance of '%s', '%s' was given",
                    get_class($this),
                    static::OBJECT_CLASS_INSTANCE,
                    $this->object
                )
            );

        }

    }

    /*******************************************
     * CREATE
     *******************************************/

    /**
     * @param array $config
     * @return ObjectInterface
     */
    public function create($config = [])
    {

        // Array
        if (!is_array($config)) {
            $config = ArrayHelper::toArray($config, [], false);
        }

        // Auto-set the class
        if ($this->object) {
            $config['class'] = $this->object;
        }

        // Create new object
        $object = ObjectHelper::create($config, static::OBJECT_CLASS_INSTANCE);

        return $object;

    }

    /*******************************************
     * FIND
     *******************************************/

    /**
     * @param null $indexBy
     * @return ObjectInterface[]
     */
    public function findAll($indexBy = null)
    {

        // Check addToCache
        if (is_null($this->_cacheAll)) {

            $this->_cacheAll = [];

            // Find record in db
            if ($records = $this->findAllRecords()) {

                foreach ($records as $record) {

                    $this->_cacheAll[] = $this->findByRecord($record);

                }

            }

        }

        return $indexBy ? ArrayHelper::index($this->_cacheAll, $indexBy) : $this->_cacheAll;

    }

    /**
     * @param $identifier
     * @return ObjectInterface|null
     */
    public function find($identifier)
    {

        if ($identifier instanceof ObjectInterface) {

            $this->addToCache($identifier);

            return $identifier;

        } elseif ($identifier instanceof RecordInterface) {

            return $this->findByRecord($identifier);

        }

        return null;

    }

    /**
     * @param $criteria
     * @param null $indexBy
     * @return ObjectInterface[]
     */
    public function findAllByCriteria($criteria, $indexBy = null)
    {

        $objects = array();

        // Find record in db
        if ($records = $this->findAllRecords($criteria)
        ) {

            foreach ($records as $record) {

                $objects[] = $this->findByRecord($record);

            }

        }

        return $indexBy ? ArrayHelper::index($objects, $indexBy) : $objects;

    }

    /**
     * @param $criteria
     * @return ObjectInterface|null
     */
    public function findByCriteria($criteria)
    {
        // Find record in db
        if ($record = $this->findRecord($criteria)
        ) {

            return $this->findByRecord($record);

        }

        return null;

    }

    /**
     * @param RecordInterface $record
     * @return ObjectInterface|null
     */
    public function findByRecord(RecordInterface $record)
    {

        // Check addToCache
        if (!$object = $this->findCacheByRecord($record)) {

            // Create object
            $object = $this->create($record);

            // Cache it
            $this->addToCache($object);

        }

        return $object;

    }

    /*******************************************
     * GET
     *******************************************/

    /**
     * @param null $indexBy
     * @return ObjectInterface[]
     * @throws InvalidObjectException
     */
    public function getAll($indexBy = null)
    {

        if (!$objects = $this->findAll($indexBy)) {

            $this->notFoundException();

        }

        return $objects;

    }

    /**
     * @param $identifier
     * @return ObjectInterface
     * @throws InvalidObjectException
     */
    public function get($identifier)
    {

        // Find object by ID
        if (!$object = $this->find($identifier)) {

            $this->notFoundException();

        }

        return $object;

    }

    /**
     * @param $criteria
     * @return ObjectInterface[]
     * @throws InvalidObjectException
     */
    public function getAllByCriteria($criteria)
    {

        if (!$objects = $this->findAllByCriteria($criteria)) {

            $this->notFoundByCriteriaException($criteria);

        }

        return $objects;

    }

    /**
     * @param $criteria
     * @return ObjectInterface
     * @throws InvalidObjectException
     */
    public function getByCriteria($criteria)
    {

        if (!$object = $this->findByCriteria($criteria)) {

            $this->notFoundByCriteriaException($criteria);

        }

        return $object;

    }

    /**
     * @param RecordInterface $record
     * @return ObjectInterface
     * @throws InvalidObjectException
     */
    public function getByRecord(RecordInterface $record)
    {
        return $this->findByRecord($record);
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

        if ($identifier instanceof RecordInterface) {

            return $this->findCacheByRecord($identifier);

        }

        return null;

    }

    /**
     * @param RecordInterface $record
     * @return null
     */
    public function findCacheByRecord(RecordInterface $record)
    {
        return null;
    }

    /**
     * @param ObjectInterface $object
     * @return $this
     */
    public function addToCache(ObjectInterface $object)
    {
        return $this;
    }


    /*******************************************
     * EXCEPTIONS
     *******************************************/

    /**
     * @throws InvalidObjectException
     */
    protected function notFoundException()
    {

        throw new InvalidObjectException(
            sprintf(
                "Object does not exist."
            )
        );

    }

    /**
     * @param null $criteria
     * @throws InvalidObjectException
     */
    protected function notFoundByCriteriaException($criteria = null)
    {

        throw new InvalidObjectException(
            sprintf(
                'Object does not exist with the criteria "%s".',
                (string)JsonHelper::encode($criteria)
            )
        );

    }

}
