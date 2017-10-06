<?php
    /**
        Quick'n'Dirty mssql to sqlsrv functions mapping
        By Nicolas VOISIN (danifty@gmail.com)
        On October 6, 2017
    **/
    define('SERVER','<MSSQL_HOST>');
    define('DB_NAME','<DATABASE_NAME>');
    define('DB_USER','<SQL_USER>');
    define('DB_PWD','<SQL_PWD>');

    define('SQLVARCHAR', SQLSRV_SQLTYPE_VARCHAR("max"));
    define('SQLINT4', SQLSRV_SQLTYPE_INT);
    
    $statement=array();
    $sqlsrv_params=array();
	function mssql_query($query, $resource=null) { 
        global $sqlsrv_params;
        if (empty($resource)) { $resource=DB_CONNECTION; }; 
        if (!isset($sqlsrv_params[intval($resource)]) || (count($sqlsrv_params[intval($resource)])==0)) { 
            return sqlsrv_query($resource, $query, array(), array("Scrollable"=>"static")); 
        } else {
            $stmt=sqlsrv_query($resource, $query, $sqlsrv_params[intval($resource)], array("Scrollable"=>"static"));}  
            $stmt="";
        }
	function mssql_close($resource) { if (empty($resource)) { $resource=DB_CONNECTION; }; return sqlsrv_close($resource); }
	function mssql_pconnect($servername, $username, $password) { return mssql_connect($servername, $username, $password); }
	function mssql_connect($servername, $username, $password) { 
		if (!defined('DB_CONNECTION'))
		{
			$resource=sqlsrv_connect($servername, array( "Database"=> "master", "UID"=>$username, "PWD"=>$password));
			if( !$resource) { die(print_r(sqlsrv_errors(),true)); }
			define('DB_CONNECTION',$resource);
		}
		return DB_CONNECTION;
	}
	function mssql_select_db($database_name, $resource) { if (empty($resource)) { $resource=DB_CONNECTION; }; return sqlsrv_query($resource, 'USE '.$database_name); }
	function mssql_fetch_array($resource) {  return sqlsrv_fetch_array($resource,SQLSRV_FETCH_NUMERIC); }
	function mssql_fetch_assoc($resource) {  return sqlsrv_fetch_array($resource,SQLSRV_FETCH_ASSOC); }
	function mssql_fetch_row($resource,$fetchType=null) {  return sqlsrv_fetch_array($resource,SQLSRV_FETCH_BOTH); }
	function mssql_num_rows($resource) {  return intval(sqlsrv_num_rows($resource)); }
    function mssql_init($sp, $resource) { global $sqlsrv_params, $statement; if (empty($resource)) { $resource=DB_CONNECTION; }; $resId=sqlsrv_prepare($resource, $sp); $statement[intval($resId)]=$sp; if (!isset($sqlsrv_params[intval($resId)])){$sqlsrv_params[intval($resId)]=array();}; return $resId;}
	function mssql_bind($resource, $param_name, $val, $type) { global $sqlsrv_params;  array_push( $sqlsrv_params[intval($resource)], array($param_name, $val , SQLSRV_PARAM_INOUT, $type)); }
    function mssql_execute($stmt) {
        global $sqlsrv_params, $statement;
        if ((count($sqlsrv_params[intval($stmt)])>0))
        {   $count=count($sqlsrv_params[intval($stmt)]);
            $data=array();
            foreach( $sqlsrv_params[intval($stmt)] as $param) { $data[]="'".str_replace("'","''",$param[1])."'"; }
            $statement[intval($stmt)]="EXEC ".$statement[intval($stmt)]." ".implode(',',$data);
            
            $stmt=sqlsrv_prepare(DB_CONNECTION, $statement[intval($stmt)]);
        }
        sqlsrv_execute($stmt); 
        return $stmt;
    }
    function mssql_data_seek($stmt, $seekpos=0){
        sqlsrv_execute($stmt);
        return $stmt;
    }
    function mssql_errors() {  	return sqlsrv_errors(); }


?>
