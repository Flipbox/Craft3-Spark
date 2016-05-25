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

use Flipbox\Craft3\Spark\Elements\Interfaces\ElementWithHandleInterface;
use Flipbox\Craft3\Spark\Helpers\ElementHelper;
use Flipbox\Craft3\Spark\Models\Interfaces\ModelInterface;
use Flipbox\Craft3\Spark\Records\Interfaces\RecordWithHandleInterface;

abstract class ElementAccessorByHandle extends ElementAccessor
{

    use Traits\ElementAccessorByHandleTrait;

    /**
     * The element instance that this class interacts with
     */
    const ELEMENT_CLASS_INSTANCE = 'Flipbox\Craft3\Spark\Elements\Interfaces\ElementWithHandleInterface';

    /**
     * The record instance that this class interacts with
     */
    const RECORD_CLASS_INSTANCE = 'Flipbox\Craft3\Spark\Records\Interfaces\RecordWithHandleInterface';

    /**
     * @param $identifier
     * @param string $scenario
     * @return ElementWithHandleInterface|null
     */
    public function find($identifier, $scenario = ElementHelper::SCENARIO_SAVE)
    {

        if (is_string($identifier)) {

            return $this->findByHandle($identifier, $scenario);

        }

        return parent::find($identifier, $scenario);

    }

    /**
     * @param $identifier
     * @return ElementWithHandleInterface|null
     */
    public function findCache($identifier)
    {

        if (is_string($identifier)) {

            return $this->findCacheByHandle($identifier);

        }

        return parent::findCache($identifier);

    }

    /**
     * @param ModelInterface $model
     * @return $this
     */
    public function addToCache(ModelInterface $model)
    {

        if ($model instanceof ElementWithHandleInterface) {

            $this->cacheByHandle($model);

        }

        return parent::addToCache($model);

    }

    /**
     * @param ModelInterface $model
     * @param bool $mirrorScenario
     * @return RecordWithHandleInterface
     */
    public function toRecord(ModelInterface $model, $mirrorScenario = true)
    {

        // Get existing record
        if ($model instanceof ElementWithHandleInterface) {

            if ($record = $this->getRecord([
                'handle' => $model->getHandle()
            ])
            ) {

                // Populate the record attributes
                $this->transferToRecord($model, $record, $mirrorScenario);

                return $record;

            }

        }

        return parent::toRecord($model, $mirrorScenario);

    }

}
