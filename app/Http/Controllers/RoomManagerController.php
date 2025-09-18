<?php

namespace App\Http\Controllers;

use App\Models\RoomManager;
use App\Models\Salles;
use App\Models\Services;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomManagerController extends Controller
{
    /**
     * The validation rules for the RoomManager resource.
     *
     * @var array
     */
   private static function getBaseRules(): array
    {
        return [
            'room_uuid' => ['required', 'exists:salles,uuid'],
            'user_uuid' => ['required', 'exists:users,uuid'],
            'date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'status' => ['required', 'string', Rule::in(['Active', 'Inactive'])],
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $getUser=$request->query('getUser');
        $getRoom=$request->query('getRoom');
        // Step 1: Get all bookings
        $bookings = RoomManager::get();
        // Step 2: Get all unique user and room UUIDs from the bookings
        if($getUser === 'true'){
            $usersUuid = $bookings->pluck('user_uuid')->unique()->toArray();
            $users = User::whereIn('uuid', $usersUuid)->get();
            $usersMap = $users->keyBy('uuid');
        }
        if($getRoom === 'true'){
            $roomsUuid = $bookings->pluck('room_uuid')->unique()->toArray();
            $rooms = Salles::whereIn('uuid', $roomsUuid)->get();
            $roomsId=$rooms->pluck('id')->unique()->toArray();
            // print(is_array($roomsId));
            $roomService=Services::whereIn('salles_id',$roomsId)->get();
            // $rooms=$rooms->merge($roomService);
            // print($roomService);
            $roomsMap = $rooms->keyBy('uuid');
            // $servicesMap = $roomService->keyBy('id');

        }
      
       
        // Step 5: Loop through bookings and manually attach the associated data
        foreach ($bookings as $booking) {
            $managerRoom=$getRoom === 'true' ? $roomsMap[$booking->room_uuid] ?? null:null;
            $booking->user =$getUser === 'true' ? $usersMap[$booking->user_uuid] ?? null:null;
            $booking->room =$managerRoom;
            $booking->services =$getRoom === 'true' ? array_filter($roomService->toArray(), fn($item) => $item['salles_id'] == $managerRoom->id):null;
        }
        return response()->json(['data'=>$bookings], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules=self::getBaseRules();
        $validatedData = $request->validate([
            'user_uuid' => [
                'required',
                // Checks that the uuid exists AND that the user has a specific role
                Rule::exists('users', 'uuid')->where(function ($query) {
                    $query->where('role', 'agent');
                }),
            ],
        ], ['user_uuid.exists' =>'The user selected is not an agent, only agents can be room managers']);
        $validatedData = $request->validate($rules);
       

        $validatedData['uuid'] = Controller::uuidGenerator('RMGR');
        
        $checkDuplicate=RoomManager::where('user_uuid', $request->user_uuid)->where('date', $request->date)->first();
        if ($checkDuplicate) {
            return response()->json(['message' => 'Room management with this user, room and date already exists'], 403);
        }
        $booking = RoomManager::create($validatedData);

        return response()->json($booking, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(RoomManager $roomManager)
    {
        $data=$roomManager;
        $data['room']=Salles::where('uuid', $roomManager->room_uuid)->first();
        $data['user']=User::where('uuid', $roomManager->user_uuid)->first();
        return response()->json($data, 200);
    }

    public static function findByUserOrRoom($id)
    {
        $data=RoomManager::where('room_uuid', $id)->orWhere('user_uuid', $id)->get();
        if(!isset($data)){
            return null;
        }
        $data['room']=Salles::where('uuid', $data->room_uuid)->first();
        return $data;
        // return response()->json($data, 200);
    }

    public static function findDailyUserRoom($id)
    {
        $data=RoomManager::Where('user_uuid', $id)->where('date', date('Y-m-d'))->first();
        if(!isset($data)){
            return null;
        }
        $data['room']=Salles::where('uuid', $data->room_uuid)->first();
        return $data;
        // return response()->json($data, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,$id)
    {
        $rules=self::getBaseRules();
        $rules['user_uuid'][] = Rule::unique('room_managers')
                ->where(fn ($query) => $query->where('user_uuid', $request->user_uuid)->where('date', $request->date));
         $validatedData = $request->validate([
            'user_uuid' => [
                'required',
                // Checks that the uuid exists AND that the user has a specific role
                Rule::exists('users', 'uuid')->where(function ($query) {
                    $query->where('role', 'agent');
                }),
            ],
        ], ['user_uuid.exists' =>'The user selected is not an agent, only agents can be room managers']);
        $validatedData = $request->validate($rules);
        $data=RoomManager::find($id);
        unset($validatedData['uuid']);
        $data->update($validatedData);

        return response()->json($data, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RoomManager $roomManager)
    {
        $roomManager->delete();
        return response()->json(null, 204);
    }
}
