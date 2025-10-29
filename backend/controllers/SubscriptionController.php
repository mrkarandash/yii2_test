<?php

namespace backend\controllers;

use Yii;
use backend\models\Subscription;
use yii\web\Controller;

class SubscriptionController extends Controller
{
    public function actionCreate()
    {
        $model = new Subscription();

        if ($model->load(Yii::$app->request->post())) {
            $model->author_id = Yii::$app->request->post('author_id');
            $model->created_at = date('Y-m-d H:i:s');
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Вы успешно подписались!');
                return $this->redirect(Yii::$app->request->referrer);
            }
        }

        Yii::$app->session->setFlash('error', 'Ошибка подписки');
        return $this->redirect(Yii::$app->request->referrer);
    }
}
