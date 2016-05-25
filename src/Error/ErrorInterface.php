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

namespace Flipbox\Craft3\Spark\Error;

interface ErrorInterface
{
    /**
     * Identify whether there are any errors
     *
     * @param null $attribute
     * @return boolean
     */
    public function hasErrors($attribute = null);

    /**
     * Returns the errors for all attribute or a single attribute.
     *
     * @param null $attribute
     * @return array
     */
    public function getErrors($attribute = null);

    /**
     * Returns the first error of every attribute in the model.
     *
     * @return array
     */
    public function getFirstErrors();

    /**
     * Returns the first error of the specified attribute.
     *
     * @param $attribute
     * @return string|null
     */
    public function getFirstError($attribute);

    /**
     * Adds a new error to the specified attribute.
     *
     * @param $attribute
     * @param string $error
     */
    public function addError($attribute, $error = '');

    /**
     * Adds a list of errors.
     *
     * @param array $items
     */
    public function addErrors(array $items);

    /**
     * Removes errors for all attributes or a single attribute.
     *
     * @param null $attribute
     */
    public function clearErrors($attribute = null);

}
