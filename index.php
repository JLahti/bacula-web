<?php
/* 
+-------------------------------------------------------------------------+
| Copyright (C) 2004 Juan Luis Franc�s Jim�nez							  |
| Copyright 2010-2011, Davide Franco			                          |
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

	try{
		$dbSql = new Bweb();
	}catch( CErrorHandler $e  ) {
		$e->raiseError();
    }
	

	// Stored files number 
	$dbSql->tpl->assign('stored_files', number_format($dbSql->getStoredFiles( FIRST_DAY, NOW ), 0, '.', "'" ) );
	  
	// Database size
	$dbSql->tpl->assign('database_size', $dbSql->getDatabaseSize());

	// Overall stored bytes
	$stored_bytes = CUtils::Get_Human_Size( $dbSql->getStoredBytes( FIRST_DAY, NOW ) );
	$dbSql->tpl->assign('stored_bytes', $stored_bytes);

	// Total bytes and files for last 24 hours
	$dbSql->tpl->assign('bytes_last', CUtils::Get_Human_Size( $dbSql->getStoredBytes( LAST_DAY, NOW ) ) );
	$dbSql->tpl->assign('files_last', number_format($dbSql->getStoredFiles( LAST_DAY, NOW ), 0, '.', "'" ) );

	// Number of clients
	$nb_clients = $dbSql->Get_Nb_Clients();
	$dbSql->tpl->assign('clientes_totales',$nb_clients["nb_client"] );

	// Backup Job list
	$dbSql->tpl->assign( 'jobs_list', $dbSql->getJobsName() );
	
	// Clients list

	$dbSql->tpl->assign( 'clients_list', $dbSql->getClients() );

	// Last 24 hours status (completed, failed and waiting jobs)
	$dbSql->tpl->assign( 'completed_jobs', $dbSql->countJobs( LAST_DAY, NOW, 'completed' ) );
	$dbSql->tpl->assign( 'failed_jobs', $dbSql->countJobs( LAST_DAY, NOW, 'failed' ) );
	$dbSql->tpl->assign( 'waiting_jobs', $dbSql->countJobs( LAST_DAY, NOW, 'waiting' ) );
	$dbSql->tpl->assign( 'canceled_jobs', $dbSql->countJobs( LAST_DAY, NOW, 'canceled' ) );

	// Last 24 hours jobs Level
	$dbSql->tpl->assign( 'incr_jobs', $dbSql->countJobs( LAST_DAY, NOW, 'ALL', J_INCR) );
	$dbSql->tpl->assign( 'diff_jobs', $dbSql->countJobs( LAST_DAY, NOW, 'ALL', J_DIFF) );
	$dbSql->tpl->assign( 'full_jobs', $dbSql->countJobs( LAST_DAY, NOW, 'ALL', J_FULL) );

	// Last 24 hours Job status graph
	$jobs_status_data = array();
	$jobs_status 	  = array( 'Running', 'Completed', 'Failed', 'Canceled', 'Waiting' );

	foreach( $jobs_status as $status )
		$jobs_status_data[] = array( $status, $dbSql->countJobs(LAST_DAY, NOW, $status) );

	$graph = new CGraph( "graph.png" );
	$graph->SetData( $jobs_status_data, 'pie', 'text-data-single' );
	$graph->SetGraphSize( 260, 180 );

	$graph->Render();
	$dbSql->tpl->assign('graph_jobs', $graph->Get_Image_file() );
	unset($graph);

	// Volumes by pools graph
	$vols_by_pool = array();
	$graph 	      = new CGraph( "graph1.png" );

	foreach( $dbSql->getPools() as $pool )
		$vols_by_pool[] = array( $pool['name'], $dbSql->countVolumes( $pool['poolid'] ) );

	$graph->SetData( $vols_by_pool, 'pie', 'text-data-single' );
	$graph->SetGraphSize( 260, 180 );

	$graph->Render();
	$dbSql->tpl->assign('graph_pools', $graph->Get_Image_file() );

	// Last 7 days stored Bytes graph
	$days_stored_bytes 	= array();
	$days = CTimeUtils::getLastDaysIntervals( 7 );
	
	foreach( $days as $day ) {
		$stored_bytes 		 = $dbSql->getStoredBytes( $day['start'], $day['end']);
		$stored_bytes 		 = CUtils::Get_Human_Size( $stored_bytes, 1, 'GB', false );
		$days_stored_bytes[] = array( date("m-d", $day['start']), $stored_bytes );  
	}

	$graph = new CGraph( "graph2.png" );
	$graph->SetData( $days_stored_bytes, 'bars', 'text-data' );
	$graph->SetGraphSize( 260, 180 );
	$graph->SetYTitle( "GB" );

	$graph->Render();
	$dbSql->tpl->assign('graph_stored_bytes', $graph->Get_Image_file() );


	// Last used volumes
	$last_volumes = array();
	
	try{
		$query  = "SELECT Media.MediaId, Media.Volumename, Media.Lastwritten, Media.VolStatus, Pool.Name as poolname FROM Media ";
		$query .= "LEFT JOIN Pool ON Media.PoolId = Pool.poolid ";
		$query .= "WHERE Media.Volstatus != 'Disabled' ";
		$query .= "AND Media.VolJobs > 0 ";
		$query .= "ORDER BY Media.Lastwritten DESC ";		
		$query .= "LIMIT 10";
		
		// Run the query
		$result = $dbSql->db_link->runQuery( $query );
			
		foreach( $result->fetchAll() as $volume ) {
			$query 				  = "SELECT COUNT(*) as jobs_count FROM JobMedia WHERE JobMedia.MediaId = '" . $volume['mediaid'] . "'";
			$jobs_by_vol 		  = $dbSql->db_link->runQuery($query);
			$jobs_by_vol 		  = $jobs_by_vol->fetchAll();
			
			// Volumes details
			$volume['jobs_count'] = $jobs_by_vol[0]['jobs_count'];
			
			// odd or even row
			if( (count($last_volumes) % 2) > 0 )
				$volume['odd_even'] = "odd";
			
			$last_volumes[] 	  = $volume;
		}
	}catch( CErrorHandler $e  ) {
		$e->raiseError();
	}

	$dbSql->tpl->assign( 'volumes_list', $last_volumes );	

	// Render template
	$dbSql->tpl->display('index.tpl');
?>
