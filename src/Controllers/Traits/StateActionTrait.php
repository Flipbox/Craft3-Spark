<?php

/**
 * @package    Spark
 * @author     Flipbox Factory <hello@flipboxfactory.com>
 * @copyright  2010-2016 Flipbox Digital Limited
 * @license    https://github.com/FlipboxFactory/Craft3-Spark/blob/master/LICENSE
 * @link       https://github.com/FlipboxFactory/Craft3-Spark
 * @since      Class available since Release 1.0.0
 */

namespace Flipbox\Craft3\Spark\Controllers\Traits;

use Craft;
use craft\app\errors\HttpException;
use Flipbox\Craft3\Spark\Exceptions\InvalidControllerServiceException;
use Flipbox\Craft3\Spark\Exceptions\InvalidModelException;
use Flipbox\Craft3\Spark\Models\Interfaces\ModelInterface;
use Flipbox\Craft3\Spark\Services\Interfaces\StateModelAccessorInterface;
use Flipbox\Craft3\Spark\Services\ModelAccessorByIdOrHandle;

trait StateActionTrait
{

    /*******************************************
     * ABSTRACTS
     *******************************************/

    /**
     * @return StateModelAccessorInterface|ModelAccessorByIdOrHandle
     */
    abstract protected function getService();

    /**
     * @param $error
     * @return mixed
     */
    abstract public function returnErrorJson($error);

    /**
     * @param array $var
     * @return mixed
     */
    abstract public function returnJson($var = array());

    /**
     * @param null $object
     * @param null $default
     * @return mixed
     */
    abstract public function redirectToPostedUrl($object = null, $default = null);

    /**
     * @return mixed
     */
    abstract public function requirePostRequest();

    /**
     * @return mixed
     */
    abstract public function requireAdmin();

    /*******************************************
     * PERMISSIONS
     *******************************************/

    /**
     * @throws InvalidControllerServiceException
     */
    protected function canStateChange()
    {

        if (!$this->getService() instanceof StateModelAccessorInterface) {

            throw new InvalidControllerServiceException(sprintf(
                "Controller service must implement '%s'.",
                'Flipbox\Craft3\Spark\Services\Interfaces\StateModelAccessorInterface'
            ));

        }

        // Require admin role
        $this->requireAdmin();

        // we require post data
        $this->requirePostRequest();

    }

    /*******************************************
     * ENABLE
     *******************************************/

    /**
     * @param ModelInterface $model
     * @return mixed
     */
    protected function enableSuccessMessage(ModelInterface $model)
    {
        return sprintf(
            "'%s' was enabled successfully",
            (string)$model
        );
    }

    /**
     * @param ModelInterface $model
     * @return mixed
     */
    protected function enableSuccessJsonResponse(ModelInterface $model)
    {
        return $this->returnJson(array(
                'success' => true,
                'message' => $this->enableSuccessMessage($model),
                'data' => $model
            )
        );
    }

    /**
     * @param ModelInterface $model
     * @return mixed
     */
    protected function enableFailMessage(ModelInterface $model)
    {
        return sprintf(
            "'%s' was NOT enabled successfully",
            (string)$model
        );
    }

    /**
     * @param ModelInterface $model
     * @return mixed
     */
    protected function enableFailJsonResponse(ModelInterface $model)
    {
        return $this->returnErrorJson(array(
                'success' => false,
                'errors' => $model->getErrors()
            )
        );
    }

    /**
     * @param ModelInterface $model
     * @return mixed
     */
    protected function enableRouteVariables(ModelInterface $model)
    {
        return array(
            'model' => $model
        );
    }

