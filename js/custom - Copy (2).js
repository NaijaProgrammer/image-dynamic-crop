$(function() 
{
jQuery.noConflict( true );
function preview(img, selection) 
{
    if (!selection.width || !selection.height)
        return;
		
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

$("form").submit(function() {
	
	var fname = this.name;
	var formVariables = getFormVariables(); //line added by Michael Orji, defined in the main php class
	var iframeId = formVariables.iframeId; //ditto
	var iframeObj = $O(iframeId); //ditto
	
	//check if they have made a thumbnail selection
	if(fname == 'upload_thumb')
	{
		if($O('x1').value =="" || $O('y1').value =="" || $O('width').value <="0" || $O('height').value <="0")
		{
			alert("You must make a selection first");
			return false;
		}
	}
	
	jQuery('#notice').text('Digesting...').fadeIn();
								  
	iframeObj.onload = function()
	{									
		var img = trim(getIFrameContent(iframeObj));
		img = img.substring(0, img.indexOf(".")+4); //look for a better way to make sure we are retrieving the path to a valid image
		
		//setIFrameContent(iframeId, ""); //just incase u need to put something else in the iframe, we don't want the image string conflicing with it
		
		var bigImgView = document.createElement("div");
		bigImgView.id = "div_upload_big";
		$O('upload_thumb').parentNode.insertBefore( bigImgView, $O('upload_thumb') );
		
		if(fname != 'upload_thumb')
		{ 
			fname = "upload_big"; //line added by Michael Orji
			var img_id = 'big';
			
			/////// get image source , this will be passed into PHP	
			jQuery('.img_src').attr('value', img)
			
			$Html('preview', '<img src="'+img+'" />');
		}
		
		$Html('div_'+fname, '<img id="'+img_id+'" src="'+img+'" />');

		//if( jQuery(img).attr('class') != 'uperror' )
		//{
			jQuery('#upload_thumb').show();
			
			//area select plugin http://odyniec.net/projects/imgareaselect/examples.html 
			jQuery('#big').imgAreaSelect({
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
		//}
		//else
		//{
			//jQuery('#upload_thumb').hide();
		//}
		
		jQuery('#notice').fadeOut();
		
		// we have to remove the values
		jQuery('.width , .height , .x1 , .y1 , #file').val('');
	}
  });
});