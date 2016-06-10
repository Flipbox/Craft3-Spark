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
use craft\app\base\ElementInterface as BaseElementInterface;
use Flipbox\Craft3\Spark\Elements\Interfaces\ElementInterface;
use Flipbox\Craft3\Spark\Exceptions\InsufficientPrivilegesException;
use Flipbox\Craft3\Spark\Helpers\RecordHelper;
use Flipbox\Craft3\Spark\Records\Interfaces\RecordWithIdInterface;

trait ElementUpdateTrait
{

    // Common update
    use BaseUpdateTrait;

    /*******************************************
     * ABSTRACTS
     *******************************************/

    /**
     * @param array $config
     * @return ElementEvent
     */
    abstract protected function createEvent($config = []);

    /**
     * @param BaseElementInterface $element
     * @param bool $mirrorScenario
     * @return RecordWithIdInterface
     */
    abstract public function toRecord(BaseElementInterface $element, $mirrorScenario = true);

    /**
     * @param ElementInterface $element
     * @param null $attributes
     * @param null $contentAttributes
     * @param bool $mirrorScenario
     * @return bool
     * @throws InsufficientPrivilegesException
     * @throws \Exception
     */
    abstract public function insert(
        ElementInterface $element,
        $attributes = null,
        $contentAttributes = null,
        $mirrorScenario = true
    );


    /*******************************************
     * PERMISSIONS
     *******************************************/

    /**
     * @param ElementInterface $element
     * @return bool
     */
    public function hasUpdatePermission(ElementInterface $element)
    {
        return true;
    }


    /*******************************************
     * EVENTS
     *******************************************/

    /**
     * @param ElementEvent $event
     */
    protected function onBeforeUpdate(ElementEvent $event)
    {
        $this->trigger(static::$onBeforeUpdateTrigger, $event);
    }

    /**
     * @param ElementEvent $event
     */
    protected function onAfterUpdate(ElementEvent $event)
    {
        $this->trigger(static::$onAfterUpdateTrigger, $event);
    }

    /*******************************************
     * UPDATE
     *******************************************/

    /**
     * @param ElementInterface $element
     * @param null $attributes
     * @param null $contentAttributes
     * @param bool $mirrorScenario
     * @return bool
     * @throws InsufficientPrivilegesException
     * @throws \Exception
     */
    public function update(
        ElementInterface $element,
        $attributes = null,
        $contentAttributes = null,
        $mirrorScenario = true
    ) {

        // Ensure we're creating a record
        if (!$element->getId()) {

            return $this->insert($element, $attributes, $contentAttributes, $mirrorScenario);

        }

        // Check permission
        if ($this->hasUpdatePermission($element)) {

            return $this->updateInternal($element, $attributes, $contentAttributes, $mirrorScenario);

        }

        throw new InsufficientPrivilegesException("Insufficient privileges.");

    }

    /**
     * @param ElementInterface $element
     * @param null $attributes
     * @param null $contentAttributes
     * @param bool $mirrorScenario
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    protected function updateInternal(
        ElementInterface $element,
        $attributes = null,
        $contentAttributes = null,
        $mirrorScenario = true
    ) {

        // Db transaction
        $transaction = RecordHelper::beginTransaction();

        try {

            // The event
            $event = $this->createEvent($element);

            // The 'before' event
            $this->onBeforeUpdate($event);

            // Green light?
            if ($event->isValid) {

                // Convert model to record
                $record = $this->toRecord($element, $mirrorScenario);

                // Validate
                if (!$record->validate($attributes)) {
                    $element->addErrors($record->getErrors());
                }

                // Validate content
                if (\Craft::$app->getEdition() == \Craft::Pro) {
                    \Craft::$app->getContent()->validateContent($element);
                }

                // Proceed (no errors)
                if (!$element->hasErrors()) {

                    // Save element (and content fields)
                    if (\Craft::$app->getElements()->saveElement($element, false)) {

                        // Update record
                        if (false !== $record->update($attributes)) {

                            // Transfer record date attribute(s) to element
                            $element->setAttributes([
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
                            $element->addErrors($record->getErrors());

                        }

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
