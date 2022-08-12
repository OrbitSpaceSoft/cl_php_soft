<?php
namespace OrbitSpaceSoft\model;

abstract class Model extends \OrbitSpaceSoft\BaseObject
{
    const COUNT_ELEMENT = 20;
    const PAGE = '_page';
    const WHERE_CONDITION = 'condition';
    const WHERE_CONDITION_OR = 'condition_or';
    const WHERE_CONDITION_AND = 'condition_and';

    const DEFAULT_WHERE_CONDITION = 'IN';

    const DEF_SORT = 'DESC';

    public $totalPages;
    public $totalCount;

    protected $db_config;

    public $db;
    public $prefix;

    public function setTrace()
    {
        if( is_object($this->db) && method_exists($this->db, 'setTrace') )
            $this->db->setTrace(true);

        return $this;
    }

    public function getTrace()
    {
        return is_object($this->db) ? $this->db->trace : null;
    }

    protected function createTable():bool
    {
        $sql = 'CREATE TABLE '.$this->getTableName().' (';

        $arF=[];$i=1;$p_k=null;
        foreach ( $this->tableFields() as $code => $field )
        {
            if( $field['name'] === in_array( $code, $arF ) || !isset($field['type']) )
                continue;

            $sql .= $code.' '.$field['type'].' '.(isset($field['type']) ? (isset($field['length'])?'('.$field['length'].')':''):'');

            if(!isset($field['def_value']))
                $sql .= ' NOT NULL ';
            else
            {
                if( in_array($field['def_value'],['NULL','null']) )
                    $sql .= ' NULL ';
                else
                    $sql .= " DEFAULT '".$field['def_value']."' ";

            }

            $sql .= isset($field['increment']) && $field['increment']?' AUTO_INCREMENT':'';

            if( count($this->tableFields()) !== $i )
                $sql .= ',';

            if( is_null($p_k) )
                $p_k = isset($field['primary_key']) && $field['primary_key'] ? ', PRIMARY KEY ('.$code.')' :'';

            $arF[] = $code;
            $i++;
        }

        if( $p_k )
            $sql .= $p_k;
        $sql .= ');';

        if( is_object($this->db) )
        {
            if( $this->db->mysqli()->query( $sql ) !== true )
            {
                $this->last_error = "create table error: " . $this->db->getLastError();
                return false;
            }
            return true;
        }
        $this->last_error = "create table error: empty object";
        return false;
    }

    public function connect( $connection_name = 'default' ): ?Model
    {
       if( !isset($this->config->db) && isset($this->config->db->{$connection_name}) )
           $this->last_error = 'DB config is not found';

       $this->db_config = $this->config->db[$connection_name];
       $this->db = $this->_connect( $this->db_config );

       if( is_object( $this->db ) && !$this->checkTable( $this->getTableName() ) )
       {
           if( !$this->createTable() )
               return null;
       }

       return $this;
    }

    public function getField( $field_id, $key=null )
    {
        $key = is_null($key) ? 'name' : $key;
        return isset( $this->tableFields()[$field_id][$key] ) ? $this->tableFields()[$field_id][$key] : false;
    }

    abstract public function tableFields();
    abstract public function tableName();

    public function getTableName()
    {
        return (!isset($this->prefix) || empty($this->prefix)?'':$this->prefix.'_').$this->tableName();
    }

    public function customiseFieldsArray( array $params )
    {
        $ins_params = $filter = [];
        foreach ( $this->tableFields() as $field => $arField )
        {
            if( isset($params[$field]) )
                $ins_params[ $arField['name'] ] = $params[$field];

            if( isset($params[ $arField['name'] ]) )
                $ins_params[ $arField['name'] ] = $params[ $arField['name'] ];
        }

        foreach ($params as $key => $param)
        {
            if( is_int( $key ) || $key === self::WHERE_CONDITION || $key === self::WHERE_CONDITION_OR )
            {
                switch ($key)
                {
                    case self::WHERE_CONDITION:
                        if( is_array( $param ) && count( $param ) === 3 )
                            $ins_params[self::WHERE_CONDITION][] = $param;
                        break;

                    case self::WHERE_CONDITION_OR:
                        if( is_array( $param ))
                        {
                            foreach ($param as $_k => $_itm)
                            {
                                if( is_int( $key ) || $key === self::WHERE_CONDITION )
                                {
                                    if( is_array( $_itm ) && count( $_itm ) === 3 )
                                        $ins_params[self::WHERE_CONDITION_OR][] = $_itm;
                                }
                                elseif( $this->getField($_k) )
                                    $ins_params[self::WHERE_CONDITION_OR][$_k] = $_itm;
                            }
                        }
                        break;

                    default: break;
                }
            }

            if( !is_int( $key ) && in_array( $key, [ self::PAGE ] ) )
                $ins_params[ $key ] = $param;

            if( $this->getField($key) && $this->getField($key,'type') === 'integer' )
                $ins_params[ $key ] =  empty($param) ? NULL : ( is_string($param) ? (int) $param :  $param );
        }

        return $ins_params;
    }

