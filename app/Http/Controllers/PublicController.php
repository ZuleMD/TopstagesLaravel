<?php

namespace App\Http\Controllers;

use App\Models\OffreStage;
use App\Models\Stagiaire;
use App\Models\DemandeStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PublicController extends Controller
{
    public function GetDepartements()
    {
        //Obtenir la liste de tous les départements activés
        $deps =  DB::collection('departments')->where('etat', 'active')->get();
        return response()->json([
            'status' => 200,
            'deps' => $deps
        ]);
    }

    public function getOffres()
    {

        //obtenir la liste de toutes les offres de stage pour le stagiaire
        $offres = OffreStage::where([['etatoffre', '!=', 'inactive'], ['etatpartage', '!=', 'unpublished']])->get();

        return response()->json([
            'status' => 200,
            'offres' => $offres,

        ]);
    }

    public function postOffreDemandee(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'domaine' => 'required',
            'stagiaireID' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages(),
                'message' => 'Veuillez choisir le domaine et le type de stage'
            ]);
        } else {
            $offredemande = new DemandeStage;
            $offredemande->domaine = $request->input('domaine');
            $offredemande->type = $request->input('type');

            $idstagiaire = $request->stagiaireID;
            $stagiaire = Stagiaire::find($idstagiaire);

            $stagiairearray[] = [
                'stagiaireId' => $stagiaire->id,
                'nom' => $stagiaire->nom,
                'prenom' => $stagiaire->prenom,
                'email' => $stagiaire->email,
            ];

            $offredemande->stagiaire =  $stagiairearray;



            $offredemande->save();

            return response()->json([
                'status' => 200,
                'offredemande' => $offredemande
            ]);
        }
    }
}
