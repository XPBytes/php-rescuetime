rescuetime-wordpress
====================

WordPress Plugin with PHP API for RescueTime

Installation
--------------------
Simply place the rescuetime.php in your `plugins` folder. Activate the plugin and you are ready to go.

Usuage
--------------------
At this time this plugin only provides helper classes to get your data. In the future, it might be able
to display graphs and tables and more fancy stuff so you can show your productivity without the embed
and process it any way you like.

###Request
To do an API request, you need an `api-key`. Get one [here](https://www.rescuetime.com/anapi/setup)..
Create a new request object:
		$rt_request = new RescueTimeRequest( $apikey );
and use one or more of the following functions to manipulate the request:
- `set_perspective`: Sets the perspective (rank, member, interval)
- `set_resolution`: Sets the resolution (hour, week, day, month)
- `set_startdate`: Sets the start date restriction
- `set_enddate`: Sets the end date restriction 
- `restrict_user`: Restricts to a certain user name or e-mail
- `restrict_group`: Restricts to a certain group name
- `restrict_kind`: Restricts to a certain kind
- `restrict_project`: Restricts to a certain project
- `restrict_thing`: Restricts to a certain taxonomy
- `restrict_thingy`: Restricts to a certain sub-taxonomy
The function calls are chainable, so you can:
		$rt_request->set_perspective( 'interval' )->set_resolution( 'hour' )->restrict_user( 'derk-jan@karrenbeld.info' );
When you are ready call the execute command:
		$rt_result = $rt_request->execute();
The request object is re-usable.
		
###Result
You can manipulate the result, as long as the request was valid:
		$rt_result->is_error(); //checks if the result has data
The data returned is tabular and the headers are provided seperately from the rows. Access those:
- `get_rows`: Gets the raw row data
- `get_row_headers`: Gets the row headers

You can `GROUP BY` by issuing one of the `get_by` commands. Does not manipulate the data:
- `get_rows_by_activity`: Group per activity
- `get_rows_by_category`: Group per category
- `get_rows_by_productivity`: Group per productivity value
- `get_rows_by_person`: Group per person (only valid with perspective member)
- `get_rows_by_date`: Group per date (only valid with perspective interval)

You can `WHERE` data by issuing one of the `get_with` commands. Simply provide one value or an array of valid values. Does not manipulate the data:
- `get_rows_with_rank`: Gets a row with a rank (only valid with perspective rank)
- `get_rows_with_activity`: Gets rows with that activity
- `get_rows_with_category`: Gets rows with that category
- `get_rows_with_productivity`: Gets rows with that productivity
- `get_rows_with_person`: Gets rows with that person (only valid with perspective member)
- `get_rows_between_time`: Gets rows between min and max time spent

You can filter data just like `WHERE` issuing one of the `filter` commands. Same arguments as the `get_with` are valid. **Note:** you don't get a subsection of rows back. Instead a new `RescueTimeResult` object is created with the subsection of data:
- `filter_time`: Filters the data on rows between min and max time spent
- `filter_min_time`: Filters the data on rows at least time spent
- `filter_max_time`: Filters the data on rows at most time spent
- `filter_activity`: Filters the data on rows with that activity
- `filter_category`: Filters the data on rows with that category
- `filter_productivity`: Filters the data on rows with that productivity
- `filter_unproductive`: Filters data to only unproductive rows
- `filter_productive`: Filters data to only productive rows
- `filter_person` : Filters rows for that person (only valid with perspective member)