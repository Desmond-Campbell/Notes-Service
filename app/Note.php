<?php

namespace App;

// use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Model;
use App\Services\Http;

class Note extends Model
{
    
    protected $guarded = [ 'id' ];

    public static function fetch( $args ) {

	  	$user_id = $args['user_id'] ?? 0;

        $notes = self::where( 'user_id', $user_id );

        if ( $args['folder_id'] ?? null ) $notes = $notes->where('folder_id', $args['folder_id']);
        if ( !( $args['show_private'] ?? false ) ) $notes = $notes->where('is_private', 0);

        $limit = (int) ( $args['paging']['limit'] ?? 15 );
        $page = (int) ( $args['paging']['page'] ?? 1 );

        $sortIcons = $args['paging']['sortIcons'] ?? [];
        $sortField = $args['paging']['sortField'] ?? 'created_at';
        $sortOrder = $args['paging']['sortOrder'] ?? 'desc';

        $notes = $notes->orderBy( $sortField, $sortOrder );

        $keywords = $args['paging']['keywords'] ?? [];

        if ( $keywords ) {

            $notes = $notes->where(function($q) use ($keywords) { 
              $q->where('title', 'like', '%' . $keywords . '%');
              $q->orWhere('keywords', 'like', '%' . $keywords . '%');
              $q->orWhere('tags', 'like', '%' . $keywords . '%');
            });

        }

        $notes = $notes->paginate( $limit, ['*'], null, $page );
        $paging = [ 'page' => $notes->currentPage(), 'total' => $notes->total(), 'pages' => $notes->lastPage(), 'limit' => $limit, 'sortIcons' =>  $sortIcons ];
        $notes = $notes->items();

        $result = [ 'notes' => $notes ];

        $result['paging'] = $paging;

        return $result;

    }

    public static function words( $string ) {

        $string = strip_tags( $string );
        $string = strtolower( $string );
        $words = explode( ' ', $string );
        $words = array_map( 'trim', $words );
        $words = array_unique( $words );
        $words = array_filter( $words );

        return $words;

    }

}
