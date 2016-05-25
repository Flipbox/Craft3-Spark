<?php

/**
 * @package    Spark
 * @author     Flipbox Factory <hello@flipboxfactory.com>
 * @copyright  2010-2016 Flipbox Digital Limited
 * @license    https://github.com/FlipboxFactory/Craft3-Spark/blob/master/LICENSE
 * @version    Release: 1.1.0
 * @link       https://github.com/FlipboxFactory/Craft3-Spark
 * @since      Class available since Release 1.1.0
 */

namespace Flipbox\Craft3\Spark\Services;

use Flipbox\Craft3\Spark\Helpers\ElementHelper;
use Flipbox\Craft3\Spark\Models\Interfaces\ModelInterface;
use Flipbox\Craft3\Spark\Elements\Interfaces\ElementWithIdInterface;
use Flipbox\Craft3\Spark\Records\Interfaces\RecordWithIdInterface;

abstract class ElementAccessorById extends ElementAccessor
{

    use Traits\ElementAccessorByIdTrait;

    /**
     * The element instance that this class interacts with
     */
    const ELEMENT_CLASS_INSTANCE = 'Flipbox\Craft3\Spark\Elements\Interfaces\ElementWithIdInterface';

    /**
     * The record instance that this class interacts with
     */
    const RECORD_CLASS_INSTANCE = 'Flipbox\Craft3\Spark\Records\Interfaces\RecordWithIdInterface';

    /**
     * @param $identifier
     * @param string $scenario
     * @return ElementWithIdInterface|null
     */
    public function find($identifier, $scenario = ElementHelper::SCENARIO_SAVE)
    {

        if (is_numeric($identifier)) {

            return $this->findById($identifier, $scenario);

        }

        return parent::find($identifier, $scenario);

    }

    /**
     * @param $identifier
     * @return ElementWithIdInterface|null
     */
    public function findCache($identifier)
    {

        if (is_numeric($identifier)) {

            return $this->findCacheById($identifier);

        }

        return parent::findCache($identifier);

    }

    /**
     * @param ModelInterface $model
     * @return $this
     */
    public function addToCache(ModelInterface $model)
    {

        if ($model instanceof ElementWithIdInterface) {

            $this->cacheById($model);

        }

        return parent::addToCache($model);

    }

    /**
     * @param ModelInterface $model
     * @param bool $mirrorScenario
     * @return RecordWithIdInterface|static
     */
    public function toRecord(ModelInterface $model, $mirrorScenario = true)
    {

        // Get existing record
        if ($model instanceof ElementWithIdInterface) {

            if ($record = $this->getRecord($model->getId())) {

                // Populate the record attributes
                $this->transferToRecord($model, $record, $mirrorScenario);

                return $record;

            }

        }

        return parent::toRecord($model, $mirrorScenario);

    }

}
