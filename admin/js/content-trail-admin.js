jQuery( function ( ) {
	jQuery( '#get_reco_key' ).click( function ( ) {
		var email_id = jQuery( '#email' ).val( );
		jQuery.ajax( {
			url: rct_data.api_url + 'verify_email?email_id=' + email_id + '&guid=' + rct_data.guid,
			method: 'GET',
			dataType: "json",
			success: function ( data ) {
				if ( data['message'] == 'email sent with key' ) {
					jQuery( '.check_email' ).text( 'We have sent you the RecoSense Key at provided Email' );
					jQuery( '.rct_key' ).show();
				}
			}, error: function ( ) {
				jQuery( '.check_email' ).text( 'Not valid Email Id' );
			}
		} );
	} );
	//No RecoSensekey
	//jQuery( '#verify_reco_key' ).click( function ( ) {
	jQuery( '#pull_content' ).click( function ( ) {
		var key = jQuery( '#reco_key' ).val( );
		jQuery.ajax( {
			//No RecoSensekey
			//url: rct_data.api_url + 'verify_key?key=' + key + '&guid=' + rct_data.guid,
			url: rct_data.api_url + 'verify_key?guid=' + rct_data.guid,
			method: 'GET',
			dataType: "json",
			success: function ( data ) {
				jQuery( '.valid_key' ).html( '<label style="margin-left:10px;color:green;">Key Validated &#9745</label>' );
				jQuery( '.rct_configure' ).show();
				jQuery.post( rct_data.ajax_url, { 'action': 'rct_save_key', 'key': key }, function ( resp ) {
					if ( resp === '1' ) {
						location.reload( );
					}
				} );

			},
			error: function ( ) {
				jQuery( '.valid_key' ).html( '<label style="margin-left:10px;color:red;">Invalid Key &#9746;</label>' );
			}
		} );
	} );
	jQuery( function () {
		if ( scriptParams != undefined ) {
			var posts = scriptParams;
			jQuery.ajax( {
				url: rct_data.api_url + 'polling',
				method: 'POST',
				contentType: "application/json",
				data: JSON.stringify( posts )
			} );
		}
	} );
} );


jQuery( function () {
	jQuery( '.wid_type .label' ).click( function () {
		jQuery( '.wid_type .label' ).removeClass( 'highlight' );
		jQuery( this ).addClass( 'highlight' );
	} );
	jQuery( '.wid_layout .label' ).click( function () {
		jQuery( '.wid_layout .label' ).removeClass( 'highlight' );
		jQuery( this ).addClass( 'highlight' );
	} );
	jQuery( '.update_config' ).click( function () {
		var data_arry = { };
		var pattern = '';
		var wid_output = jQuery( '.wid_type .highlight' ).text();
		var wid_layout = jQuery( '.wid_layout .highlight' ).text();
		/*jQuery('.slider_opt input[type=radio]').each(function(){
			if(jQuery(this).is(':checked')){
				var slider_opt = jQuery(this).attr('value');
				if(slider_opt=='1')
					data_arry['slider_opt'] = 1;
				else
					data_arry['slider_opt'] = 0;
			}
		});*/
		//No RecoSensekey
		//data_arry['recosense_key'] = jQuery( '#reco_key' ).val();
		data_arry['guid'] = rct_data.guid;
		jQuery( '.border' ).each( function () {
			if ( jQuery( this ).is( ':checked' ) )
				pattern = jQuery( this ).attr( 'value' );
		} );
		if ( !wid_output ) {
			alert( "Choose Widget Output Type" );
			return false;
		}
		if ( !wid_layout ) {
			alert( "Choose Widget Layout" );
			return false;
		}

		if ( !pattern ) {
			alert( "Choose look" );
			return false;
		} else if ( pattern == 'border' ) {
			data_arry['reco_container'] 	= 'border: 1px solid rgb(221, 221, 221); margin-bottom: 10px; padding: 10px; min-height:115px';
			data_arry['thumb_container'] 	= 'width: 100px;float: left; height: 95px;';
			data_arry['title_container'] 	= 'margin-left: 110px;height: 100%;';
			data_arry['image'] 				= 'width:100%;height:100%';
		} else if ( pattern == 'two-column' ) {
			data_arry['reco_container'] 	= 'border-bottom: 1px solid rgb(221, 221, 221); margin-bottom: 10px; padding: 10px; height: 223px;width:50%;float:left';
			data_arry['thumb_container'] 	= 'width: 100px; height: 92px;';
			data_arry['title_container'] 	= 'height: 100%;';
			data_arry['image'] 				= 'width:100%;height:100%';
		} else if ( pattern == 'border-bottom' ) {
			data_arry['reco_container'] 	= 'border-bottom: 1px solid #ddd;margin-bottom:10px;padding:10px;min-height: 115px;';
			data_arry['thumb_container'] 	= 'width: 100px;float: left;height: 92px;';
			data_arry['title_container'] 	= 'margin-left: 110px;height: 100%;';
			data_arry['image'] 				= 'width:100%;height:100%';
		} else {
			data_arry['reco_container'] 	= 'border: 1px solid #ddd;margin-bottom:10px;padding:10px;/* height: 123px; */';
			data_arry['thumb_container'] 	= '/* width: 100px; *//* float: left; *//* height: 100px; */';
			data_arry['title_container'] 	= '/* margin-left: 110px; */height: 100%;font-size: 20px;';
			data_arry['image'] 				= 'height:auto;width:100%';
		}
		if(wid_output=='Texts only'){
			data_arry['title_container'] 	=	 'margin-left: 0px;height: 100%;';
			data_arry['reco_container'] 	=	 'border-bottom: 1px solid #ddd;margin-bottom:10px;padding:10px;min-height: 75px;';
		}
		if ( jQuery( '.wid_type .label' ).hasClass( 'highlight' ) )
			data_arry['widget_type'] = jQuery( '.wid_type .highlight' ).text();
		if ( jQuery( '.wid_layout .label' ).hasClass( 'highlight' ) )
			data_arry['widget_layout'] = jQuery( '.wid_layout .highlight' ).attr( 'clmns' );
		jQuery.ajax( {
			url: 'http://wpplugin.recosenselabs.com/webhooks/layout',
			method: 'POST',
			contentType: "application/json",
			dataType: "json",
			data: JSON.stringify( data_arry ),
			success: function ( data ) {
				jQuery( '.domain_error' ).hide();
				alert( "updated" );
			},
			error: function ( data ) {
				jQuery( '.domain_error' ).show();
			}

		} );
	} );
	if(rct_data.key!==undefined && rct_data.key.length>0){
		jQuery('.rct_configure').show();
	}
} );
