<?php

namespace Users\Controller;

use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class UserManagerController extends AbstractActionController {
    
    protected $authservice;
    
    public function indexAction() {
        $user_email = $this->getAuthService()->getStorage()->read();
        
        if(!$user_email) {
            return $this->redirect()->toRoute('users', array('action' => 'index'));
        } else {
            $userTable = $this->getServiceLocator()->get('UserTable');
            $viewModel = new ViewModel(array(
                'users' => $userTable->fetchAll()
            ));

            return $viewModel;
        }
    }

    public function editAction() {
        $id = (int) $this->params()->fromRoute('id');
        $userTable = $this->getServiceLocator()->get('UserTable');
        
        $user = $userTable->getUser($id);
        $form = $this->getServiceLocator()->get('UserEditForm');
        $form->bind($user);
        
        $viewModel = new ViewModel(array(
            'form' => $form,
            'user_id' => $id,
        ));
        return $viewModel;
    }

    public function processAction() {
        $post = $this->request->getPost();
        $userTable = $this->getServiceLocator()->get('UserTable');

        $user = $userTable->getUser($post->id);

        $form = $this->getServiceLocator()->get('UserEditForm');
        $form->bind($user);
        $form->setData($post);

        if (!$form->isValid()) {
            $model = new ViewModel(array(
                'error' => true,
                'form' => $form,
            ));
            $model->setTemplate('users/user-manager/edit');

            return $model;
        }

        $userTable->saveUser($user);

        return $this->redirect()->toRoute(NULL, array(
                    'controller' => 'user-manager',
                    'action' => 'index',
        ));
    }

    public function deleteAction() {
        $this->getServiceLocator()->get('UserTable')
                ->deleteUser($this->params()->fromRoute('id'));

        return $this->redirect()->toRoute(NULL, array(
                    'controller' => 'user-manager',
                    'action' => 'index',
        ));
    }

    public function addAction() {
        if (!$this->request->isPost()) {
            $form = $this->getServiceLocator()->get('AddUserForm');
            $viewModel = new ViewModel(array(
                'form' => $form,
            ));
            return $viewModel;
        }

        $post = $this->request->getPost();
        $form = $this->getServiceLocator()->get('AddUserForm');
        $form->setData($post);
        if (!$form->isValid()) {
            $model = new ViewModel(array(
                'error' => TRUE,
                'form' => $form,
            ));
            $model->setTemplate('users/user-manager/add');
            return $model;
        }

        $user = new \Users\Model\User();
        $user->exchangeArray($form->getData());
        $userTable = $this->getServiceLocator()->get('UserTable');
        $userTable->saveUser($user);

        return $this->redirect()->toRoute(NULL, array(
                    'controller' => 'user-manager',
                    'action' => 'index'
        ));
    }
    
    public function getAuthService() {
        if (!$this->authservice) {
            $authService = new AuthenticationService();
            $this->authservice = $authService;
        }
        return $this->authservice;
    }

}
