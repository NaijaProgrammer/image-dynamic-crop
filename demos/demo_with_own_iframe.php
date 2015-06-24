<?php include('../config.php'); ?>
<?php
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$fname = 'mike';
	$file_extension = 'png';
	$tmp_filename   = $fname. '.'. $file_extension;
	$app_dir = dirname(dirname(__FILE__));
    $upl_dir = $app_dir.'/uploads';
	if( isset($_FILES['photo'])  )
	{
		move_uploaded_file($_FILES['photo']['tmp_name'], $upl_dir.'/temp/'. $tmp_filename);
		//don't bother unlinking the temp uploaded file, it is handled automatically by the ImageUploadCrop::process_img_crop below
	}
	
	/**
	* After successfully processing your image upload, 
	* call this function to do the actual cropping and saving of your image to its final destination
	*
	* When form is initially submitted,
	* The if condition above, processes it, and moves the file to the temporary upload directory specified above
	* Then, the process_img_crop() call, crops it using the supplied width and height dimensions, and moves it to the supplied original_media_save_directory.
	*
	* Meanwhile, the dynamic form created by get_dynamic_crop_enabler, 
	* contains input fields which (in conjunction with supplied iframe) hold values related to the just uploaded image
	* this just uploaded image is loaded from the original_media_save_directory, and the needed dimensions are captured by javascript
	* then, when user clicks 'create thumbnail' submit button, 
	* the process_img_crop function is once again called, this time, passed the values from the dynamic form, among which is the 'cropped' dimensions
	* The image is then cropped to the supplied 'final_filename' using the captured dimensions.
	* If the save_original_image value is set to true, the image in the original_media_save_directory is retained, otherwise it is deleted,
	* leaving the cropped final_filename as the image file
	*/
	ImageUploadCrop::process_img_crop
	(
	  array
	  (
	  'temp_directory'=>$upl_dir.'/temp/', 'upload_dir'=>$upl_dir, 'final_filename'=>$fname, 'file_extension'=>$file_extension, 
	  'width'=>450, 'height'=>450, 'save_original_image'=>false, 'original_image_save_directory'=>$upl_dir.'/original/'
	  )
	);
}
?>
<?php $action_page = $_SERVER['PHP_SELF']; ?>
<html>
<head></head>
<body>
 <form id="upload_form" method="post" enctype="multipart/form-data" action="<?php echo $action_page; ?>" target="dummy">
  <label for="photo"><h3>1. Upload An Image :</h3></label><input name="photo" size="27" type="file" />              
  <input type="submit" name="action" value="Upload" />
 </form>
 <iframe id="dummy" name="dummy"></iframe>
 <h3>2. Uploaded Image - click and set the thumbnail dimension</h3>
 <?php 
 /*
 * After creating your form above,
 * call this function, and supply your arguments, to enable the dynamic crop functionality on your form
 */
 //echo ImageUploadCrop::enable_dynamic_crop_on_form( 
 echo ImageUploadCrop::get_dynamic_crop_enabler(
 array('form_id'=>'upload_form', 'action_page'=>$action_page, 'include_thumb_scale_details'=>false, 'iframe_id'=>'dummy', 'iframe_name'=>'dummy') 
 ); 
 ?>
</body>
</html>