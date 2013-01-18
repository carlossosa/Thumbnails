<?php
/**
 * Thumbnails:
 * Create thumbnails for a given image.
 *
 * @author Carlos Sosa
 * @version 0.1 
 */
class Thumbnails {
    //Position of selection in Original Image
    const IMAGE_STRETCH = 1;
    const IMAGE_CENTER = 2;
    const IMAGE_POS_TOP = 4;
    const IMAGE_POS_BOTTOM = 8;
    const IMAGE_POS_LEFT = 16;
    const IMAGE_POS_RIGHT = 32;

    //Format for Generated Thumbnail
    const OUTPUT_JPG = 'jpg';
    const OUTPUT_PNG = 'png';
    const OUTPUT_GIF = 'gif';
    const OUTPUT_AUTO = 0;
    
    /**
     * createThumb
     * 
     * Example:
     * Generate a thumbnail from image, if aspect ratio of both images is not equal then select area from top right corner.
     * <pre><code>
     * <?php
     * Thumbnails::createThumb( '/path/to/big_img.png', '/path/to/thumb_big_img.gif', 60, 60, Thumbnails::IMAGE_CENTER | Thumbnails::IMAGE_POS_TOP | Thumbnails::IMAGE_POS_RIGHT, Thumbnails::OUTPUT_GIF );
     * ?>
     * </code></pre>
     * 
     * @param type $imgPath     Full path to Orignal Image
     * @param type $thumbPath   Full to store Thumbnail
     * @param type $thumb_w     Thumbnail Width
     * @param type $thumb_h     Thumbnail Height
     * @param type $options     Position of selection in Original Image
     * @param type $format      Format for Generated Thumbnail
     * @throws Exception
     */
    public static function createThumb ( $imgPath, $thumbPath, $thumb_w, $thumb_h, $options = self::IMAGE_CENTER, $format = self::OUTPUT_AUTO)            
    {
        $ext = strtolower(pathinfo($imgPath, PATHINFO_EXTENSION));
        $ext = ( $ext == 'jpg' ) ? 'jpeg' : $ext;
        
        $func = 'imagecreatefrom'.$ext;
        if ( $format != self::OUTPUT_AUTO)
            $func_save = 'image'.$format;
        else 
            $func_save = 'image'.$ext;
        
        if ( ! function_exists($func))
        {
            throw new Exception('PHP: Format not supported.');
        }
        
        //Load image
        $image = $func($imgPath);
        //Img sizes
        $img_w = imagesx($image);
        $img_h = imagesy($image);
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
                if ( $options == self::IMAGE_CENTER ) //center
                    $O_x = ($img_w - $O_w) / 2;
                else {
                    if ( ( $options & self::IMAGE_POS_RIGHT ) == self::IMAGE_POS_RIGHT ) {
                        $O_x = ($img_w - $O_w);
                    }
                }
            } 
            
            if ($O_h < $img_h) { //y
                if ( $options == self::IMAGE_CENTER )
                    $O_y = ($img_h - $O_h) / 2;                
                else {
                    if ( ( $options & self::IMAGE_POS_BOTTOM ) == self::IMAGE_POS_BOTTOM ) {
                        $O_y = ($img_h - $O_h);
                    }
                }
            }//
        }//
        
        //Create blank image
        $black_img = imagecreatetruecolor($thumb_w, $thumb_h);
        
        //Copy and resize the Big image into Thumbnail
        imagecopyresampled( $black_img, $image, 0, 0, $O_x, $O_y, $thumb_w, $thumb_h, $O_w, $O_h);
        
        //Save thumbnail
        $func_save($black_img,$thumbPath);
    }    
}