<?php

namespace App\Http\Controllers;

use App\Jobs\NotificateVisitor;
use App\Jobs\NotifyRemovedVisitor;
use App\Mail\NotifyRemoved;
use App\Models\Temperature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Ownership;
use App\Models\Incubator;
use App\Models\User;
use Illuminate\Validation\Rules\In;


class IncubatorController extends Controller
{
    public function addIncubator(Request $request){
        $validate = Validator::make($request->all(),[
            'code' => 'required|unique:incubators|size:5',
            'name' => 'required|max:35'
        ],[
            'code' => [
                'required' => 'You need the code of your incubator',
                'unique'   => 'This Incubator already has been registered, you can still use it but you will
                                    need to ask for the Owner\'s permission',
                'size'     => 'This code is only 5 characters long'
            ],
            'name' => [
                'required' => 'You need a name for this incubator',
                'max'      => 'You only have 35 characters long'
            ]
        ]);
        if ($validate->fails())
            return response()->json(['Message' => $validate->errors()], 403);

        $incubator = new Incubator();
        $incubator->code = $request->code;
        $incubator->name = $request->name;
        $incubator->save();

        $ownership = new Ownership();
        $ownership->user_id = $request->user()->id;
        $ownership->incubator_id = $incubator->id;
        $ownership->role_id = 1;
        $ownership->save();

        return response()->json([
           'Message' => 'Success..'
        ], 201);
    }

    public function showIncubator(Request $request, int $id){
        $incubator = Incubator::find($id)->first();
        $owner = Ownership::query()
            ->where('user_id','=', $request->user()->id)
            ->where('incubator_id', '=', $id)
            ->where('role_id', '=', 1)
            ->first();
        if(!$owner)
            return response()->json(["Message" => "You don't own this incubator"]);
        return response()->json([
            'Msg' => "Success",
            "Incubator" => $incubator
        ]);
    }

    public function showAllIncubators(Request $request){
        $ownership = Ownership::query()
            ->where('user_id', '=', $request->user()->id)
                ->with('incubatorData')
                    ->with('roleData')
                        ->get();
        $count = Ownership::query()->where('user_id', '=', $request->user()->id)
            ->count('user_id');

        return response()->json([
            'Count' => $count,
            'Data'  => $ownership
        ]);
    }

    public function addVisitor(Request $request){
        $validate = Validator::make($request->all(),[
            'code' => 'required|exists:incubators',
            'email' => 'required|exists:users'
        ],[
            'code' => [
                'required' => 'You need the code of your incubator',
                'exists'   => 'The incubator must exists'
            ],
            'email' => [
                'required' => 'You need the user to add as visitor',
                'exists'   => 'The user must exists'
            ]
        ]);
        if ($validate->fails())
            return response()->json(['Message' => $validate->errors()], 403);
        $incubator = Incubator::query()
            ->where('code', '=', $request->code)->first();
        $visitor = User::query()
            ->where('email', '=', $request->email)->where('status', true)->first();
        if(!$visitor)
            return response()->json(["Msg" => "User not authenticated"]);
        $data = Ownership::query()
            ->where('user_id', '=', $request->user()->id)
            ->where('incubator_id', '=', $incubator->id)
            ->first();
        $seen = Ownership::query()
            ->where('user_id', '=', $visitor->id)
            ->where('incubator_id', '=', $incubator->id)
            ->first();
        if(!$data || ($data->role_id == 2))
            return response()->json(["Message" => "You don't own this incubator"]);
        if($seen)
            return response()->json(["Message" => "You can already see this incubator"]);
        $ownership = new Ownership();
        $ownership->user_id = $visitor->id;
        $ownership->incubator_id = $incubator->id;
        $ownership->role_id = 2;
        $ownership->save();
        NotificateVisitor::dispatch($visitor, $incubator)->delay(30);
        return response()->json(["Message" => "Success..."], 201);
    }