    /**
     * Enable a record
     *
     * @throws HttpException
     */
    public function actionEnable()
    {

        $this->canStateChange();

        try {

            $model = $this->getService()->get(
                Craft::$app->getRequest()->getRequiredBodyParam('identifier')
            );

        } catch (InvalidModelException $e) {

            throw new HttpException(500, $e->getMessage());

        }

        // Allow controllers to perform additional actions
        $this->onBeforeEnable($model);

        if ($this->getService()->enable($model)) {

            // Allow controllers to perform additional actions
            $this->onAfterEnable($model);

            // Handle AJAX calls
            if (Craft::$app->getRequest()->getIsAjax()) {

                return $this->enableSuccessJsonResponse($model);

            }

            // Set flash success notice
            Craft::$app->getSession()->setNotice(
                $this->enableSuccessMessage($model)
            );

            return $this->redirectToPostedUrl($model);

        }

        // Handle AJAX calls
        if (Craft::$app->getRequest()->getIsAjax()) {

            return $this->enableFailJsonResponse($model);

        }

        // Set flash success notice
        Craft::$app->getSession()->setError(
            $this->enableFailMessage($model)
        );

        // Set route variables
        Craft::$app->getUrlManager()->setRouteParams(
            $this->enableRouteVariables($model)
        );

    }

    /**
     * Allow manipulations to model prior to saving.
     *
     * @param ModelInterface $model
     * @return ModelInterface
     */
    protected function onBeforeEnable(ModelInterface $model)
    {
        return $model;
    }

    /**
     * Allow manipulations to model after to saving.
     *
     * @param ModelInterface $model
     * @return ModelInterface
     */
    protected function onAfterEnable(ModelInterface $model)
    {
        return $model;
    }

    /*******************************************
     * DISABLE
     *******************************************/

    /**
     * @param ModelInterface $model
     * @return mixed
     */
    protected function disableSuccessMessage(ModelInterface $model)
    {
        return sprintf(
            "'%s' was disabled successfully",
            (string)$model
        );
    }

    /**
     * @param ModelInterface $model
     * @return mixed
     */
    protected function disableSuccessJsonResponse(ModelInterface $model)
    {
        return $this->returnJson(array(
                'success' => true,
                'message' => $this->disableSuccessMessage($model),
                'data' => $model
            )
        );
    }

    /**
     * @param ModelInterface $model
     * @return mixed
     */
    protected function disableFailMessage(ModelInterface $model)
    {
        return sprintf(
            "'%s' was NOT disabled successfully",
            (string)$model
        );
    }

    /**
     * @param ModelInterface $model
     * @return mixed
     */
    protected function disableFailJsonResponse(ModelInterface $model)
    {
        return $this->returnErrorJson(array(
                'success' => false,
                'errors' => $model->getErrors()
            )
        );
    }

    /**
     * @param ModelInterface $model
     * @return mixed
     */
    protected function disableRouteVariables(ModelInterface $model)
    {
        return array(
            'model' => $model
        );
    }

    /**
     * Enable a record
     *
     * @throws HttpException
     */
    public function actionDisable()
    {

        $this->canStateChange();

        try {

            $model = $this->getService()->get(
                Craft::$app->getRequest()->getRequiredBodyParam('identifier')
            );

        } catch (InvalidModelException $e) {

            throw new HttpException(500, $e->getMessage());

        }

        // Allow controllers to perform additional actions
        $this->onBeforeDisable($model);

        if ($this->getService()->disable($model)) {

            // Allow controllers to perform additional actions
            $this->onAfterDisable($model);

            // Handle AJAX calls
            if (Craft::$app->getRequest()->getIsAjax()) {

                return $this->disableSuccessJsonResponse($model);

            }

            // Set flash success notice
            Craft::$app->getSession()->setNotice(
                $this->disableSuccessMessage($model)
            );

            return $this->redirectToPostedUrl($model);

        }

        // Handle AJAX calls
        if (Craft::$app->getRequest()->getIsAjax()) {

            return $this->disableFailJsonResponse($model);

        }

        // Set flash success notice
        Craft::$app->getSession()->setError(
            $this->disableFailMessage($model)
        );

        // Set route variables
        Craft::$app->getUrlManager()->setRouteParams(
            $this->disableRouteVariables($model)
        );

    }

    /**
     * Allow manipulations to model prior to saving.
     *
     * @param ModelInterface $model
     * @return ModelInterface
     */
    protected function onBeforeDisable(ModelInterface $model)
    {
        return $model;
    }

    /**
     * Allow manipulations to model after to saving.
     *
     * @param ModelInterface $model
     * @return ModelInterface
     */
    protected function onAfterDisable(ModelInterface $model)
    {
        return $model;
    }

}
