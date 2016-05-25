<?php

/**
 * @package    Spark
 * @author     Flipbox Factory <hello@flipboxfactory.com>
 * @copyright  2010-2016 Flipbox Digital Limited
 * @license    https://github.com/FlipboxFactory/Craft3-Spark/blob/master/LICENSE
 * @version    Release: 1.0.0
 * @link       https://github.com/FlipboxFactory/Craft3-Spark
 * @since      Class available since Release 1.0.0
 */

namespace Flipbox\Craft3\Spark\Models\Interfaces;

interface ModelWithHandleInterface extends ModelInterface
{

    /**
     * @return string
     */
    public function getHandle();

    /**
     * @param $handle
     * @return $this
     */
    public function setHandle($handle);

}
