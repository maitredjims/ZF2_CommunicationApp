<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Users;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Users\Model\User;
use Users\Model\UserTable;
use Users\Model\Upload;
use Users\Model\UploadTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable\CredentialTreatmentAdapter as DbTableAuthAdapter;

class Module implements AutoloaderProviderInterface
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
		    // if we're in a namespace deeper than one level we need to fix the \ in the path
                    __NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/' , __NAMESPACE__),
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap(MvcEvent $e)
    {
        // You may not need to do this if you're doing it elsewhere in your
        // application
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }
    
    public function getServiceConfig() {
        return array(
            'abstract_factories' => array(),
            'aliases' => array(),
            'factories' => array(
                // DB
                'UserTable' => function($sm) {
                    $tableGateway = $sm->get('UserTableGateway');
                    $table = new UserTable($tableGateway);
                    
                    return $table;
                },
                'UserTableGateway' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new User());
                    
                    return new TableGateway('user', $dbAdapter, NULL, $resultSetPrototype);
                }, 
                'UploadTable' => function($sm) {
                    $tableGateway = $sm->get('UploadTableGateway');
                    $uploadSharingTableGateway = $sm->get('UploadSharingTableGateway');
                    $table = new UploadTable($tableGateway, $uploadSharingTableGateway);
                    
                    return $table;
                },
                'UploadTableGateway' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Upload());
                    
                    return new TableGateway('uploads', $dbAdapter, NULL, $resultSetPrototype);
                },
                'UploadSharingTableGateway' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    
                    return new TableGateway('uploads_sharing', $dbAdapter);
                },
                'ChatMessagesTableGateway' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    
                    return new TableGateway('chat_messages', $dbAdapter);
                },
                // FORMS
                'LoginForm' => function($sm) {
                    $form = new \Users\Form\LoginForm();
                    $form->setInputFilter($sm->get('LoginFilter'));
                    
                    return $form;
                },
                'RegisterForm' => function($sm) {
                    $form = new \Users\Form\RegisterForm();
                    $form->setInputFilter($sm->get('RegisterFilter'));
                    
                    return $form;
                },
                'UserEditForm' => function($sm) {
                    $form = new \Users\Form\UserEditForm();
                    $form->setInputFilter($sm->get('UserEditFilter'));
                    
                    return $form;
                },
                'AddUserForm' => function ($sm) {
                    $form = new \Users\Form\AddUserForm();
                    $form->setInputFilter($sm->get('RegisterFilter'));
                    
                    return $form;
                },
                'UploadForm' => function ($sm) {
                    $form = new \Users\Form\UploadForm();
                    $form->setInputFilter($sm->get('UploadFilter'));
                    return $form;
                },
                'UploadEditForm' => function ($sm) {
                    $form = new \Users\Form\UploadEditForm();
                    
                    return $form;
                },
                'UploadShareForm' => function ($sm) {
                    $form = new \Users\Form\UploadShareForm();
                    
                    return $form;
                },
                // FILTERS
                'LoginFilter' => function($sm) {
                    return new \Users\Form\LoginFilter();
                },
                'RegisterFilter' => function($sm) {
                    return new \Users\Form\RegisterFilter();
                },
                'UserEditFilter' => function($sm) {
                    return new \Users\Form\UserEditFilter();
                },
                'UploadFilter' => function($sm) {
                    return new \Users\Form\UploadFilter();
                },
            ),
            'invokables' => array(),
            'services' => array(),
            'shared' => array(),
        );
    }
}
