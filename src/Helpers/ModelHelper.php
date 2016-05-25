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

namespace Flipbox\Craft3\Spark\Helpers;

use Flipbox\Craft3\Spark\Exceptions\InvalidConfigurationException;
use Flipbox\Craft3\Spark\Exceptions\InvalidModelException;
use Flipbox\Craft3\Spark\Models\Interfaces\ModelInterface;

class ModelHelper
{

    /**
     * The scenario used to populate a model
     */
    const SCENARIO_POPULATE = 'populate';

    /**
     * The scenario used to insert a model
     */
    const SCENARIO_INSERT = 'insert';

    /**
     * The scenario used to update a model
     */
    const SCENARIO_UPDATE = 'update';

    /**
     * The scenario used to save a model
     */
    const SCENARIO_SAVE = 'save';

    /**
     * @param $config
     * @param null $instanceOf
     * @param string $toScenario
     * @return ModelInterface
     * @throws InvalidConfigurationException
     */
    public static function create($config, $instanceOf = null, $toScenario = self::SCENARIO_UPDATE)
    {

        // Get class from config
        $class = ObjectHelper::checkConfig($config, $instanceOf);

        // New model
        $model = new $class();

        return static::populate($model, $config, $toScenario);

    }

    /**
     * @param $model
     * @return ModelInterface
     */
    public static function copy($model)
    {
        if ($model instanceof ModelInterface) {

            return $model::create($model->getAttributes());

        }
        return static::create($model);
    }

    /**
     * @param ModelInterface $model
     * @param $config
     * @param string $toScenario
     * @return ModelInterface
     * @throws InvalidModelException
     */
    public static function populate(ModelInterface $model, $config, $toScenario = ModelHelper::SCENARIO_UPDATE)
    {

        // Set scenario
        if (!$toScenario) {

            $toScenario = $model->getScenario();

        }

        // Always set the populate scenario
        $model->setScenario(static::SCENARIO_POPULATE);

        // Populate model attributes
        $model->setAttributes($config);

        // Set scenario
        $model->setScenario($toScenario);

        return $model;

    }

}