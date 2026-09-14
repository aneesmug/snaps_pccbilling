<?php
if (!defined('APP_RUN')) {
    exit('No direct access allowed');
}
authenticate_admin();
$request_method = request_method();

$action = route(1,'files');
switch ($request_method) {
    case 'GET':
        switch ($action) {

            case 'files':

                $media_files = MediaFile::orderBy('id', 'desc')->get();
                \view('media', [
                    'media_files' => $media_files,
                    'selected_navigation' => 'media',
                ]);

                break;


            case 'image':

                $server = League\Glide\ServerFactory::create([
                    'source' => APP_STORAGE_PATH . '/media',
                    'cache' => APP_CACHE_PATH . '/media',
                ]);

                $path = route(2);

                if(file_exists(APP_STORAGE_PATH . '/media/' . $path))
                {
                    $server->outputImage($path, $_GET);
                }
                else
                {
                    $server->outputImage('default.png', $_GET);
                }


                break;

            case 'delete':

                $id = route(2);
                $media_file = MediaFile::find($id);
                if ($media_file) {
                    $path = APP_STORAGE_PATH . '/media/' . $media_file->path;

                    if(file_exists($path))
                    {
                        unlink($path);
                    }

                    $media_file->delete();

                    redirect_to('media',[
                        'success' => 'Media file deleted successfully'
                    ]);

                }

                break;

            case 'choose':

                $media_files = MediaFile::orderBy('id', 'desc')->get();
                \view('media_choose', [
                    'media_files' => $media_files,
                ]);

                break;
        }

    case 'POST':
        switch ($action)
        {
            case 'upload':

                if (APP_STAGE == 'Demo') {
                    api_response([
                        'errors' => [
                            'file' => 'Upload is disabled in demo mode',
                        ],
                    ], 422);
                }

                $uploader = new Uploader();
                $uploader->setDir(APP_STORAGE_PATH.'/media/');
                $uploader->setExtensions(['jpg','jpeg','gif','svg','webp','png']);
                if ($uploader->uploadFile('file')) {
                    $uploaded = $uploader->getUploadName();
                    $extension = $uploader->getExtension($uploaded);
                    $media_file = new MediaFile();
                    $media_file->title = $_FILES['file']['name'] ?? $uploaded;
                    $media_file->path = $uploaded;
                    $media_file->extension = $extension;
                    $media_file->save();
                } else {
                    $error_message = $uploader->getMessage();
                    api_response([
                        'errors' => [
                            'file' => $error_message
                        ],
                    ], 422);
                }

                break;
        }
        break;
}
