jQuery(document).ready(function(){
    jQuery('.file_upload input').change(function () {
      //console.log(this.files[0].name);

        if (this.files.length <= 1){
            jQuery('.file_upload p').text(this.files.length + " file(s) selected");
            jQuery('#gform_preview_15_27').html("<b style='color: #790000;'>Maximum number of files reached </b><br>" + this.files[0].name);
        }else{
            jQuery('#gform_preview_15_27').html("<b style='color: #790000;'>Not More Than 1 Image Upload </b>");
         }

    });
});