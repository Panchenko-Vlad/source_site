<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\helpers\Url;

class Email extends Model
{
    /**
     * Отправляем на почту новую новость всем пользователям, кто на это подписан
     * @param model $article
     */
    public static function sendArticle($article)
    {
        $users = Email::getAllUsersForEmail();

        foreach ($users as $user) {
            Yii::$app->mailer->compose('news', [
                'user' => $user,
                'article' => $article,
                'pathToImage' => Url::to('@app/web') . $article->getImage()
            ])
                ->setFrom([ChangeAccount::getSupportEmail() => Yii::$app->name])
                ->setTo($user->email)
                ->setSubject($article->title . ' (' . Yii::$app->name . ')')
                ->send();
        }
    }

    /**
     * Получаем список пользователей, какие подписаны на рассылку новых новостей на почту
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getAllUsersForEmail()
    {
        return User::find()->where(['isSendEmail' => 1])->andWhere(['status' => 1])->all();
    }

    /**
     * Отправляем на почту ссылку для подтверждения аккаунта
     * @param $email
     * @param $user
     * @return bool
     */
    public static function sendActivationEmail($email, $user)
    {
        return Yii::$app->mailer->compose('activationEmail', ['user' => $user])
            ->setFrom([ChangeAccount::getSupportEmail() => Yii::$app->name])
            ->setTo($email)
            ->setSubject('Активация для ' . Yii::$app->name)
            ->send();
    }


    /**
     * Отправляем на почту информацию о новом пользователе всем админам, какие имеются в базе
     * @param $newUser
     */
    public static function sendNewUser($newUser)
    {
        $users = Email::getAllAdminsForEmail();

        foreach ($users as $user) {
            Yii::$app->mailer->compose('user', ['user' => $user, 'newUser' => $newUser])
                ->setFrom([ChangeAccount::getSupportEmail() => Yii::$app->name])
                ->setTo($user->email)
                ->setSubject('Новый пользователь (' . Yii::$app->name . ')')
                ->send();
        }
    }

    /**
     * Получаем список всех админов
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getAllAdminsForEmail()
    {
        return User::find()->where(['isAdmin' => 1])->all();
    }

    /**
     * Отправляем на почту ссылку для изменения пароля пользователя
     * @param $user
     */
    public static function sendLinkForChangePassword($user)
    {
        Yii::$app->mailer->compose('linkChangePassword', ['user' => $user])
            ->setFrom([ChangeAccount::getSupportEmail() => Yii::$app->name])
            ->setTo($user->email)
            ->setSubject('Изменение пароля в профиле ' . Yii::$app->name)
            ->send();
    }

    /**
     * Отправляем пользователю его данные и новый пароль
     * @param model $user
     * @param string $newPassword
     */
    public static function sendSuccessChangePassword($user, $newPassword)
    {
        Yii::$app->mailer->compose('changedPassword', ['user' => $user, 'newPassword' => $newPassword])
            ->setFrom([ChangeAccount::getSupportEmail() => Yii::$app->name])
            ->setTo($user->email)
            ->setSubject('Пароль изменен (' . Yii::$app->name . ')')
            ->send();
    }
}