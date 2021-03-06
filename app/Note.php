<?php

namespace App;

// use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Model;
use App\Services\Http;

class Note extends Model
{

    protected $guarded = [ 'id' ];

    public static function fetch( array $args ): array {
	  	$user_id = $args['user_id'] ?? 0;
        $notes = self::where( 'user_id', $user_id );
        if ( $args['paging']['folder_id'] ?? null ) $notes = $notes->where('folder_id', $args['paging']['folder_id']);
        // if ( !( $args['show_private'] ?? false ) ) $notes = $notes->where('is_private', 0);
        $limit = (int) ( $args['paging']['limit'] ?? 15 );
        $page = (int) ( $args['paging']['currentPage'] ?? 1 );
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
        $paging = [ 'currentPage' => $notes->currentPage(), 'total' => $notes->total(), 'pageCount' => $notes->lastPage(), 'limit' => $limit, 'sortIcons' =>  $sortIcons ];

        if ( $args['paging']['folder_id'] ?? null ) {
            $paging['folder_id'] = $args['paging']['folder_id'];
        }

        $notes = $notes->items();
        $result = [ 'notes' => static::transformAll( $notes ) ];
        $result['paging'] = $paging;

        return $result;

    }

    public static function words( $string ): array {
        $string = strip_tags( $string );
        $string = strtolower( $string );
        $words = explode( ' ', $string );
        $words = array_map( 'trim', $words );
        $words = array_unique( $words );
        $words = array_filter( $words );
        return $words;
    }

    public static function transformAll(array $entities): array {
        for($i = 0; $i < count( $entities ); $i++) {
            $entity = static::transform($entities[$i]);
            $entities[$i] = $entity;
        }
        return $entities;
    }

    public static function transform(Object $entity): Object {
        $entity->stack = array_values( $entity->stack ?? [] );
        return $entity;
    }

}
