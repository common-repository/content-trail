function capitalize( str ) {
	return str.replace( /\w\S*/g, function ( txt ) {
		return txt.charAt( 0 ).toUpperCase( ) + txt.substr( 1 ).toLowerCase( );
	} );
}
jQuery( document ).ready( function () {
	var fp1 = new Recoprint( );
	var device_id = fp1.get( );

	jQuery( document ).on( "click", "a", function ( event ) {
		event.preventDefault( );
		var item_id = jQuery( this ).attr( 'item_id' );
		var url = '';
		var user_details = { };
		user_details['device_id'] = device_id;
		user_details['guid'] = window.location.hostname;
		if ( item_id !== undefined ) {
			url = jQuery( this ).attr( 'href' );
			user_details['item_id'] = item_id;
			jQuery.ajax( {
				url: rct_data.api_url + 'webhooks',
				type: "POST",
				contentType: "application/json",
				data: JSON.stringify( user_details ),
				success: function ( response ) {
					window.location = url;
				}, error: function ( ) {
					window.location = url;
				}
			} );
		} else if ( item_id !== '0' || item_id === undefined ) {

			url = jQuery( this ).attr( 'href' );
			jQuery.ajax( {
				url: rct_data.ajax_url,
				type: "POST",
				data: ( {
					action: "rct_url_to_post_id",
					url: url
				} ),
				success: function ( value ) {
					if ( value !== '0' ) {
						user_details['item_id'] = value;
						jQuery.ajax( {
							url: rct_data.api_url + 'webhooks',
							type: "POST",
							contentType: "application/json",
							data: JSON.stringify( user_details ),
							success: function ( response ) {
								window.location = url;
							}, error: function ( ) {
								window.location = url;
							}
						} );
					} else {
						window.location = url;
					}
				}
			} );
		}
	} );

	//Personalisation Widget
	if ( jQuery( '.RecoSense_personalisation_container' ).length ) {
		jQuery.post( rct_data.ajax_url, { action: 'rct_api_call', endpoint: 'personalisation', device_id: device_id }, function ( data ) {
		data = JSON.parse( data );	
				var personalisation = '';
				var str = { };
				var config_widget = { };
				if ( data['config'] ) {
					config_widget['widget_class'] 		=	 data['config']['widget_layout'];
					config_widget['reco_container'] 	=	 data['config']['reco_container'];
					config_widget['widget_type'] 		=	 data['config']['widget_type'];
					config_widget['thumb_container']	=	 data['config']['thumb_container'];
					config_widget['image'] 				=	 data['config']['image'];
					config_widget['title_container']	=	 data['config']['title_container'];
				} else {
					config_widget['widget_class'] 		=	 'column';
					config_widget['reco_container'] 	=	 'border-bottom: 1px solid #ddd;margin-bottom:10px;padding:10px;min-height: 115px;';
					config_widget['widget_type'] 		=	 'Images and Texts';
					config_widget['thumb_container']	=	 'width: 100px;float: left;height: 92px;';
					config_widget['image'] 				=	 'width:100%;height:100%';
					config_widget['title_container']	=	 'margin-left: 110px;height: 100%;';
				}
				if ( data['content'] ) {
					personalisation += '<h3>' + capitalize( data['content'][0]['rail_name'] ) + '</h3>';
					personalisation += '<ul>';
					personalisation += '<li style="border:none;list-style:none">';
					personalisation += tag_cloud( data );
					personalisation += '</li>';
					jQuery.each( data['content'], function ( index, value ) {
						jQuery.each( data['content'][index]['items'], function ( ind, val ) {
							str[ind] = data['content'][index]['items'][ind]['title']
							if ( str[ind].length > 90 )
								str[ind] = str[ind].substring( 0, 90 ) + "...";
							personalisation += '<li style="border:none;list-style:none">';
							personalisation += '<div class="' + config_widget['widget_class'] + '" style="' + config_widget['reco_container'] + '">';
							if ( config_widget['widget_type']=='Images and Texts' ) {
								personalisation += '<div class="image_container" style="' + config_widget['thumb_container'] + '">';
								if ( data['content'][index]['items'][ind]['image'] != undefined )
									personalisation += '<img src="' + data['content'][index]['items'][ind]['image'] + '" style="' + config_widget['image'] + '"\>';
								personalisation += '</div>';
								personalisation += '<div class="title_container" style="' + config_widget['title_container'] + '">';
								personalisation += '<a item_id="' + data['content'][index]['items'][ind]['item_id'] + '" href="' + data['content'][index]['items'][ind]['url'] + '">' + str[ind] + '</a>';
								personalisation += '</div>';
							}else if(config_widget['widget_type']=='Texts only'){
								personalisation += '<div class="title_container" style="' + config_widget['title_container'] + '">';
								personalisation += '<a item_id="' + data['content'][index]['items'][ind]['item_id'] + '" href="' + data['content'][index]['items'][ind]['url'] + '">' + str[ind] + '</a>';
								personalisation += '</div>';
							} else {
								personalisation += '<a href="' + data['content'][index]['items'][ind]['url'] + '">' + str[ind] + '</p>';
							}
							personalisation += '</div>';
							personalisation += '</li>';
						} );
					} );
					personalisation += '</ul>';
				} else {
					personalisation += '<h3>You May Also Like</h3>';
					personalisation += '<ul>';
					personalisation += '<li style="border:none;list-style:none">';
					personalisation += tag_cloud( data );
					personalisation += '</li>';
					personalisation += '</ul>';
				}
				//if(data['config']['slider_opt']==1){
					//jQuery( '.RecoSense_personalisation_container' ).html( personalisation ).hide().show("slide", {direction: "right"});
				//}else{
					jQuery( '.RecoSense_personalisation_container' ).html( personalisation );	
				//}
			});
	}

	//Recommendation Widget
	if ( jQuery( '.RecoSense_container' ).length ) {
		jQuery.post( rct_data.ajax_url, { action: 'rct_api_call', endpoint: 'recommendation', item_id: rct_data.item_id }, function ( data ) {
			data = JSON.parse( data );
			var recommendation = '';
			var str = { };
			var config_widget = { };
			if ( data['config'] ) {
				config_widget['widget_class'] = data['config']['widget_layout'];
				config_widget['reco_container'] = data['config']['reco_container'];
				config_widget['widget_type'] = data['config']['widget_type'];
				config_widget['thumb_container'] = data['config']['thumb_container'];
				config_widget['image'] = data['config']['image'];
				config_widget['title_container'] = data['config']['title_container'];
			} else {
				config_widget['widget_class'] = 'column';
				config_widget['reco_container'] = 'border-bottom: 1px solid #ddd;margin-bottom:10px;padding:10px;min-height: 115px;';
				config_widget['widget_type'] = 'Images and Texts';
				config_widget['thumb_container'] = 'width: 100px;float: left;height: 92px;';
				config_widget['image'] = 'width:100%;height:100%';
				config_widget['title_container'] = 'margin-left: 110px;height: 100%;';
			}
			if ( data['content'] ) {
				recommendation += '<h3>' + capitalize( data['content'][0]['rail_name'] ) + '</h3>';
				recommendation += '<ul>';
				recommendation += '<li style="border:none;list-style:none">';
				recommendation += tag_cloud( data );
				recommendation += '</li>';
				jQuery.each( data['content'], function ( index, value ) {
					//personalisation += '<h3>'+capitalize(data['content'][0]['rail_name'])+'</h3>';
					jQuery.each( data['content'][index]['items'], function ( ind, val ) {
						str[ind] = data['content'][index]['items'][ind]['title']
						if ( str[ind].length > 90 )
							str[ind] = str[ind].substring( 0, 90 ) + "...";
						recommendation += '<li style="border:none;list-style:none">';
						recommendation += '<div class="' + config_widget['widget_class'] + '" style="' + config_widget['reco_container'] + '">'
						if ( config_widget['widget_type'] ) {
							recommendation += '<div class="image_container" style="' + config_widget['thumb_container'] + '">';
							if ( data['content'][index]['items'][ind]['image'] != undefined )
								recommendation += '<img src="' + data['content'][index]['items'][ind]['image'] + '" style="' + config_widget['image'] + '"\>';
							recommendation += '</div>';
							recommendation += '<div class="title_container" style="' + config_widget['title_container'] + '">';
							recommendation += '<a item_id="' + data['content'][index]['items'][ind]['item_id'] + '" href="' + data['content'][index]['items'][ind]['url'] + '">' + str[ind] + '</a>';
							recommendation += '</div>';
						} else {
							recommendation += '<a href="' + data['content'][index]['items'][ind]['url'] + '">' + str[ind] + '</p>';
						}
						recommendation += '</div>';
						recommendation += '</li>';
					} );
				} );
				recommendation += '</ul>';
			} else {
				recommendation += '<h3>Recommendation</h3>';
				recommendation += '<ul>';
				recommendation += '<li style="border:none;list-style:none">';
				recommendation += tag_cloud( data );
				recommendation += '</li>';
				recommendation += '</ul>';
			}
			//if(data['config']['slider_opt']==1){
				//jQuery( '.RecoSense_container' ).html( recommendation ).hide().show("slide", {direction: "right"});
			//}else{
				jQuery( '.RecoSense_container' ).html( recommendation );	
			//}
			
		} );

	}

	//Trending Widget
	if ( jQuery( '.RecoSense_trending_container' ).length ) {
		jQuery.post( rct_data.ajax_url, { action: 'rct_api_call', endpoint: 'trending' }, function ( data ) {
			data = JSON.parse( data );
			var str = { };
			var trending = '';
			var config_widget = { };
			if ( data['config'] ) {
				config_widget['widget_class'] = data['config']['widget_layout'];
				config_widget['reco_container'] = data['config']['reco_container'];
				config_widget['widget_type'] = data['config']['widget_type'];
				config_widget['thumb_container'] = data['config']['thumb_container'];
				config_widget['image'] = data['config']['image'];
				config_widget['title_container'] = data['config']['title_container'];
			} else {
				config_widget['widget_class'] = 'column';
				config_widget['reco_container'] = 'border-bottom: 1px solid #ddd;margin-bottom:10px;padding:10px;min-height: 115px;';
				config_widget['widget_type'] = 'Images and Texts';
				config_widget['thumb_container'] = 'width: 100px;float: left;height: 92px;';
				config_widget['image'] = 'width:100%;height:100%';
				config_widget['title_container'] = 'margin-left: 110px;height: 100%;';
			}
			if ( data['content'] ) {
				trending += '<h3>' + capitalize( data['content'][0]['rail_name'] ) + '</h3>';
				trending += '<ul>';
				trending += '<li style="border:none;list-style:none">';
				trending += tag_cloud( data );
				trending += '</li>';
				jQuery.each( data['content'], function ( index, value ) {
					jQuery.each( data['content'][index]['items'], function ( ind, val ) {
						str[ind] = data['content'][index]['items'][ind]['title']
						if ( str[ind].length > 90 )
							str[ind] = str[ind].substring( 0, 90 ) + "...";
						trending += '<li style="border:none;list-style:none">';
						trending += '<div class="' + config_widget['widget_class'] + '" style="' + config_widget['reco_container'] + '">'
						if ( config_widget['widget_type'] ) {
							trending += '<div class="image_container" style="' + config_widget['thumb_container'] + '">';
							if ( data['content'][index]['items'][ind]['image'] != undefined )
								trending += '<img src="' + data['content'][index]['items'][ind]['image'] + '" style="' + config_widget['image'] + '"\>';
							trending += '</div>';
							trending += '<div class="title_container" style="' + config_widget['title_container'] + '">';
							trending += '<a item_id="' + data['content'][index]['items'][ind]['item_id'] + '" href="' + data['content'][index]['items'][ind]['url'] + '">' + str[ind] + '</a>';
							trending += '</div>';
						} else {
							trending += '<a href="' + data['content'][index]['items'][ind]['url'] + '">' + str[ind] + '</p>';
						}
						trending += '</div>';
						trending += '</li>';
					} );
				} );
				trending += '</ul>';
			} else {
				trending += '<h3>Trending</h3>';
				trending += '<ul>';
				trending += '<li style="border:none;list-style:none">';
				trending += tag_cloud( data );
				trending += '</li>';
				trending += '</ul>';
			}
			//if(data['config']['slider_opt']==1){
				//jQuery( '.RecoSense_trending_container' ).html( trending ).hide().show("slide", {direction: "right"});
			//}else{
				jQuery( '.RecoSense_trending_container' ).html( trending );	
			//}
			
		});
	}
});


function tag_cloud( data ) {
	var tags = '<div style="border:1px solid #ddd;padding:8px;word-wrap:break-word;">';
	jQuery.each( data['tag_cloud'], function ( ind, val ) {
		jQuery.each( data['tag_cloud'][ind], function ( index, value ) {
			if ( index != 'url' ) {
				if ( val[index] > 8 )
					tags += '<a style="font-size:' + 8 * 4 + 'px;"href="' + val['url'] + '" >' + capitalize( index ) + '</a>&nbsp;&nbsp;';
				else if ( val[index] > 6 && val[index] <= 8 )
					tags += '<a style="font-size:' + 8 * 3 + 'px;"href="' + val['url'] + '" >' + capitalize( index ) + '</a>&nbsp;&nbsp;';
				else if ( val[index] > 3 && val[index] <= 6 )
					tags += '<a style="font-size:' + 8 * 2 + 'px;"href="' + val['url'] + '" >' + capitalize( index ) + '</a>&nbsp;&nbsp;';
				else
					tags += '<a style="font-size:' + 8 * val[index] + 'px;"href="' + val['url'] + '" >' + capitalize( index ) + '</a>&nbsp;&nbsp;';
			}
		} );
	} );
	tags += '</div>';
	return tags;
}
