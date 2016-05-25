<?php

/**
 * @package    Spark
 * @author     Flipbox Factory <hello@flipboxfactory.com>
 * @copyright  2010-2016 Flipbox Digital Limited
 * @license    https://github.com/FlipboxFactory/Craft3-Spark/blob/master/LICENSE
 * @link       https://github.com/FlipboxFactory/Craft3-Spark
 * @since      Class available since Release 1.0.0
 */

namespace Flipbox\Craft3\Spark\Services\Traits;

use craft\app\events\Event as ModelEvent;
use Flipbox\Craft3\Spark\Exceptions\InsufficientPrivilegesException;
use Flipbox\Craft3\Spark\Helpers\RecordHelper;
use Flipbox\Craft3\Spark\Models\Interfaces\ModelInterface;
use Flipbox\Craft3\Spark\Models\Interfaces\ModelWithIdInterface;
use Flipbox\Craft3\Spark\Records\Interfaces\RecordWithIdInterface;

trait ModelUpdateTrait
{

    // Common update
    use BaseUpdateTrait;

    /*******************************************
     * ABSTRACTS
     *******************************************/

    /**
     * @param array $config
     * @return ModelEvent
     */
    abstract protected function createEvent($config = []);

    /**
     * @param ModelInterface $model
     * @param bool $mirrorScenario
     * @return RecordWithIdInterface
     */
    abstract public function toRecord(ModelInterface $model, $mirrorScenario = true);

    /**
     * @param ModelWithIdInterface $model
     * @param null $properties
     * @param bool $mirrorScenario
     * @return bool
     */
    abstract public function insert(ModelWithIdInterface $model, $properties = null, $mirrorScenario = true);


    /*******************************************
     * PERMISSIONS
     *******************************************/

    /**
     * @param ModelWithIdInterface $model
     * @return bool
     */
    public function hasUpdatePermission(ModelWithIdInterface $model)
    {
        return true;
    }


    /*******************************************
     * EVENTS
     *******************************************/

    /**
     * @param ModelEvent $event
     */
    protected function onBeforeUpdate(ModelEvent $event)
    {
        $this->trigger(static::$onBeforeUpdateTrigger, $event);
    }

    /**
     * @param ModelEvent $event
     */
    protected function onAfterUpdate(ModelEvent $event)
    {
        $this->trigger(static::$onAfterUpdateTrigger, $event);
    }

    /*******************************************
     * UPDATE
     *******************************************/

    /**
     * @param ModelWithIdInterface $model
     * @param null $attributes
     * @param bool $mirrorScenario
     * @return bool
     * @throws InsufficientPrivilegesException
     * @throws \Exception
     */
    public function update(ModelWithIdInterface $model, $attributes = null, $mirrorScenario = true)
    {

        // Ensure we're creating a record
        if (!$model->getId()) {

            return $this->insert($model, $attributes, $mirrorScenario);

        }

        // Check permission
        if ($this->hasUpdatePermission($model)) {

            return $this->updateInternal($model, $attributes, $mirrorScenario);

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
    protected function updateInternal(ModelWithIdInterface $model, $attributes = null, $mirrorScenario = true)
    {

        // Db transaction
        $transaction = RecordHelper::beginTransaction();

        try {

            // The event
            $event = $this->createEvent($model);

            // The 'before' event
            $this->onBeforeUpdate($event);

            // Green light?
            if ($event->isValid) {

                // Convert model to record
                $record = $this->toRecord($model, $mirrorScenario);

                // Validate
                if (!$record->validate($attributes)) {
                    $model->addErrors($record->getErrors());
                }

                if (!$model->hasErrors()) {

                    // Insert record
                    if ($record->update($attributes)) {

                        // Transfer record date attribute(s) to model
                        $model->setAttributes([
                            'dateUpdated' => $record->getAttribute('dateUpdated'),
                        ]);

                        // The 'after' event
                        $this->onAfterUpdate($event);

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
