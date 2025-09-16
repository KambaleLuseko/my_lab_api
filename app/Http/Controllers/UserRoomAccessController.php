<?php

namespace App\Http\Controllers;

use App\Models\RoomManager;
use App\Models\Salles;
use App\Models\User;
use App\Models\UserRoomAccess;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
    public function index(Request $request)
    {
        $accesses = UserRoomAccess::all();
        $getUser=$request->query('getUser');
        $getRoom=$request->query('getRoom');
        if($getUser === 'true'){
              $userUuid=$accesses->pluck('user_uuid')->unique()->toArray();
        $users = User::whereIn('uuid', $userUuid)->get();
        $usersMap = $users->keyBy('uuid');
        }
        if($getRoom === 'true'){
            $roomUuid=$accesses->pluck('room_uuid')->unique()->toArray();
            $rooms = Salles::whereIn('uuid', $roomUuid)->get();
            $roomsMap = $rooms->keyBy('uuid');
        }
        foreach ($accesses as $access) {
            $access->user = $getUser==='true'? $usersMap[$access->user_uuid] ?? null:null;
            $access->room = $getRoom==='true' ? $roomsMap[$access->room_uuid] ?? null:null;
        }
        return response()->json(['data'=>$accesses], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = self::getRules();

        $rules['user_uuid'][] = Rule::unique('user_room_accesses')
            ->where(fn ($query) => $query
                ->where('user_uuid', $request->user_uuid)
                // ->where('room_uuid', $request->room_uuid)
                ->where('date', $request->date));
        
         $validatedData = $request->validate([
            'user_uuid' => [
                'required',
                // Checks that the uuid exists AND that the user has a specific role
                Rule::exists('users', 'uuid')->where(function ($query) {
                    $query->where('role', 'etudiant');
                    $query->orWhere('role', 'visiteur');
                }),
            ],
        ], ['user_uuid.exists' =>'The user selected is not a student, only students can get access to rooms']);

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
        $validatedAccesses=UserRoomAccess::where('room_uuid', $request->room_uuid)
        ->where('date', $request->date)
        // ->where('status', 'Approved')
        ->get();
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
        if($userRoomAccess->status!='Pending'){
            return response()->json(['message' => "Demand cannot be updated because it's not pending"], 403);
        }
        $rules = self::getRules();


        // Add the uniqueness rule for update, ignoring the current record
        $rules['room_uuid'][] = Rule::unique('user_room_accesses')
            ->ignore($userRoomAccess->uuid, 'uuid')
            ->where(fn ($query) => $query
                ->where('user_uuid', $request->user_uuid)
                ->where('room_uuid', $request->room_uuid)
                ->where('date', $request->date));
        
         $validatedData = $request->validate([
            'user_uuid' => [
                'required',
                // Checks that the uuid exists AND that the user has a specific role
                Rule::exists('users', 'uuid')->where(function ($query) {
                    $query->where('role', 'etudiant');
                    $query->orWhere('role', 'visiteur');
                }),
            ],
        ], ['user_uuid.exists' =>'The user selected is not a student, only students can get access to rooms']);

        $validatedData = $request->validate($rules);
        unset($validatedData['uuid']);
        unset($validatedData['status']);
        $userRoomAccess->update($validatedData);
        return response()->json($userRoomAccess, 200);
    }

    /**
     * 
     * Aprove or reject multiple demands
     * 
     * $request Map{data:UserAccessModel[]}
     * 
     */

    public function updateStatus(Request $request){
        $data=$request->data;
        if(is_array($data)==false){
            $data=json_decode($data, true);
        }
        if(is_array($data)==false){
            return response()->json(['message' => 'Data must be an array'], 403);
        }
        $roomsUuid=array_map(fn($item) => $item['room_uuid'], $data);
        $rooms=DB::table('room_managers')
                ->join('salles', 'room_managers.room_uuid', '=', 'salles.uuid')
              ->  select('room_managers.*', 'salles.capacity')
                ->whereIn('room_managers.room_uuid', (array)$roomsUuid)
                ->where('room_managers.date', '>=','2025-09-10'// DB::raw('CURDATE()')
                ) // <-- Correct use of DB::raw()
                ->get();
        /**
         * Defining variables for global validation
         */
        $filledRooms=[];
        $filledRoomUuids=[];
        $rejectedDemands=[];
        $approvedDemands=[];

        /**
         * Check if the demand is for a date that exists in rooms
         */
        foreach($data as $demand){
            if(!in_array( $demand['date'],array_map(fn($item) => $item->date, $rooms->toArray()))){
                UserRoomAccess::where('uuid', $demand['uuid'])->update(['status'=>'Canceled']);
                $demand['status']='Canceled';
                array_push($rejectedDemands, $demand);
            }
        }

        /**
         * Validating user access requests
         */
        foreach($rooms as $room){
            $validatedAccesses=UserRoomAccess::where('room_uuid', $room->room_uuid)
            ->where('date', $room->date)
            ->where('status', 'Approved')
            ->get();

            /**
             * If room is full, reject request
             * 
             * Then fill the $filledRooms and $filledRoomsUuids with correponsponding data
             * 
             * Else, set room reservations count to the number of current approved requests
             */
            if(count($validatedAccesses)>=$room->capacity){
                array_push($filledRooms, $room);
                array_push($filledRoomUuids, $room->room_uuid);
                
                UserRoomAccess::where('room_uuid', $room->room_uuid)->where('date', $room->date)->update(['status'=>'Rejected']);
                $rejectedData=array_filter($data, function($item) use ($room) {
                    return $item['room_uuid']==$room->room_uuid && $item['date']==$room->date;
                });
                foreach($rejectedData as $demand){
                    $demand['status']='Rejected';
                    array_push($rejectedDemands, $demand);
                }
            }else{
                $room->reservations=count($validatedAccesses);
            }
        }

        /**
         * Defining available rooms for request approval
         */
        $availableRooms=$rooms->whereNotIn('room_uuid', $filledRoomUuids)->toArray();


        /**
         * Checking each room individualy
         */
        foreach ($availableRooms as $room)  {
            $approved=0;
            $demands=array_filter($data, function($item) use ($filledRoomUuids, $room) {
                return $item['room_uuid']==$room->room_uuid && in_array($item['room_uuid'], $filledRoomUuids)==false&& $item['date']==$room->date;
            }); 

            /**
             * If no demand on the room, continue the loop
             */
            if(count($demands)==0){
                continue;
            }

            foreach($demands as $demand){
                if($approved>=($room->capacity-$room->reservations)){
                    array_push($rejectedDemands, $demand);
                }
                UserRoomAccess::where('uuid', $demand['uuid'])->first()->update(['status'=>'Approved']);
                $demand['status']='Approved';
                array_push($approvedDemands, $demand);
                $approved++;
            }
        }
        return response()->json([ 'rejectedDemands'=>$rejectedDemands, 'approvedDemands'=>$approvedDemands], 200);
    }

    /**
     * 
     * Reject a demand
     */

    public function rejectDemands(Request $request){
        if(!isset($request->uuid)){
            return response()->json(['message' => "Demand identifier not found"], 403);
        }
        $demand=UserRoomAccess::where('uuid', $request->uuid)->first();
        if(!isset($demand)){
            return response()->json(['message' => "Demand not found"], 403);
        }
        // if(Auth::check()==false){
        //     return response()->json(['message' => "We are unable to authenticate your request"], 401);
        // }
        $manager=RoomManager::where('room_uuid', $demand->room_uuid)->where('date', $demand->date)
        // ->where('user_uuid', Auth::user()->uuid)
        ->first();
        if(!isset($manager)){
            return response()->json(['message' => "Room manager not found on this demand"], 403);
        }
        // if($demand->room_uuid!=$manager->room_uuid){
        //     return response()->json(['message' => "Demand cannot be rejected because it's not for your room"], 403);
        // }
        if($demand->status!='Pending'){
            return response()->json(['message' => "Demand cannot be rejected because it's not pending"], 403);
        }
        $demand->status='Rejected';
        $demand->save();
        return response()->json($demand, 200);
    }

    /**
     * 
     */

    public function cancelDemands(Request $request){
        if(!isset($request->uuid)){
            return response()->json(['message' => "Demand identifier not found"], 403);
        }
         $demand=UserRoomAccess::where('uuid', $request->uuid)->first();
        if(!isset($demand)){
            return response()->json(['message' => "Demand not found"], 403);
        }
        // if(Auth::check()){
        //     if(Auth::user()->uuid==$demand->user_uuid){
                $demand->status='Canceled';
                $demand->save();
                return response()->json($demand, 200);
        //     }else{
        //         return response()->json(['message' => "Demand cannot be canceled because it's not yours"], 403);
        //     }
        // }else{
        //     return response()->json(['message' => "We are unable to authenticate your request"], 403);
        // }
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
