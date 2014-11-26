<?php

/**
 * Description of PhotoManager
 *
 * @author Janssen Canturias
 */
namespace Helpers\FileManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PhotoManager {
    
    public function create(UploadedFile $uploadedFile, $type, $typeId, $caption = null) {
        $userId = is_null(\Auth::id())?0:\Auth::id();
        
        $time = time();
        $extension = $uploadedFile->getClientOriginalExtension();
        
        if($type == 'property') {
            $property = \Property::find($typeId);
            $fileName = "$property->id-$userId-$time.$extension";
            $path = "/uploads/properties/$property->id";
        }
        else if($type == 'user') {
            $user = \User::find($typeId);
            $fileName = "$user->id-$userId-$time.$extension";
            $path = "/uploads/users";
        }
        else if($type == 'amenity') {
            $amenity = \Amenity::find($typeId);
            $fileName = "$amenity->id-$userId-$time.$extension";
            $path = "/uploads/properties/$amenity->property_id/amenities";
        }
        else if($type == 'content') {
            $content = \Content::find($typeId);
            $fileName = "$content->id-$userId-$time.$extension";
            $path = "/uploads/contents";
        }
        else if($type == 'developer') {
            $developer = \Developer::find($typeId);
            $fileName = "$developer->id-$userId-$time.$extension";
            $path = "/uploads/developers";
        }
        else {
            return null;
        }
        
        if(!\File::isDirectory($path)) {
            \File::makeDirectory($path, 0775, true);
        }
        
        $publicPath = public_path() . "$path";
        $moved = $uploadedFile->move($publicPath, $fileName);
        
        if($moved) {
            $photo = new \Photo();
            $photo->path = "$publicPath/$fileName";
            $photo->url = \URL::to("$path/$fileName");
            $photo->uploaded_by = $userId;
            $photo->caption = $caption;
            $photo->save();
            
            return $photo;
        }
        else {
            return null;
        }
    }
}