    public function onlyTableFields( array $params)
    {
        $ins_params = [];
        foreach ( $this->tableFields() as $field => $arField )
        {
            if( isset($params[$field]) )
                $ins_params[ $arField['name'] ] = $params[$field];

            if( isset($params[ $arField['name'] ]) )
                $ins_params[ $arField['name'] ] = $params[ $arField['name'] ];
        }

        return $ins_params;
    }

    public function customizeConditions( array $where): bool
    {
        if( count($where) )
        {
            if( isset($where['orderBy'] ) )
            {
                if( is_array($where['orderBy']) )
                {

                    if( !is_array($where['orderBy'][0] ) )
                        $this->db->orderBy(
                            $where['orderBy'][0],
                            (isset($where['orderBy'][1])?$where['orderBy'][1]:self::DEF_SORT),
                            (isset($where['orderBy'][2]) && is_array($where['orderBy'][2]) ?$where['orderBy'][2]:null)
                        );
                    else
                    {
                        foreach ($where['orderBy'] as $order)
                        {
                            $this->db->orderBy(
                                $order[0],
                                (isset($order[1])?$order[1]:self::DEF_SORT),
                                (isset($order[2]) && is_array($order[2]) ?$order[2]:null)
                            );
                        }
                    }
                }

                unset($where['orderBy']);
            }

            //[ $model::WHERE_CONDITION => [ [ field, array, condition ] ] ]
            //or [ field => [ condition => array] ]
            //or [ field => value ]

            foreach ($where as $k => $v)
            {
                if( $k === self::WHERE_CONDITION )
                {
                    foreach ( $v as $_v_v )
                        $this->db->where($_v_v[0],$_v_v[1],$_v_v[2]);
                }
                elseif ( $k === self::WHERE_CONDITION_OR )
                {
                    $_q = '( ';
                    $ii=1;

                    foreach ($v as $_or_k => $_or_v )
                    {
                        if( is_array($_or_v) )
                        {
                            ////example (self::WHERE_CONDITION, Array(1, 5, 27, -1, 'd'), 'IN');
                            if( $_or_v[0] === self::WHERE_CONDITION )
                            {
                                if (in_array($_or_v[2], ['>', '<', '>=', '<=', '<>', '!=', '=', '<=>']))
                                    $_q .= $_or_k . " " . $_or_v[2] . " " . (int)$_or_v[1];
                                else {
                                    if (in_array(mb_strtoupper($_or_v[2]), ['IN', 'NOT IN']))
                                        $_q .= $_or_k . " " . mb_strtoupper($_or_v[2]) . " ("
                                            . (is_array($_or_v[1])
                                                ? ("'" . implode("','", $_or_v[1]) . "'")
                                                : ("'" . $_or_v[1] . "'")
                                            ) .")";
                                    else
                                        $_q .= $_or_k . " " . mb_strtoupper($_or_v[2]) . " "
                                            . (is_null($_or_v[1])
                                                ? 'NULL'
                                                : ("'" . $_or_v[1] . "'")
                                            ) . " ";
                                }
                            }
                            else
                                $_q .= $_or_k.' IN '.("('" . implode("','", $_or_v) . "')");
                        }

                        else
                            $_q .= $_or_k." = '".$_or_v."'";

                        if( $ii !== count($v) )
                            $_q .= ' and ';
                        $ii++;
                    }
                    $_q .= ' )';

                    $this->db->orWhere($_q);
                }
                else
                {
                    if( !is_array($v) )
                        $this->db->where($k, $v);
                    else
                        $this->db->where($k, $v, static::DEFAULT_WHERE_CONDITION);
                }
            }
            return true;
        }
        return false;
    }

