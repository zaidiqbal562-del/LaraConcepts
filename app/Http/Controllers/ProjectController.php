<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Mail\ProjectCreatedMail;
use App\Jobs\SendProjectCreatedEmail;
class ProjectController extends Controller
{
    public function index()
    {
        // $projects=Project::with('managerUser')->where('manager',auth()->id())->latest()->get();
        // $projects=Project::all()->where('manager',auth()->id());
        if (auth()->user()->is_super_user) {

            $projects = Project::all();
        } else {
            $projects = Project::where('manager', auth()->id())->get();
        }
            
        return view('projects.index', compact('projects'));
    }


    public function create()
    {
        $users = User::all();
        
        return view('projects.create', compact('users')); //FOR SELECTING AND SHOWING MANAGER 
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'manager' => 'required|exists:users,id',
            'paid' => 'required|boolean'
        ]);

        $project = Project::create($request->all());

        $creator = auth()->user();

        // Dispatch a queued job to email all other users about the new project
        SendProjectCreatedEmail::dispatch($project, $creator);

        return redirect()->route('projects.index')->with('success', 'Project created successfully.');
    }

    public function edit(Project $project) //laravel route model binding will automatically fetch the project based on the ID in the URL
    {

        // dd($project);
        $users = User::all();
        return view('projects.edit', compact('project', 'users'));
    }


    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required',
            'manager' => 'required|exists:users,id',
            'paid' => 'required|boolean'
        ]);

        $project->update($request->all());
        return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
    }


    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }

    public function search(Request $request)
    {
        $Search = $request->Search;
        if (auth()->user()->is_super_user) {
            $projects = Project::where('name', 'LIKE', "%{$Search}%")->get();
            if($projects->isEmpty()){
                return response()->json(['message'=>'No data found'],404);
            }
            else{
                return response()->json($projects);
            }

        } 
        
    }
}
