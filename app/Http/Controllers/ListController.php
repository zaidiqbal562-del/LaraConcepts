<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ListController extends Controller
{
    public function index()
    {
        // Hardcoded list of items
        $employees =[
            ['name' => 'John', 'department' => 'IT'], 
            ['name' => 'Alice', 'department' => 'HR'], 
            ['name' => 'Bob', 'department' => 'IT'],
            ['name' => 'David', 'department' => 'Finance'], 
            ['name' => 'Eve', 'department' => 'HR']
               
            ];

            $grouped=[];
            foreach($employees as $employee){
                $grouped[$employee['department']][]=$employee['name'];
            }
            
            //sorting and referencing the same array to save memory
            foreach($grouped as &$groupsorted){
                sort($groupsorted);
            }

        return response()->json($grouped);
        // return view('listing.index', compact('items'));
    }

    public function checkAge(Request $request){
        $age=$request->age;
        $message=($age>=18)?"You are adult" : "You are minor";
        return view('list.index',compact('message'));

    }
}
