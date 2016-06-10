<?php

/**
 * @package    Spark
 * @author     Flipbox Factory <hello@flipboxfactory.com>
 * @copyright  2010-2016 Flipbox Digital Limited
 * @license    https://github.com/FlipboxFactory/Craft3-Spark/blob/master/LICENSE
 * @link       https://github.com/FlipboxFactory/Craft3-Spark
 * @since      Class available since Release 1.2.0
 */

namespace Flipbox\Craft3\Spark\Services;

use craft\app\base\ElementInterface;
use craft\app\db\ActiveRecord;
use Flipbox\Craft3\Spark\Helpers\ElementHelper;

abstract class ElementAccessorByString extends ElementAccessor
{

    use Traits\ElementAccessorByStringTrait;

    /**
     * @param $identifier
     * @param string $scenario
     * @return ElementInterface|null
     */
    public function find($identifier, $scenario = ElementHelper::SCENARIO_SAVE)
    {

        if (is_string($identifier)) {

            return $this->findByString($identifier, $scenario);

        }

        return parent::find($identifier);

    }

    /**
     * @param $identifier
     * @return null
     */
    public function findCache($identifier)
    {

        if (is_string($identifier)) {

            return $this->findCacheByString($identifier);

        }

        return parent::findCache($identifier);

    }

    /**
     * @param ElementInterface $element
     * @return $this
     */
    public function addToCache(ElementInterface $element)
    {

        // Cache by String
        $this->cacheByString($element);

        return parent::addToCache($element);

    }

    /**
     * @param ElementInterface $element
     * @param bool $mirrorScenario
     * @return ActiveRecord
     */
    public function toRecord(ElementInterface $element, $mirrorScenario = true)
    {

        // Find existing record
        if ($record = $this->findRecordByString($this->getStringValue($element))) {

            // Populate the record attributes
            $this->transferToRecord($element, $record, $mirrorScenario);

            return $record;

        }

        return parent::toRecord($element, $mirrorScenario);

    }

}
