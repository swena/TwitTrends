<html>
<head>
	<!-- AIzaSyB45rLge0qJX25y20ejv_B9iJG-mHLwt5E -->
    <meta name="viewport" content="initial-scale=1.0">
    <meta charset="utf-8">
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=XXXXXX"></script>
    <script type="text/javascript">
		var map, pointarray, heatmap;
		var latlongData = [];

		var cur_zoom = 2;
		var cur_centre = new google.maps.LatLng(40.52, 4.34);

		var is_bounds_changed = false;

    	function initialize(){

			if(map) cur_zoom = map.getZoom();
    		var latlng = new google.maps.LatLng(40.52, 4.34);

    		var myOptions = {
    				zoom: cur_zoom,
    				center: latlng,
    				mapTypeId: google.maps.MapTypeId.ROADMAP
    			};

    		map = new google.maps.Map(document.getElementById("map_canvas"),
    			myOptions);

    	}
		function createMarker(locs){
			var marker;
			var i;
			var icons = {
			  'positive': {
			    icon: 'images/green.png'
			  },
			  'negative': {
			    icon: 'images/red.png'
			  },
			  'neutral': {
			    icon: 'images/blue.png'
			  },
			  default: {
			  	icon: 'images/lime.png'
			  }
			};
			for(i=0;i<locs.length;i++)
			{
				if(!(locs[i][1]=='' && locs[i][2]=='')) {
					marker = new google.maps.Marker({
						animation: google.maps.Animation.DROP,
						position: new google.maps.LatLng(locs[i][1], locs[i][2]),
						map: map,
						icon: icons[locs[i][3]].icon,
						title: locs[i][0]
					});
				}
			}
		}
	</script>
	<style>
		body{
			background-color: black;
			color:white;
		}
		.heading{
			text-align: center;
		}
		.filters{
			width:15%;
			display: block;
			float:left;
		}
		#map_canvas{
			width:70%;
			display:inline-block;
		}
	</style>
    
</head>
<body>
	<div class="heading"><h1>TwitTrends</h1></div>
	<div class="filters">
		<form name="form" id="form" method="post" action="">
			<h3> Keywords</h3>
			<select name="keyword" id="keyword">
				<option selected="true" value="- Select Keyword -"> Select Keyword  </option>
				<option value="vote" <?php if(isset($_POST['keyword']) && $_POST['keyword']=='vote') echo 'selected=true';?>>Vote</option>
				<option value="love" <?php if(isset($_POST['keyword']) && $_POST['keyword']=='love') echo 'selected=true';?>>Love</option>
				<option value="food" <?php if(isset($_POST['keyword']) && $_POST['keyword']=='food') echo 'selected=true';?>>Food</option>
				<option value="holiday" <?php if(isset($_POST['keyword']) && $_POST['keyword']=='holiday') echo 'selected=true';?>>Holiday</option>
				<option value="sale" <?php if(isset($_POST['keyword']) && $_POST['keyword']=='sale') echo 'selected=true';?>>Sale</option>
				<option value="hollywood" <?php if(isset($_POST['keyword']) && $_POST['keyword']=='hollywood') echo 'selected=true';?>>Hollywood</option>
				<option value="trump" <?php if(isset($_POST['keyword']) && $_POST['keyword']=='trump') echo 'selected=true';?>>Trump</option>
				<option value="president" <?php if(isset($_POST['keyword']) && $_POST['keyword']=='president') echo 'selected=true';?>>President</option>
				<option value="you" <?php if(isset($_POST['keyword']) && $_POST['keyword']=='you') echo 'selected=true';?>>You</option>
			</select>

			<input type="submit" name="submit" id="submit" value="Pin">
		</form>
	</div>
	<div id="map_canvas" style="width:1200px; height:600px;"></div>
<?php
require_once ('init.php');
$params = array();
    $params['hosts'] = array (
        'http://localhost'        // SSL to localhost
    );
$es = new Elasticsearch\Client($params);
if(isset($_POST["submit"]))
	{
		$key = $_POST["keyword"];

		$query = $es->search([
						'index' => 'twittrend',
    					'type' => 'tweet',
			            'body' => [
			                'size'  => 100,
			                'query' => [
			                    'bool' => [
			                        'should' => [
			                            'match' => ['keyword' => $key]
			                        ] 
			                    ]
			                ]
			            ]
			        ]);
		/*print "<pre>";
		print_r($query);
		print "</pre>";*/
		?>


		<script>
		
			initialize();
			var locations = [];
			var j=1;
		<?php
		
		for($i=0;$i<count($query['hits']['hits']);$i++)
	    {

			$lat = $query['hits']['hits'][$i]['_source']['lat'];
			$long = $query['hits']['hits'][$i]['_source']['long'];
			$sentiment = $query['hits']['hits'][$i]['_source']['sentiment'];

			?>
			locations.push(['Tweet'+<?php echo $i;?>, '<?php echo $lat; ?>','<?php echo $long; ?>','<?php echo $sentiment; ?>']);

			j++;
			<?php
		}
		?>
		createMarker(locations);
		</script>
		<?php
	}

?>
