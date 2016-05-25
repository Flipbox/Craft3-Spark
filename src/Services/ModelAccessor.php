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

use Flipbox\Craft3\Spark\Exceptions\InvalidModelException;
use Flipbox\Craft3\Spark\Exceptions\InvalidRecordException;
use Flipbox\Craft3\Spark\Helpers\ArrayHelper;
use Flipbox\Craft3\Spark\Helpers\JsonHelper;
use Flipbox\Craft3\Spark\Helpers\ModelHelper;
use Flipbox\Craft3\Spark\Models\Interfaces\ModelInterface;
use Flipbox\Craft3\Spark\Records\Interfaces\RecordInterface;

abstract class ModelAccessor extends RecordAccessor
{

    /**
     * @var ModelInterface[] indexed by Id
     */
    protected $_cacheAll;

    /**
     * The model instance that this class interacts with
     */
    const MODEL_CLASS_INSTANCE = 'Flipbox\Craft3\Spark\Models\Interfaces\ModelInterface';

    /**
     * The default scenario
     */
    const DEFAULT_SCENARIO = ModelHelper::SCENARIO_SAVE;

    /**
     * @var string the associated model class
     */
    public $model;

    /**
     * @throws InvalidModelException
     * @throws InvalidRecordException
     */
    public function init()
    {

        parent::init();

        // todo - support multiple instances
        if (!is_subclass_of($this->model, static::MODEL_CLASS_INSTANCE)) {

            throw new InvalidModelException(
                sprintf(
                    "The class '%s' requires a model class that is an instance of '%s', '%s' was given",
                    get_class($this),
                    static::MODEL_CLASS_INSTANCE,
                    $this->model
                )
            );

        }

    }

    /*******************************************
     * CREATE
     *******************************************/

    /**
     * @param array $config
     * @param string $scenario
     * @return ModelInterface
     */
    public function create($config = [], $scenario = self::DEFAULT_SCENARIO)
    {

        // Force Array
        $config = ArrayHelper::toArray($config, [], false);

        // Set the class the model should be
        $config['class'] = $this->model;

        // Create new model
        $model = ModelHelper::create($config, static::MODEL_CLASS_INSTANCE, $scenario);

        return $model;

    }

    /*******************************************
     * FIND
     *******************************************/

    /**
     * @param null $indexBy
     * @param string $scenario
     * @return ModelInterface[]
     */
    public function findAll($indexBy = null, $scenario = self::DEFAULT_SCENARIO)
    {

        // Check addToCache
        if (is_null($this->_cacheAll)) {

            $this->_cacheAll = [];

            // Find record in db
            if ($records = $this->findAllRecords()) {

                foreach ($records as $record) {

                    $this->_cacheAll[] = $this->findByRecord($record, $scenario);

                }

            }

        }

        return $indexBy ? ArrayHelper::index($this->_cacheAll, $indexBy) : $this->_cacheAll;

    }

    /**
     * @param $identifier
     * @param string $scenario
     * @return ModelInterface|null
     */
    public function find($identifier, $scenario = self::DEFAULT_SCENARIO)
    {

        if ($identifier instanceof ModelInterface) {

            $this->addToCache($identifier);

            if ($scenario) {

                $identifier->setScenario($scenario);

            }

            return $identifier;

        } elseif ($identifier instanceof RecordInterface) {

            return $this->findByRecord($identifier, $scenario);

        }

        return null;

    }

    /**
     * @param $criteria
     * @param null $indexBy
     * @param string $scenario
     * @return ModelInterface[]
     */
    public function findAllByCriteria($criteria, $indexBy = null, $scenario = self::DEFAULT_SCENARIO)
    {

        $models = array();

        // Find record in db
        if ($records = $this->findAllRecords($criteria)
        ) {

            foreach ($records as $record) {

                $models[] = $this->findByRecord($record, $scenario);

            }

        }

        return $indexBy ? ArrayHelper::index($models, $indexBy) : $models;

    }

