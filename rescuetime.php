<?php
/*
Plugin Name: RescueTime for WordPress
Plugin URI: http://derk-jan.org
Description: Enables the Data API for RescueTime in WordPress
Version: 1.0
Author: Derk-Jan Karrenbeld <derk-jan+wp@karrenbeld.info>
Author URI: http://www.derk-jan.com
*/

if( !class_exists( 'WP_Http' ) )
        include_once( ABSPATH . WPINC. '/class-http.php' );

/**
 * Holds the parameters for a RescueTime API request. 
 * 
 * The RescueTimeRequest class spawns objects that can be used to execute API
 * requests, given a valid key is provided when constructing the object. After
 * execution the objects are reusable. All the set methods are chainable.
 */
class RescueTimeRequest {
	
	protected $apikey;
	protected $params = array();
	
	protected $base_url = 'https://www.rescuetime.com/anapi/data?';
		
	/**
	 * Constructs a new Request object
	 * @param string $apikey the API key
	 */
	public function __construct( $apikey ) {
		$this->apikey = $apikey;
	}
	
	/**
	 * Retrieves the API key
	 * @return string api key
	 */
	public function get_api_key() {
		return $this->apikey;
	}
	
	/**
	 * Retrieves the set parameters
	 * @return string[]
	 */
	public function get_parameters() {
		$copy_params = $this->params;
		return $copy_params;
	}
	
	/**
	 * Retrieves a single parameters value
	 *
	 * @param string $key the parameter
	 * @return string the value
	 */
	public function get_parameter( $key )
	{
		$copy_parameter = $this->params[ $key ];
		return $copy_parameter;
	}
	
	/**
	 * Sets an arbitrary parameters value
	 *
	 * @param string $key the parameter
	 * @param string $value the value
	 * @return RescueTimeRequest
	 */
	public function set_parameter( $key, $value ) {
		$this->params[ $key ] = $value;	
		return $this;	
	}
	
	/**
	 * Sets the perspective for the request
	 *
	 * @param string $pv perspective
	 * @return RescueTimeRequest
	 */
	public function set_perspective( $pv ) {
		return $this->set_parameter( 'pv', $pv);
	}
	
	/**
	 * Sets the resolution for the request
	 *
	 * @param string $time resolution string
	 * @return RescueTimeRequest
	 */
	public function set_resolution( $time ) {
		return $this->set_parameter( 'rs', $time);
	}
	
	/**
	 * Sets the start date restriction for the request
	 *
	 * @param string|int|DateTime $datetime strtotime string, unix timestamp or DateTime
	 * @return RescueTimeRequest
	 */	
	public function set_startdate( $datetime ) {
		return $this->restrict( 'rb' , $this->parse_date( $datetime ) );
	}
	
	/**
	 * Sets the end date restriction for the request
	 *
	 * @param string|int|DateTime $datetime strtotime string, unix timestamp or DateTime
	 * @return RescueTimeRequest
	 */	
	public function set_enddate( $datetime ) {
		return $this->restrict( 're' , $this->parse_date( $datetime ) );
	}
	
	/**
	 * Parses a datetime
	 *
	 * @param string|int|DateTime $datetime strtotime string, unix timestamp or DateTime
	 * @return string|null Formatted DateTime as Y-m-d or NULL
	 */	
	protected function parse_date( $datetime ) 	{
		if ( is_object( $datetime ) ) :
			if ( get_class( $datetime ) === 'DateTime' ) :
				return $datetime->format( 'Y-m-d' );
			endif;
		elseif ( is_string( $datetime ) ) :
			return $this->set_startdate( new DateTime( "@" . strtotime( $datetime ) ) );
		elseif ( is_numeric( $datetime ) ) :
			return $this->set_startdate( new DateTime( "@" . $datetime ) );
		endif;
		return NULL;
	}
	
	/**
	 * Restricts the request to a certain user
	 *
	 * @param string $user user name or user e-mail
	 * @return RescueTimeRequest
	 */
	public function restrict_user( $user ) {
		return $this->restrict( 'ru' , $user );
	}
	
	/**
	 * Restricts the request to a certain group
	 *
	 * @param string $group group name
	 * @return RescueTimeRequest
	 */
	public function restrict_group( $group ) {
		return $this->restrict( 'rg' , $group );
	}
	
	/**
	 * Restricts the request to a certain kind
	 *
	 * @param string $kind kind name
	 * @return RescueTimeRequest
	 */
	public function restrict_kind( $kind ) {
		return $this->restrict( 'rk' , $kind );
	}
	
	/**
	 * Restricts the request to a certain project
	 *
	 * @param string $project project name
	 * @return RescueTimeRequest
	 */
	public function restrict_project( $project ) {
		return $this->restrict( 'rj' , $project );	
	}
	
