<?php
/**
 * Thumbnails:
 * Create thumbnails for a given image.
 *
 * @author Carlos Sosa
 * @version 0.1 
 * 
 * @method Thumbnails   saveAsPng           ( $imagePath)   Save thumbnail as png into path
 * @method Thumbnails   saveAsJpeg          ( $imagePath)   Save thumbnail as png into path
 * @method Thumbnails   saveAsJpg           ( $imagePath)   Save thumbnail as png into path
 * @method Thumbnails   saveAsGif           ( $imagePath)   Save thumbnail as png into path
 * @method Thumbnails   saveAsGd            ( $imagePath)   Save thumbnail as png into path
 * @method Thumbnails   saveAsGd2           ( $imagePath)   Save thumbnail as png into path
 * @method Thumbnails   saveAsXbm           ( $imagePath)   Save thumbnail as png into path
 * @method null         printThumbnailAsPng ()              Print thumbnail as png
 * @method null         printThumbnailAsJpeg()              Print thumbnail as jpg
 * @method null         printThumbnailAsJpg ()              Print thumbnail as jpeg
 * @method null         printThumbnailAsGif ()              Print thumbnail as gif
 * @method null         printThumbnailAsGd  ()              Print thumbnail as gd
 * @method null         printThumbnailAsGd2 ()              Print thumbnail as gd2
 * @method null         printThumbnailAsXbm ()              Print thumbnail as xbm
 */
class Thumbnails {      
    //Position of selection in Original Image
    const IMAGE_STRETCH = 1;
    const IMAGE_CENTER = 2;
    const IMAGE_POS_TOP = 4;
    const IMAGE_POS_BOTTOM = 8;
    const IMAGE_POS_LEFT = 16;
    const IMAGE_POS_RIGHT = 32;
    
    //Method to use for resize
    const RESIZE_RESIZE = 'imagecopyresized';
    const RESIZE_RESAMPLING = 'imagecopyresampled ';
    
    //Supported formats (GD 1.8 & PHP 5.4.4)
    const IMAGE_FORMAT_JPEG = 'jpeg';
    const IMAGE_FORMAT_PNG = 'png';
    const IMAGE_FORMAT_GIF = 'gif';
    const IMAGE_FORMAT_GD = 'gd';
    const IMAGE_FORMAT_GD2 = 'gd2';
    const IMAGE_FORMAT_XBM = 'xbm';
    
    //Image
    protected $image;
    //Thumb
    protected $thumb;
    protected $thumb_options;
    //Method of resize
    protected $resize_method;

    /**
     * Create instance of Thumbnails
     * 
     * @param string $imagePath         Path to image (any path supported by fopen)
     * @param string $format            Force to use specific format to load the image, by default is 'auto' for try autodetect format by extension.
     * @param string $resize_function   Set the function to resize.
     * @throws Thumbnails_Exception_FileNotFound
     * @throws Thumbnails_Exception_FormatNotSupported
     * @throws Thumbnails_Exception_ErrorToLoad
     */
    public function __construct ( $imagePath, $format = 'auto', $resize_function = self::RESIZE_RESAMPLING) {
        //Verify exists
        if ( !file_exists( $imagePath))
            throw new Thumbnails_Exception_FileNotFound($imagePath);
               
        //prepare the extension
        $ext = ( strtolower($format) != 'auto' ) ? strtolower($format) : 
                                                    strtolower(pathinfo($imgPath, PATHINFO_EXTENSION));
        $ext = ( $ext == 'jpg' ) ? self::IMAGE_FORMAT_JPEG : $ext;
        
        $func = 'imagecreatefrom'.$ext;
      
        if ( ! function_exists($func))
        {
            throw new Thumbnails_Exception_FormatNotSupported($ext);
        }
        
        //Load image
        $this->image = $func($imgPath);
        
        //Check if loaded
        if ( !is_resource($this->image))
            throw new Thumbnails_Exception_ErrorToLoad($imagePath);
        
        $this->thumb_options = NULL;  
        $this->setMethodToResize();
    }
    
    /**
     * Set options to use always do make thumbnail.
     * 
     * Example:
     * <pre><code>
     * <?php
     *   $obj = new Thumbnails("/var/www/image.jpg");
     *   $obj->setThumbnailDefaultOptions(Thumbnails::IMAGE_CENTER | Thumbnails::IMAGE_POS_TOP | Thumbnails::IMAGE_POS_RIGHT)
     *       ->doThumbnail(100,20)
     *       ->save('/var/www/image_min.png')
     *       ->doThumbnail(200,40)
     *       ->save('/var/www/image_med.png')
     *       ->doThumbnail(400,80)
     *       ->save('/var/www/image_big.png');
     * ?>
     * </code></pre>
     * 
     * 
     * @param type $options     Position of selection in Original Image
     * @return \Thumbnails
     */
    public function setThumbnailDefaultOptions( $options) {
        $this->thumb_options = $options;        
        return $this;
    }
    
