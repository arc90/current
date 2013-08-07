<?php
require_once('php_console.php');
PhpConsole::start(true, true, dirname(__FILE__));

//  =========================
//  NOTE: You Must CHMOD 777 the caches directory, so PHP can access it.
//  =========================

//  =========================
//  NOTE: If you add a new service, you must add it's corresponding information in the following:
//      API_Call class
//          - setup_api_configuration()
//          - verify_api_response()
//          - trim_api_response()
//          - parse_verified_api_response()
//  =========================

class Service {
    public  $id,                    // Set in __construct() - passed in on initiate
            $api_endpoint,          // Set in setup_api_configuration() on construct
            $api_response_format,   // Set in setup_api_configuration() on construct
            $api_call_required,     // Set in setup_api_configuration() on construct
            $api_response_verified, // Set in verify_api_response() on construct
            $cache;

    public  $api_response = array(); // api response converted to PHP array
    
    /**
     *
     * Construct
     * Requires an ID be passed in
     *
     */
    public function __construct($id) {
        
        // Set the ID 
        $this->id = $id;

        // Configure API settings in prepartion for making call
        $this->setup_api_configuration($this->id);
        $this->cache = new Cache_File($this->id);

        // If API call is required, make it
        // Otherwise, it's our own cached filed and we do nothing else
        if ($this->api_call_required) {
            
            // Make the API call
            $this->make_api_call();

            // Trim the API response
            $this->trim_api_response();
            
            // Verify the API response
            $this->verify_api_response();

            // If the response is verified, parse it!
            if ( $this->api_response_verified ) {
                $this->parse_verified_api_response();
            }
            // Otherwise, set the response to null
            else {
                $this->api_response = null;
            }
        } else {debug('test');}
    }

    public function setup_api_configuration() {
        switch ($this->id) {
            case 'scriptogram':
                $userID                         = 'jimniels';
                $this->api_response_format      = 'xml';
                $this->api_endpoint             = 'http://scriptogr.am/'. $userID .'/feed/';
                $this->api_call_required        = true;
                break;
            case 'instagram':
                $token                          = '';
                $this->api_response_format      = 'json';
                $this->api_endpoint             = 'https://api.instagram.com/v1/users/self/media/recent/?access_token='. $token .'';
                $this->api_call_required        = true;
                break;
            case 'dribbble':
                $userID                         = 'jimniels';
                $this->api_response_format      = 'json';
                $this->api_endpoint             = 'http://api.dribbble.com/players/'. $userID .'/shots/';
                $this->api_call_required        = true;
                break;
            case 'github':
                $userID                         = 'jimniels';
                $this->api_response_format      = 'xml';
                $this->api_endpoint             = 'http://github.com/'. $userID .'.atom';
                $this->api_call_required        = true;
                break;
            case 'readability':
                $userID                         = 'jimniels';
                $this->api_response_format      = 'xml';
                $this->api_endpoint             = 'http://readability.com/'. $userID .'/latest/feed';
                $this->api_call_required        = true;
                break;
            case 'yelp':    
                $this->api_response_format      = 'xml';
                $this->api_endpoint             = 'http://www.yelp.com/syndicate/user/oTitPvqBylUX0kvyFeBURw/rss.xml';
                $this->api_call_required        = true;
                break;
            case 'twitter':
                $userID                         = 'jimniels';
                $this->api_response_format      = 'xml';
                $this->api_endpoint             = 'http://www.twitter-rss.com/user_timeline.php?screen_name='.$userID;
                $this->api_call_required        = true;
                break;
            default:
                // Otherwise it's my own JSON files, doesn't need an API call
                $this->api_call_required        = false;
                break;
        }
    }

    /**
     * Verify API Resonse
     * Make sure the data is good by ensuring a piece of the data we want is in it
     *
     * Sets $this->api_response_verified as true or false
     */
    public function verify_api_response() {

        switch ($this->id) {
            case 'dribbble':
                $verified_piece = $this->api_response[0]['url'];
                break;
            case 'readability':
            case 'scriptogram':
            case 'yelp':
            case 'twitter':
                $verified_piece = $this->api_response[0]['link'];
                break;
            case 'github':
                $verified_piece = $this->api_response[0]['content'];
                break;
            case 'instagram':
                $verified_piece = $this->api_response[0]['link'];
                break;
            default:
                $verified_piece = null;
                debug('Did not verify data from ' . $this->id);
        }
        
        if ($verified_piece != null && $verified_piece != '') {
            $this->api_response_verified = true;
        } else {
            $this->api_response_verified = false;
        }
        
    }


