<?php

namespace App\Http\Controllers;

use App\Models\RoomManager;
use App\Models\Salles;
use App\Models\UserRoomAccess;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserRoomAccessController extends Controller
{
     /**
     * Get the base validation rules for the UserRoomAccess resource.
     *
     * @return array
     */
    private static function getRules(): array
    {
        return [
            'user_uuid' => ['required', 'string', 'exists:users,uuid'],
            'room_uuid' => ['required', 'string', 'exists:salles,uuid'],
            'date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            // 'status' => ['nullable', 'string', Rule::in(['Approved', 'Pending', 'Canceled','Unavailable'])],
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $accesses = UserRoomAccess::all();
        return response()->json(['data'=>$accesses], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = self::getRules();

        // Add the uniqueness rule for store operation
        $rules['user_uuid'][] = Rule::unique('user_room_accesses')
            ->where(fn ($query) => $query
                ->where('user_uuid', $request->user_uuid)
                // ->where('room_uuid', $request->room_uuid)
                ->where('date', $request->date));

        $validatedData = $request->validate($rules);

        $validatedData['uuid'] = Controller::uuidGenerator('URMA');
        $validatedData['status'] = 'Pending';

        /**
         * Check if the room is available
         */
        $isRoomAvailable=RoomManager::where('room_uuid', $request->room_uuid)->where('date', $request->date)->first();
        if(!isset($isRoomAvailable)){
            return response()->json(['message' => 'Room is not available on this date'], 403);
        }
        /**
         * Check if the user has already submitted an access
         */
        $checkDuplicate=UserRoomAccess::where('user_uuid', $request->user_uuid)->where('date', $request->date)->first();
        if($checkDuplicate){
            return response()->json(['message' => 'This user already submitted an access'], 403);
        }
        /**
         * Check if the room is open
         */
        $room=Salles::where('uuid', $request->room_uuid)->first();
        $openedAt=new DateTime($room->opened_at);
        $openedAt=$openedAt->format('H:i');
        $closedAt=new DateTime($room->closed_at);
        $closedAt=$closedAt->format('H:i');
        $startTime=new DateTime($validatedData['start_time']);
        $startTime=$startTime->format('H:i');
        $endTime=new DateTime($validatedData['end_time']);
        $endTime=$endTime->format('H:i');
        if($startTime<$openedAt){
            
            return response()->json(['message' => 'Room opens at '.$room->opened_at.''], 403);
        }

        if( $endTime>$closedAt){
            
            return response()->json(['message' => 'Room closes at '.$room->closed_at.''], 403);
        }

     
        /**
         * Check if the room is full
         */
        $validatedAccesses=UserRoomAccess::where('room_uuid', $request->room_uuid)->where('date', $request->date)->where('status', 'Approved')->get();
        if(count($validatedAccesses)>=$room->capacity){
            return response()->json(['message' => 'Room is full'], 403);
        }
        $access = UserRoomAccess::create($validatedData);

        return response()->json($access, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(UserRoomAccess $userRoomAccess)
    {
        return response()->json($userRoomAccess, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserRoomAccess $userRoomAccess)
    {
        $rules = self::getRules();

        // Add the uniqueness rule for update, ignoring the current record
        $rules['room_uuid'][] = Rule::unique('user_room_accesses')
            ->ignore($userRoomAccess->uuid, 'uuid')
            ->where(fn ($query) => $query
                ->where('user_uuid', $request->user_uuid)
                ->where('room_uuid', $request->room_uuid)
                ->where('date', $request->date));

        $validatedData = $request->validate($rules);
        unset($validatedData['uuid']);
        $userRoomAccess->update($validatedData);
        return response()->json($userRoomAccess, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserRoomAccess $userRoomAccess)
    {
        $userRoomAccess->delete();
        return response()->json(null, 204);
    }
}
