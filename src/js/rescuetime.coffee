###
# Provides visualisation for Rescuetime PHP api.
#
# Licensed under the MIT license
#
# @author Derk-Jan Karrenbeld <derk-jan+github@karrenbeld.info>
# @version 0.3.0
###
class RescueTime
		
	# Creates a new RescueTime chart
	# 
	# @param chartid [String] the canvas element id
	#
	constructor: ( chartid ) ->
		@_context = document.getElementById( chartid ).getContext( "2d" )
		@chart = new Chart( @_context )
		
	# Requests data from an url
	#
	# @param url [String] url to request from
	# @param type [String] type of data to request
	# @note override this if you don't want to use ajax
	# 
	request: ( url, type ) ->
		$.get( url, { type: type } )
			
			.done( ( data ) => 
				@_data = JSON.parse( data )
				@redraw()
			)
			
			.fail( ( error ) ->
				console.log error
			)
	
	redraw: () ->
		
		unless @_data?
			return
			
		@chart = new Chart( @_context )
		@draw( @_data[ 'type' ], @_data[ 'data' ], @_data[ 'labels' ] ? { } )
	
	# Draws data on the screen
	#
	# @param type [String] type of chart to display
	# @param data [Object] the data object
	# @param display [Object] override for labels
	#
	draw: ( type, data, display = {} ) ->
	
		switch type
		
			when 'radar'
				labels = []
				dataset = 
					fillColor: "rgba(151,187,205,0.5)"
					strokeColor: "rgba(151,187,205,1)"
					pointColor: "rgba(151,187,205,1)"
					pointStrokeColor: "#fff"
					data: []
					
				total = 0
				
				for key, value of data
					labels.push display[ key ] ? key
					dataset.data.push value
					total += value
				
				for key, value of dataset.data
					dataset.data[ key ] = Math.round( value / total * 100 )
				
				data =
					labels: labels
					datasets: [ dataset ]
				
				options =
					animationEasing: "easeInOutQuart"
					tooltips: 
						labelTemplate: '<%=label%>: <%=value%> percent'
					
				@chart.Radar( data, options ) 
			
			when 'bar'
			
				labels = []
				dataset =
					fillColor: "rgba(151,187,205,0.5)"
					strokeColor: "rgba(151,187,205,1)"
					data: []
					
				for key, value of data
					labels.push display[ key ] ? key
					dataset.data.push Math.round( value / 60 )
				
				data =
					labels: labels
					datasets: [ dataset ]
					
				options =
					animationEasing: "easeInOutQuart"
					tooltips: 
						labelTemplate: '<%=label%>: <%=value%> minutes'
					
				@chart.Bar( data, options ) 
			
( exports ? this ).RescueTime = RescueTime