
<html>
<head>
    <title>Yelp</title>
    <script src="http://maps.googleapis.com/maps/api/js"></script>	
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
	<link rel="stylesheet" type="text/css" href="sample.css" />	    

	  
</head>
<body>


<div id='searchrestaurant'>
	<form method='post'>
  	Find: <input type="text" name="find" id='find' placeholder='Tacos, Spaghetti, Fried Food'>
  	Near: <input type="text" name="near" id='near' placeholder='Address, Neighborhood, City, State'>
  	#ofLocations: <input type="text" name="number" id='number' placeholder='1,2,3,4'>
  	<input type="submit" id='submit' name="submityelpsearch" value="Find Locations">
  	<br>
  	<br>
	</form>
</div>


<div id='listofbusiness'>

<?php
/**
 * Yelp API v2.0 code sample.
 *
 * This program demonstrates the capability of the Yelp API version 2.0
 * by using the Search API to query for businesses by a search term and location,
 * and the Business API to query additional information about the top result
 * from the search query.
 * 
 * Please refer to http://www.yelp.com/developers/documentation for the API documentation.
 * 
 * This program requires a PHP OAuth2 library, which is included in this branch and can be
 * found here:
 *      http://oauth.googlecode.com/svn/code/php/
 * 
 * Sample usage of the program:
 * `php sample.php --term="bars" --location="San Francisco, CA"`
 */
// Enter the path that the oauth library is in relation to the php file
require_once('lib/OAuth.php');
// Set your OAuth credentials here  
// These credentials can be obtained from the 'Manage API Access' page in the
// developers documentation (http://www.yelp.com/developers)


$CONSUMER_KEY = 'MGt8i5_bU5IHsJAg_-TfWg';
$CONSUMER_SECRET = 'G0sewYuint0R-dMZ84O1lDHlIz4';
$TOKEN = 'I7vX3pkLaGcFhQyZFlPOdXq_7wN8pz9g';
$TOKEN_SECRET = 'pgSrKj7I9ngcEH_5ESXsQcK0Cdg';
$API_HOST = 'api.yelp.com';

$SEARCH_PATH = '/v2/search/';
$BUSINESS_PATH = '/v2/business/';
/** 
 * Makes a request to the Yelp API and returns the response
 * 
 * @param    $host    The domain host of the API 
 * @param    $path    The path of the APi after the domain
 * @return   The JSON response from the request      
 */
function request($host, $path) {
    $unsigned_url = "https://" . $host . $path;
    // Token object built using the OAuth library
    $token = new OAuthToken($GLOBALS['TOKEN'], $GLOBALS['TOKEN_SECRET']);
    // Consumer object built using the OAuth library
    $consumer = new OAuthConsumer($GLOBALS['CONSUMER_KEY'], $GLOBALS['CONSUMER_SECRET']);
    // Yelp uses HMAC SHA1 encoding
    $signature_method = new OAuthSignatureMethod_HMAC_SHA1();
    $oauthrequest = OAuthRequest::from_consumer_and_token(
        $consumer, 
        $token, 
        'GET', 
        $unsigned_url
    );
    
    // Sign the request
    $oauthrequest->sign_request($signature_method, $consumer, $token);
    
    // Get the signed URL
    $signed_url = $oauthrequest->to_url();
    
    // Send Yelp API Call
    try {
        $ch = curl_init($signed_url);
        if (FALSE === $ch)
            throw new Exception('Failed to initialize');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $data = curl_exec($ch);
        if (FALSE === $data)
            throw new Exception(curl_error($ch), curl_errno($ch));
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (200 != $http_status)
            throw new Exception($data, $http_status);
        curl_close($ch);
    } catch(Exception $e) {
        trigger_error(sprintf(
            'Curl failed with error #%d: %s',
            $e->getCode(), $e->getMessage()),
            E_USER_ERROR);
    }
    
    return $data;
}
/**
 * Query the Search API by a search term and location 
 * 
 * @param    $term        The search term passed to the API 
 * @param    $location    The search location passed to the API 
 * @return   The JSON response from the request 
 */
