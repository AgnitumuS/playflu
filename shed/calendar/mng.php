<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
        <style type="text/css">
    .btn.active {                
	display: none;		
}

.btn span:nth-of-type(1)  {            	
	display: none;
}
.btn span:last-child  {            	
	display: block;		
}

.btn.active  span:nth-of-type(1)  {            	
	display: block;		
}
.btn.active span:last-child  {            	
	display: none;			
}
    </style>

<div id="result"></div>
<?php
$plsid = $_POST["plsid"];
//include 'theme/header.php';
require_once '../../config.php';
$output = '';

//$output.= "ID PLS: ".$_POST["plsid"];

if(isset($_POST["query"]))
{
	$search = mysqli_real_escape_string($link, $_POST["query"]);
	$query = "
	SELECT * FROM media
	WHERE fname LIKE '%".$search."%' ORDER BY id DESC
	";
}
else
{
	$query = "SELECT * FROM media ORDER BY id DESC";
}
$result = mysqli_query($link, $query);
mysqli_set_charset($link,"utf8");
if(mysqli_num_rows($result) > 0)
{
  $name = $row['fname'];
 

	$output .= '<table id="editable_table" class="table table-bordered table-striped">
						<tr>
							<th>#</th>
							<th>Name</th>
							<th>Duration</th>
							<th>On Air</th>
							<th>VOD</th>
							<th>Size</th>
							<th>Encoded</th>
							<th>Local/S3</th>
							<th>Preview</th>
							<th>Erase</th>
						</tr>';
	while($row = mysqli_fetch_array($result))
	{
 $idmedia = $row['id'];
 $duration = $row['duration'];
 $duration_custom = $row['duration_custom'];
		$duration = gmdate("H:i:s", $row['duration']);
     	$output .= '
			<tr>
				<td>'.$row["id"].'</td>
				<td><button id="additem" name="'.htmlspecialchars($row["fname"], ENT_QUOTES).'" value="'.$row["id"].'" class="additem"><i class="fa fa-plus-circle"></i></button> '.$row["fname"];

$filename = '/home/playtube/crud/media/files/'.$row["name"].'';

if (file_exists($filename)) {
    $output .=' ';
} else {
    $output .=' <i class="fas fa-exclamation-triangle">(отсутствует в локальной папке, возможно удален)</i>';
}

	    $output .='</td>
				<td>'.$duration.'</td>
				<td><div data-toggle="buttons">';

         if($row['onair'] !='0')
		{
			//$output .= '<input type="checkbox" name="toggle" id="toggle_'.$row["id"].'" value="'.$row["id"].'" data-toggle="toggle" data-on="Ready" data-off="Not Ready" data-onstyle="success" data-offstyle="default">';

			$output .= '<label class="btn btn-success">
                <input type="radio" name="toggle" value="'.$row["id"].'" id="false">
                <i class="fa fa-play"></i> On air
            </label>  
            <label class="btn btn-default active">
                <input type="radio" name="toggle" value="'.$row["id"].'" id="true">
                <i class="fa fa-pause"></i> Offline
            </label>        
        ';
			
		}


		 if($row['onair'] !='1')
		{
			
			 //$output .= '<input type="checkbox" name="toggle" id="toggle_'.$row["id"].'" value="'.$row["id"].'" data-toggle="toggle" data-on="Ready" data-off="Not Ready" data-onstyle="success" data-offstyle="warning" checked>';
			 $output .= '<label class="btn btn-default">
                <input type="radio" name="toggle" value="'.$row["id"].'" id="true">
                <i class="fa fa-pause"></i> Offline
            </label>  
            <label class="btn btn-success active">
                <input type="radio" name="toggle" value="'.$row["id"].'" id="false">
                <i class="fa fa-play"></i> On air
            </label>  
           ';
			
		}		

		$output .= '
			</div></td>';

			if ($row["vod"] == '1')
            {
			$output .= '<td><i class="fas fas fa-check fa-lg"></i></td>';
			}
			else
			{
			$output .= '<td>none</td>';
			}
			$output .= '<td>'.humanFileSize($row['size']).'</td>';

        


                                            if ($row['encoded'] != '1')
                                             {
                                            $output .= '<td><i class="fas fa-cog fa-spin fa-lg"></i></td>';
                                             }
                                             else 
                                             {
                                            $output .= '<td><i class="fas fas fa-check fa-lg"></i></td>';
                                                   }
                                             if (($row['stored'] == '0') && ($row['encoded'] == '1'))
                                             {
                                            $output .= '<td><a href="addfile-'.$row["id"].'" title="Put in Storage" data-toggle="tooltip"><span class="glyphicon glyphicon-cloud-upload"></span></a>&nbsp;Loc</td>';
                                             }
                                             elseif (($row['stored'] == '1') && ($row['encoded'] == '1'))
                                             {
                                            $output .= '<td>Loc & S3</td>';
                                                   }
                                                   else
                                                   {

                                                    $output .= '<td>Store to cloud is possible after encoding..</td>';
                                                   }
                                                   
                                            $output .= '<td>';

                                                $output .= '<a href="item-'.$row["id"].'" title="View Record data-toggle="tooltip"><span class="glyphicon glyphicon-eye-open"></span></a>';
                                            
                                                //$output .= '<a href="erase-'.$row["id"].'" title="Delete Record" data-toggle="tooltip"><span class="glyphicon glyphicon-trash"></span></a>';

                                            $output .= '</td>';
	    //$output .= '<td>'.$row["encoded"].'</td>
		//		<td>'.$row["stored"].'</td>
		//		<td>some action</td>';
			$output .='</tr>';
	}
	echo $output;
}


?>
     <script>
      $('input[name=toggle]').change(function(){
        var mode=$(this).prop('id');
        var id=$(this).val();
        $.ajax({
          type:'POST',
          dataType:'JSON',
          url:'do_switch.php',
          data:{mode:mode,id:id},
          success:function(data)
          {
            var data=eval(data);
            message=data.message;
            success=data.success;
            $("#heading").html(success);
            $("#body").html(message);
          }
        });
      });
    </script>
<script>  
 $('.additem').click( function() {
  var idmedia =$(this).attr("value");
  var name = $(this).attr("name");
  var idpls = '<?php echo $plsid; ?>';
  var duration ='<?php echo $duration; ?>';
  var duration_custom ='<?php echo $duration_custom; ?>';

         $.ajax({
  method: "POST",
  url: "additem.php",
  data: {idmedia:idmedia,name:name,idpls:idpls}
})
  .done(function( msg ) {
    $.notify(msg);
     reload_div();
 
  });

    }); 
 </script>

