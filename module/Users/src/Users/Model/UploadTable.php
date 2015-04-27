<?php

namespace Users\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class UploadTable {

    protected $tableGateway;
    protected $uploadSharingTableGateway;

    public function __construct(TableGateway $tableGateway, TableGateway $uploadSharingTableGateway) {
        $this->tableGateway = $tableGateway;
        $this->uploadSharingTableGateway = $uploadSharingTableGateway;
    }

    public function fetchAll() {
        $resultSet = $this->tableGateway->select();

        return $resultSet;
    }

    public function saveUpload(Upload $upload) {
        $data = array(
            'filename' => $upload->filename,
            'label' => $upload->label,
            'user_id' => $upload->user_id,
        );

        $id = (int) $upload->id;

        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getUpload($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Upload ID does not exist');
            }
        }
    }

    public function getUpload($id) {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();

        if (!$row) {
            throw new \Exception("Could not find row $id");
        }

        return $row;
    }

    public function getUploadsByUserId($userId) {
        $userId = (int) $userId;
        $rowset = $this->tableGateway->select(array('user_id' => $userId));

        return $rowset;
    }

    public function deleteUpload($id) {
        $this->tableGateway->delete(array('id' => $id));
    }

    public function addSharing($uploadId, $userId) {
        $data = array(
            'upload_id' => (int) $uploadId,
            'user_id' => (int) $userId,
        );

        $this->uploadSharingTableGateway->insert($data);
    }

    public function removeSharing($uploadId, $userId) {
        $data = array(
            'upload_id' => (int) $uploadId,
            'user_id' => (int) $userId,
        );

        $this->uploadSharingTableGateway->delete($data);
    }

    public function getSharedUsers($uploadId) {
        $uploadId = (int) $uploadId;
        $rowset = $this->uploadSharingTableGateway->select(array('upload_id' => $uploadId));

        return $rowset;
    }

    public function getSharedUploadsForUserId($userId) {
        $userId = (int) $userId;

        $rowset = $this->uploadSharingTableGateway->select(
                function (Select $select) use ($userId) {
            $select->columns(array())
                    ->where(array('uploads_sharing.user_id' => $userId))
                    ->join('uploads', 'uploads_sharing.upload_id = uploads.id');
        }
        );

        return $rowset;
    }

    public function getSharedUpload($id) {
        $id = (int) $id;
        $rowset = $this->uploadSharingTableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find upload sharing $id");
        }
        return $row;
    }

    public function deleteSharedUpload($id) {
        $this->uploadSharingTableGateway->delete(array('id' => $id));
    }

}
