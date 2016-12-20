<?php

/**
 * @package    Spark
 * @author     Flipbox Factory <hello@flipboxfactory.com>
 * @copyright  2010-2016 Flipbox Digital Limited
 * @license    https://github.com/FlipboxFactory/Craft3-Spark/blob/master/LICENSE
 * @link       https://github.com/FlipboxFactory/Craft3-Spark
 * @since      Class available since Release 1.1.0
 */

namespace Flipbox\Craft3\Spark\Services;

use craft\app\base\ElementInterface;
use craft\app\db\ActiveRecord;
use craft\app\elements\db\ElementQueryInterface;
use Flipbox\Craft3\Spark\Exceptions\InvalidElementException;
use Flipbox\Craft3\Spark\Exceptions\InvalidRecordException;
use Flipbox\Craft3\Spark\Helpers\ArrayHelper;
use Flipbox\Craft3\Spark\Helpers\ElementHelper;
use Flipbox\Craft3\Spark\Helpers\JsonHelper;

abstract class ElementAccessor extends RecordAccessor
{

    use Traits\ElementAccessorByIdTrait;

    /**
     * @var ElementInterface[] indexed by Id
     */
    protected $_cacheAll;

    /**
     * The element instance that this class interacts with
     */
    const ELEMENT_CLASS_INSTANCE = 'Flipbox\Craft3\Spark\Elements\ElementInterface';

    /**
     * The default scenario
     */
    const DEFAULT_SCENARIO = ElementHelper::SCENARIO_SAVE;

    /**
     * @var string the associated element class
     */
    public $element;

