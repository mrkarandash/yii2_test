<?php
    use yii\helpers\Html;
    use yii\widgets\ActiveForm;

    /* @var $author backend\models\Author */
    /* @var $subscription backend\models\Subscription */
    ?>

    <?php $form = ActiveForm::begin(); ?>
    <?= Html::hiddenInput('author_id', $author->id) ?>
    <?= $form->field($subscription, 'phone')->textInput(['placeholder' => '+79991234567']) ?>
    <?= Html::submitButton('Подписаться', ['class' => 'btn btn-success']) ?>
<?php ActiveForm::end(); ?>
