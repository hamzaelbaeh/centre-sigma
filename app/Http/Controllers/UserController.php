<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('username')->paginate(25);
        return view('users.index', compact('users'));
    }

    public function create() { return view('users.create'); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username'=>'required|string|unique:users,username',
            'name'=>'required|string',
            'role'=>'nullable|string',
            'password'=>'required|string|min:4',
            'is_active'=>'nullable|boolean',
        ]);
        $data['role'] = $data['role'] ?? 'Secrétaire';
        $data['is_active'] = $request->boolean('is_active', true);
        $data['password'] = Hash::make($data['password']);
        User::create($data);
        return redirect()->route('users.index')->with('success','Utilisateur créé.');
    }

    public function edit(User $user) { return view('users.edit', compact('user')); }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'username'=>['required','string',Rule::unique('users')->ignore($user->id)],
            'name'=>'required|string',
            'role'=>'nullable|string',
            'password'=>'nullable|string|min:4',
            'is_active'=>'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        if (empty($data['password'])) unset($data['password']);
        else $data['password'] = Hash::make($data['password']);
        $user->update($data);
        return redirect()->route('users.index')->with('success','Utilisateur mis à jour.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) return back()->withErrors(['error'=>'Impossible de supprimer votre propre compte.']);
        $user->delete();
        return redirect()->route('users.index')->with('success','Utilisateur supprimé.');
    }
}
