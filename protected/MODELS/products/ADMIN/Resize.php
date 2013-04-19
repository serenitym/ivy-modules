<?php
/**
 * 
 * Class Resize
 * Resizes a given image, according to the following set of parameters:
 * - name of the file
 * - the X and Y dimensions
 * - a path for the new file (optional, )
 * - an optional prefix for the newly created files 
 * @author Victor Nitu <victor@serenitymedia.ro>
 * 
 * @license AGPL
 *
 */
class Resize {
	var $sPhotoFileName;	// Reference to the input file, as a path on disk or value in $_FILES
	var $aPhotoFilename=array();	// Input file as a $_FILES subarray, alternative to $sPhotoFileName
	var $sFileName;
	var $sFileExtension;
	var $width;				// Destination file width
	var $height;			// Destination file height
	var $path;				// Path to destination file
	var $prefix;			// New file prefix (as in $prefix.$filename)
	var $suffix;			// New file suffix (as in $filename.$suffix)
	var $sTempFileName;		// Temporary file used in data uploads, original file in case of local resize :) 
	var $oSourceImage;		// Image re-created from binary data in file, 
							// fails if not an image, so you cannot pass a 
							// non-image file with a joeg extension
	var $oDestinationImage;	// Resulting image, created from ASCII data in the source file

	var $src='';			// Source of the image: file/upload
	var $sThumbnailFileName; // Final image name, w/o path
	var $sFinalImage;		// Final image name, w/ path
    var $extension;         //jpg sau png
    var $defFileName;

    var $newWidth;
    var $newHeight;

    var $oldWidth;
    var $oldHeight;
	public function __construct($sPhotoFileName, $width, $height, $path='.', $prefix='', $suffix='',$extension='',$defFileName='') {

	    $this->aPhotoFileName = $sPhotoFileName;

        $this->sPhotoFileName = $this->aPhotoFileName['name'];
		$this->width = $width;
		$this->height = $height;
		$this->path = $path;
		$this->prefix = $prefix;
		$this->suffix = $suffix;
        $this->extension = $extension;
        $this->defFileName = $defFileName;

           $this->setImage();
           // $this->resize_II($sPhotoFileName);

			

	}

    function setImage(){

        $aFileNameParts = explode(".",end(explode('/',$this->sPhotoFileName)));
         $this->sFileExtension = end($aFileNameParts);
         array_pop($aFileNameParts);

         $this->sFileName = implode('.',$aFileNameParts);
         $this->sTempFileName = $this->aPhotoFileName['tmp_name'];



        if ($this->sFileExtension == 'jpg')       $this->oSourceImage = imagecreatefromjpeg($this->sTempFileName);
        elseif ($this->sFileExtension == 'jpeg')  $this->oSourceImage = imagecreatefromjpeg($this->sTempFileName);
        elseif ($this->sFileExtension == 'png')   $this->oSourceImage = imagecreatefrompng($this->sTempFileName);
        elseif ($this->sFileExtension == 'gif')   $this->oSourceImage = imagecreatefromgif($this->sTempFileName);


        if(isset($this->oSourceImage))
        {
            $this->setNEWsize();
            $this->resize();

        }

    }
    function setNEWsize()
    {
        list($oldWidth, $oldHeight)=getimagesize($this->sTempFileName);
       #___________________________________________________________________________________________________
            // goal dimensions
            $newWidth =  $iGoalWidth  =  $this->width;
                         $iGoalHeight =  $this->height;

            if($oldWidth > $iGoalWidth ||  $oldHeight >$iGoalHeight )    # daca imaginea este mai mare
            {
                $newHeight = floor(($newWidth * $oldHeight) / $oldWidth);
                if($newHeight > $iGoalHeight)
                {
                    $newHeight = $iGoalHeight; $newWidth =  floor(($newHeight * $oldWidth) / $oldHeight);
                }
            }
            else                                 # nu are rost sa ii mai facem resize daca este mai mica decat maxim
            {
                $newWidth  =   $oldWidth ;
                $newHeight =  $oldHeight ;
            }
       #___________________________________________________________________________________________________

        $this->newHeight = $newHeight;
        $this->newWidth = $newWidth;

        $this->oldHeight = $oldHeight;
        $this->oldWidth =  $oldWidth;

    }
	function resize() {


      $newHeight = $this->newHeight;
      $newWidth  = $this->newWidth;

      $oldHeight = $this->oldHeight;
      $oldWidth  = $this->oldWidth;




    //__________________________________________________________________________________________________________


        $this->sFileName = ($this->defFileName!='' ? $this->defFileName : $this->sFileName);
        $this->sFileExtension = ($this->extension ?  $this->extension  : $this->sFileExtension);
        $this->sThumbnailFileName = $this->prefix.$this->sFileName.$this->suffix.'.'.$this->sFileExtension;



        $this->oDestinationImage = imagecreatetruecolor($newWidth, $newHeight);

        if($this->sFileExtension =='png')
        {
             // integer representation of the color black (rgb: 0,0,0)
            $background = imagecolorallocate($this->oDestinationImage, 0, 0, 0);
            // removing the black from the placeholder
            imagecolortransparent($this->oDestinationImage, $background);

            // turning off alpha blending (to ensure alpha channel information
            // is preserved, rather than removed (blending with the rest of the
            // image in the form of black))
            imagealphablending($this->oDestinationImage, false);

            // turning on alpha channel information saving (to ensure the full range
            // of transparency is preserved)
            imagesavealpha($this->oDestinationImage, true);
        }
        elseif($this->sFileExtension =='gif')
        {
              // integer representation of the color black (rgb: 0,0,0)
                $background = imagecolorallocate($this->oDestinationImage, 0, 0, 0);
                // removing the black from the placeholder
                imagecolortransparent($this->oDestinationImage, $background);
        }




        imagecopyresampled($this->oDestinationImage, $this->oSourceImage, 0, 0, 0, 0,
              			          $newWidth, $newHeight,
              			          $oldWidth, $oldHeight);
       //__________________________________________________________________________________________________________






             if ($this->sFileExtension == 'jpg')      imagejpeg($this->oDestinationImage, $this->path.'/'.$this->sThumbnailFileName, 100);
             elseif ($this->sFileExtension == 'jpeg') imagejpeg($this->oDestinationImage, $this->path.'/'.$this->sThumbnailFileName, 100);
             elseif ($this->sFileExtension == 'png')  imagepng($this->oDestinationImage, $this->path.'/'.$this->sThumbnailFileName, 9);
            elseif ($this->sFileExtension == 'gif')   imagegif($this->oDestinationImage, $this->path.'/'.$this->sThumbnailFileName, 100);


        imagedestroy($this->oDestinationImage);





	    }
	

}