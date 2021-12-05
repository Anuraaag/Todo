<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Todo;
use Carbon\Carbon;


class TodoController extends Controller
{
    public function index(){
        try{
            $tasks = Todo::get()->toArray();
            if($tasks){
                $response = [
                    'code'=> '200',
                    'data' => $tasks
                ];    
            }    
            else{
                $response = [
                    'code'=> '400',
                    'data' => []
                ];  
            }
            return json_encode($response);    
        }
        catch(\Exception $e){
            return json_encode(['code' => '400', 'data' => "Error in retrieval: ".$e->getMessage()]);
        }
    }

    public function listPendingTasks(){
        try{
            $tasks = Todo::where('completed', 0)->orderBy('due_date')->get();
            if($tasks){
                $response = [
                    'code'=> '200',
                    'data' => $tasks
                ];    
            }    
            else{
                $response = [
                    'code'=> '400',
                    'data' => 'Error in retrieval'
                ];  
            }
            return json_encode($response);    
        }
        catch(\Exception $e){
            return json_encode(['code' => '400', 'data' => "Error in retrieval: ".$e->getMessage()]);
        }
    }

    public function filterPendingTasks(Request $request){
        try{
            $tasks = "";
            switch($request->filter){

                case "today": $tasks = Todo::where('completed', 0)
                ->where("due_date", Carbon::now()->toDateString())
                ->orderBy('due_date')->get();
                break;
                
                case "this_week": $tasks = Todo::where('completed', 0)
                ->where("due_date", "<=", Carbon::now()->endOfWeek()->toDateString())
                ->orderBy('due_date')->get();
                break;                
                
                case "next_week": $tasks = Todo::where('completed', 0)
                ->where("due_date", "<=", Carbon::now()->endOfWeek()->addDays(7)->toDateString())
                ->orderBy('due_date')->get();
                break;
                
                case "overdue": $tasks = Todo::where('completed', 0)
                ->where("due_date", "<", Carbon::now()->toDateString())
                ->orderBy('due_date')->get();
                break;

                default: $tasks = Todo::where('completed', 0)->orderBy('due_date')->get();
            }
        
            if($tasks){
                $response = [
                    'code'=> '200',
                    'data' => $tasks
                ];    
            }    
            else{
                $response = [
                    'code'=> '400',
                    'data' => 'Error in retrieval'
                ];  
            }
            return json_encode($response);    
        }
        catch(\Exception $e){
            return json_encode(['code' => '400', 'data' => "Error in retrieval: ".$e->getMessage()]);
        }
    }

    public function searchTasks(Request $request, $data){
        try{
            $tasks = Todo::where('completed', 0)->where('title', 'LIKE', "%{$data}%")->orderBy('due_date')->get();
            if($tasks){
                $response = [
                    'code'=> '200',
                    'data' => $tasks
                ];    
            }    
            else{
                $response = [
                    'code'=> '400',
                    'data' => "Error in retrieval"
                ];  
            }
            return $response;  
        }
        catch(\Exception $e){
            return json_encode(['code' => '400', 'data' => "Error in retrieval: ".$e->getMessage()]);
        }
        
    }

    public function store(Request $request){
        try{
            $data = request()->validate([
                'title' => 'required',
                'due_date' => 'required|date|after_or_equal:' . Carbon::now()->toDateString(),
                'parent_id' => ''
            ]);

            if(isset($request->parent_id)){
                $parent_task = Todo::where('id', $request->parent_id)->get();
                if($parent_task->count() == 0){
                    return json_encode([
                        'code'=> '400',
                        'data' => 'Invalid Parent ID'
                    ]);    
                }
                else if($parent_task->toArray()[0]['completed'] == 1){
                    return json_encode([
                        'code'=> '400',
                        'data' => 'Sub tasks can not be created under finished tasks'
                    ]);
                }
                else if($parent_task->toArray()[0]['parent_id'] != "0"){
                    return json_encode([
                        'code'=> '400',
                        'data' => 'Sub tasks can not contain more sub tasks'
                    ]);
                }
            }

            if(Todo::create($data)){
                $response = [
                    'code'=> '200',
                    'data' => 'Task created'
                ];
            }
            else{
                $response = [
                    'code'=> '400',
                    'data' => 'Task creation failed'
                ];
            }
            return json_encode($response);    
        }
        catch(\Exception $e){
            return json_encode(['code' => '400', 'data' => 'Error in creating task: '.$e->getMessage()]);
        }
    }

    public function update (Todo $task){
        try{
            if($task->parent_id == "0"){
                if(Todo::where('parent_id', $task->id)->update(array('completed' => 1)) == 0){
                    return json_encode([
                        'code'=> '400',
                        'data' => 'Sub task updating failed'
                    ]);
                }
            }
            if(Todo::where('id', $task->id)->update(array('completed' => 1)) == 0){
                $response = [
                    'code'=> '400',
                    'data' => 'Task updation failed'
                ];
            }
            else{
                $response = [
                    'code'=> '200',
                    'data' => 'Task and sub tasks marked as complete'
                ];
            }
            return json_encode($response);
        }
        catch(\Exception $e){
            return json_encode(['code' => '400', 'data' => "Error in Updation: ".$e->getMessage()]);
        }
    }


    public function destroy(Todo $task){

        try{
            if($task->parent_id == "0"){
                $tasks = Todo::where('parent_id', $task->id)->get();
                foreach($tasks as $task){
                    if(!$task->delete()){
                        return json_encode([
                            'code'=> '400',
                            'data' => 'Sub task deletion failed'
                        ]);
                    }
                }
            }
            if ($task->delete()){
                $response = [
                    'code'=> '200',
                    'data' => 'Task and sub tasks deleted'
                ];
            }
            else{
                $response = [
                    'code'=> '400',
                    'data' => 'Task deletion failed'
                ];
            }
            return json_encode($response);
        }
        catch(\Exception $e){
            return json_encode(['code' => '400', 'data' => "Error in deletion: ".$e->getMessage()]);
        }
    }


}
