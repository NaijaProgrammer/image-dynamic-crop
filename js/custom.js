(function($) 
{
/*
	function preview(img, selection) 
	{
		if (!selection.width || !selection.height)
		{
			return;
		}
		
		//200 is the #preview dimension, change this to your liking
		var scaleX = 200 / selection.width; 
		var scaleY = 200 / selection.height;
	
		jQuery('#preview img').css({
			width: Math.round(scaleX * jQuery('#big').attr('width')),
			height: Math.round(scaleY * jQuery('#big').attr('height')),
			marginLeft: -Math.round(scaleX * selection.x1),
			marginTop: -Math.round(scaleY * selection.y1)
		});
	
		jQuery('.x1').val(selection.x1);
		jQuery('.y1').val(selection.y1);
		jQuery('.x2').val(selection.x2);
		jQuery('.y2').val(selection.y2);
		jQuery('.width').val(selection.width);
		jQuery('.height').val(selection.height); 
	}
*/

	$("form").submit(function() 
	{ 
		var fname = jQuery(this).attr('name'); //this.name; //get the name of the form, the name of our dynamic form is 'upload_thumb'
		var formVariables = getFormVariables(); //line added by Michael Orji, defined in the main php class
		var iframeId  = formVariables.iframeId; //ditto
		var iframeName = formVariables.iframeName; //ditto
		var iframeObj = $O(iframeId) ? $O(iframeId) : document.getElementsByName(iframeName)[0]; //$O(iframeId)  || jQuery('iframe[name="' + iframeName + '"]'); //ditto
		
		/*
		for(var x in formVariables)
		{
			console.log(x + '=' + formVariables[x] + "\r\n");
		}
		var ev = new Function(formVariables.cropSuccessCallback);
		
		alert(typeof ev);
		*/
		
		/*
		* if the form being submitted is our dynamic form,
		* check to ensure they have made a thumbnail selection
		*/
		if(fname == 'upload_thumb')
		{
			if($O('x1').value =="" || $O('y1').value =="" || $O('width').value <="0" || $O('height').value <="0")
			{
				alert("You must make a selection first");
				return false;
			}
		}
	/* Keep working on this
		if(typeof formVariables.uploadStatusCallback === 'string')
		{
			uploadStatusCallback = new Function(formVariables.uploadStatusCallback);
		}
	*/	
		if(typeof uploadStatusCallback === 'function')
		{ 
			uploadStatusCallback(); //$('#notice').text('Digesting...').fadeIn();
		}
					  
		//iframeObj.onload = 
		EventManager.attachEventListener(iframeObj, 'load', function()
		{
			function getImageFromIframe()
			{
				var img = trim(getIFrameContent(iframeObj)); 
			
				/*
				* where the iframe's content contains more than just the image's url,
				* get only the image url.
				* Example of such a case, is in the integration of this plugin with the MultimediaManager plugin,
				* whose iframe's body contains some scripts to run on successful upload.
				*/
				//img = img.substring(0, img.indexOf(".")+4);
				img = img.substring(img.lastIndexOf('http:'), img.lastIndexOf(".")+4); //look for a better way to make sure we are retrieving the path to a valid image
				//setIFrameContent(iframeId, ""); //just incase u need to put something else in the iframe, we don't want the image string conflicing with it
				return img;
			}
			
			if(fname != 'upload_thumb')
			{								
				var img = getImageFromIframe()
			
				if(formVariables.inputContainerId)
				{
					var bigImgView = $O(formVariables.inputContainerId);
					bigImgView.id  = formVariables.inputContainerId;
				}
				
				else
				{
					/*
					* dynamically create a large image view, and insert it before our dynamic form
					*/
					var bigImgView = document.createElement("div");
					bigImgView.id = "div_upload_big";
				}
				
				$O('upload_thumb').parentNode.insertBefore( bigImgView, $O('upload_thumb') );
		 
				/*
				* set an id for our large image view
				*/
				var img_id = bigImgView.id + '-big'; //'big';
				
				/*
				* set the src attribute of our dynamic form hidden input field called img_src to the image loaded from our Iframe.
				* This gets passed to the server-side processing script when our dynamic form is submitted
				*/
				jQuery('.img_src').attr('value', img); /////// get image source , this will be passed into PHP

				if(formVariables.previewContainerId)
				{
					//display the image in the preview container
					$Html(formVariables.previewContainerId, '<img src="'+img+'" />');
				}
				
				//$Html('div_'+fname, '<img id="'+img_id+'" src="'+img+'" />');
				//$Html('div_upload_big', '<img id="'+img_id+'" src="'+img+'" />');
				$Html(bigImgView.id, '<img id="'+img_id+'" src="'+img+'" />');
				
				fname = 'upload_thumb'; //set the name, so that else part below will work
			}
		    
			else if(fname == 'upload_thumb')
			{ 
				var img = getImageFromIframe();
				if(formVariables.outputContainerId)
				{
					$Html(formVariables.outputContainerId, '<img id="" src="'+img+'" />'); //display the image in the thumbnail container
				}
				
			/* Keep working on this	
				if(typeof formVariables.cropSuccessCallback === 'string')
				{
					cropSuccessCallback = new Function(formVariables.cropSuccessCallback);
				}
			*/	
				if(typeof cropSuccessCallback === 'function')
				{  
					cropSuccessCallback(img);
				}
			}

			jQuery('#upload_thumb').show(); //display the dynamic form
			
			//area select plugin http://odyniec.net/projects/imgareaselect/examples.html 
			//jQuery('#big').imgAreaSelect({
			$('#' + img_id).imgAreaSelect({
				aspectRatio: '1:1', 
				handles: true,
				fadeSpeed: 200,
				resizeable:false,
				maxHeight:150,
				maxWidth:150,			
				minHeight:100,
				minWidth:50,			
				onSelectChange: preview
			});
		
			//jQuery('#notice').fadeOut();
		
			// we have to remove the values
			jQuery('.width , .height , .x1 , .y1 , #file').val('');
		}
		);
		
		function preview(img, selection) 
		{
			if (!selection.width || !selection.height)
			{
				return;
			}
			
			if( formVariables.previewContainerId )
			{
				//200 is the #preview dimension, change this to your liking
				var scaleX = 200 / selection.width; 
				var scaleY = 200 / selection.height;
			
				jQuery('#' + formVariables.previewContainerId + ' img').css({
					width: Math.round(scaleX * jQuery('#big').attr('width')),
					height: Math.round(scaleY * jQuery('#big').attr('height')),
					marginLeft: -Math.round(scaleX * selection.x1),
					marginTop: -Math.round(scaleY * selection.y1)
				});
			}
		
			jQuery('.x1').val(selection.x1);
			jQuery('.y1').val(selection.y1);
			jQuery('.x2').val(selection.x2);
			jQuery('.y2').val(selection.y2);
			jQuery('.width').val(selection.width);
			jQuery('.height').val(selection.height); 
		}
	});
})(jQuery);
