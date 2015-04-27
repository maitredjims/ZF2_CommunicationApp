<?php

namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;
use Users\Form\UploadForm;

class UploadManagerController extends AbstractActionController {

    protected $authservice;

    public function indexAction() {
        $uploadTable = $this->getServiceLocator()->get('UploadTable');
        $userTable = $this->getServiceLocator()->get('UserTable');

        $userEmail = $this->getAuthService()->getStorage()->read();
        /*
          $user = $userTable->getUserByEmail($userEmail);

          $viewModel = new ViewModel(array(
          'myUploads' => $uploadTable->getUploadsByUserId($user->id),
          ));

          return $viewModel;
         */
        if (!$userEmail) {
            return $this->redirect()->toRoute('users', array('action' => 'index'));
        } else {
            $user = $userTable->getUserByEmail($userEmail);
            $shareUploads = $uploadTable->getSharedUploadsForUserId($user->id);

            $sharedUploadsList = array();
            foreach ($shareUploads as $sharedUpload) {
                $sharedUsers = $uploadTable->getSharedUsers($sharedUpload->id);
                $sharedUserNames = array();

                foreach ($sharedUsers as $sharedUser) {
                    $userI = $userTable->getUser($sharedUser->user_id);
                    $userName = $userI->name;
                    $sharedUserNames[] = $userName;
                }

                $uploadOwner = $userTable->getUser($sharedUpload->user_id);
                $sharedUploadInfo = array();
                $sharedUploadInfo['filename'] = $sharedUpload->filename;
                $sharedUploadInfo['owner'] = $uploadOwner->name;

                $sharedUploadInfo['sharedUsers'] = $sharedUserNames;
                $sharedUploadsList[$sharedUpload->id] = $sharedUploadInfo;
            }

            $viewModel = new ViewModel(array(
                'myUploads' => $uploadTable->getUploadsByUserId($user->id),
                'sharedUploadsList' => $sharedUploadsList,
                'user' => $user,
            ));

            return $viewModel;
        }
    }

    public function uploadAction() {
        $form = new UploadForm();
        $viewModel = new ViewModel(array('form' => $form));

        return $viewModel;
    }

    public function processAction() {
        $uploadFile = $this->params()->fromFiles('fileupload');
        $form = new UploadForm();
        $form->setData($this->request->getPost());

        if ($form->isValid()) {
            $uploadPath = $this->getFileUploadLocation();

            $userTable = $this->getServiceLocator()->get('UserTable');
            $userEmail = $this->getAuthService()->getStorage()->read();
            $user = $userTable->getUserByEmail($userEmail);

            $adapter = new \Zend\File\Transfer\Adapter\Http();
            $adapter->setDestination($uploadPath);

            if ($adapter->receive($uploadFile['name'])) {
                $exchange_data = array();
                $exchange_data['label'] = $this->request->getPost()->get('label');
                $exchange_data['filename'] = $this->request->getPost()->get('filename');
                $exchange_data['user_id'] = $user->id;

                $upload = new \Users\Model\Upload();
                $upload->exchangeArray($exchange_data);
                $uploadTable = $this->getServiceLocator()->get('UploadTable');
                $uploadTable->saveUpload($upload);

                return $this->redirect()->toRoute(NULL, array(
                            'controller' => 'UploadManager',
                            'action' => 'index',
                ));
            } else {
                $model = new ViewModel(array(
                    'error' => TRUE,
                    'form' => $form,
                ));
                $model->setTemplate('users/upload-manager/upload');
                return $model;
            }
        }
    }

    public function processEditAction() {
        if (!$this->request->isPost()) {
            return $this->redirect()->toRoute('users/upload-manager', array(
                        'action' => 'edit'
            ));
        }

        $post = $this->request->getPost();
        $uploadTable = $this->getServiceLocator()->get('UploadTable');

        $upload = $uploadTable->getUpload($post->id);

        $form = $this->getServiceLocator()->get('UploadEditForm');
        $form->bind($upload);
        $form->setData($post);

        if (!$form->isValid()) {
            $model = new ViewModel(array(
                'error' => true,
                'form' => $form
            ));

            $model->setTemplate('users/upload-manager/edit');
            return $model;
        }

        $this->getServiceLocator()->get('UploadTable')->saveUpload($upload);

        return $this->redirect()->toRoute('users/upload-manager');
    }