function search($term, $location) {
    $url_params = array();
    
    $url_params['term'] = $term ?: $GLOBALS['DEFAULT_TERM'];
    $url_params['location'] = $location?: $GLOBALS['DEFAULT_LOCATION'];
    $url_params['limit'] = $GLOBALS['SEARCH_LIMIT'];
    $search_path = $GLOBALS['SEARCH_PATH'] . "?" . http_build_query($url_params);
    
    return request($GLOBALS['API_HOST'], $search_path);
}
/**
 * Query the Business API by business_id
 * 
 * @param    $business_id    The ID of the business to query
 * @return   The JSON response from the request 
 */
function get_business($business_id) {
    $business_path = $GLOBALS['BUSINESS_PATH'] . urlencode($business_id);
    
    return request($GLOBALS['API_HOST'], $business_path);
}
/**
 * Queries the API by the input values from the user 
 * 
 * @param    $term        The search term to query
 * @param    $location    The location of the business to query
 */
function query_api($term, $location) {     
    
     
	
    $response = json_decode(search($term, $location));
    $limit= $GLOBALS['SEARCH_LIMIT'];
    //$business_id = $response->businesses[2]->id;
    
    //print sprintf(
      //  "%d businesses found, querying business info for the top result \"%s\"\n\n",         
        //count($response->businesses),
        //$business_id
    //);
    
    for ($x = 0; $x < $limit; $x++) {
    $business_id = $response->businesses[$x]->id;
    $response2 = json_decode(get_business($business_id));
    
    $name= $response2->name;
    $categories= $response2->categories[0][0];
    $address0= $response2->location->display_address[0];
    $address1= $response2->location->display_address[1];
    $address2= $response2->location->display_address[2];
    $phonenumber= $response2->phone;
    $rating= $response2->rating;
    $reviews= $response2->reviews[0]->excerpt;
    $image=$response2->image_url;
    $lat=$response2->location->coordinate->latitude;
    $long=$response2->location->coordinate->longitude;
    $url= $response2->url;
    $divcambiante= "business".strval($x);
    
    //print sprintf("Result for business \"%s\" found:\n", $business_id);
	//echo '<a href="' . $item->link . '">' . $item->title . '</a><br>';
	

		echo "<div class='businessgeneral'>
      			<h2>$name<h2>\n";
      	echo " <h3>$categories </h3>\n";		
      	echo " <h3>$address0, $address1, $address2 </h3>\n";
      	echo " <h3>Phone: $phonenumber \n";
      	echo " <h3>Rating: $rating </h3>\n";
      	echo " <h3>Reviews: $reviews </h3>\n";
      	echo "<img src='" . $image . "' alt='" . $name . "' style='width:200px;height:142px;'>\n<br><br>";
		echo "<a href='" . $url . "'>  Restaurant Link </a>\n<br><br>";
      	echo "<div class='googleMap'id='".$divcambiante."' style='width:200px;height:200px;'></div>";
      	
      	//<div id="googleMap" style="width:500px;height:380px;"></div>
      
      ?>	
      <script>

		function initialize() {
		  var latitude = "<?php echo $lat; ?>";	
		  var longitude = "<?php echo $long; ?>";
		  var divcorrecto = "<?php echo $divcambiante; ?>";	
  			
		  var mapProp = {
			center:new google.maps.LatLng(latitude,longitude),
			zoom:18,
			mapTypeId:google.maps.MapTypeId.ROADMAP
		  };
		  var map=new google.maps.Map(document.getElementById(divcorrecto), mapProp);
		}
//var myButton = document.getElementById('locationchange');
//google.maps.event.addDomListener(locationchange, 'click', initialize);
	</script>
    
    <script>
    	initialize();
    </script> 		
      		
      		
      <?php		
       
        echo "</div>";
    
    	
    
    //echo nl2br("\nName: $name\n Address: <h1>$address </h1>\n Phone: $phonenumber\n Rating: $rating\n Reviews: $reviews \n <a href='" .$url. "'>" . $url ."</a>\n ");

    }

}
/**
 * User input is handled here 
 */
 if(isset($_POST['submityelpsearch'])){
$DEFAULT_TERM = $_POST['find'];
$DEFAULT_LOCATION = $_POST['near'];
$SEARCH_LIMIT = intval($_POST['number']);
	
$longopts  = array(
    "term::",
    "location::",
);
    
$options = getopt("", $longopts);
$term = $options['term'] ?: '';
$location = $options['location'] ?: '';
//$limit = $options['limit'] ?: '';

query_api($term, $location);
}
?>





</div>



</body>
</html>