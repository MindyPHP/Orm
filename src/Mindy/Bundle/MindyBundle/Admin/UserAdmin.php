<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 09/10/2016
 * Time: 22:59
 */

namespace Mindy\Bundle\MindyBundle\Admin;

use Mindy\Bundle\MindyBundle\Form\ChangePasswordFormType;
use Mindy\Bundle\MindyBundle\Form\UserFormType;
use Mindy\Bundle\MindyBundle\Model\User;
use Mindy\Orm\ModelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserAdmin extends AbstractModelAdmin
{
    public $columns = [
        'email',
        'is_superuser',
        'is_active'
    ];

    public function getCustomBreadrumbs(Request $request, ModelInterface $model, string $action)
    {
        if ($action == 'change_password') {
            return [
                ['name' => (string)$model, 'url' => $this->getAdminUrl('update', ['pk' => $request->query->get('pk')])],
                ['name' => 'Изменение пароля']
            ];
        }
        return parent::getCustomBreadrumbs($request, $model, $action);
    }

    /**
     * @return string model class name
     */
    public function getModelClass()
    {
        return User::class;
    }

    public function getFormType()
    {
        return UserFormType::class;
    }

    public function changePasswordAction(Request $request)
    {
        $pk = $request->query->getInt('pk');

        $user = $this->getQuerySet()->get(['id' => $pk]);
        if (null === $user) {
            throw new NotFoundHttpException;
        }

        $form = $this->createForm(ChangePasswordFormType::class, [], [
            'method' => 'POST'
        ]);

        if ($request->getMethod() === 'POST') {
            if ($form->handleRequest($request)->isValid()) {
                $data = $form->getData();

                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);

                $salt = md5(time());
                $user->salt = $salt;
                $user->password = $encoder->encodePassword($data['password'], $salt);

                if ($user->save()) {
                    $this->addFlash(self::FLASH_SUCCESS, 'Пароль успешно изменен');

                    return $this->redirect($this->getAdminUrl('info', ['pk' => $request->query->get('pk')]));
                }

                $this->addFlash(self::FLASH_ERROR, 'Ошибка при изменении пароля');

                return $this->redirect($request->getRequestUri());
            }
        }

        return $this->render($this->findTemplate('change_password.html'), [
            'form' => $form->createView(),
            'breadcrumbs' => $this->fetchBreadcrumbs($request, $user, 'change_password')
        ]);
    }
}