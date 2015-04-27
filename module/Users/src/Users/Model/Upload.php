<?php

namespace Users\Model;

class Upload {
    public $id;
    public $filename;
    public $label;
    public $user_id;
    
    public function exchangeArray($data) {
        $this->id = (isset($data['id'])) ? $data['id'] : NULL;
        $this->filename = (isset($data['filename'])) ? $data['filename'] : NULL;
        $this->label = (isset($data['label'])) ? $data['label'] : NULL;
        $this->user_id = (isset($data['user_id'])) ? $data['user_id'] : NULL;
    }
    
    public function getArrayCopy() {
        return get_object_vars($this);
    }
    
}