    public function showVisitors(Request $request){
        $validate = Validator::make($request->all(),[
            'code' => 'required|exists:incubators'
        ],[
            'code' => [
                'required' => 'You need the code of your incubator',
                'exists'   => 'The incubator must exists'
            ]
        ]);

        if ($validate->fails())
            return response()->json(['Message' => $validate->errors()], 403);
        $incubator = Incubator::query()->where('code', '=', $request->code)->first();
        $ownership = Ownership::query()
            ->where('user_id', '=', $request->user()->id)
            ->where('incubator_id', '=', $incubator->id)
            ->first();
        if(!$ownership)
            return response()->json(["Message" => "You don't own this incubator"]);
        $data = Ownership::query()
            ->where('incubator_id', '=', $incubator->id)
            ->where('role_id', '=', 2)
            ->with('userData')
            ->get();
        $count = Ownership::query()
            ->where('incubator_id', '=', $incubator->id)
            ->where('role_id', '=', 2)
            ->count();
        return response()->json([
            "Count" => $count,
            "Visitors   "  => $data
        ]);
    }

    public function removeVisitor(Request $request){
        $validate = Validator::make($request->all(),[
            'code' => 'required|exists:incubators',
            'email' => 'required|exists:users'
        ],[
            'code' => [
                'required' => 'You need the code of your incubator',
                'exists'   => 'The incubator must exists'
            ],
            'user' => [
                'required' => 'You need the user to add as visitor',
                'exists'   => 'The user must exists'
            ]
        ]);
        if ($validate->fails())
            return response()->json(['Message' => $validate->errors()], 403);

        $incubator = Incubator::query()
            ->where('code', '=', $request->code)->first();

        $owner = Ownership::query()
            ->where('user_id','=', $request->user()->id)
            ->where('incubator_id', '=', $incubator->id)
            ->where('role_id', '=', 1)
            ->first();

        if(!$owner)
            return response()->json(["Message" => "You don't own this incubator"]);

        $visitor = User::query()
            ->where('email', '=', $request->email)->first();

        if($visitor->id == $request->user()->id)
            return response()->json(["Message" => "You can't remove yourself from ownership"]);

        $data = Ownership::query()
            ->where('user_id', '=', $visitor->id)
            ->where('incubator_id', '=', $incubator->id)
            ->first();
        if(!$data)
            return response()->json(["Message" => "No registers"]);
        NotifyRemovedVisitor::dispatch($visitor, $incubator)->delay(30);
        $data->delete();
        return response()->json(["Message" => "Removed"]);
    }

    public function deleteIncubator(Request $request){
        $validate = Validator::make($request->all(),[
            'code' => 'required|exists:incubators',
        ],[
            'code' => [
                'required' => 'You need the code of your incubator',
                'exists'   => 'The incubator must exists'
            ]
        ]);
        if ($validate->fails())
            return response()->json(['Message' => $validate->errors()], 403);

        $incubator = Incubator::query()
            ->where('code', '=', $request->code)
            ->first();

        $owner = Ownership::query()
            ->where('user_id','=', $request->user()->id)
            ->where('incubator_id', '=', $incubator->id)
            ->where('role_id', '=', 1)
            ->first();

        if(!$owner)
            return response()->json(["Message" => "You don't own this incubator"]);

        $incubator->delete();
        return response()->json([
            "Message" => "Removed...",
            "Data"    => $incubator
        ]);
    }

    public function allDataDunno(Request $request, int $id){
        $incubator = Incubator::query()
            ->where('id', $id)
            ->first();

        if(!$incubator)
            return response()->json(["Msg" => "You arent the owner"]);

        $owner = Ownership::query()
            ->where('user_id','=', $request->user()->id)
            ->where('incubator_id', '=', $incubator->id)
            ->where('role_id', '=', 1)
            ->first();

        if(!$owner)
            return response()->json(["Message" => "You don't own this incubator"]);

        $temperature = Temperature::latest()->where('incubator_id', '=', $id)->first();

        $data = Incubator::latest()
            ->where('id', '=', $id)
            ->with('allTemperature')
            ->with('allHumidity')
            ->with('allDioxide')
            ->first();

        return response()->json([
            'Data' => $temperature
        ]);
    }
}
