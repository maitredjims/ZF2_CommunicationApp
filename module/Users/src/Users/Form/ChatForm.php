<?php

namespace Users\Form;

use Zend\Form\Form;

class ChatForm extends Form {
    public function __construct($name = null) {
        parent::__construct('Chat');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');
        
        $this->add(array(
            'name' => 'message',
            'attributes' => array(
                'type' => 'text',
                'id' => 'messageText',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Message',
            ),
        ));
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'send',
            ),
        ));
        
        $this->add(array(
            'name' => 'refresh',
            'attributes' => array(
                'type' => 'button',
                'id' => 'bntRefresh',
                'value' => 'Refresh',
            ),
        ));
    }
}

