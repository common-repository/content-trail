jQuery(function () {
	jQuery.get("http://sonyanalytics.recosenselabs.com/watched?demo=true", function(data, status){
		var date= data.date.slice(data.date.length-30,data.date.length);
		var data_users= data.users.slice(data.users.length-30,data.users.length);
		var data_duration= data.duration.slice(data.duration.length-30,data.duration.length);
	jQuery('#engage_analysis').highcharts({
		title: {
			text: 'Users Engagemment',
			x: -20 //center
		},
		subtitle: {
			text: 'Source: recosenselabs.com',
			x: -20
		},
		xAxis: {
			categories: date
		},
		yAxis: {
			title: {
				text: 'Counts'
			},
			plotLines: [{
				value: 0,
				width: 1,
				color: '#808080'
			}]
		},
		series: [{
			name: 'No. of users',
			data:data_users
		}, {
			name: 'Duration In hours',
			data:data_duration
		}]
	});
	});
});