    protected function _connect( array $config )
    {
        return new \MysqliDb(
            [   'host' => $config['host'],
                'username' => $config['username'],
                'password' => $config['password'],
                'db'=> $config['database'],
                'port' => $config['port'],
                'charset' => $config['charset']
            ]);
    }

    protected function checkTable ( $name=false )
    {
        if( !$name || is_null($name) ) return false;
        return !is_null( $this->db->rawQueryValue("SHOW TABLES FROM ".$this->db_config['database']." like '".$name."'") );
    }

    public function checkRequiredFields( array $fields )
    {
        if( !is_array($this->tableFields()) || !count($this->tableFields()) )
            return true;

        $arNames = [];
        foreach ($this->tableFields() as $key => $field)
        {
            if( !in_array($field['name'], $arNames ) )
                $arNames[] = $field['name'];
            else continue;

            if( isset($field['required']) && $field['required'] )
            {
                if( !isset($fields[$key]) || empty(trim($fields[$key])) || strlen(trim($fields[$key])) === 0 )
                {
                    $this->last_error = 'field '.$key.' is required';
                    return false;
                }
            }
        }

        return true;
    }

    public function issetElement(array $params, $return_id=false)
    {
        $inf = $this->select( $params, 1, [$this->getField('id')] );
        return $inf ? ($return_id ? $inf[0][$this->getField('id')] : true ) : false;
    }

    public static function DateFormat()
    {
        return 'Y-m-d H:i:s';
    }

    public static function MySQLDate()
    {
        return date( self::DateFormat() );
    }

    public function transactionStart()
    {
        $this->db->startTransaction();
        return $this;
    }

    public function transactionRollback()
    {
        $this->db->rollback();
        return $this;
    }

    public function transactionCommit()
    {
        $this->db->commit();
        return $this;
    }

    public function insert(array $data, $multi=false, $table=null)
    {
        if( !is_null($table) )
        {
            if( !$this->checkTable( $table ) )
            {
                $this->last_error = 'Table '.$table.' no exists!';
                return false;
            }
        }
        else $table = $this->getTableName();

        $method = !$multi ? 'insert' : 'insertMulti';
        if( !$res = $this->db->{$method}( $table, $data) )
        {
            $this->last_error = $this->db->getLastError();
            return false;
        }
        return $res;
    }

    public function update( array $where=[], array $data, $count=1, $table=null)
    {
        if( !is_null($table) )
        {
            if( !$this->checkTable( $table ) )
            {
                $this->last_error = 'Table '.$table.' no exists!';
                return false;
            }
        }
        else $table = $this->getTableName();

        $this->customizeConditions($where);

        if( !$res = $this->db->update( $table, $data, $count) )
        {
            $this->last_error = $this->db->getLastError();
            return false;
        }
        return $res;
    }

    public function select( array $where=[], $count=self::COUNT_ELEMENT, array $cols=[], $table=null )
    {
        if( !is_null($table) )
        {
            if( !$this->checkTable( $table ) )
            {
                $this->last_error = 'Table '.$table.' no exists!';
                return false;
            }
        }
        else $table = $this->getTableName();

        $select_page = false;
        if( isset($where[self::PAGE]) && $select_page = (int) $where[self::PAGE] )
        {
            $this->db->pageLimit = $count;
            unset($where[self::PAGE]);
        }

        $this->customizeConditions($where);

        if( $select_page ) {
            $res = $this->db->withTotalCount()->arraybuilder()->paginate($this->getTableName(), $select_page);
            $this->totalPages = $this->db->totalPages;
            $this->totalCount = (int) $this->db->totalCount;
        }
        else
        {
            $res = $this->db->withTotalCount()->get( $table, $count, $cols );
            $this->totalCount = (int) $this->db->totalCount;
        }

        if( $this->db->count > 0 )
            return $res;

        $this->last_error = $this->db->getLastError();

        return false;
    }

    public function delete(  array $where, $table=null )
    {
        if( !count($where) )
            return false;

        if( !is_null($table) )
        {
            if( !$this->checkTable( $table ) )
            {
                $this->last_error = 'Table '.$table.' no exists!';
                return false;
            }
        }
        else $table = $this->getTableName();

        $this->customizeConditions($where);

        if( $this->db->delete( $table ) )
            return true;

        $this->last_error = $this->db->getLastError();

        return false;
    }
}