<?php
namespace OrbitSpaceSoft\model;

class TokenModel extends Model
{
    public function tableFields()
    {
        return [
            'id' => [
                'name' => 'id',
                'type' => 'int',
                'primary_key' => true,
                'increment' => true,
                'sort' => 100
            ],
            'token' => [
                'name' => 'token',
                'type' => 'varchar',
                'length' => '150',
                'sort' => 150
            ],
            'refresh' => [
                'name' => 'refresh',
                'type' => 'varchar',
                'length' => '150',
                'sort' => 200
            ],
            'user' => [
                'name' => 'user',
                'type' => 'varchar',
                'length' => '100',
                'def_value' => 'NULL',
                'sort' => 250
            ],
            'active' => [
                'name' => 'active',
                'type' => 'varchar',
                'length' => '1',
                'def_value' => "Y",
                'sort' => 300
            ],
            'date_create' => [
                'name' => 'date_create',
                'type' => 'varchar',
                'length' => '100',
                'sort' => 350
            ],
            'date_refresh' => [
                'name' => 'date_refresh',
                'type' => 'varchar',
                'length' => '100',
                'def_value' => 'NULL',
                'sort' => 400
            ],
        ];
    }

    public $prefix = 'os';
    public function tableName()
    {
        return 'sys_token';
    }

    public function add( $token, $token_refresh, $user = null )
    {
        return $this->insert([
            $this->getField('token') => $token,
            $this->getField('refresh') => $token_refresh,
            $this->getField('date_create') => self::MySQLDate(),
            $this->getField('user') => $user
        ]);
    }
}