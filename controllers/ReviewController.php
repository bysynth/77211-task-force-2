<?php

namespace app\controllers;

use app\models\CreateReviewForm;
use app\models\Task;
use app\services\ReviewService;
use Yii;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\web\Response as WebResponse;

class ReviewController extends SecuredController
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['create'],
                        'roles' => ['customerCanCreateReview'],
                        'roleParams' => fn($rule) => [
                            'task' => Task::findOne(Yii::$app->request->post('CreateReviewForm')['task_id'])
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return WebResponse|bool
     * @throws StaleObjectException
     * @throws Exception
     */
    public function actionCreate(): WebResponse|bool
    {
        $reviewForm = new CreateReviewForm();
        $customer = Yii::$app->user->identity;
        $reviewService = new ReviewService();

        if ($reviewForm->load(Yii::$app->request->post()) && $reviewForm->validate()) {

            $task = Task::findOne($reviewForm->task_id);

            if ($customer->id === $task->customer_id) {
                $review = $reviewService->createReview($reviewForm, $task);
                return $this->redirect(['tasks/view', 'id' => $review->task_id]);
            }
        }

        return false;
    }
}