<?php

namespace App\Http\Controllers\Api;

use App\Http\Traits\CanLoadRelationships;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Contracts\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller
{
    use CanLoadRelationships;
    private readonly array $relations;

    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->authorizeResource(Event::class, 'event');
        $this->relations = [ 'user', 'attendees', 'attendees.user' ];
        $this->middleware('throttle:aoi')->only(['store', 'destroy', 'update']);
    }

    public function index(): AnonymousResourceCollection
    {
        $query = $this->loadRelationships(Event::query(), $this->relations);

        return EventResource::collection($query->latest()->paginate());
    }

    public function store(Request $request): EventResource
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        $validatedData['user_id'] = $request->user()->id;

        $event = Event::create($validatedData);

        return new EventResource( $this->loadRelationships($event, $this->relations) );
    }

    public function show( ?Event $event ): EventResource
    {
        return new EventResource($this->loadRelationships($event));
    }

    public function update( Request $request, Event $event ): EventResource
    {
      //  $this->authorize('update-event', $event);

       $event->update(
           $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after:start_time',
        ])
       );

       return new EventResource($this->loadRelationships($event));
    }

    public function destroy( Event $event ): ResponseFactory
    {
        $event->delete();

        return response(status: 204);
    }

}