    /**
     * @param $criteria
     * @param string $scenario
     * @return ModelInterface|null
     */
    public function findByCriteria($criteria, $scenario = self::DEFAULT_SCENARIO)
    {
        // Find record in db
        if ($record = $this->findRecord($criteria)
        ) {

            return $this->findByRecord($record, $scenario);

        }

        return null;

    }

    /**
     * @param RecordInterface $record
     * @param string $scenario
     * @return ModelInterface|null
     */
    public function findByRecord(RecordInterface $record, $scenario = self::DEFAULT_SCENARIO)
    {

        // Check addToCache
        if (!$model = $this->findCacheByRecord($record)) {

            // Create model
            $model = $this->create($record, $scenario);

            // Cache it
            $this->addToCache($model);

        }

        return $model;

    }

    /*******************************************
     * GET
     *******************************************/

    /**
     * @param null $indexBy
     * @param string $scenario
     * @return ModelInterface[]
     * @throws InvalidModelException
     */
    public function getAll($indexBy = null, $scenario = self::DEFAULT_SCENARIO)
    {

        if (!$models = $this->findAll($indexBy, $scenario)) {

            $this->notFoundException();

        }

        return $models;

    }

    /**
     * @param $identifier
     * @param string $scenario
     * @return ModelInterface
     * @throws InvalidModelException
     */
    public function get($identifier, $scenario = self::DEFAULT_SCENARIO)
    {

        // Find model by ID
        if (!$model = $this->find($identifier, $scenario)) {

            $this->notFoundException();

        }

        return $model;

    }

    /**
     * @param $criteria
     * @param string $scenario
     * @return ModelInterface[]
     * @throws InvalidModelException
     */
    public function getAllByCriteria($criteria, $scenario = self::DEFAULT_SCENARIO)
    {

        if (!$models = $this->findAllByCriteria($criteria, $scenario)) {

            $this->notFoundByCriteriaException($criteria);

        }

        return $models;

    }

    /**
     * @param $criteria
     * @param string $scenario
     * @return ModelInterface
     * @throws InvalidModelException
     */
    public function getByCriteria($criteria, $scenario = self::DEFAULT_SCENARIO)
    {

        if (!$model = $this->findByCriteria($criteria, $scenario)) {

            $this->notFoundByCriteriaException($criteria);

        }

        return $model;

    }

    /**
     * @param RecordInterface $record
     * @param string $scenario
     * @return ModelInterface|null
     * @throws InvalidModelException
     */
    public function getByRecord(RecordInterface $record, $scenario = self::DEFAULT_SCENARIO)
    {
        return $this->findByRecord($record, $scenario);
    }

    /*******************************************
     * Model -to- Record
     *******************************************/

    /**
     * @param ModelInterface $model
     * @param RecordInterface $record
     * @param bool $mirrorScenario
     */
    public function transferToRecord(ModelInterface $model, RecordInterface $record, $mirrorScenario = true)
    {

        if ($mirrorScenario === true) {

            // Mirror scenarios
            $record->setScenario($model->getScenario());

        }

        // Transfer attributes
        $record->setAttributes($model->toArray());

    }

    /**
     * @param ModelInterface $model
     * @param bool $mirrorScenario
     * @return RecordInterface|static
     */
    public function toRecord(ModelInterface $model, $mirrorScenario = true)
    {

        $record = $this->createRecord();

        // Populate the record attributes
        $this->transferToRecord($model, $record, $mirrorScenario);

        return $record;

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
     * @param ModelInterface $model
     * @return $this
     */
    public function addToCache(ModelInterface $model)
    {
        return $this;
    }

    /*******************************************
     * EXCEPTIONS
     *******************************************/

    /**
     * @throws InvalidModelException
     */
    protected function notFoundException()
    {

        throw new InvalidModelException(
            sprintf(
                "Model does not exist."
            )
        );

    }

    /**
     * @param null $criteria
     * @throws InvalidModelException
     */
    protected function notFoundByCriteriaException($criteria = null)
    {

        throw new InvalidModelException(
            sprintf(
                'Model does not exist with the criteria "%s".',
                (string)JsonHelper::encode($criteria)
            )
        );

    }

}
