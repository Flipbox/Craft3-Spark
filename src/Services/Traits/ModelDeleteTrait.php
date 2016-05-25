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
use Flipbox\Craft3\Spark\Helpers\RecordHelper;
use Flipbox\Craft3\Spark\Models\Interfaces\ModelWithIdInterface;
use Flipbox\Craft3\Spark\Records\Interfaces\RecordWithIdInterface;

trait ModelDeleteTrait
{

    // Common delete
    use BaseDeleteTrait;

    /*******************************************
     * ABSTRACTS
     *******************************************/

    /**
     * @param array $config
     * @return ModelEvent
     */
    abstract protected function createEvent($config = []);

    /**
     * @param $condition
     * @param string $scenario
     * @return RecordWithIdInterface
     */
    abstract public function getRecord($condition, $scenario = RecordHelper::SCENARIO_SAVE);

    /*******************************************
     * PERMISSIONS
     *******************************************/

    /**
     * @param ModelWithIdInterface $model
     * @return bool
     */
    public function hasDeletePermission(ModelWithIdInterface $model)
    {
        return true;
    }


    /*******************************************
     * EVENTS
     *******************************************/

    /**
     * @param ModelEvent $event
     */
    protected function onBeforeDelete(ModelEvent $event)
    {
        $this->trigger(static::$onBeforeDeleteTrigger, $event);
    }

    /**
     * @param ModelEvent $event
     */
    protected function onAfterDelete(ModelEvent $event)
    {
        $this->trigger(static::$onAfterDeleteTrigger, $event);
    }


    /*******************************************
     * INSERT
     *******************************************/

    /**
     * @param ModelWithIdInterface $model
     * @return bool
     * @throws InsufficientPrivilegesException
     * @throws \Exception
     */
    public function delete(ModelWithIdInterface $model)
    {

        // Check permission
        if ($this->hasDeletePermission($model)) {

            return $this->deleteInternal($model);

        }

        throw new InsufficientPrivilegesException("Insufficient privileges.");

    }

    /**
     * @param ModelWithIdInterface $model
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    protected function deleteInternal(ModelWithIdInterface $model)
    {

        // Db transaction
        $transaction = RecordHelper::beginTransaction();

        try {

            // The event
            $event = $this->createEvent($model);

            // The 'before' event
            $this->onBeforeDelete($event);

            // Green light?
            if ($event->isValid) {

                // Get record
                $record = $this->getRecord($model->getId());

                // Insert record
                if ($record->delete()) {

                    // The 'after' event
                    $this->onAfterDelete($event);

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
