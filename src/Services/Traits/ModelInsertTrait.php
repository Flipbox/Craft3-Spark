<?php

/**
 * @package    Spark
 * @author     Flipbox Factory <hello@flipboxfactory.com>
 * @copyright  2010-2016 Flipbox Digital Limited
 * @license    https://github.com/FlipboxFactory/Craft3-Spark/blob/master/LICENSE
 * @version    Release: 1.1.0
 * @link       https://github.com/FlipboxFactory/Craft3-Spark
 * @since      Class available since Release 1.0.0
 */

namespace Flipbox\Craft3\Spark\Services\Traits;

use craft\app\events\Event as ModelEvent;
use Flipbox\Craft3\Spark\Exceptions\InsufficientPrivilegesException;
use Flipbox\Craft3\Spark\Helpers\ModelHelper;
use Flipbox\Craft3\Spark\Helpers\RecordHelper;
use Flipbox\Craft3\Spark\Models\Interfaces\ModelWithIdInterface;
use Flipbox\Craft3\Spark\Records\Interfaces\RecordWithIdInterface;

trait ModelInsertTrait
{

    // Common insert
    use BaseInsertTrait;

    /*******************************************
     * ABSTRACTS
     *******************************************/

    /**
     * @param array $config
     * @return ModelEvent
     */
    abstract protected function createEvent($config = []);

    /**
     * @param array $config
     * @param string $scenario
     * @return RecordWithIdInterface
     */
    abstract public function createRecord($config = [], $scenario = RecordHelper::SCENARIO_SAVE);

    /**
     * @param ModelWithIdInterface $model
     * @param null $properties
     * @param bool $mirrorScenario
     * @return bool
     */
    abstract public function update(ModelWithIdInterface $model, $properties = null, $mirrorScenario = true);


    /*******************************************
     * PERMISSIONS
     *******************************************/

    /**
     * @param ModelWithIdInterface $model
     * @return bool
     */
    public function hasInsertPermission(ModelWithIdInterface $model)
    {
        return true;
    }


    /*******************************************
     * EVENTS
     *******************************************/

    /**
     * @param ModelEvent $event
     */
    protected function onBeforeInsert(ModelEvent $event)
    {
        $this->trigger(static::$onBeforeInsertTrigger, $event);
    }

    /**
     * @param ModelEvent $event
     */
    protected function onAfterInsert(ModelEvent $event)
    {
        $this->trigger(static::$onAfterInsertTrigger, $event);
    }

    /*******************************************
     * INSERT
     *******************************************/

    /**
     * @param ModelWithIdInterface $model
     * @param null $attributes
     * @param bool $mirrorScenario
     * @return bool
     * @throws InsufficientPrivilegesException
     * @throws \Exception
     */
    public function insert(ModelWithIdInterface $model, $attributes = null, $mirrorScenario = true)
    {

        // Ensure we're creating a record
        if ($model->getId()) {

            return $this->update($model, $attributes, $mirrorScenario);

        }

        // Check permission
        if ($this->hasInsertPermission($model)) {

            return $this->insertInternal($model, $attributes, $mirrorScenario);

        }

        throw new InsufficientPrivilegesException("Insufficient privileges.");

    }

    /**
     * @param ModelWithIdInterface $model
     * @param null $attributes
     * @param bool $mirrorScenario
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    protected function insertInternal(ModelWithIdInterface $model, $attributes = null, $mirrorScenario = true)
    {

        // Db transaction
        $transaction = RecordHelper::beginTransaction();

        try {

            // The event
            $event = $this->createEvent($model);

            // The 'before' event
            $this->onBeforeInsert($event);

            // Green light?
            if ($event->isValid) {

                // New record (from model)
                if ($mirrorScenario) {

                    $record = $this->createRecord($model, $model->getScenario());

                } else {

                    $record = $this->createRecord($model);

                }

                // Validate
                if (!$record->validate($attributes)) {
                    $model->addErrors($record->getErrors());
                }

                if (!$model->hasErrors()) {

                    // Insert record
                    if ($record->insert($attributes)) {

                        // Transfer record Id to model
                        $model->setId($record->getId());

                        // Transfer record date attribute(s) to model
                        $model->setAttributes([
                            'dateUpdated' => $record->getAttribute('dateUpdated'),
                            'dateCreated' => $record->getAttribute('dateCreated')
                        ]);

                        // Change scenario
                        $model->setScenario(ModelHelper::SCENARIO_UPDATE);

                        // The 'after' event
                        $this->onAfterInsert($event);

                        // Green light?
                        if ($event->isValid) {

                            // Commit db transaction
                            if ($transaction) {

                                $transaction->commit();

                            }

                            return true;

                        }

                    } else {

                        // Transfer errors to model
                        $model->addErrors($record->getErrors());

                    }

                }

            }

        } catch (\Exception $e) {

            // Roll back all db actions (fail)
            if ($transaction) {

                $transaction->rollback();

            }

            throw $e;

        }

        // Roll back all db actions (fail)
        if ($transaction) {

            $transaction->rollback();

        }

        return false;

    }

}
