<?php

namespace Users\Controller;

use Users\Form\LoginForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;
//use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter; -- deprecated
use Zend\Authentication\Adapter\DbTable\CredentialTreatmentAdapter as DbTableAuthAdapter;

class LoginController extends AbstractActionController {

    protected $authservice;

    public function indexAction() {
        $form = new LoginForm();
        $viewModel = new ViewModel(array('form' => $form));
        
        return $viewModel;
    }
    
    public function confirmAction() {
        $user_email = $this->getAuthService()->getStorage()->read();
        $viewModel = new ViewModel(array(
            'user_email' => $user_email
        ));
        
        return $viewModel;
    }

    public function processAction() {
        if (!$this->request->getPost()) {
            return $this->redirect()->toRoute(NULL, array(
                        'controller' => 'login',
                        'action' => 'index'
            ));
        }

        $post = $this->request->getPost();
        /*
        $form = new LoginForm();
        $inputFilter = new LoginFilter();
        $form->setInputFilter($inputFilter);
        */
        $form = $this->getServiceLocator()->get('LoginForm');
        
        $form->setData($post);
        
        if (!$form->isValid()) {
            $model = new ViewModel(array(
                'error' => true,
                'form' => $form
            ));
            
            $model->setTemplate('users/login/index');
            return $model;
        }

        $this->getAuthService()->getAdapter()
                ->setIdentity($this->request->getPost('email'))
                ->setCredential($this->request->getPost('password'));
        
        $result = $this->getAuthService()->authenticate();
        
        if ($result->isValid()) {
            $this->getAuthService()->getStorage()
                    ->write($this->request->getPost('email'));
            return $this->redirect()->toRoute(NULL, array(
                        'controller' => 'login',
                        'action' => 'confirm'
            ));
        } else {
            $model = new ViewModel(array(
                'error' => true,
                'form' => $form,
            ));
            
            $model->setTemplate('users/login/index');
            return $model;
        }
    }
    
    public function getAuthService() {
        if (!$this->authservice) {
            $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
            $dbTableAuthAdapter = new DbTableAuthAdapter($dbAdapter, 'user', 'email', 'password', 'MD5(?)');

            $authService = new AuthenticationService();
            $authService->setAdapter($dbTableAuthAdapter);

            $this->authservice = $authService;
        }

        return $this->authservice;
    }
    
    public function logoutAction() {
        $this->getAuthService()->clearIdentity();
        
        return $this->redirect()->toRoute(NULL, array(
            'controller' => 'login',
            'action' => 'index',
        ));
    }

}
