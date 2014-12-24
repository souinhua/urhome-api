<?php

/**
 * Description of PhotoManager
 *
 * @author Janssen Canturias
 */

namespace Helpers\FileManager;

class PhotoManager {

    private $api;

    function __construct() {
        $this->api = new \Cloudinary\Api();
    }

    /**
     * Creates a photo resource from a cloudinary image
     * 
     * @param Cloudinary Public ID $public_id
     * @param Eloquent $model
     * @param String $caption
     * @param array $trnsfrsmtn
     * @return Photo
     */
    public function createCloudinary($public_id, \Eloquent $model, $caption = null, array $trnsfrsmtn = null) {
        $time = time();
        $userId = \Auth::id();

        $resource = $this->api->resource($public_id);
        $fileName = "$model->id-$userId-$time";
        if ($model instanceof \User) {
            $path = "users/$model->id/$fileName";
            $tags = "user";
        } else if ($model instanceof \Property) {
            $path = "properties/$model->id/$fileName";
            $tags = "property";
        } else if ($model instanceof \Amenity) {
            $path = "properties/$model->property_id/amenities/$fileName";
            $tags = "amenity";
        } else if ($model instanceof \Unit) {
            $path = "properties/$model->property_id/units/$model->id/$fileName";
            $tags = "unit";
        }

        \Cloudinary\Uploader::rename($public_id, $path, array("overwrite" => true));
        
        $updated = $this->api->resource($path);
        $this->api->update($path, array(
            "tags" => $tags
        ));
        
        $photo = new \Photo();
        $photo->public_id = $updated['public_id'];

        $width = $updated['width'];
        $height = $updated['height'];

        $url = $updated['url'];
        $sUrl = $updated['secure_url'];
        if (!is_null($trnsfrsmtn)) {
            $width = $trnsfrsmtn['width'];
            $height = $trnsfrsmtn['height'];
            $photo->x = isset($trnsfrsmtn['x'])?$trnsfrsmtn['x']:null;
            $photo->y = isset($trnsfrsmtn['y'])?$trnsfrsmtn['y']:null;
            $photo->crop = isset($trnsfrsmtn['crop'])?$trnsfrsmtn['crop']:null;

            $options = array(
                "width" => $trnsfrsmtn['width'],
                "height" => $trnsfrsmtn['height'],
            );
            
            if(isset($trnsfrsmtn['x']) && isset($trnsfrsmtn['y'])) {
                $options['x'] = $trnsfrsmtn['x'];
                $options['y'] = $trnsfrsmtn['y'];
            }
            
            if(isset($trnsfrsmtn['crop'])) {
                $options['crop'] = $trnsfrsmtn['crop'];
            }
            
            $url = cloudinary_url($updated['public_id'], $options);
            $options['secure'] = true;
            $sUrl = cloudinary_url($updated['public_id'], $options);
        }

        $photo->width = $width;
        $photo->height = $height;
        $photo->bytes = $updated['bytes'];
        $photo->url = $url;
        $photo->secure_url = $sUrl;
        $photo->caption = $caption;
        $photo->uploaded_by = $userId;
        $photo->save();

        return $photo;
    }
}
