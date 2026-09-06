<?php
namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::current();
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = Setting::current();
        $data = $request->validate([
            'nom_etablissement'=>'required|string',
            'sous_titre'=>'nullable|string',
            'adresse'=>'nullable|string',
            'telephone'=>'nullable|string',
            'email'=>'nullable|email',
            'logo'=>'nullable|file|mimes:png,jpg,jpeg,gif,webp,svg|max:2048',
            'couleur_principale'=>'nullable|string',
            'couleur_sidebar'=>'nullable|string',
        ]);
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }
        $settings->update($data);
        return back()->with('success',__('Paramètres enregistrés.'));
    }

    public function deleteLogo()
    {
        $settings = Setting::current();
        if ($settings->logo) {
            Storage::disk('public')->delete($settings->logo);
            $settings->update(['logo'=>null]);
        }
        return back()->with('success',__('Logo supprimé.'));
    }
}