    /**
     *
     * Make API Call
     * Make the call to the API
     *
     */
    public function make_api_call() {
        
        // Go get the data
        $data = file_get_contents($this->api_endpoint);

        // If the response is in XML, convert it to JSON
        if ($this->api_response_format == 'xml'){
            $data = convert_xml_to_json($data);
        }

        // Convert api response from JSON to array object for PHP to handle
        // Then set it as the API response
        $this->api_response = json_decode($data, true);
    }

    /**
     *
     * Trim API Response
     * Trim down the array to the part of the API response we'll parse for our data
     *
     */
    public function trim_api_response() {
        switch ($this->id) {
            case 'dribbble':
                $this->api_response = $this->api_response['shots'];
                break;
            case 'readability':
            case 'scriptogram':
            case 'yelp':
            case 'twitter':
                $this->api_response = $this->api_response['channel']['item'];
                break;
            case 'github':
                $this->api_response = $this->api_response['entry'];
                break;
            case 'instagram':
                $this->api_response = $this->api_response['data'];
                break;
            default:
                break;
        }   
    }

    /**
     *
     * Parse Verified API Response
     * Parse the verified API response for the data we want to send to the front-end
     * Then set it as $this->api_response
     *
     */
    public function parse_verified_api_response(){
        $parsed_api_response = array();

        // Loop over each item, rather than writing the for loop in each switch case
        for($i=0;$i<count($this->api_response);$i++) {

            switch($this->id) {
                case 'readability':
                    $parsed_api_response[]  = array(
                        'id'                => $this->id,
                        'title'             => $this->api_response[$i]['title'],
                        'link'              => $this->api_response[$i]['link'],
                        'excerpt'           => $this->api_response[$i]['description'],
                        'source'            => get_readability_source( $this->api_response[$i]['link'] ),
                        
                        // Original Format: Wed, 12 Jun 2013 09:40:29 -0400
                        'date'              => strtotime( $this->api_response[$i]['pubDate'] )
                    );
                    break;
                case 'scriptogram':
                    $parsed_api_response[]  = array(
                        'id'                => $this->id,
                        'title'             => $this->api_response[$i]['title'],
                        'link'              => $this->api_response[$i]['link'],
                        'excerpt'           => $this->api_response[$i]['description'],
                        
                        // Original Format: Tue, 11 Jun 2013 00:00:00 -0400
                        'date'              => strtotime( $this->api_response[$i]['pubDate'] )
                    );
                    break;
                case 'yelp':
                    $parsed_api_response[]  = array(
                        'id'                => $this->id,
                        'title'             => $this->api_response[$i]['title'],
                        'link'              => $this->api_response[$i]['link'],
                        'excerpt'           => $this->api_response[$i]['description'],
                        
                        // Original Format: Tue, 19 Mar 2013 15:50:30  PST
                        'date'              => strtotime( $this->api_response[$i]['pubDate'] )
                    );
                    break;
                case 'dribbble':
                    $parsed_api_response[] = array(
                        'id'                => $this->id,
                        'image_url'         => $this->api_response[$i]['image_url'],
                        'link'              => $this->api_response[$i]['url'],

                        // Original Format: 2013/05/08 12:22:52 -0400
                        'date'              => strtotime( $this->api_response[$i]['created_at'] )
                    );
                    break;
                case 'instagram':
                    $parsed_api_response[]  = array(
                        'id'                => $this->id,
                        'link'              => $this->api_response[$i]['link'],
                        'image_url'         => $this->api_response[$i]['images']['standard_resolution']['url'],                  
                        
                        // Instagram date served in UNIX time, which is what we want
                        'date'              => $this->api_response[$i]['created_time']
                    );
                    break;
                case 'github':
                    $parsed_api_response[]  = array(
                        'id'                => 'github',
                        'content'           => $this->api_response[$i]['content'],
                        
                        // Original Format: 2013-05-19T20:39:36Z
                        'date'              => strtotime( $this->api_response[$i]['published'] )
                    );
                    break;
                case 'twitter':
                    $parsed_api_response[]  = array(
                        'id'                => $this->id,
                        'content'           => $this->api_response[$i]['description'],
                        'link'              => $this->api_response[$i]['link'],
                        
                        // Original Format: Fri, 21 Jun 2013 12:28:05 +0000
                        'date'              => strtotime( $this->api_response[$i]['pubDate'] )
                    );
                    break;
                default:
                    debug('Data not parsed for '. $this->id);
                    break;
            }
        }
        $this->api_response = $parsed_api_response;
    }

}