    public function editAction() {
        /*
          $uploadTable = $this->getServiceLocator()->get('UploadTable');
          $userTable = $this->getServiceLocator()->get('UserTable');

          $uploadId = $this->params()->fromRoute('id');
          $upload = $uploadTable->getUpload($uploadId);

          $form = $this->getServiceLocator()->get('UploadEditForm');
          $form->bind($upload);
          $viewModel = new ViewModel(array(
          'form' => $form,
          'upload_id' => $uploadId,
          ));
          return $viewModel;
         */
        $uploadTable = $this->getServiceLocator()->get('UploadTable');
        $userTable = $this->getServiceLocator()->get('UserTable');

        $uploadId = $this->params()->fromRoute('id');
        $upload = $uploadTable->getUpload($uploadId);
        $userEmail = $this->getAuthService()->getStorage()->read();

        //Shared Users List
        $SharedUsers = array();
        $SharedUsers2 = array();
        $sharedUsersResult = $uploadTable->getSharedUsers($uploadId);
        foreach ($sharedUsersResult as $SharedUser) {
            $user = $userTable->getUser($SharedUser->user_id);
            $SharedUsers[$SharedUser->id] = $user->name;
            $SharedUsers2[$SharedUser->user_id] = $user->name;
        }
        // Add sharing
        $uploadShareForm = $this->getServiceLocator()->get('UploadShareForm');
        $allusers = $userTable->fetchAll();
        $userList = array();
        foreach ($allusers as $user) {
            $name = '';
            foreach ($SharedUsers2 as $id=>$name) {
                if ($id == $user->id) {
                    continue 2;
                }
            }
            //$userList[$user->id] = $user->name;
                        
            if($userEmail != $user->email){
                $userList[$user->id] = $user->name;
            }
            
            
        }
        $uploadShareForm->get('upload_id')->setValue($uploadId);
        $uploadShareForm->get('user_id')->setValueOptions($userList);

        //edit form
        $form = $this->getServiceLocator()->get('UploadEditForm');
        $form->bind($upload);
        $viewModel = new ViewModel(array(
            'form' => $form,
            'upload_id' => $uploadId,
            'sharedUsers' => $SharedUsers,
            'uploadShareForm' => $uploadShareForm
        ));
        return $viewModel;
    }

    public function deleteAction() {
        //$uploadPath = $this->getFileUploadLocation();
        $uploadId = $this->params()->fromRoute('id');
        $uploadTable = $this->getServiceLocator()->get('UploadTable');
        $upload = $uploadTable->getUpload($uploadId);
        $uploadPath = $this->getFileUploadLocation();

        unlink($uploadPath . "/" . $upload->filename);
        $uploadTable->deleteUpload($uploadId);
        //$file = $uploadTable->getUpload($this->params()->fromRoute('id'));
        //unlink($uploadPath . DIRECTORY_SEPARATOR . $file->filename);
        //$uploadTable->deleteUpload($this->params()->fromRoute('id'));

        return $this->redirect()->toRoute(NULL, array(
                    'controller' => 'UploadManager',
                    'action' => 'index',
        ));
    }

    public function getFileUploadLocation() {
        $config = $this->getServiceLocator()->get('config');
        return $config['module_config']['upload_location'];
    }

    public function getAuthService() {
        if (!$this->authservice) {
            $authService = new AuthenticationService();
            $this->authservice = $authService;
        }
        return $this->authservice;
    }

    public function fileDownloadAction() {
        $uploadId = $this->params()->fromRoute('id');
        $uploadTable = $this->getServiceLocator()->get('UploadTable');
        $upload = $uploadTable->getUpload($uploadId);

        $uploadPath = $this->getFileUploadLocation();
        $file = file_get_contents($uploadPath . "/" . $upload->filename);

        $response = $this->getEvent()->getResponse();
        $response->getHeaders()->addHeaders(array(
            'Content-type' => 'application/octet-stream',
            'Content-disposition' => 'attachment;filename="' . $upload->filename . '"',
        ));

        $response->setContent($file);

        return $response;
    }

    public function deleteShareAction() {
        $shareId = $this->params()->fromRoute('id');
        $uploadTable = $this->getServiceLocator()->get('UploadTable');
        $share = $uploadTable->getsharedUpload($shareId);
        $uploadId = $share->upload_id;
        // Delete Records
        $uploadTable->deleteSharedUpload($shareId);
        return $this->redirect()->toRoute('users/upload-manager', array('action' => 'edit', 'id' => $uploadId));
    }

    public function processAddShareAction() {
        $uploadTable = $this->getServiceLocator()->get('UploadTable');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $uploadId = $request->getPost()->get('upload_id');
            $userId = $request->getPost()->get('user_id');
            if ($userId) {
                $uploadTable->addSharing($uploadId, $userId);
                }
            return $this->redirect()->toRoute('users/upload-manager', array('action' => 'edit', 'id' => $uploadId));
        }
    }

}