    /**
     * setMethodToResize
     * 
     * @param type $method Function name to use when for resize the image.
     * @return \Thumbnails
     * @throws Thumbnails_Exception_NotCallableMethod
     */
    public function setMethodToResize( $method = self::RESIZE_RESAMPLING) {
        if (is_callable($method))
            throw new Thumbnails_Exception_NotCallableMethod($method);
            
        $this->resize_method = $method;        
        return $this;
    }

    /**
     * Make a thumbnail for Loaded Image
     * 
     * @param type $thumb_w     Thumbnail Width
     * @param type $thumb_h     Thumbnail Height
     * @param type $options     Position of selection in Original Image
     */
    public function doThumbnail (   $thumb_w, $thumb_h, 
                                    $options = self::IMAGE_CENTER )  {   
        //Options
        if ( $this->thumb_options !== NULL )
            $options = $this->thumb_options;
        
        //Img sizes
        $img_w = imagesx($this->image);
        $img_h = imagesy($this->image);
        //Calc image ratios
        $img_r = $img_w / $img_h;
        $thumb_r = $thumb_w / $thumb_h;        
        //Calc sizes
        $O_h = ( $options % 2 != 0 ) ? $img_w : $img_w / $thumb_r;
        $O_w = $img_w;
        //Correct sizes
        if ( $img_r > $thumb_r){
            $O_h_diff = $O_h-$img_h;
            $O_h = $img_h;
            $O_w = $O_w - ($O_h_diff * $thumb_r);
        } 
        //X,Y Pos in Image
        //By default is aligned to left and top.
        $O_x = $O_y = 0;
        if ($options % 2 == 0) { //If not stretch then calc Pos
            if ( $O_w < $img_w ) { //x
                if ( ( $options & self::IMAGE_POS_RIGHT ) ) {
                    $O_x = ($img_w - $O_w);
                } else if ( !( $options & self::IMAGE_POS_LEFT ) ) //center
                    $O_x = ($img_w - $O_w) / 2;                
            }//x 
            
            if ($O_h < $img_h) { //y
                if ( ( $options & self::IMAGE_POS_BOTTOM ) ) {
                    $O_y = ($img_h - $O_h);
                } else if ( !( $options & self::IMAGE_POS_TOP ) )
                    $O_y = ($img_h - $O_h) / 2;                                
            }//y
        }//center
        
        //Create blank image
        if ( $this->thumb)            
            $this->thumb = NULL;
        $this->thumb = imagecreatetruecolor($thumb_w, $thumb_h);
        
        //Copy and resize the Big image into Thumbnail
        //imagecopyresampled( $this->thumb, $this->image, 0, 0, $O_x, $O_y, $thumb_w, $thumb_h, $O_w, $O_h);        
        call_user_func( $this->resize_method, $this->thumb, $this->image, 0, 0, $O_x, $O_y, $thumb_w, $thumb_h, $O_w, $O_h);        
        
        return $this;       
    }
    
    /**
     * Save thumbnail
     * 
     * Example:
     * <pre><code>
     * <?php
     *   $obj = new Thumbnails("/var/www/image.jpg");
     *   $obj->doThumbnail(60, 60, Thumbnails::IMAGE_CENTER | Thumbnails::IMAGE_POS_TOP | Thumbnails::IMAGE_POS_RIGHT)
     *       ->saveAsJpeg("/var/www/image_thumb.jpg")
     * ?>
     * </code></pre>
     * 
     * @param type $path
     * @param type $format
     * @throws Thumbnails_Exception_FileNotFound
     * @throws Thumbnails_Exception_FormatNotSupported
     */
    public function save( $path, $format='auto') {
        //Verify writeable
        if ( !file_exists( $imagePath))
            throw new Thumbnails_Exception_FileNotFound($imagePath);
               
        //prepare the extension
        $ext = ( strtolower($format) != 'auto' ) ? strtolower($format) : 
                                                    strtolower(pathinfo($imgPath, PATHINFO_EXTENSION));
        $ext = ( $ext == 'jpg' ) ? self::IMAGE_FORMAT_JPEG : $ext;
        
        $func = 'image'.$ext;
        
        if ( ! function_exists($func))
        {
            throw new Thumbnails_Exception_FormatNotSupported($ext);
        }
        
        //Save thumbnail
        $func_save($black_img,$thumbPath);
    }
    
