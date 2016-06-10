<?php

/**
 * @package    Spark
 * @author     Flipbox Factory <hello@flipboxfactory.com>
 * @copyright  2010-2016 Flipbox Digital Limited
 * @license    https://github.com/FlipboxFactory/Craft3-Spark/blob/master/LICENSE
 * @link       https://github.com/FlipboxFactory/Craft3-Spark
 * @since      Class available since Release 1.2.0
 */

namespace Flipbox\Craft3\Spark\Services\Traits;

use craft\app\base\ElementInterface;
use craft\app\records\Element as ElementRecord;
use Flipbox\Craft3\Spark\Exceptions\ElementNotFoundException;
use Flipbox\Craft3\Spark\Helpers\ElementHelper;
use Flipbox\Craft3\Spark\Helpers\RecordHelper;

trait ElementAccessorByStringTrait
{

    /**
     * @var ElementInterface[]
     */
    protected $_cacheByString = [];

    /*******************************************
     * ABSTRACTS
     *******************************************/

    /**
     * @param $condition
     * @param string $scenario
     * @return ElementInterface|null
     */
    public abstract function findRecord($condition, $scenario = RecordHelper::SCENARIO_SAVE);

    /**
     * @param $criteria
     * @param string $scenario
     * @return ElementInterface
     */
    abstract public function getByQuery($criteria, $scenario = ElementHelper::SCENARIO_SAVE);

    /**
     * @param $string
     * @return ElementRecord|null
     */
    abstract protected function findRecordByString($string);

    /**
     * @param $string
     * @return ElementInterface|null
     */
    abstract protected function internalFreshFindByString($string);

    /**
     * @param ElementInterface $element
     * @return ElementInterface|null
     */
    abstract protected function getStringValue(ElementInterface $element);

    /*******************************************
     * FRESH FIND
     *******************************************/

    /**
     * @param $string
     * @param string $scenario
     * @return ElementInterface|null
     */
    public function freshFindByString($string, $scenario = ElementHelper::SCENARIO_SAVE)
    {

        if ($element = $this->internalFreshFindByString($string)) {

            if ($scenario) {

                // Set scenario
                $element->setScenario($scenario);

            }

        }

        return $element;

    }

    /**
     * @param $string
     * @param string $scenario
     * @return ElementInterface
     * @throws ElementNotFoundException
     */
    public function freshGetByString($string, $scenario = ElementHelper::SCENARIO_SAVE)
    {

        if (!$element = $this->freshFindByString($string, $scenario)) {

            $this->notFoundByStringException($string);

        }

        return $element;

    }


    /*******************************************
     * FIND
     *******************************************/

    /**
     * @param $string
     * @param string $scenario
     * @return ElementInterface|null
     */
    public function findByString($string, $scenario = ElementHelper::SCENARIO_SAVE)
    {

        // Check cache
        if (!$element = $this->findCacheByString($string)) {

            // Find new element
            if ($element = $this->freshFindByString($string, $scenario)) {

                // Cache it
                $this->cacheByString($element);

            } else {

                // Cache nothing
                $this->_cacheByString[$string] = $element;

            }

        }

        return $element;

    }


    /*******************************************
     * GET
     *******************************************/

    /**
     * @param $string
     * @param string $scenario
     * @return ElementInterface
     * @throws ElementNotFoundException
     */
    public function getByString($string, $scenario = ElementHelper::SCENARIO_SAVE)
    {

        // Find by Handle
        if (!$element = $this->findByString($string, $scenario)) {

            $this->notFoundByStringException($string);

        }

        return $element;

    }


    /*******************************************
     * CACHE
     *******************************************/

    /**
     * Find an existing cache by Handle
     *
     * @param $string
     * @return null
     */
    public function findCacheByString($string)
    {

        // Check if already in cache
        if ($this->isCachedByString($string)) {

            return $this->_cacheByString[$string];

        }

        return null;

    }

    /**
     * Identify whether in cached by Handle
     *
     * @param $string
     * @return bool
     */
    protected function isCachedByString($string)
    {
        return array_key_exists($string, $this->_cacheByString);
    }

    /**
     * @param ElementInterface $element
     * @return $this
     */
    protected function cacheByString(ElementInterface $element)
    {

        $stringValue = $this->getStringValue($element);

        // Check if already in cache
        if ($stringValue && !$this->isCachedByString($stringValue)) {

            // Cache it
            $this->_cacheByString[$stringValue] = $element;

        }

        return $this;

    }

    /*******************************************
     * EXCEPTIONS
     *******************************************/

    /**
     * @param null $string
     * @throws ElementNotFoundException
     */
    protected function notFoundByStringException($string = null)
    {

        throw new ElementNotFoundException(
            sprintf(
                'Element does not exist with the string "%s".',
                (string)$string
            )
        );

    }

}