	/**
	 * Restricts the request to a certain taxonomy
	 *
	 * @param string $thing taxonomy name
	 * @return RescueTimeRequest
	 */
	public function restrict_thing( $thing ) {
		return $this->restrict( 'rt' , $thing );	
	}
	
	/**
	 * Restricts the request to a certain subtaxonomy
	 *
	 * @param string $thingy taxonomy name
	 * @return RescueTimeRequest
	 */
	public function restrict_thingy( $thingy ) {
		return $this->restrict( 'ry', $thingy );
	}	
	
	/**
	 * Restricts the request on key with value or unsets
	 *
	 * @param string $thingy taxonomy name
	 * @return RescueTimeRequest
	 */
	protected function restrict( $key, $value ) {
		if ( !empty( $value ) )
			return $this->set_parameter( $key, $value );
		unset( $this->params[ $key ] );
		return $this;
	}
	
	/**
	 * Executes the request
	 *
	 * @return RescueTimeResult|null a result object or NULL
	 */
	public function execute() {
		
		// Build the parameters
		$parameters = array( 
			'key' => $this->apikey, 
			'format' => 'json', 
			'op' => 'select' 
		) + $this->get_parameters();
		
		// Build the URL
		$url = $this->base_url . http_build_query( $parameters, '', '&' );		
		
		// Execue the request
		$result = wp_remote_retrieve_body( wp_remote_get( $url, array('sslverify' => false ) ) );
		if ( empty( $result ) )
			return NULL;
		return new RescueTimeResult( $result );
	}
}

/**
 *  Holds the results for a RescueTime API request. 
 */
class RescueTimeResult { 

	protected $json_data;
	
	protected $headers = array( 
		'rank' => "Rank",
		'date' => "Date",
		'person' => "Person",
		'time' => "Time Spent (seconds)",
		'n' => "Number of People",
		'activity' => "Activity",
		'category' => "Category",
		'productivity' => "Productivity"
	);

	/**
	 * Constructs a new result from json data
	 * @param string|object $json encoded json string or json object
	 */
	public function __construct( $json ) {
		if ( is_string( $json ) )
			$json = json_decode( $json );
		$this->json_data = $json;
	}
	
	/**
	 * Sees if the result has error data
	 * @return boolean flag true if error
	 */
	public function is_error() {
		return isset( $this->json_data->error ) || !isset( $this->json_data->rows );
	}

	/**
	 * Returns the perspective of the result
	 * @return string|null the perspective
	 */	
	public function get_perspective() {
		if ( $this->get_column_index( 'rank' ) )
			return 'rank';
		if ( $this->get_column_index( 'date' ) )
			return 'interval';
		if ( $this->get_column_index( 'person' ) )
			return 'member';
			
		return NULL;
	}
	
	/**
	 * Returns the notes for this request
	 * @return RescueTimeResult|null a result object or NULL
	 */
	public function get_notes() {
		return $this->json_data->notes;
	}
	
	/**
	 * Returns the row headers
	 * @return string[]
	 */
	public function get_row_headers() {
		return $this->json_data->row_headers;	
	}
	
	/**
	 * Gets the column index for a certain header
	 *
	 * @param string $header keys from $this->headers
	 * @return int index in row data
	 */
	protected function get_column_index( $header ) {
		return array_search( $this->headers[ $header ], $this->get_row_headers() );
	}
	
	/**
	 * Gets the raw row data
	 * @return (int|string)[][] rows
	 */
	public function get_rows() {
		return $this->json_data->rows;
	}
	
	/**
	 * Gets the rows grouped per activity
	 * @return (int|string)[][] rows
	 */
	public function get_rows_by_activity( ) {
		return $this->get_rows_grouped( $this->get_column_index( 'activity' ) );
	}
	
	/**
	 * Gets the rows grouped per category
	 * @return (int|string)[][] rows
	 */
	public function get_rows_by_category( ) {
		return $this->get_rows_grouped( $this->get_column_index( 'category' ) );
	}
	
	/**
	 * Gets the rows grouped per productivity
	 * @return (int|string)[][] rows
	 */
	public function get_rows_by_productivity( ) {
		return $this->get_rows_grouped( $this->get_column_index( 'productivity' ) );
	}
	
	/**
	 * Gets the rows grouped per person
	 * @return (int|string)[][] rows
	 */
	public function get_rows_by_person( ) {
		return $this->get_rows_grouped( $this->get_column_index( 'person' ) );
	}
	
	/**
	 * Gets the rows grouped per date
	 * @return (int|string)[][] rows
	 */
	public function get_rows_by_date( ) {
		return $this->get_rows_grouped( $this->get_column_index( 'date' ) );
	}
	
