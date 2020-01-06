<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\{User, Note, Folder};
use Auth;
use Illuminate\Support\Facades\Storage;

class NotesController extends \App\Http\Controllers\Controller
{
    
    public function store(Request $r){

        $data = $r->data;

        $params = $r->params;
        $files = $r->params['files'] ?? [];
        $user_id = $params['user_id'] ?? 0;

        $id = $data['_id'] ?? null;
        $folder_id = $data['folder_id'] ?? null;
        $title = $data['title'] ?? '';
        $tags = $data['tags'] ?? '';
        $stack = $data['stack'] ?? [];
        $content = implode( "\n\n", $stack ); 
        $is_private = $data['is_private'] ?? null;
        $access_level = $data['access_level'] ?? null;

        if ( $id ) {

            $note = Note::where('user_id', $user_id)->find( $id );

            if ( $folder_id ) { $note->folder_id = $folder_id; }
            if ( $title ) { $note->title = $title; }
            if ( $content ) { 
                $note->content = $content; 
                $note->stack = $stack;
            }
            if ( $tags ) { $note->tags = $tags; }
            if ( $access_level ) { $note->access_level = $access_level; }
            if ( !is_null( $is_private ) ) { $note->is_private = $is_private; }
        
            $note->keywords = implode( ' ', Note::words( $content . ' ' . $tags . ' ' . $title ) ); 

            $note->save();

        } else {

            if ( !$folder_id ) {

                $folder = Folder::where('user_id', $user_id)->where('is_default', 1)->first();

                if ( !$folder ) {

                    $folder = Folder::getDefaultFolder($user_id);

                }

                $folder_id = $folder->id;

            }

            if ( !$title ) {

                $title = 'Random Note - ' . date('F d, H:i a');

            }

            $note_data = [];
            $note_data['folder_id'] = $folder_id;
            $note_data['title'] = $title;
            $note_data['content'] = $content;
            $note_data['stack'] = $stack; 
            $note_data['keywords'] = implode( ' ', Note::words( $content . ' ' . $tags . ' ' . $title ) ); 
            $note_data['tags'] = $tags;
            $note_data['is_private'] = $is_private ?? false;
            $note_data['access_level'] = $access_level ?? 0;
            $note_data['user_id'] = $user_id;

            $note = Note::create( $note_data );

        }

        return response()->json( [ 'note' => $note, 'success' => true ] );

    }

    public function delete(Request $r){

        $user_id = $r->user_id;
        $id = $r->id;

        $note = Note::where('user_id', $user_id)->find($id);

        if ( $note ) {

            $note->delete();

            return response()->json( [ 'success' => true, 'note' => [ 'title' => 'Untitled Note', '_id' => null, 'message' => 'Original requested note not found. This is a new note.', 'stack' => [ '' ] ] ] );

        }

        return response()->json( [ 'success' => false ] );

    }

    public function get(Request $r){

        $user_id = $r->user_id;
       
        $id = $r->_id;

        $note = Note::where('user_id', $user_id)->find($id);

        if ( !$note ) {
            $note = [ 'title' => 'Untitled Note', '_id' => null, 'message' => 'Original requested note not found. This is a new note.', 'stack' => [ '' ] ];
        }

        return response()->json( $note );

    }

    public function browse(Request $r){

        $user_id = $r->user_id;
        
        $paging = $r->input('paging') ?? [ 'page' => 1, 'limit' => 15 ];

        $args = [ 'paging' => $paging, 'user_id' => $user_id ];

        $result = Note::fetch( $args );

        return response()->json( $result );

    }

    public function storeFolder(Request $r){

        $data = $r->data;

        $params = $r->params;

        $user_id = $params['user_id'];
      
        $id = $data['_id'] ?? null;
        $name = $data['name'] ?? '';

        if ( $id ) {

            $folder = Folder::where('user_id', $user_id)->find( $id );
            
            if ( $name ) $folder->name = $name;
            
            $folder->save();

        } else {

            $folder = [];
            $folder['name'] = $name;
            $folder['user_id'] = $user_id;

            $folder = Folder::create( $folder );

        }

        $folders = Folder::fetch( [ 'user_id' => $user_id ] );

        return response()->json( [ 'folder' => $folder, 'success' => true, 'folders' => $folders ] );

    }

    public function deleteFolder(Request $r){

        $id = $r->id;
        $user_id = $r->user_id;

        $folder = Folder::where('user_id', $user_id)->find($id);

        if ( $folder ) {

            if ( $folder->is_default ) {
                return response()->json( [ 'errors' => 'Cannot delete this folder as it is set as the default folder.' ] );
            }

            $notes = Note::where('folder_id', $folder->id)->get();

            if ( $notes ) {

                foreach ( $notes as $note ) {
                    $note->folder_id = Folder::getDefaultFolder($user_id)->id;
                    $note->save();
                }

            }

            $folder->delete();

            return response()->json( [ 'success' => true ] );

        }

        return response()->json( [ 'success' => false ] );

    }

    public function getFolders(Request $r){

        $user_id = $r->user_id;
    
        $paging = $r->input('paging') ?? [ 'page' => 1, 'limit' => 15 ];

        $args = [ 'paging' => $paging, 'user_id' => $user_id ];

        $result = Folder::fetch( $args );

        return response()->json( $result );

    }

    private function str_random( $length ) {

        $chars = str_split( 'qwePSDFHBGEWVLSUIYUHJKLKMLQEJIH2JUTGKJMRLBKJNlhgfyglYFSMVLKLAJWKHJKKLQI4HJTLKlkvgrwhjfmewovinhybiefnvodmwfnbhnvwjdfvbnieifvjGRMDVLBKDJGLMDVASCVMSBNKJEWLDWNIHETJGNRKMEWFLOPIRETHJEGKRLDFVKBJVDLSCrtyuiopasdfghjlklanbfvgubhejnLHGVJHBIIFEH34IPTPLEEJNKBHDWFVGJMLIBHYUVJKWCEWIUEBHEFKJWDVOFBIOJGNDFLKSDMVLLKDFJBKGSVDlkvuishyukejtjiwhrgsd', 1 );
        $n = count( $chars );
        $string = '';

        for ( $i = 0; $i < $length; $i++ ) {

            $r = rand(0, $n-1);
            $string .= $chars[$r];

        }

        return $string;

    }

}
