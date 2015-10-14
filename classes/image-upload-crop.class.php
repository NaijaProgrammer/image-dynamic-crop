<?php
/*
* @author Michael Orji
*/
class ImageUploadCrop
{
    /**
	* if your form already has another iframe as its target, supply the iframe's id, otherwise, leave it empty
	*
	* 'form_id' is the id of the form to which to attach the dynamic crop functionality
	*/
	public static function get_dynamic_crop_enabler( $opts = array() )
	{
		$defs = array('form_id'=>'', 'action_page'=>$_SERVER['PHP_SELF'], 'include_thumb_scale_details'=>false, 'iframe_id'=>'', 'iframe_name'=>'');
		ArrayManipulator::copy_array($defs, $opts);
		foreach($defs AS $key => $value)
		{
			$$key = $value;
		}
		$app_url = self::_get_app_url(); 
		$iframe_name = !empty($iframe_name) ? $iframe_name : "upload_target"; 

		$html = ''.
		'<link rel="stylesheet" type="text/css" href="'. $app_url. '/css/imgareaselect-default.css" />'.
		'<link rel="stylesheet" type="text/css" href="'. $app_url. '/css/styles.css" />'. 
		'<script type="text/javascript" src="'. $app_url. '/js/JSLib.js"></script>'.
		'<script type="text/javascript" src="'. $app_url. '/js/jquery.min.js"></script>'.
		'<script type="text/javascript" src="'. $app_url. '/js/jquery.imgareaselect.min.js"></script>'.
		'<script type="text/javascript" src="'. $app_url. '/js/custom.js"></script>'.
		
		//'<div id="notice">Digesting..</div>'.
		'<div id="uploaded">'.
		 '<form style="display:none;" name="upload_thumb" id="upload_thumb" method="post" action="'. $action_page. '?act=create-thumb" target="'. $iframe_name. '">'.
		  '<input type="hidden" name="img_src" id="img_src" class="img_src" />'.
		  '<input type="hidden" name="height" value="0" id="height" class="height" />'.
		  '<input type="hidden" name="width" value="0" id="width" class="width" />'.  
		  '<input type="hidden" id="y1" class="y1" name="y" />'.
		  '<input type="hidden" id="x1" class="x1" name="x" />'.
		  '<input type="hidden" id="y2" class="y2" name="y1" />'.
		  '<input type="hidden" id="x2" class="x2" name="x1" />'.                        
		  '<input type="submit" value="create thumbnail" />'.
		 '</form>'.   
		'</div>'.
		'<div id="thumbnail">'.
		 '<h3>Preview</h3>'.
		 '<div id="preview"></div>'.                            
		 '<h3>Thumbnail</h3>'.
		 '<div id="div_upload_thumb"></div>';
		 
		 if($include_thumb_scale_details)
		 {
			$html .= ''.
			'<div id="details">'.
			 '<table width="200">'.
              '<tr><td colspan="2">Image Source<br /><input type="text" name="img_src" class="img_src" size="35" /></td></tr>'.
              '<tr>'.
               '<td>Height<br /><input type="text" name="height" class="height" size="5" /></td>'.
               '<td>Width<br /><input type="text" name="width" class="width" size="5"/></td>'.
              '</tr>'.
              '<tr>'.
               '<td>Y1<br /><input type="text" class="y1"  size="5"/></td>'.
               '<td>X1<br /><input type="text" class="x1" size="5" /></td>'.
              '</tr>'.
              '<tr>'.
               '<td>Y2<br /><input type="text" class="y2" size="5" /></td>'.
               '<td>X2<br /><input type="text" class="x2" size="5" /></td>'.
              '</tr>'.
             '</table>'.
            '</div>';
		 }
		
		$html .= '</div>';

		$create_iframe = empty($iframe_id) ? true : false;
		if($create_iframe)
		{
			$iframe_id = "upload_target";
			$html .= '<iframe id="'.$iframe_id.'" name="'.$iframe_name.'" src="" style="width:100%;height:400px;border:1px solid #ccc; display:normal"></iframe>';
		}
		if(!$create_iframe)
		{
			$html .= '<script type="text/javascript">document.getElementById("'. $iframe_id. '").setAttribute("name", "'. $iframe_name. '");</script>';	
		}
		if(!empty($form_id))
		{
			$html .= ''.
    			'<script type="text/javascript">'.
			     '$O("'. $form_id. '").setAttribute("target", "'. $iframe_name. '");'.
			     'function getFormVariables()'.
				 '{'.
					'return {"formId":"'. $form_id. '", "iframeName":"'. $iframe_name. '", "iframeId":"'. $iframe_id. '"}'.
				 '}'.
				'</script>';
		}
		
		return $html;
	}
	public static function process_img_crop($opts=array())
	{
		$app_directory = self::_get_app_directory();
		$uploads_dir   = $app_directory.'/uploads/';
		$defs = array( 
		'exit_on_end'                   =>true, 
		'file_field_name'               =>'photo', 
		'temp_directory'                =>$app_directory.$uploads_dir. 'temp/', 
		'upload_directory'              =>$uploads_dir, 'final_filename'=>time(), 
		'file_extension'                =>'jpg', 
		'width'                         =>450, 
		'height'                        =>450, 
		'save_original_image'           =>true, 
		'original_image_save_directory' =>$uploads_dir.'original/',
		'on_end_callback'               =>'',
		'on_end_callback_path'          =>''
		);
		ArrayManipulator::copy_array($defs, $opts);
		foreach($defs AS $key => $value)
		{ 
			$$key = $value; 
		}
		$width  = isset($_POST['width'])  ? $_POST['width']  : $width;
		$height = isset($_POST['height']) ? $_POST['height'] : $height;
		$arr    = array( 
		'exit_on_end'                   =>$exit_on_end, 
		'tempdir'                       =>$temp_directory, 
		'height'                        =>$height, 
		'width'                         =>$width, 
		'final_filename'                =>$final_filename, 
		'file_extension'                =>$file_extension, 
		'save_original_image'           =>$save_original_image,  
		'original_image_save_directory' =>$original_image_save_directory,
		'on_end_callback'               =>$on_end_callback,
		'on_end_callback_path'          =>$on_end_callback_path
		);
		
		/**
		* dynamic form created by get_dynamic_crop_enabler()
		* crops and saves the image to $final_filename, specified in the options
		* cropping is based on the dimensions captured by JS
		* using the x and y values of hidden input fields created by the dynamic form
		*/
		if( isset($_GET['act']) && ($_GET['act'] == 'create-thumb') )
		{
			$thumb_arr = array_merge($arr, array('uploaddir'=>$upload_directory.'/', 'x'=>$_POST['x'], 'y'=>$_POST['y'], 'img_src'=>$_POST['img_src'], 'thumb'=>true ));
			self::_resizeThumb($thumb_arr);
		}
		
		/*
		* Original form submitted by user,
		* crops and saves the image to the original directory, specified in the array of options
		*/
		else
		{
			$big_arr = array_merge($arr, array( 'uploaddir'=>$original_image_save_directory, 'image_file'=>$_FILES[$file_field_name], 'x'=>0, 'y'=>0 ) );
			self::_resizeImg($big_arr);	
		}
	}
	private static function _resizeImg($arr)
	{
		$uploaddir 	= $arr['uploaddir'];
		$tempdir	= $arr['tempdir'];
		$image_file = $arr['image_file'];
		$img_parts 	= pathinfo($image_file['name']);
		$new_name 	= strtolower($arr['final_filename']. '.'. $arr['file_extension']); //.'.'.$img_parts['extension']);
		
		$arr['temp_file_destination']  = $tempdir;
		$arr['temp_file_name']         = $new_name;
        $arr['final_file_destination'] = $uploaddir;
		$arr['final_file_name']        = $new_name;
				
		echo self::_cropImg($arr);	

		if( !$arr['save_original_image'] )
		{
			if(($tempdir.'/'. $new_name) != ($uploaddir. '/'. $new_name))
			{
				unlink($arr['temp_file_destination']. $arr['temp_file_name']); 
			}
		}
		
		exit;
	}
	private static function _resizeThumb($arr)
	{	
		$temp_file_path = $arr['img_src'];
		if( StringManipulator::get_last_character_in_string($temp_file_path) == '/' )
		{
			$temp_file_path = substr($temp_file_path, 0, -1);
		}
		$arr['temp_file_destination']  = substr( $temp_file_path, 0, strrpos($temp_file_path, '/') );
		$arr['temp_file_name']         = substr( $temp_file_path, strrpos($temp_file_path, '/') );
		$arr['final_file_destination'] = $arr['uploaddir'];
		$arr['final_file_name']        = strtolower($arr['final_filename']. '.'. $arr['file_extension']); //'.jpg';
		echo self::_cropImg($arr);
		
		if($arr['exit_on_end'])
		{
			exit;
		}
		$callback = $arr['on_end_callback'];
		$callback_path = $arr['on_end_callback_path'];
		if(file_exists($callback_path))
		{
			require_once($callback_path);
			if(function_exists($callback))
			{
				$callback();
			}
		}
	}
	private static function _cropImg($arr)
	{
		include_once(self::_get_app_directory(). '/lib/wideimage/WideImage.php');
		$height  = $arr['height'];
		$width   = $arr['width'];
		$x		 = $arr['x'];
		$y		 = $arr['y'];
		$final_filename = $arr['final_file_destination']. '/'. $arr['final_file_name'];
		$temp_filename  = $arr['temp_file_destination']. '/'. $arr['temp_file_name']; 
		if( isset($arr['thumb']) && ($arr['thumb'] === true) )
		{
			WideImage::load($temp_filename)->crop($x, $y, $width, $height)->saveToFile($final_filename);
			if( file_exists($arr['original_image_save_directory']. '/'. $arr['final_file_name']) && (!$arr['save_original_image']) )
			{
				unlink($arr['original_image_save_directory']. '/'. $arr['final_file_name']);
			}
		}
		else
		{
			WideImage::load($temp_filename)->resize($width, $height, $fit = 'inside', $scale = 'any')->saveToFile($final_filename);
		}
		return DirectoryInspector::get_resource_url( $final_filename, self::_get_app_url() );
	}
	private static function _get_app_directory()
	{
		return IMAGE_CROP_APP_DIR_PATH;
	}
	private static function _get_app_url()
	{
		return IMAGE_CROP_APP_HTTP_PATH;
	}
}
?>
