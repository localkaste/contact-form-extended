<?php

namespace contactformextended\models;

use contactformextended\validators\ToEmailValidator;
use craft\base\Model;

class Settings extends Model
{
    public $fields = [
        'fromEmail' => [
            'name'    => 'E-Mail',
            'filters' => 'trim|sanitize_email',
            'rules'   => 'required|valid_email'
        ]
    ];

    public $toEmail = [];

    public function rules()
    {
        return [
            [['toEmail'], ToEmailValidator::class]
        ];
    }

    public function getFields(): Array
    {
        $fields = [];

        foreach ( $this->fields as $key => $field )
        {
            if ( isset($field['name']) )
            {
                $fields[$key] = $field['name'];
            }
        }

        return $fields;
    }

    public function getFieldFilters(): Array
    {
        $filters = [];

        foreach ( $this->fields as $key => $field )
        {
            if ( isset($field['filters']) )
            {
                $filters[$key] = $field['filters'];
            }
        }

        return $filters;
    }

    public function getFieldRules(): Array
    {
        $rules = [];

        foreach ( $this->fields as $key => $field )
        {
            if ( isset($field['rules']) )
            {
                $rules[$key] = $field['rules'];
            }
        }

        return $rules;
    }
}
