<?php

namespace App\Http\Livewire\Admin\Users;

use App\Http\Livewire\Admin\AdminComponent;
use App\Models\User;
use Illuminate\Support\Facades\Validator;


class ListUsers extends AdminComponent
{

    // usando individualmente wire:model.defer="name"
    //public $name;
    // public $email;
    // public $password;
    // public $password_confirmation;

    // usando dentro de um array, declara todas nativamente com wire:model.defer="state.name"
    public $state = [];

    public $user;

    public $userIdBeingRemoved = null;

    public $showEditModal = false;

    public function addNew()
    {
        //dd('here');
        $this->showEditModal = false;
        $this->state = [];
        $this->dispatchBrowserEvent('show-form');
    }

    public function createUser()
    {
        //dd('here');
        //dd($this->state);
        $validateData = Validator::make($this->state, [
           'name' => 'required',
           'email' => 'required|email|unique:users',
           'password' => 'required|confirmed',
        ])->validate();

        $validateData['password'] = bcrypt($validateData['password']);

        //dd($validateData);
        User::create($validateData);
        //session()->flash('message', 'User add successfully!');
        $this->dispatchBrowserEvent('hide-form', ['message' => 'User added successfully!']);
        //return redirect()->back();
    }

    public function edit(User $user)
    {
        //dd($user);
        $this->showEditModal = true;
        $this->state = $user->toArray();
        $this->user = $user;
        // dd($this->state);
        $this->dispatchBrowserEvent('show-form');
    }

    public function updateUser(){
        //dd('here');
        //dd($this->state);
        $validateData = Validator::make($this->state, [
           'name' => 'required',
           'email' => 'required|email|unique:users,email,'.$this->user->id,
           'password' => 'sometimes|confirmed',
        ])->validate();

        if(!empty($validateData['passoword'])){
            $validateData['password'] = bcrypt($validateData['password']);
        }

        //dd($validateData);
        $this->user->update($validateData);
        //session()->flash('message', 'User add successfully!');
        $this->dispatchBrowserEvent('hide-form', ['message' => 'User updated successfully!']);

    }

    public function confirmUserRemoval($userId)
    {
        //dd($userId);
        $this->userIdBeingRemoved = $userId;
        $this->dispatchBrowserEvent('show-delete-modal');
    }

    public function deleteUser()
    {
        $user = User::findOrFail($this->userIdBeingRemoved);

		$user->delete();

		$this->dispatchBrowserEvent('hide-delete-modal', ['message' => 'User deleted successfully!']);
    }

    public function render()
    {
        $users = User::latest()->paginate(5);
        return view('livewire.admin.users.list-users', [
            'users' => $users,
        ]);
    }
}
