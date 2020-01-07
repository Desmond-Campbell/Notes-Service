<?php

namespace App;

// use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Model;
use App\Services\Http;

class Folder extends Model
{
    
    protected $guarded = [ 'id' ];

    public static function fetch( $args ) {

        $user_id = $args['user_id'] ?? 0;

        $folders = self::where( 'user_id', $user_id );

        $limit = (int) ( $args['paging']['limit'] ?? 15 );
        $page = (int) ( $args['paging']['page'] ?? 1 );

        $sortIcons = $args['paging']['sortIcons'] ?? [];
        $sortField = $args['paging']['sortField'] ?? 'created_at';
        $sortOrder = $args['paging']['sortOrder'] ?? 'desc';

        $folders = $folders->orderBy( $sortField, $sortOrder );

        $keywords = $args['paging']['keywords'] ?? [];

        if ( $keywords ) {

            $folders = $folders->where(function($q) use ($keywords) { 
              $q->where('name', 'like', '%' . $keywords . '%');
            });

        }

        $folders = $folders->paginate( $limit, ['*'], null, $page );
        $paging = [ 'page' => $folders->currentPage(), 'total' => $folders->total(), 'pages' => $folders->lastPage(), 'limit' => $limit, 'sortIcons' =>  $sortIcons ];
        $folders = $folders->items();

        foreach ( $folders as $folder ) {

            $folder->counter = Note::where('folder_id', $folder->_id)->where('user_id', $user_id)->count();

        }

        $total_counter = Note::where('user_id', $user_id)->count();

        $result = [ 'folders' => $folders, 'total_counter' => $total_counter ];

        $result['paging'] = $paging;

        return $result;

    }

    public static function getDefaultFolder( $user_id ) {

        $folder = self::where('user_id', $user_id)->where('is_default', 0)->orderBy('created_at', 'asc')->first();

        if ( !$folder ) {

            $folder = self::create( [ 'name' => 'General', 'is_default' => 1, 'user_id' => $user_id ] );

        }

        return $folder;
        
    }

}