	/**
	 * Groups the rows on certain column
	 *
	 * @param int $column column index
	 * @return (int|string)[][] rows
	 */
	protected function get_rows_grouped( $column ) {
		$rows = array();
		foreach( $this->get_rows() as $row ) :
			if ( !isset( $rows[ $row[ $column ] ] ) )
				$rows[ $row[ $column ] ] = array();
			array_push( $rows[ $row[ $column ] ], $row );
		endforeach;
		return $rows;
	}
	
	/**
	 * Gets a certain row
	 * 
	 * @param int|int[] $rank the rank of the rows
	 * @return (int|string)[]|(int|string)[][]|null row or null when not found
	 */
	public function get_rows_with_rank( $rank ) {
		$column = $this->get_column_index( 'rank' );
		$rank = is_array( $rank ) ? $rank : array( $rank );
		$need = $rank;
		$rows = array();
		foreach( $this->get_rows() as $row ) :
			if ( ($need_index = array_search( $row[ $column ], $need )) !== false ) :
				array_push( $rows, $row );
				unset( $need[ $need_index ] );
				if ( empty( $need ) )
					break;
			endif;			
		endforeach;
				
		if ( empty( $rows ) )
		 	return NULL;
		if ( count( $rank ) > 1 )
			return $rows;
		return array_shift( $rows );
	}
	
	/**
	 * Gets the rows grouped per activity
	 *
	 * @param int|int[] $activity allowed activities
	 * @return (int|string)[][] rows
	 */
	public function get_rows_with_activity( $activity ) {
		$activity = is_array( $activity ) ? $activity : array( $activity );
		return $this->get_rows_in_array( $this->get_column_index( 'activity' ) , $activity );
	}
	
	/**
	 * Gets the rows grouped per category
	 *
	 * @param int|int[] $category allowed categories
	 * @return (int|string)[][] rows
	 */
	public function get_rows_with_category( $category ) {
		$category = is_array( $category ) ? $category : array( $category );
		return $this->get_rows_in_array( $this->get_column_index( 'category' ) , $category );
	}
	
	/**
	 * Returns the rows with a certain productivity
	 *
	 * @param int|int[] $productivity allowed productivities
	 * @return (int|string)[][] rows
	 */
	public function get_rows_with_productivity( $productivity ) {
		$productivity = is_array( $productivity ) ? $productivity : array( $productivity );
		return $this->get_rows_in_array( $this->get_column_index( 'productivity' ) , $productivity );
	}
	
	/**
	 * Returns the rows with a certain person
	 *
	 * @param int|int[] $person allowed persons
	 * @return (int|string)[][] rows
	 */
	public function get_rows_with_person( $person ) {
		$person = is_array( $person ) ? $person : array( $person );
		return $this->get_rows_in_array( $this->get_column_index( 'person' ) , $person );
	}
	
	/**
	 * Returns the rows with a certain time spent
	 *
	 * @param null|int|DateTime[] $min_time minimum number of seconds spent
	 * @param null|int|DateTime[] $max_time maximum number of seconds spent
	 * @return (int|string)[][] rows
	 */
	public function get_rows_between_time( $min_time = NULL, $max_time = NULL ) {
		if ( empty( $min_time ) && empty( $max_time ) )
			return $this->get_rows();
		
		// Bounds
		$min_time = empty( $min_time ) ? 0 : $min_time;
		$max_time = empty( $max_time ) ? INF : $max_time;
		
		// Convert from Datetime
		if ( get_class( $min_time ) == "DateTime" )
			$min_time = $min_time->getTimestamp();
		if ( get_class( $max_time ) == "DateTime" )
			$max_time = $max_time->getTimestamp();
			
		// Fetch
		$lbound = min( $min_time, $max_time );
		$ubound = max( $min_time, $max_time );
		$column = $this->get_column_index( 'time' );
		$rows = array();
		foreach( $this->get_rows() as $row ) :
			if ( $row[ $column ] >= $lbound && $row[ $column ] < $ubound )
				array_push( $rows, $row );
		endforeach;
		return $rows;
	}
	
	/**
	 * Returns the rows with a certain productivity
	 *
	 * @param int|int[] $productivity allowed productivities
	 * @return (int|string)[][] rows
	 */
	protected function get_rows_in_array( $column, $array ) {
		$rows = array();
		foreach( $this->get_rows() as $row ) :
			if ( in_array( $row[ $column ], $array ) )
				array_push( $rows, $row );
		endforeach;
		return $rows;
	}
	
	/**
	 * Filters the results on activity and returns a new RescueTimeResult
	 *
	 * @param numeric $min_time minimal number of seconds spent
	 * @param numeric $max_time maximum number of seconds spent
	 * @return RescueTimeResult filtered result
	 */
	public function filter_time( $min_time, $max_time ) {
		return $this->filter( $this->get_rows_between_time( $min_time, $max_time ) );
	}
	
