<?php

/**
 * @package    Spark
 * @author     Flipbox Factory <hello@flipboxfactory.com>
 * @copyright  2010-2016 Flipbox Digital Limited
 * @license    https://github.com/FlipboxFactory/Craft3-Spark/blob/master/LICENSE
 * @link       https://github.com/FlipboxFactory/Craft3-Spark
 * @since      Class available since Release 1.1.0
 */

namespace Flipbox\Craft3\Spark\Services\Traits;

use Flipbox\Craft3\Spark\Elements\Interfaces\ElementInterface;
use Flipbox\Craft3\Spark\Elements\Interfaces\ElementWithHandleInterface;
use Flipbox\Craft3\Spark\Exceptions\ElementNotFoundException;
use Flipbox\Craft3\Spark\Helpers\ElementHelper;
use Flipbox\Craft3\Spark\Helpers\RecordHelper;
use Flipbox\Craft3\Spark\Records\Interfaces\RecordWithHandleInterface;

trait ElementAccessorByHandleTrait
{

    /**
     * @var ElementWithHandleInterface[]
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
    public abstract function findRecord($condition, $scenario = RecordHelper::SCENARIO_SAVE);

    /**
     * @param $criteria
     * @param string $scenario
     * @return ElementInterface
     */
    abstract public function getByQuery($criteria, $scenario = ElementHelper::SCENARIO_SAVE);


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
     * @return ElementWithHandleInterface|null
     */
    public function freshFindByHandle($handle, $scenario = ElementHelper::SCENARIO_SAVE)
    {
        return $this->getByQuery(['handle' => $handle], $scenario);
    }

    /**
     * @param $handle
     * @param string $scenario
     * @return ElementWithHandleInterface
     * @throws ElementNotFoundException
     */
    public function freshGetByHandle($handle, $scenario = ElementHelper::SCENARIO_SAVE)
    {

        if (!$element = $this->freshFindByHandle($handle, $scenario)) {

            $this->notFoundByHandleException($handle);

        }

        return $element;

    }


    /*******************************************
     * FIND
     *******************************************/

    /**
     * @param $handle
     * @param string $scenario
     * @return ElementWithHandleInterface|null
     */
    public function findByHandle($handle, $scenario = ElementHelper::SCENARIO_SAVE)
    {

        // Check cache
        if (!$element = $this->findCacheByHandle($handle)) {

            $element = $this->freshFindByHandle($handle, $scenario);

            $this->cacheByHandle($element);

        }

        return $element;

    }


    /*******************************************
     * GET
     *******************************************/

    /**
     * @param $handle
     * @param string $scenario
     * @return ElementWithHandleInterface
     * @throws ElementNotFoundException
     */
    public function getByHandle($handle, $scenario = ElementHelper::SCENARIO_SAVE)
    {

        // Find by Handle
        if (!$element = $this->findByHandle($handle, $scenario)) {

            $this->notFoundByHandleException($handle);

        }

        return $element;

    }


    /*******************************************
     * CACHE
     *******************************************/

    /**
     * Find an existing cache by Handle
     *
     * @param $handle
     * @return null
     */
    public function findCacheByHandle($handle)
    {

        // Check if already in cache
        if ($this->isCachedByHandle($handle)) {

            return $this->_cacheByHandle[$handle];

        }

        return null;

    }

    /**
     * Identify whether in cached by Handle
     *
     * @param $handle
     * @return bool
     */
    protected function isCachedByHandle($handle)
    {
        return array_key_exists($handle, $this->_cacheByHandle);
    }

    /**
     * @param ElementWithHandleInterface $element
     * @return $this
     */
    protected function cacheByHandle(ElementWithHandleInterface $element)
    {

        // Check if already in cache
        if (!$this->isCachedByHandle($element->getHandle())) {

            // Cache it
            $this->_cacheByHandle[$element->getHandle()] = $element;

        }

        return $this;

    }

    /*******************************************
     * EXCEPTIONS
     *******************************************/

    /**
     * @param null $handle
     * @throws ElementNotFoundException
     */
    protected function notFoundByHandleException($handle = null)
    {

        throw new ElementNotFoundException(
            sprintf(
                'Element does not exist with the handle "%s".',
                (string)$handle
            )
        );

    }

}
