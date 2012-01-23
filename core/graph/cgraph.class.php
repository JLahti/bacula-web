<?php
/* 
+-------------------------------------------------------------------------+
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

class CGraph{
	private $title;
	private $ytitle;
	
	private $data;
	private $data_type;
	private $type;
	
	private $colors;
	private $shading;
	
	private $width;
	private $height;
	private $output_file;
	private $plot;
	
	function __construct( $filename = "graph.png" )
	{
		$this->output_file = 'templates_c/' . $filename;
	}
	
	public function SetData( $data_in, $type, $data_type, $shading = 5 )
	{
		$this->data 		= $data_in;
		$this->type 		= $type;
		$this->data_type 	= $data_type;
		$this->shadding 	= $shading;
	}
	
	public function SetGraphSize( $width, $height )
	{
		$this->width  = $width;
		$this->height = $height;
	}
	
	public function SetTitle( $title )
	{
		if( !empty($title) )
			$this->title = $title;
		else
			die( "Please provide a non empty title for the graph" );
	}
	
	public function SetYTitle( $ytitle )
	{
		if( !empty($ytitle) )
			$this->ytitle = $ytitle;
		else
			die( "Please provide a non empty title for the Y axis" );
	}
	
	public function SetColors( $colors )
	{
		if( is_array( $colors ) )
			$this->colors = $colors;
		else
			die( "Please provide a array in BGraph->SetColors()" );
	}
	
	public function Get_Image_file()
	{
		return $this->output_file;
	}
	
	public function Render()
	{
		// Setting the size
		$this->plot = new PHPlot( $this->width, $this->height );
		
		// Render to file instead of screen
		$this->plot->SetOutputFile( $this->output_file );
		$this->plot->SetFileFormat("png");
		$this->plot->SetIsInline( true );
		
		
		$this->plot->SetImageBorderType('plain');

		// Data, type and data type
		$this->plot->SetPlotType( $this->type );
		$this->plot->SetDataType( $this->data_type );
		$this->plot->SetDataValues( $this->data );
		
		// Plot colors
		$this->plot->SetDataColors( $this->colors );
		
		// Plot shading
		$this->plot->SetShading( $this->shading );
		
		// Image border
		$this->plot->SetImageBorderType( 'none' );

		switch( $this->type )
		{
			case 'pie':
				$this->plot->SetPlotAreaPixels( 10, 10, ($this->width / 2), $this->height-10 );
				$this->plot->SetLabelScalePosition( 0.2 );
				
				$legends = array();
				foreach( $this->data as $key => $legend )
					$this->plot->SetLegend( implode(': ',$legend) );
			break;
			case 'bars':
				$this->plot->SetXLabelAngle(90);
			break;
		}
		
		// Legend position (calculated regarding the width and height of the graph)
		$this->plot->SetLegendPixels( ($this->width / 2) + 10, 25 );
		
		// Graph title
		$this->plot->SetTitle( $this->title );
		$this->plot->SetYTitle( $this->ytitle );

		# Turn off X tick labels and ticks because they don't apply here:
		$this->plot->SetXTickLabelPos('none');
		$this->plot->SetXTickPos('none');
		$this->plot->SetPlotAreaWorld(NULL, 0, NULL, NULL);


		$this->plot->DrawGraph();
	} // end function Render()
} // end BGraph classe
?>
