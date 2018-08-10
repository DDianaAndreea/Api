<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Role;
use App\User;
use GenTux\Jwt\JwtToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{

    public function display()
    {
        try {
            $user = $this->validateSession();
            $task=DB::table('tasks')->paginate(10);
            
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
        
    }

    // public function addTask(Request $request)
    // {
    //     try {
    //         $rules = [
    //             'name' => 'required',
    //             'description'=>'required',
    //             'status'=>'required',
    //             // 'user_id' => 'required|user_id|unique:users',
    //             'user_id' => 'required',
    //             'assign' => 'assign'

    //         ];

    //         $validator = Validator::make($request->all(), $rules);

    //         if (!$validator->passes()) {
    //             return $this->returnBadRequest('Please fill all required fields');
    //         }

    //         $task = new Task();

    //         $task->name = $request->name;
    //         $task->description = $request->description;
    //         $task->status = $request->has('status') ? $request->status : Task::STATUS_INACTIVE;
    //         $task->user_id = $request->user_id;
    //         $task->assign = $request->assign;

    //         $TASK->save();

    //         return $this->returnSuccess($TASK);
    //     } catch (\Exception $e) {
    //         return $this->returnError($e->getMessage());
    //     }

    // }

    // public function editTask(Request $request, $id)
    // {
    //     try {
    //         $task = Task::find($id);

    //         if ($request->has('name')) {
    //             $task->name = $request->name;
    //         }

    //         if ($request->has('description')) {
                
    //             $task->description = $request->description;
    //         }

    //         if ($request->has('status')) {
    //             $task->status = $request->status;
    //         }

    //         if ($request->has('user_id')) {
    //             $task->user_id = $request->user_id;
    //         }

    //         if ($request->has('assign')) {
    //             $task->assign = $request->assign;
    //         }

    //         $task->save();

    //         return $this->returnSuccess($task);
    //     } catch (\Exception $e) {
    //         return $this->returnError($e->getMessage());
    //     }

    // }

    // public function deleteTask($id)
    // {
    //     try {
    //         $task = Task::find($id);

    //         $task->delete();

    //         return $this->returnSuccess();
    //     } catch (\Exception $e) {
    //         return $this->returnError($e->getMessage());
    //     }
    // }

    
}