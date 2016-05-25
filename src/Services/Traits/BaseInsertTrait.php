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

use yii\base\Event as BaseEvent;

trait BaseInsertTrait
{

    /*******************************************
     * TRIGGERS
     *******************************************/

    /**
     * @var string The event that is triggered after an object is inserted
     */
    public static $onBeforeInsertTrigger = 'onBeforeInsert';

    /**
     * @var string The event that is triggered after an object is inserted
     */
    public static $onAfterInsertTrigger = 'onAfterInsert';


    /*******************************************
     * ABSTRACTS
     *******************************************/

    /**
     * @param $name
     * @param BaseEvent|null $event
     * @return mixed
     */
    abstract public function trigger($name, BaseEvent $event = null);

}