    /**
     * @throws InvalidElementException
     * @throws InvalidRecordException
     */
    public function init()
    {

        parent::init();

        // Validate element class
        if (!is_subclass_of($this->element, static::ELEMENT_CLASS_INSTANCE)) {

            throw new InvalidElementException(
                sprintf(
                    "The class '%s' requires an element class that is an instance of '%s', '%s' was given",
                    get_class($this),
                    static::ELEMENT_CLASS_INSTANCE,
                    $this->element
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
     * @return ElementInterface
     */
    public function create($config = [], $scenario = self::DEFAULT_SCENARIO)
    {

        // Force Array
        $config = ArrayHelper::toArray($config, [], false);

        // Set the class the element should be
        $config['class'] = $this->element;

        // Create new model
        return ElementHelper::create($config, static::ELEMENT_CLASS_INSTANCE, $scenario);

    }

    /*******************************************
     * FIND
     *******************************************/

    /**
     * @param null $indexBy
     * @param string $scenario
     * @return ElementInterface[]
     */
    public function findAll($indexBy = null, $scenario = self::DEFAULT_SCENARIO)
    {

        // Check cache
        if (is_null($this->_cacheAll)) {

            $this->_cacheAll = $this->findAllByCriteria([], null, $scenario);

        } else {

            if ($scenario) {

                /** @var ElementInterface $element */
                foreach ($this->_cacheAll as $element) {

                    // Set scenario
                    $element->setScenario($scenario);

                }

            }

        }

        return $indexBy ? ArrayHelper::index($this->_cacheAll, $indexBy) : $this->_cacheAll;

    }

    /**
     * @param $identifier
     * @param string $scenario
     * @return ElementInterface|null
     */
    public function find($identifier, $scenario = self::DEFAULT_SCENARIO)
    {

        if ($identifier instanceof ElementInterface) {

            $this->addToCache($identifier);

            if ($scenario) {

                $identifier->setScenario($scenario);

            }

            return $identifier;

        } elseif (is_numeric($identifier)) {

            return $this->findById($identifier, $scenario);

        } elseif (is_array($identifier)) {

            return $this->findByQuery($identifier, $scenario);

        }

        return null;

    }

    /**
     * @param $criteria
     * @param null $indexBy
     * @param string $scenario
     * @return ElementInterface[]
     */
    public function findAllByCriteria($criteria, $indexBy = null, $scenario = self::DEFAULT_SCENARIO)
    {

        /** @var ElementInterface[] $elements */
        $elements = $this->getQuery($criteria)->all();

        if ($scenario) {

            $returnElements = [];

            /** @var ElementInterface $element */
            foreach ($elements as $element) {

                // Set scenario
                $element->setScenario($scenario);

                $returnElements[] = $element;

            }

        } else {

            $returnElements = $elements;

        }

        return $indexBy ? ArrayHelper::index($returnElements, $indexBy) : $returnElements;

    }

    /**
     * @param $criteria
     * @param string $scenario
     * @return ElementInterface|null
     */
    public function findByCriteria($criteria, $scenario = self::DEFAULT_SCENARIO)
    {

        /** @var ElementInterface $element */
        if ($element = $this->getQuery($criteria)->one()) {

            if ($scenario) {

                // Set scenario
                $element->setScenario($scenario);

            }

            return $element;

        }

        return null;

    }


    /*******************************************
     * GET
     *******************************************/

    /**
     * @param null $indexBy
     * @param string $scenario
     * @return ElementInterface[]
     * @throws InvalidElementException
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
     * @return ElementInterface
     * @throws InvalidElementException
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
     * @param null $indexBy
     * @param string $scenario
     * @return ElementInterface[]
     * @throws InvalidElementException
     */
    public function getAllByCriteria($criteria, $indexBy = null, $scenario = self::DEFAULT_SCENARIO)
    {

        if (!$models = $this->findAllByCriteria($criteria, $indexBy, $scenario)) {

            $this->notFoundByCriteriaException($criteria);

        }

        return $models;

    }

    /**
     * @param $criteria
     * @param string $scenario
     * @return ElementInterface
     * @throws InvalidElementException
     */
    public function getByCriteria($criteria, $scenario = self::DEFAULT_SCENARIO)
    {

        if (!$model = $this->findByCriteria($criteria, $scenario)) {

            $this->notFoundByCriteriaException($criteria);

        }

        return $model;

    }


    /*******************************************
     * QUERY
     *******************************************/

    /**
     * Get query
     *
     * @param $criteria
     * @return ElementQueryInterface
     */
    public function getQuery($criteria = [])
    {

        /** @var ElementInterface $elementClass */
        $elementClass = $this->element;

        return $elementClass::find()->configure($criteria);

    }

    /**
     * Alias to findByCriteria
     *
     * @param $criteria
     * @param string $scenario
     * @return ElementInterface|null
     */
    public function findByQuery($criteria, $scenario = self::DEFAULT_SCENARIO)
    {
        return $this->findByCriteria($criteria, $scenario);
    }

    /**
     * Alias to findAllByCriteria
     *
     * @param $criteria
     * @param null $indexBy
     * @param string $scenario
     * @return ElementInterface[]
     */
    public function findAllByQuery($criteria, $indexBy = null, $scenario = self::DEFAULT_SCENARIO)
    {
        return $this->findAllByCriteria($criteria, $indexBy, $scenario);
    }

    /**
     * Alias to getByCriteria
     *
     * @param $criteria
     * @param string $scenario
     * @return ElementInterface
     */
    public function getByQuery($criteria, $scenario = self::DEFAULT_SCENARIO)
    {
        return $this->getByCriteria($criteria, $scenario);
    }

    /**
     *
     * Alias to getAllByCriteria
     *
     * @param $criteria
     * @param null $indexBy
     * @param string $scenario
     * @return ElementInterface[]
     */
    public function getAllByQuery($criteria, $indexBy = null, $scenario = self::DEFAULT_SCENARIO)
    {
        return $this->getAllByCriteria($criteria, $indexBy, $scenario);
    }

    /*******************************************
     * ELEMENT -to- RECORD
     *******************************************/

    /**
     * @param ElementInterface $element
     * @param ActiveRecord $record
     * @param bool $mirrorScenario
     */
    public function transferToRecord(ElementInterface $element, ActiveRecord $record, $mirrorScenario = true)
    {

        if ($mirrorScenario === true) {

            // Mirror scenarios
            $record->setScenario($element->getScenario());

        }

        // Transfer attributes
        $record->setAttributes($element->toArray());

    }

    /**
     * @param ElementInterface $element
     * @param bool $mirrorScenario
     * @return ActiveRecord|static
     */
    public function toRecord(ElementInterface $element, $mirrorScenario = true)
    {

        $id = $element->id;

        // Find a record
        if ($id && (!$record = $this->findRecord($id))) {

            // Create new record
            $record = $this->createRecord();

        }

        // Populate the record attributes
        $this->transferToRecord($element, $record, $mirrorScenario);

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

        if (is_numeric($identifier)) {

            return $this->findCacheById($identifier);

        }

        return null;

    }

    /**
     * @param ElementInterface $element
     * @return $this
     */
    public function addToCache(ElementInterface $element)
    {

        $this->cacheById($element);

        return $this;

    }

    /*******************************************
     * EXCEPTIONS
     *******************************************/

    /**
     * @throws InvalidElementException
     */
    protected function notFoundException()
    {

        throw new InvalidElementException(
            sprintf(
                "Element does not exist."
            )
        );

    }

    /**
     * @param null $criteria
     * @throws InvalidElementException
     */
    protected function notFoundByCriteriaException($criteria = null)
    {

        throw new InvalidElementException(
            sprintf(
                'Element does not exist with the criteria "%s".',
                (string)JsonHelper::encode($criteria)
            )
        );

    }

}