	/**
	 * Filters the results on a minimum number of seconds
	 *
	 * @param numeric $min_time minimal number of seconds spent
	 * @return RescueTimeResult filtered result
	 */
	public function filter_min_time( $min_time ) {
		return $this->filter_time( $min_time, NULL );
	}
	
	/**
	 * Filters the results on a maximum number of seconds
	 *
	 * @param numeric $min_time maximum number of seconds spent
	 * @return RescueTimeResult filtered result
	 */
	public function filter_max_time( $max_time ) {
		return $this->filter_time( NULL, $max_time );
	}
	
	/**
	 * Filters the results on activity and returns a new RescueTimeResult
	 *
	 * @param int|int[] $activity allowed activities
	 * @return RescueTimeResult filtered result
	 */
	public function filter_activity( $activity ) {
		return $this->filter( $this->get_rows_with_activity( $activity ) );
	}
	
	/**
	 * Filters the results on category and returns a new RescueTimeResult
	 *
	 * @param int|int[] $category allowed categories
	 * @return RescueTimeResult filtered result
	 */
	public function filter_category( $category ) {
		return $this->filter( $this->get_rows_with_category( $category ) );
	}
	
	/**
	 * Filters the results on productivity and returns a new RescueTimeResult
	 *
	 * @param int|int[] $productivity allowed productivities
	 * @return RescueTimeResult filtered result
	 */
	public function filter_productivity( $productivity ) {
		return $this->filter( $this->get_rows_with_productivity( $productivity ) );
	}
	
	/**
	 * Filters the results by unproductive rows and returns a new RescueTimeResult
	 *
	 * @param boolean $include_neutral includes productivity neutral
	 * @return RescueTimeResult filtered result
	 */
	public function filter_unproductive( $include_neutral = false ) {
		$productivity = array( -2, -1 );
		if ( $include_neutral )
			$productivity[] = 0;
		return $this->filter( $this->get_rows_with_productivity( $productivity ) );
	}
	
	/**
	 * Filters the results by productive rows and returns a new RescueTimeResult
	 *
	 * @param boolean $include_neutral includes productivity neutral
	 * @return RescueTimeResult filtered result
	 */
	public function filter_productive( $include_neutral = false ) {
		$productivity = array( 2, 1 );
		if ( $include_neutral )
			$productivity[] = 0;
		return $this->filter( $this->get_rows_with_productivity( $productivity ) );
	}
	
	/**
	 * Filters the results on person and returns a new RescueTimeResult
	 *
	 * @param int|int[] $person allowed person
	 * @return RescueTimeResult filtered result
	 */
	public function filter_person( $person ) {
		return $this->filter( $this->get_rows_with_person( $person ) );
	}
	
	/**
	 * Creates a new result from rows
	 *
	 * @param (string|int)[][] $rows rows
	 * @return RescueTimeResult filtered result
	 */
	protected function filter( $rows ) {
		$result_data = array();
		foreach( $this->json_data as $key => $value )
			$result_data[ $key ] = $value;
		$result_data['rows'] = $rows;
		return new RescueTimeResult( json_encode( $result_data ) );	
	}
}

/**hey


$parameter_options = array(
    'operation' => array( 
		'select' => 'select', 
		'_' => 'select' ),
	'perspective' => array( 
		'rank' => 'Organized around a calculated value, usually a sum like time spent.', 
		'interval' => 'Organized around calendar time.', 
		'member' => 'Organized around users and groups.',
		'_' => 'rank' ),
	'resolution_time' => array(
		'month' => 'Chunk size of a month.',
		'week' => 'Chunk size of a week.',
		'day' => 'Chunk size of a day.',
		'hour' => 'Chunk size of an hour.',
		'_' => 'hour'
	),
	'restrict_group' => 'group name',
	'restrict_user' => 'user name or email',
	'restrict_begin' => 'date time',
	'restrict_end' => 'date time',
	'restrict_kind' => array(
		'category' => 'Sums statistics for all activities into their category.',
		'activity' => 'Sums statistics for individual applications / web sites / activities.',
		'productivity' => 'Productivity calculation.',
		'efficiency' => 'Efficiency calculation (not in rank perspective).' ),
	'restrict_project' => 'project name',
	'restrict_thing' => 'overview name',
	'restrict_thingy' => 'subname',
);

$shortcodes = array(
	'operation' => 'op',
	'version' => 'vn',
	'perspective' => 'pv',
	'resolution_time' => 'rs',
	'restrict_group' => 'rg',
	'restrict_user' => 'ru',
	'restrict_begin' => 'rb',
	'restrict_end' => 're', 
	'restrict_kind' => 'rk',
	'restrict_project' => 'rj',
	'restrict_thing' => 'rt',
	'restrict_thingy' => 'ry',
);
		
		
$params = array(
	'key' => $key,
	'format' => 'json',	
	
	);
	
*/
?>