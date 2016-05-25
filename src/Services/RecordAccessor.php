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
use Flipbox\Craft3\Spark\Exceptions\RecordNotFoundException;
use Flipbox\Craft3\Spark\Helpers\ArrayHelper;
use Flipbox\Craft3\Spark\Helpers\RecordHelper;
use Flipbox\Craft3\Spark\Records\Interfaces\RecordInterface;
use yii\base\Component as BaseComponent;

abstract class RecordAccessor extends BaseComponent
{

    /**
     * The record instance that this class interacts with
     */
    const RECORD_CLASS_INSTANCE = 'Flipbox\Craft3\Spark\Records\Interfaces\RecordInterface';

    /**
     * @var string the associated record class
     */
    public $record;

    /**
     * @throws InvalidRecordException
     */
    public function init()
    {

        parent::init();

        // todo - support multiple instances
        if (!is_subclass_of($this->record, static::RECORD_CLASS_INSTANCE)) {

            throw new InvalidRecordException(
                sprintf(
                    "The class '%s' requires a record class that is an instance of '%s', '%s' was given",
                    get_class($this),
                    static::RECORD_CLASS_INSTANCE,
                    $this->record
                )
            );

        }

    }

    /*******************************************
     * RECORD
     *******************************************/

    /**
     * @param array $properties
     * @param string $scenario
     * @return RecordInterface
     */
    protected function createRecord($properties = [], $scenario = RecordHelper::SCENARIO_INSERT)
    {

        // Create new model
        /** @var RecordInterface $record */
        $record = new $this->record();

        // Set scenario
        if ($scenario) {

            $record->setScenario($scenario);

        }

        // Do we need to set properties too
        if ($properties) {

            $record->setAttributes(ArrayHelper::toArray($properties));

        }

        return $record;

    }

    /**
     * @param $condition
     * @param string $scenario
     * @return RecordInterface|null
     */
    public function findRecord($condition, $scenario = RecordHelper::SCENARIO_SAVE)
    {

        if (!empty($condition)) {

            // Assume it's an id
            if (!is_array($condition)) {

                $condition = array(
                    'id' => $condition
                );

            }

            /** @var RecordInterface $recordClass */
            $recordClass = $this->record;

            /** @var RecordInterface $record */
            if ($record = $recordClass::findOne($condition)) {

                // Set scenario
                if ($scenario) {

                    $record->setScenario($scenario);

                }

            }

            return $record;

        }

        return null;

    }

    /**
     * @param $condition
     * @param string $scenario
     * @return RecordInterface
     * @throws RecordNotFoundException
     */
    public function getRecord($condition, $scenario = RecordHelper::SCENARIO_SAVE)
    {

        if (!$record = $this->findRecord($condition, $scenario)) {

            $this->notFoundRecordException();

        }

        return $record;

    }

    /**
     * @param array $condition
     * @param string $scenario
     * @return RecordInterface[]
     */
    public function findAllRecords($condition = [], $scenario = RecordHelper::SCENARIO_SAVE)
    {

        /** @var RecordInterface $recordClass */
        $recordClass = $this->record;

        if (empty($condition)) {

            $records = $recordClass::find()->all();

        } else {

            // Assume it's an id
            if (!is_array($condition)) {

                $condition = array(
                    'id' => $condition
                );

            }

            $records = $recordClass::findAll($condition);

        }

        // Set scenario
        if ($scenario) {

            /** @var RecordInterface $record */
            foreach ($records as $record) {

                // Set scenario
                $record->setScenario($scenario);

            }

        }

        return $records;

    }

    /**
     * @param array $condition
     * @param string $scenario
     * @return RecordInterface[]
     * @throws RecordNotFoundException
     */
    public function getAllRecords($condition = [], $scenario = RecordHelper::SCENARIO_UPDATE)
    {

        if (!$records = $this->findAllRecords($condition, $scenario)) {

            $this->notFoundRecordException();

        }

        return $records;

    }

    /*******************************************
     * EXCEPTIONS
     *******************************************/

    /**
     * @throws InvalidModelException
     */
    protected function notFoundRecordException()
    {

        throw new RecordNotFoundException(
            sprintf(
                "Record does not exist."
            )
        );

    }

}
