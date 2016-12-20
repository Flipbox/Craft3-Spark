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
use Flipbox\Craft3\Spark\Elements\Interfaces\ElementInterface;
use Flipbox\Craft3\Spark\Exceptions\InsufficientPrivilegesException;
use Flipbox\Craft3\Spark\Helpers\ElementHelper;
use Flipbox\Craft3\Spark\Helpers\RecordHelper;
use Flipbox\Craft3\Spark\Records\Interfaces\RecordWithIdInterface;

trait ElementInsertTrait
{

    // Common insert
    use BaseInsertTrait;

    /*******************************************
     * ABSTRACTS
     *******************************************/

    /**
     * @param array $config
     * @return ElementEvent
     */
    abstract protected function createEvent($config = []);

    /**
     * @param array $config
     * @param string $scenario
     * @return RecordWithIdInterface
     */
    abstract public function createRecord($config = [], $scenario = RecordHelper::SCENARIO_SAVE);

    /**
     * @param ElementInterface $element
     * @param null $attributes
     * @param null $contentAttributes
     * @param bool $mirrorScenario
     * @return bool
     * @throws InsufficientPrivilegesException
     * @throws \Exception
     */
    abstract public function update(
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
    public function hasInsertPermission(ElementInterface $element)
    {
        return true;
    }


    /*******************************************
     * EVENTS
     *******************************************/

    /**
     * @param ElementEvent $event
     */
    protected function onBeforeInsert(ElementEvent $event)
    {
        $this->trigger(static::$onBeforeInsertTrigger, $event);
    }

    /**
     * @param ElementEvent $event
     */
    protected function onAfterInsert(ElementEvent $event)
    {
        $this->trigger(static::$onAfterInsertTrigger, $event);
    }

    /*******************************************
     * INSERT
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
    public function insert(
        ElementInterface $element,
        $attributes = null,
        $contentAttributes = null,
        $mirrorScenario = true
    ) {

        // Ensure we're creating a record
        if ($element->getId()) {

            return $this->update($element, $attributes, $contentAttributes, $mirrorScenario);

        }

        // Check permission
        if ($this->hasInsertPermission($element)) {

            return $this->insertInternal($element, $attributes, $contentAttributes, $mirrorScenario);

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
    protected function insertInternal(
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
            $this->onBeforeInsert($event);

            // Green light?
            if ($event->isValid) {

                // New record (from model)
                if ($mirrorScenario) {

                    $record = $this->createRecord($element, $element->getScenario());

                } else {

                    $record = $this->createRecord($element);

                }

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

                        // Transfer id to the new records
                        $record->id = $element->getId();
                        
                        // Insert record
                        if (false !== $record->insert(false, $attributes)) {

                            // Transfer record Id to element
                            $element->setId($record->getId());

                            // Transfer record date attribute(s) to element
                            $element->setAttributes([
                                'dateUpdated' => $record->getAttribute('dateUpdated'),
                                'dateCreated' => $record->getAttribute('dateCreated')
                            ]);

                            // Change scenario
                            $element->setScenario(ElementHelper::SCENARIO_UPDATE);

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
