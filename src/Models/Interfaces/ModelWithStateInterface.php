<?php

/**
 * @package    Spark
 * @author     Flipbox Factory <hello@flipboxfactory.com>
 * @copyright  2010-2016 Flipbox Digital Limited
 * @license    https://github.com/FlipboxFactory/Craft3-Spark/blob/master/LICENSE
 * @link       https://github.com/FlipboxFactory/Craft3-Spark
 * @since      Class available since Release 1.0.0
 */

namespace Flipbox\Craft3\Spark\Models\Interfaces;

interface ModelWithStateInterface extends ModelInterface
{

    /**
     * @return bool
     */
    public function isEnabled();

    /**
     * @return bool
     */
    public function isDisabled();

    /**
     * @return $this
     */
    public function toEnabled();

    /**
     * @return $this
     */
    public function toDisabled();

}
