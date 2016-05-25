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

namespace Flipbox\Craft3\Spark\Services\Interfaces;

use Flipbox\Craft3\Spark\Models\Interfaces\ModelWithIdInterface;

interface SavableModelAccessorInterface
{

    /**
     * @param ModelWithIdInterface $model
     * @param null $attributes
     * @param bool $mirrorScenario
     * @return bool
     */
    public function save(ModelWithIdInterface $model, $attributes = null, $mirrorScenario = true);

    /**
     * @param ModelWithIdInterface $model
     * @param null $attributes
     * @param bool $mirrorScenario
     * @return bool
     */
    public function update(ModelWithIdInterface $model, $attributes = null, $mirrorScenario = true);

    /**
     * @param ModelWithIdInterface $model
     * @param null $attributes
     * @param bool $mirrorScenario
     * @return bool
     */
    public function insert(ModelWithIdInterface $model, $attributes = null, $mirrorScenario = true);

}
