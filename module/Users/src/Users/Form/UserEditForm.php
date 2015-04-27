<?php

namespace Users\Form;

use Zend\Form\Form;

class UserEditForm extends Form {
    public function __construct($name = null) {
        parent::__construct('UserEdit');
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'type' => 'hidden',
            'name' => 'id',
        ));
        
        $this->add(array(
            'name' => 'name',
            'atributes' => array(
                'type' => 'text',
            ),
            'options' => array(
                'label' => 'Full Name',
            ),
        ));
        
        $this->add(array(
            'name' => 'email',
            'attributes' => array(
                'type' => 'email',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Email',
            ),
            'filters' => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
            'validators' => array(
                array(
                    'name' => 'EmailAddress',
                    'options' => array(
                        'messages' => array(
                        \Zend\Validator\EmailAddress::INVALID_FORMAT => 'Email address format is invalid'
                        ),
                    ),
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Edit',
            ),
        ));
    }
}

