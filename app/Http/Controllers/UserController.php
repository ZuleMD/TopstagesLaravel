<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;



class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::all();

        return response()->json([
            'status' => 200,
            'users' => $users
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Pour créer un utilisateur
        $validator = Validator::make($request->all(), [
            'matricule' => ['required', 'numeric', 'unique:users'],
            'tel' => ['required', 'regex:/^[2459]\d{7}$/'],
            'nom' => 'required|alpha',
            'prenom' => 'required|alpha',
            'adresse' => 'required',
            'role_id' => 'required',
            'departement' => 'required',
            'service' => 'required',
            'loginTOPNET' => ['required', 'string', 'unique:users'],
            'email' => ['required', 'string', 'email', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'max:25'],
            'image' => 'required|mimes:jpeg,jpg,png',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages(),
            ]);
        } else {
            $user = new User;

            $user->role_id = $request->input('role_id');
            $user->departement = $request->input('departement');
            $user->service = $request->input('service');

            $user->matricule = $request->input('matricule');
            $user->adresse = $request->input('adresse');
            $user->nom = $request->input('nom');
            $user->prenom = $request->input('prenom');
            $user->tel = $request->input('tel');
            $user->loginTOPNET = $request->input('loginTOPNET');
            $user->email = $request->input('email');
            $user->password = bcrypt($request->input('password'));
            $user->etat = 'active';

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '.' . $extension;
                $file->move('img/user/', $filename);
                $user->image = 'img/user/' . $filename;
            }

            $user->save();


            return response()->json([
                'status' => 200,
                'message' => 'Compte créé avec succès',
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  mixed  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //Afficher un utilisateur par son id
        return User::where('_id', $id)->first();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //Afficher le formulaire de modification de l'utilisateur spécifié par id. 
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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //Mettre à jour un utilisateur  
        $validator = Validator::make($request->all(), [
            'tel' => ['required', 'regex:/^[2459]\d{7}$/'],
            'adresse' => 'required',
            'role_id' => 'required',
            'departement' => 'required',
            'service' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages(),
            ]);
        } else {

            $user = User::find($id);
            if ($user) {

                $user->role_id = $request->input('role_id');
                $user->departement = $request->input('departement');
                $user->service = $request->input('service');

                $user->matricule = $request->input('matricule');
                $user->adresse = $request->input('adresse');
                $user->nom = $request->input('nom');
                $user->prenom = $request->input('prenom');
                $user->tel = $request->input('tel');
                $user->loginTOPNET = $request->input('loginTOPNET');
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
                    'message' => 'Compte mis à jour avec succès',
                ]);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'Utilisateur non trouvé',
                ]);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    public function GetRoles()
    {
        //Obtenir la liste de tous les rôles sauf  "Stagiaire"
        $roles =  DB::collection('roles')->where('nom', '!=', 'Stagiaire')->get();
        return response()->json([
            'status' => 200,
            'roles' => $roles
        ]);
    }


    public function GetDepartements()
    {
        //Obtenir la liste de tous les départements activés
        $deps =  DB::collection('departments')->where('etat', 'active')->get();
        return response()->json([
            'status' => 200,
            'departements' => $deps
        ]);
    }

    public function GetServices()
    {
        //Obtenir la liste de tous les services activés
        $services =  DB::collection('services')->where('etat', 'active')->get();
        return response()->json([
            'status' => 200,
            'services' => $services
        ]);
    }


    public function desactiverUser($id)
    {
        $user  = User::find($id);
        if ($user) {
            if ($user->etat == 'active') {
                $user->etat = 'inactive';
                $user->save();
                return response()->json([
                    'status' => 200,
                    'message' => 'Compte utilisateur est désactivé'
                ]);
            } else {
                $user->etat = 'active';
                $user->save();
                return response()->json([
                    'status' => 201,
                    'message' => 'Compte utilisateur est activé'
                ]);
            }
        } else {
            return response()->json([
                'status' => 401,
                'message' => "Utilisateur non trouvé"
            ]);
        }
    }
}
