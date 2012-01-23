<?php
/*
+-------------------------------------------------------------------------+
| Copyright 2010-2011, Davide Franco                                              |
|                                                                         |
| This program is free software; you can redistribute it and/or           |
| modify it under the terms of the GNU General Public License             |
| as published by the Free Software Foundation; either version 2          |
| of the License, or (at your option) any later version.                  |
|                                                                         |
| This program is distributed in the hope that it will be useful,         |
| but WITHOUT ANY WARRANTY; without even the implied warranty of          |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
| GNU General Public License for more details.                            |
+-------------------------------------------------------------------------+
*/
  session_start();
  include_once( 'config/global.inc.php' );

  $dbSql = new Bweb();

  $jobid = 0;
  $query = "";
  $log_lines 	= array();

  // ===============================================================
  // Get Job ID from GET or POST
  // ===============================================================
  $http_post = CHttpRequest::getRequestVars( $_POST );
  $http_get  = CHttpRequest::getRequestVars( $_GET );

  // Backup job name
  if( isset( $http_post['jobid'] ) )
    $jobid = $http_post['jobid'];
  elseif( isset( $http_get['jobid'] ) )
    $jobid = $http_get['jobid'];
  else
    die( "Please specify a job id " );


  $query            = "SELECT Time, LogText FROM Log WHERE JobId=$jobid ORDER BY Time";

  try {
    $result = $dbSql->db_link->runQuery( $query );

    foreach( $result->fetchAll() as $log )
    {
       $log['message'] = nl2br($log['logtext']);
       array_push( $log_lines, $log);
    } // end while
  }catch( CErrorHandler $e  ) {
    $e->raiseError();
  }
  $dbSql->tpl->assign( 'log_lines', $log_lines );
  $dbSql->tpl->display('log.tpl');
?>