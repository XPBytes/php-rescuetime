class RescueTime
	
	constructor: ( chartid ) ->
		context = document.getElementById( chartid ).getContext( "2d" )
		@chart = new Chart( context )
		
	request: ( url, type ) ->
		$.get( url, { type: type } )
			
			.done( ( data ) => 
				data = JSON.parse( data )
				console.log data
				@draw( data[ 'type' ], data[ 'data' ], data[ 'labels' ] ? { } )
			)
			
			.fail( ( error ) ->
				console.log error
			)
	
	draw: ( type, data, display = {} ) ->
	
		console.log type, data, display
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
					dataset.data[ key ] = value / total
				
				data =
					labels: labels
					datasets: [ dataset ]
				
				options =
					animationEasing: "easeInOutQuart"
					
				console.log data
				@chart.Radar( data, options ) 
			
			when 'bar'
			
				labels = []
				dataset =
					fillColor: "rgba(151,187,205,0.5)"
					strokeColor: "rgba(151,187,205,1)"
					data: []
					
				for key, value of data
					labels.push display[ key ] ? key
					dataset.data.push value
				
				data =
					labels: labels
					datasets: [ dataset ]
					
				options =
					animationEasing: "easeInOutQuart"
					
				console.log data
				@chart.Bar( data, options ) 
			
	

( exports ? this ).RescueTime = RescueTime