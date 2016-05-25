<?php

/**
 * @package    Spark
 * @author     Flipbox Factory <hello@flipboxfactory.com>
 * @copyright  2010-2016 Flipbox Digital Limited
 * @license    https://github.com/FlipboxFactory/Craft3-Spark/blob/master/LICENSE
 * @version    Release: 1.0.0
 * @link       https://github.com/FlipboxFactory/Craft3-Spark
 * @since      Class available since Release 1.1.0
 */

namespace Flipbox\Craft3\Spark\Elements\Traits;

use Flipbox\Craft3\Spark\Helpers\ElementHelper;

trait ElementWithStateTrait
{

    /**
     * @var boolean Enabled
     */
    public $enabled;

    /**
     * @inheritdoc
     */
    public function isEnabled()
    {
        return (bool)$this->enabled;
    }

    /**
     * @inheritdoc
     */
    public function isDisabled()
    {
        return !$this->isEnabled();
    }

    /**
     * @inheritdoc
     */
    public function toEnabled()
    {
        $this->enabled = true;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function toDisabled()
    {
        $this->enabled = false;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {

        return [
            [
                [
                    'enabled'
                ],
                'safe',
                'on' => [
                    ElementHelper::SCENARIO_POPULATE,
                    ElementHelper::SCENARIO_SAVE,
                    ElementHelper::SCENARIO_INSERT,
                    ElementHelper::SCENARIO_UPDATE
                ]
            ]
        ];

    }

}
