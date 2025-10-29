<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\models\Book $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="book-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'year')->textInput() ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'isbn')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'authorsArray')->checkboxList(
        \yii\helpers\ArrayHelper::map(\backend\models\Author::find()->all(), 'id', 'full_name')
    ) ?>

    <?= $form->field($model, 'coverFile')->fileInput() ?>

    <?php if ($model->cover_url): ?>
        <div>
            <p>Текущая обложка:</p>
            <img src="<?= $model->cover_url ?>" width="150">
        </div>
    <?php endif; ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
