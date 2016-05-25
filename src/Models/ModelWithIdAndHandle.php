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

namespace Flipbox\Craft3\Spark\Models;

abstract class ModelWithIdAndHandle extends Model implements Interfaces\ModelWithIdInterface, Interfaces\ModelWithHandleInterface
{

    use Traits\ModelWithIdTrait, Traits\ModelWithHandleTrait {
        Traits\ModelWithIdTrait::rules as _traitRulesWithId;
        Traits\ModelWithHandleTrait::rules as _traitRulesWithHandle;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {

        return array_merge(
            parent::rules(),
            $this->_traitRulesWithId(),
            $this->_traitRulesWithHandle()
        );

    }

}
