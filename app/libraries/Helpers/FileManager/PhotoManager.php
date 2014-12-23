<?php

/**
 * Description of PhotoManager
 *
 * @author Janssen Canturias
 */

namespace Helpers\FileManager;

//use Symfony\Component\HttpFoundation\File\UploadedFile;

class PhotoManager {

    private $api;

    function __construct() {
        $this->api = new \Cloudinary\Api();
    }

    public function createCloudinary($public_id, \Eloquent $model, $caption = null, array $trnsfrsmtn = null) {
        $time = time();
        $userId = \Auth::id();

        $resource = $this->api->resource($public_id);
        $fileName = "$model->id-$userId-$time";
        if ($model instanceof \User) {
            $path = "users/$fileName";
        } else if ($model instanceof \Property) {
            $path = "properties/$fileName";
        } else if ($model instanceof \Amenity) {
            $path = "properties/$model->property_id/amenities/$fileName";
        } else if ($model instanceof \Unit) {
            $path = "properties/$model->property_id/units/$model->id/$fileName";
        }

        \Cloudinary\Uploader::rename($public_id, $path, array("overwrite" => true));
        $updated = $this->api->resource($path);

        $photo = new \Photo();
        $photo->public_id = $updated['public_id'];

        $width = $updated['width'];
        $height = $updated['height'];

        $url = $updated['url'];
        $sUrl = $updated['secure_url'];
        if (!is_null($trnsfrsmtn)) {
            $width = $trnsfrsmtn['width'];
            $height = $trnsfrsmtn['height'];
            $photo->x = $trnsfrsmtn['x'];
            $photo->y = $trnsfrsmtn['y'];
            $photo->crop = $trnsfrsmtn['crop'];

            $options = array(
                "width" => $trnsfrsmtn['width'],
                "height" => $trnsfrsmtn['height'],
                "x" => $trnsfrsmtn['x'],
                "y" => $trnsfrsmtn['y'],
                "crop" => $trnsfrsmtn['crop'],
            );
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

    public function create(UploadedFile $uploadedFile, $type, $typeId, $caption = null) {
        $userId = is_null(\Auth::id()) ? 0 : \Auth::id();

        $time = time();
        $extension = $uploadedFile->getClientOriginalExtension();

        if ($type == 'property') {
            $property = \Property::find($typeId);
            $fileName = "$property->id-$userId-$time.$extension";
            $path = "/uploads/properties/$property->id";
        } else if ($type == 'user') {
            $user = \User::find($typeId);
            $fileName = "$user->id-$userId-$time.$extension";
            $path = "/uploads/users";
        } else if ($type == 'amenity') {
            $amenity = \Amenity::find($typeId);
            $fileName = "$amenity->id-$userId-$time.$extension";
            $path = "/uploads/properties/$amenity->property_id/amenities";
        } else if ($type == 'content') {
            $content = \Content::find($typeId);
            $fileName = "$content->id-$userId-$time.$extension";
            $path = "/uploads/contents";
        } else if ($type == 'developer') {
            $developer = \Developer::find($typeId);
            $fileName = "$developer->id-$userId-$time.$extension";
            $path = "/uploads/developers";
        } else if ($type == 'unit') {
            $unit = \Unit::find($typeId);
            $fileName = "$unit->id-$userId-$time.$extension";
            $path = "/uploads/properties/$unit->property_id/units/$unit->id";
        } else {
            return null;
        }

        $publicPath = public_path() . "$path";
        if (!\File::isDirectory($publicPath)) {
            \File::makeDirectory($publicPath, 0775, true);
        }

        $moved = $uploadedFile->move($publicPath, $fileName);

        if ($moved) {
            $photo = new \Photo();
            $photo->path = "$publicPath/$fileName";
            $photo->url = \URL::to("$path/$fileName");
            $photo->uploaded_by = $userId;
            $photo->caption = $caption;
            $photo->save();

            return $photo;
        } else {
            return null;
        }
    }
}
