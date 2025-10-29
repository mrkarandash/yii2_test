<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use common\models\User;

/**
 * Управление пользователями через консоль
 */
class UserController extends Controller
{
    /**
     * Создание администратора
     * Пример: php yii user/create-admin admin password123
     */
    public function actionCreateAdmin($username, $password, $email = null)
    {
        if (User::find()->where(['username' => $username])->exists()) {
            $this->stdout("Пользователь {$username} уже существует.\n");
            return 1;
        }

        $user = new User();
        $user->username = $username;
        $user->email = $email ?? $username . '@example.com';
        $user->password_hash = Yii::$app->security->generatePasswordHash($password);
        $user->auth_key = Yii::$app->security->generateRandomString();

        if ($user->save()) {
            $this->stdout("Администратор {$username} успешно создан.\n");

            // Назначаем роль через RBAC (phpManager или dbManager)
            if (Yii::$app->authManager) {
                $auth = Yii::$app->authManager;
                $role = $auth->getRole('admin');
                if (!$role) {
                    $role = $auth->createRole('admin');
                    $auth->add($role);
                }
                $auth->assign($role, $user->id);
                $this->stdout("Роль admin назначена пользователю {$username}.\n");
            }

            return 0;
        } else {
            $this->stderr("Ошибка при создании пользователя.\n");
            return 1;
        }
    }
}