    /**
     * getThumbnailAsString 
     * Store thumbnails into string
     * 
     * Example:
     * <pre><code>
     * <?php
     *   $obj = new Thumbnails("/var/www/image.jpg");
     *   $obj->doThumbnail(60, 60);
     *   
     *   echo "My image thumbnail : ";
     *   echo '<img src="data:image/png;base64,'. base64_encode($obj->getThumbnailAsString(Thumbnails::IMAGE_FORMAT_PNG)) .'">';
     * ?>
     * </code></pre>
     * 
     * @param type $format
     * @return type
     */
    public function getThumbnailAsString ( $format = self::IMAGE_FORMAT_PNG){
        ob_start();
        $this->printThumbnail( $format);
        return ob_get_clean();
    }
    
    /**
     * printThumbnail
     * 
     * Example:
     * <pre><code>
     * <?php
     *   $obj = new Thumbnails("/var/www/image.jpg");
     *   $obj->doThumbnail(60, 60);
     *   
     *   header("Pragma: public"); 
     *   header('Content-disposition: filename=image_thumb.png'); 
     *   header("Content-type: image/png"); 
     *   header('Content-Transfer-Encoding: binary'); 
     *   ob_clean(); 
     *   flush(); 
     *   //You can used simplified method
     *   //or $obj->printThumbnail(Thumbnails::IMAGE_FORMAT_PNG);
     *   $obj->printThumbnailAsPng();
     * ?>
     * </code></pre>
     * 
     * @param string $format Format to use for generate the thumbnail
     */
    public function printThumbnail( $format = self::IMAGE_FORMAT_PNG) {
        $this->save( NULL, 'png');
    }
    
    /**
     * @ignore
     */
    public function __call($name, $arguments) {        
        if (substr($name, 0, 6) == 'saveAs'){
            call_user_func( array($this,'save'), $arguments[0], strtolower(substr($name, 6)));
        } else if (substr($name, 0, 16) == 'printThumbnailAs'){
            call_user_func( array($this,'printThumbnail'), strtolower(substr($name, 16)));
        }
    }

    /**
     * createThumb
     * 
     * Example:
     * Generate a thumbnail from image, if aspect ratio of both 
     * images is not equal then select area from top right corner.
     * <pre><code>
     * <?php
     * Thumbnails::createThumb( '/path/to/big_img.png', '/path/to/thumb_big_img.gif', 60, 60, Thumbnails::IMAGE_CENTER | Thumbnails::IMAGE_POS_TOP | Thumbnails::IMAGE_POS_RIGHT, Thumbnails::IMAGE_FORMAT_GIF );
     * ?>
     * </code></pre>
     * 
     * @param type $imgPath     Full path to Orignal Image
     * @param type $thumbPath   Full to store Thumbnail
     * @param type $thumb_w     Thumbnail Width
     * @param type $thumb_h     Thumbnail Height
     * @param type $options     Position of selection in Original Image
     * @param type $format      Format for Generated Thumbnail
     * @return Thumbnails Return object used to make the thumbnail.
     * @throws Exception
     */
    public static function createThumb (    $imgPath, $thumbPath, 
                                            $thumb_w, $thumb_h, 
                                            $options = self::IMAGE_CENTER, 
                                            $format = 'auto')            
    {
        $obj = new self($imgPath);
        $obj->setThumbnailDefaultOptions($options)
                ->doThumbnail($thumb_w, $thumb_h)
                ->save($thumbPath,$format);
        
        return $obj;
    }            
}

final class Thumbnails_Exception_FileNotFound extends Exception {
    public function __construct ($path) {
        parent::__construct( "Image in {$path} not found.", 10001);
    }
}

final class Thumbnails_Exception_FormatNotSupported extends Exception {
    public function __construct ($format){
        parent::__construct( "Format {$format} isn't supported by PHP.", 10002);
    }
}

final class Thumbnails_Exception_ErrorToLoad extends Exception {
    public function __construct ($path){
        parent::__construct( "Error to load {$path}.", 10003);
    }
}

final class Thumbnails_Exception_NotCallableMethod extends Exception {
    public function __construct ($method){
        parent::__construct( "Method {$method} isn't callable.", 10004);
    }
}