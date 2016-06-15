<?php

/**
 * @package    Spark
 * @author     Flipbox Factory <hello@flipboxfactory.com>
 * @copyright  2010-2016 Flipbox Digital Limited
 * @license    https://github.com/FlipboxFactory/Craft3-Spark/blob/master/LICENSE
 * @link       https://github.com/FlipboxFactory/Craft3-Spark
 * @since      Class available since Release 1.1.0
 */

namespace Flipbox\Craft3\Spark\Services\Traits;

use craft\app\events\Event as ElementEvent;
use craft\app\base\ElementInterface;
use Flipbox\Craft3\Spark\Exceptions\InsufficientPrivilegesException;
use Flipbox\Craft3\Spark\Helpers\RecordHelper;
use Flipbox\Craft3\Spark\Records\Interfaces\RecordWithIdInterface;

trait ElementDeleteTrait
{

    // Common delete
    use BaseDeleteTrait;

    /*******************************************
     * ABSTRACTS
     *******************************************/

    /**
     * @param array $config
     * @return ElementEvent
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
     * @param ElementInterface $element
     * @return bool
     */
    public function hasDeletePermission(ElementInterface $element)
    {
        return true;
    }


    /*******************************************
     * EVENTS
     *******************************************/

    /**
     * @param ElementEvent $event
     */
    protected function onBeforeDelete(ElementEvent $event)
    {
        $this->trigger(static::$onBeforeDeleteTrigger, $event);
    }

    /**
     * @param ElementEvent $event
     */
    protected function onAfterDelete(ElementEvent $event)
    {
        $this->trigger(static::$onAfterDeleteTrigger, $event);
    }


    /*******************************************
     * INSERT
     *******************************************/

    /**
     * @param ElementInterface $element
     * @return bool
     * @throws InsufficientPrivilegesException
     * @throws \Exception
     */
    public function delete(ElementInterface $element)
    {

        // Check permission
        if ($this->hasDeletePermission($element)) {

            return $this->deleteInternal($element);

        }

        throw new InsufficientPrivilegesException("Insufficient privileges.");

    }

    /**
     * @param ElementInterface $element
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    protected function deleteInternal(ElementInterface $element)
    {

        // Db transaction
        $transaction = RecordHelper::beginTransaction();

        try {

            // The event
            $event = $this->createEvent($element);

            // The 'before' event
            $this->onBeforeDelete($event);

            // Green light?
            if ($event->isValid) {

                // Delete record
                if (\Craft::$app->getElements()->deleteElementById($element->id)) {

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