/**
 * 
 * Get Readability Source
 * Readability gives us a link like this: http://www.readability.com/read?url=http://alistapart.com/article/designing-for-breakpoints
 * Extract the domain (alistapart) from that
 *
 */
function get_readability_source($link) {
    $source = explode('url=', $link); // Result: $source[1]=http://alistapart.com/article/designing-for-breakpoints
    $source = parse_url($source[1], PHP_URL_HOST);

    // If it starts with 'WWW.', remove it
    if(substr($source,0,4) == 'www.') {
        $source = explode('www.', $source);
        $source = $source[1];
    }

    return $source;
}


/**
 *
 * Converts XML to JSON
 *  - http://lostechies.com/seanbiefeld/2011/10/21/simple-xml-to-json-with-php/
 *
 */
function convert_xml_to_json($xml) {
    $xml = str_replace(array("\n", "\r", "\t"), '', $xml);
    $xml = trim(str_replace('"', "'", $xml));
    $xml_object = simplexml_load_string($xml);
    $json = json_encode($xml_object);
    return $json;
}

/**
 *
 * Cache Files Class
 * Gets me info for each cache file
 *
 */
class Cache_File {
    public  $id,
            $file,
            $creation,
            $expiration;
    public  $file_exists = false;

    public function __construct($id) {
        $this->id = $id;
        $this->file = dirname(__FILE__) . '/caches/' . $id .'.json';
        $this->expiration = time() - 100000000; // seconds
        if ( file_exists($this->file) ) {
            $this->creation = filemtime($this->file);
            $this->file_exists = true;
        }
    }
}


function generate_feed_data_cache_file($feed_data_file) {
    
    // A list of all services
    $services = array(
        'readability',
        'dribbble',
        'published-articles',
        'scriptogram',
        'yelp',
        'github',
        'instagram',
        'twitter'
        // links - local json
        // youtube
        // flickr
    );

    // We'll append all data from each service to this
    $feed_data = array();
    $array = array();

    // Get data and write cache files for individual services 
    foreach ($services as $service) {

        // Create class for each
        $service = new Service($service);

        // If it requires an API call and the response was verified, write the result as a cached JSON file
        // Then add the result to feed_data_contents
        if ( $service->api_call_required && $service->api_response_verified ) {
            $f = file_put_contents($service->cache->file, json_encode( $service->api_response) );
            if($f) {
                debug('Wrote ' . $service->id . ' to the cache!');
            }
            $feed_data = array_merge($feed_data, $service->api_response);
        }
        // Otherwise don't write it, we'll grab the cached file and add it's contents to feed-data
        else {
            debug('WARNING: Could not write ' . $service->id . ' to the cache! Your data is not verified. Using cache instead.');
            $feed_data = array_merge( $feed_data, json_decode( file_get_contents($service->cache->file) ) );
        }
    }


    // Write individual files to compiled cached
    $f = file_put_contents($feed_data_file->file, json_encode($feed_data));
    if($f)
        debug('wrote FEED DATA');
    else
        debug('ERROR: Could not write FEED DATA to the cache!');
}

/**
 * Get Feed Data
 *
 * Main function
 * Checks to see if the feed-data.json file exists and hasn't expired. 
 * Generates the cache file if it doesn't exist and/or has expired
 */
function serve_feed_data(){

    $feed_data = new Cache_File('feed-data');

    // Check to see if the cached file exists 
    //  - If it does, check if it has expired
    //      - If it expired, generate a new one
    //      - If it hasn't expired, serve it
    //  - If it doesn't, generate a new one, then serve it

    // If file doesn't exist
    if (!$feed_data->file_exists) {
        generate_feed_data_cache_file($feed_data);
    }

    // If the file exists but has expired
    if ( $feed_data->file_exists && $feed_data->creation < $feed_data->expiration ) {
        generate_feed_data_cache_file($feed_data);
    } 

    // Return the cached file
    return file_get_contents($feed_data->file);
}

// Write the feed data (from cached file)
//header('Content-Type: application/json');
echo serve_feed_data();

?>