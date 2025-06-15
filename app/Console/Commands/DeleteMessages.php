<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Message;
 use Illuminate\Support\Facades\Storage;


class DeleteMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-messages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete messages';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //get softdeleted messages
        $deletedMessages = Message::onlyTrashed()->get();

        //decode folderpaths and thumbnails
        foreach ($deletedMessages as $message) {
            // Decode JSON arrays from DB
            $folderPaths = json_decode($message->folder_path, true) ?: [];
            $thumbnailPaths = json_decode($message->thumbnail_path, true) ?: [];

            foreach($folderPaths as $index => $folderPath){

                //delete file
                 if ($folderPath && file_exists(storage_path("app/public/{$folderPath}"))) {
                     Storage::disk('public')->delete($folderPath);
                }

                $relativePath = str_replace('storage/', '', $thumbnailPaths[$index] );

                //delete thumbnail
                if ($thumbnailPaths[$index] && file_exists(storage_path("app/public/{$relativePath}"))) {
                     Storage::disk('public')->delete( $relativePath);
                }

            }
        }

        //delete soft deleted rows
        Message::onlyTrashed()->forceDelete();

        //delete everything in toBeDeleted folder
        $files = Storage::disk('public')->allFiles('toBeDeleted');
        Storage::disk('public')->delete($files);

    }
}
