<?php

/**
 * @package    Spark
 * @author     Flipbox Factory <hello@flipboxfactory.com>
 * @copyright  2010-2016 Flipbox Digital Limited
 * @license    https://github.com/FlipboxFactory/Craft3-Spark/blob/master/LICENSE
 * @version    Release: 1.0.0
 * @link       https://github.com/FlipboxFactory/Craft3-Spark
 * @since      Class available since Release 1.1.0
 */

namespace Flipbox\Craft3\Spark\Services\Traits;

use Flipbox\Craft3\Spark\Elements\Interfaces\ElementWithIdInterface;
use Flipbox\Craft3\Spark\Exceptions\ElementNotFoundException;
use Flipbox\Craft3\Spark\Helpers\ElementHelper;
use Flipbox\Craft3\Spark\Helpers\RecordHelper;
use Flipbox\Craft3\Spark\Records\Interfaces\RecordWithIdInterface;

trait ElementAccessorByIdTrait
{

    /**
     * @var ElementWithIdInterface[]
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
     * @return ElementWithIdInterface|null
     */
    public function freshFindById($id, $scenario = ElementHelper::SCENARIO_SAVE)
    {

        if ($element = \Craft::$app->getElements()->getElementById($id)) {

            if ($scenario) {

                $element->setScenario($scenario);

            }

        }

        return $element;

    }

    /**
     * @param $id
     * @param string $scenario
     * @return ElementWithIdInterface
     * @throws ElementNotFoundException
     */
    public function freshGetById($id, $scenario = ElementHelper::SCENARIO_SAVE)
    {

        if (!$element = $this->freshFindById($id, $scenario)) {

            $this->notFoundByIdException($id);

        }

        return $element;

    }


    /*******************************************
     * FIND
     *******************************************/

    /**
     * @param $id
     * @param string $scenario
     * @return ElementWithIdInterface|null
     */
    public function findById($id, $scenario = ElementHelper::SCENARIO_SAVE)
    {

        // Check cache
        if (!$element = $this->findCacheById($id)) {

            $element = $this->freshFindById($id, $scenario);

            $this->cacheById($element);

        }

        return $element;

    }


    /*******************************************
     * GET
     *******************************************/

    /**
     * @param $id
     * @param string $scenario
     * @return ElementWithIdInterface
     * @throws ElementNotFoundException
     */
    public function getById($id, $scenario = ElementHelper::SCENARIO_SAVE)
    {

        // Find by ID
        if (!$element = $this->findById($id, $scenario)) {

            $this->notFoundByIdException($id);

        }

        return $element;

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
     * Identify whether in cached by ID
     *
     * @param $id
     * @return bool
     */
    protected function isCachedById($id)
    {
        return array_key_exists($id, $this->_cacheById);
    }

    /**
     * @param ElementWithIdInterface $element
     * @return $this
     */
    protected function cacheById(ElementWithIdInterface $element)
    {

        // Check if already in cache
        if (!$this->isCachedById($element->getId())) {

            // Cache it
            $this->_cacheById[$element->getId()] = $element;

        }

        return $this;

    }

    /*******************************************
     * EXCEPTIONS
     *******************************************/

    /**
     * @param null $id
     * @throws ElementNotFoundException
     */
    protected function notFoundByIdException($id = null)
    {

        throw new ElementNotFoundException(
            sprintf(
                'Element does not exist with the id "%s".',
                (string)$id
            )
        );

    }

}
