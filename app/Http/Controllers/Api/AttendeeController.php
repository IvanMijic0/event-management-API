<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendeeResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Routing\ResponseFactory;

class AttendeeController extends Controller
{
    use CanLoadRelationships;

    private readonly array $relations;

    public function __construct()
    {
        $this->relations = [ 'user' ];
        $this->middleware( 'auth:sanctum' )->except( [ 'index', 'show', 'update' ] );
        $this->authorizeResource( Attendee::class, 'attendee' );
        $this->middleware('throttle:api')->only(['store', 'destroy']);
    }

    public function index( Event $event ): AnonymousResourceCollection
    {
        $attendees = $this->loadRelationships( $event->attendees()->latest(), $this->relations );

        return AttendeeResource::collection( $attendees->paginate( 10 ) );
    }

    public function store( Request $request, Event $event ): AttendeeResource
    {
        $attendee = $this->loadRelationships( $event->attendees()->create( [
            'user_id' => $request->user()->id,
        ] ), $this->relations );

        return new AttendeeResource( $attendee );
    }

    public function show( Event $event, Attendee $attendee ): AttendeeResource
    {
        return new AttendeeResource( $this->loadRelationships( $attendee, $this->relations ) );
    }

    public function destroy( Event $event, Attendee $attendee ): ResponseFactory
    {
        $attendee->delete();

        return response( status: 204 );
    }
}
