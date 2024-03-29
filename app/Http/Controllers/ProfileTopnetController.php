<?php

namespace App\Http\Controllers;

use App\Models\Stagiaire;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProfileTopnetController extends Controller
{
    public function show($id)
    {
        //Afficher un utilisateur par son id
        return User::where('_id', $id)->first();
    }



    public function editProfil($id)
    { //Afficher le formulaire de modification de profil pour tous les utilisateur sauf le stagiaire
        $user = $this->show($id);
        if ($user) {
            return response()->json([
                'status' => 200,
                'user' => $user,
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Aucun utilisateur trouvé',
            ]);
        }
    }



    public function updateProfil(Request $request, $id)
    { //Mettre à jour le profil de l'utilisateur pour tous les utilisateur sauf le stagiaire
        $validator = Validator::make($request->all(), [
            'nom' => 'required|alpha',
            'prenom' => 'required|alpha',
            'tel' => ['required', 'regex:/^[2459]\d{7}$/'],
            'adresse' => 'required',
            'role_id' => 'required',
            'departement' => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages(),
            ]);
        } else {

            $user = User::find($id);
            if ($user) {

                $user->adresse = $request->input('adresse');
                $user->nom = $request->input('nom');
                $user->prenom = $request->input('prenom');
                $user->tel = $request->input('tel');
                $user->email = $request->input('email');

                if ($request->hasFile('image')) {
                    $path = $user->image;
                    if (File::exists($path)) {

                        File::delete($path);
                    }
                    $file = $request->file('image');
                    $extension = $file->getClientOriginalExtension();
                    $filename = time() . '.' . $extension;
                    $file->move('img/user/', $filename);
                    $user->image = 'img/user/' . $filename;
                }

                $user->update();

                return response()->json([
                    'status' => 200,
                    'message' => 'Profil mis à jour avec succès',
                ]);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'Utilisateur non trouvé',
                ]);
            }
        }
    }
}
