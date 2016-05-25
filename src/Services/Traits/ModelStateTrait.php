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
use Flipbox\Craft3\Spark\Models\ModelWithIdHandleAndState;
use Flipbox\Craft3\Spark\Records\RecordWithIdHandleAndState;

trait ModelStateTrait
{

    /**
     * @var string The event that is triggered after an object is enabled
     */
    public static $onBeforeEnableTrigger = 'onBeforeEnable';

    /**
     * @var string The event that is triggered after an object is enabled
     */
    public static $onAfterEnableTrigger = 'onAfterEnable';

    /**
     * @var string The event that is triggered after an object is disabled
     */
    public static $onBeforeDisableTrigger = 'onBeforeDisable';

    /**
     * @var string The event that is triggered after an object is disabled
     */
    public static $onAfterDisableTrigger = 'onAfterDisable';


    /*******************************************
     * ABSTRACTS
     *******************************************/

    /**
     * @param $name
     * @param ModelEvent|null $event
     * @return mixed
     */
    abstract public function trigger($name, ModelEvent $event = null);


    /**
     * @param array $config
     * @return ModelEvent
     */
    abstract protected function createEvent($config = []);

    /**
     * @param $condition
     * @param string $scenario
     * @return RecordWithIdHandleAndState
     */
    abstract public function getRecord($condition, $scenario = RecordHelper::SCENARIO_SAVE);

    /*******************************************
     * PERMISSIONS
     *******************************************/

    /**
     * @param ModelWithIdHandleAndState $model
     * @return bool
     */
    public function hasEnablePermission(ModelWithIdHandleAndState $model)
    {
        return true;
    }

    /**
     * @param ModelWithIdHandleAndState $model
     * @return bool
     */
    public function hasDisablePermission(ModelWithIdHandleAndState $model)
    {
        return true;
    }


    /*******************************************
     * EVENTS
     *******************************************/

    /**
     * @param ModelEvent $event
     */
    protected function onBeforeEnable(ModelEvent $event)
    {
        $this->trigger(static::$onBeforeEnableTrigger, $event);
    }

    /**
     * @param ModelEvent $event
     */
    protected function onAfterEnable(ModelEvent $event)
    {
        $this->trigger(static::$onAfterEnableTrigger, $event);
    }

    /**
     * @param ModelEvent $event
     */
    protected function onBeforeDisable(ModelEvent $event)
    {
        $this->trigger(static::$onBeforeDisableTrigger, $event);
    }

    /**
     * @param ModelEvent $event
     */
    protected function onAfterDisable(ModelEvent $event)
    {
        $this->trigger(static::$onAfterDisableTrigger, $event);
    }


    /*******************************************
     * ENABLE
     *******************************************/

    /**
     * @param ModelWithIdHandleAndState $model
     * @return bool
     * @throws \Exception
     */
    public function enable(ModelWithIdHandleAndState $model)
    {

        // Check permission
        if ($this->hasEnablePermission($model)) {

            return $this->enableInternal($model);

        }

        throw new InsufficientPrivilegesException("Insufficient privileges.");

    }

    /**
     * @param ModelWithIdHandleAndState $model
     * @return bool
     * @throws \CDbException
     * @throws \Exception
     */
    protected function enableInternal(ModelWithIdHandleAndState $model)
    {

        // Db transaction
        $transaction = RecordHelper::beginTransaction();

        try {

            // The event
            $event = $this->createEvent($model);

            // The 'before' event
            $this->onBeforeEnable($event);

            // Green light?
            if ($event->isValid) {

                // Get record
                $record = $this->getRecord($model->getId());

                // Disable it
                $record->toEnabled();

                // Enable record
                if ($record->update(['enabled'])) {

                    // The 'after' event
                    $this->onAfterEnable($event);

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

    /*******************************************
     * DISABLE
     *******************************************/

    /**
     * @param ModelWithIdHandleAndState $model
     * @return bool
     * @throws \Exception
     */
    public function disable(ModelWithIdHandleAndState $model)
    {

        // Check permission
        if ($this->hasDisablePermission($model)) {

            return $this->disableInternal($model);

        }

        throw new InsufficientPrivilegesException("Insufficient privileges.");

    }

    /**
     * @param ModelWithIdHandleAndState $model
     * @return bool
     * @throws \CDbException
     * @throws \Exception
     */
    protected function disableInternal(ModelWithIdHandleAndState $model)
    {

        // Db transaction
        $transaction = RecordHelper::beginTransaction();

        try {

            // The event
            $event = $this->createEvent($model);

            // The 'before' event
            $this->onBeforeDisable($event);

            // Green light?
            if ($event->isValid) {

                // Get record
                $record = $this->getRecord($model->getId());

                // Disable it
                $record->toDisabled();

                // Disable record
                if ($record->update(['enabled'])) {

                    // The 'after' event
                    $this->onAfterDisable($event);

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
