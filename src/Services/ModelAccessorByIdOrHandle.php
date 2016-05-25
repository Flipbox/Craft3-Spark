<?php

/**
 * @package    Spark
 * @author     Flipbox Factory <hello@flipboxfactory.com>
 * @copyright  2010-2016 Flipbox Digital Limited
 * @license    https://github.com/FlipboxFactory/Craft3-Spark/blob/master/LICENSE
 * @version    Release: 1.0.0
 * @link       https://github.com/FlipboxFactory/Craft3-Spark
 * @since      Class available since Release 1.0.0
 */

namespace Flipbox\Craft3\Spark\Services;

use Flipbox\Craft3\Spark\Helpers\ModelHelper;
use Flipbox\Craft3\Spark\Models\Interfaces\ModelInterface;
use Flipbox\Craft3\Spark\Models\Interfaces\ModelWithHandleInterface;
use Flipbox\Craft3\Spark\Models\Interfaces\ModelWithIdInterface;
use Flipbox\Craft3\Spark\Models\ModelWithIdAndHandle;
use Flipbox\Craft3\Spark\Records\Interfaces\RecordInterface;
use Flipbox\Craft3\Spark\Records\Interfaces\RecordWithHandleInterface;
use Flipbox\Craft3\Spark\Records\Interfaces\RecordWithIdInterface;

abstract class ModelAccessorByIdOrHandle extends ModelAccessor
{

    use Traits\ModelAccessorByIdTrait, Traits\ModelAccessorByHandleTrait {
        Traits\ModelAccessorByIdTrait::findCacheByRecord as _traitFindCacheByRecordWithId;
        Traits\ModelAccessorByHandleTrait::findCacheByRecord as _traitFindCacheByRecordWithHandle;
    }

    /**
     * @param $identifier
     * @param string $scenario
     * @return ModelWithIdAndHandle|null
     */
    public function find($identifier, $scenario = ModelHelper::SCENARIO_SAVE)
    {

        if (is_numeric($identifier)) {

            return $this->findById($identifier, $scenario);

        } elseif (is_string($identifier)) {

            return $this->findByHandle($identifier, $scenario);

        }

        return parent::find($identifier);

    }

    /**
     * @param $identifier
     * @return null
     */
    public function findCache($identifier)
    {

        if (is_numeric($identifier)) {

            return $this->findCacheById($identifier);

        } elseif (is_string($identifier)) {

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

        if ($model instanceof ModelWithIdInterface) {

            $this->cacheById($model);

        }

        if ($model instanceof ModelWithHandleInterface) {

            $this->cacheByHandle($model);

        }

        return parent::addToCache($model);

    }

    /**
     * @param ModelInterface $model
     * @param bool $mirrorScenario
     * @return RecordWithIdInterface|RecordWithHandleInterface
     */
    public function toRecord(ModelInterface $model, $mirrorScenario = true)
    {

        $record = null;

        // Get existing record
        if (is_null($record) && $model instanceof ModelWithIdInterface) {

            $record = $this->findRecordById($model->getId());

        }

        // Get existing record
        if (is_null($record) && $model instanceof ModelWithHandleInterface) {

            $record = $this->findRecordByHandle($model->getHandle());

        }

        if (!is_null($record)) {

            // Populate the record attributes
            $this->transferToRecord($model, $record, $mirrorScenario);

            return $record;

        }

        return parent::toRecord($model, $mirrorScenario);

    }

    /**
     * @param RecordInterface $record
     * @return ModelWithIdInterface|ModelWithHandleInterface|null
     */
    public function findCacheByRecord(RecordInterface $record)
    {

        if (!$model = $this->_traitFindCacheByRecordWithId($record)) {

            if (!$model = $this->_traitFindCacheByRecordWithHandle($record)) {

                return parent::findCacheByRecord($record);

            }

        }

        return $model;

    }

}
