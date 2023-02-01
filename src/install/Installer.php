<?php
namespace OrbitSpaceSoft\install;

use OrbitSpaceSoft\model;

class Installer extends BaseObject
{
    public function firstInstallation( bool $check_log = true):bool
    {
        if( $check_log && $this->checkLog() )
        {
            $this->last_error = 'installation has already been completed';
            return false;
        }

        $model['token'] = new model\TokenModel($this->config);

        if( $model['token']->connect() === null )
        {
            $this->last_error = $model['token']->last_error;
            return false;
        }

        if( !$model['token']->checkTable() )
        {
            $model['token']->transactionStart();
            if($model['token']->createTable())
            {
                $model['token']->transactionCommit();
                $this->writeLog('create Model TokenModel', 'Create table '.$model['token']->getTableName());
            }
            else
            {
                $model['token']->transactionRollback();
                $this->last_error = $model['token']->last_error;
                return false;
            }
        }

        $this->createLog();
        return true;
    }
}