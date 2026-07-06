<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable=['name', 'manager', 'paid'];

    // Relation to the User who is the manager.
    // Named `managerUser` to avoid colliding with the `manager` attribute
    // (which stores the foreign key). This makes it explicit in views
    // and controllers when accessing the related model, e.g.:
    //   $project->managerUser->name
    // The relation is eager-loaded in the controller with `with('managerUser')`.
    public function managerUser()
    {
        return $this->belongsTo(User::class, 'manager');
    }
}

