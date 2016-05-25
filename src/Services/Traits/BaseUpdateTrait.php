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

use yii\base\Event as BaseEvent;

trait BaseUpdateTrait
{

    /*******************************************
     * TRIGGERS
     *******************************************/

    /**
     * @var string The event that is triggered after an object is updated
     */
    public static $onBeforeUpdateTrigger = 'onBeforeUpdate';

    /**
     * @var string The event that is triggered after an object is updated
     */
    public static $onAfterUpdateTrigger = 'onAfterUpdate';


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