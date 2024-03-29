<?php

namespace App\Http\Controllers;

use App\Models\Critere;
use App\Models\Reponse;
use App\Models\Question;
use App\Models\Stagiaire;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\DemandeStage;
use App\Models\NotificationDocuments;
use App\Models\OffreStage;
use Illuminate\Support\Facades\File;


class DemandestageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexQuestionsReponses(Request $request)
    {
        // le type de stage sélectionné par le stagiaire
        $postID = DemandeStage::get()->last()->_id;
        $type = DemandeStage::get()->last()->type;

        //obtenir le critère correspondant au type de stage
        $critere = Critere::where('typestage', $type)->where('etat', 'active')->first();

        //s'il est trouvé
        if ($critere) {

            $duree = 0; //durée test
            $RandomQuestions = []; //array de de questions

            $nbrquestionsfaciles = $critere->nombrequestionsfaciles;
            $nbrquestionsmoyennes = $critere->nombrequestionsmoyennes;
            $nbrquestionsdifficiles = $critere->nombrequestionsdifficiles;

            $notequestionfacile = $critere->notequestionfacile * $nbrquestionsfaciles;
            $notequestionmoyenne = $critere->notequestionmoyenne * $nbrquestionsmoyennes;
            $notequestiondifficile = $critere->notequestiondifficile * $nbrquestionsdifficiles;

            $notetotale = $notequestionfacile + $notequestionmoyenne + $notequestiondifficile; //note test
            $pourcentage = $critere->pourcentage;

            $randomQuestionsFacile = Question::where('niveau', 'Facile')->get()->random($nbrquestionsfaciles);

            foreach ($randomQuestionsFacile as $qf) {
                $duree = $duree + $qf->duree;

                $RandomQuestions[] = [
                    'question' => $qf,
                    'reponses' => Reponse::where('questionID', $qf->_id)->where('etat', 'active')->get(),
                    'reponsecorrecte' => DB::collection('reponses')->where('questionID', $qf->_id)->where('reponseCorrecte', 'Oui')->get()->where('etat', 'active'),


                ];
            }

            $randomQuestionsMoyenne = Question::where('niveau', 'Moyenne')->get()->random($nbrquestionsmoyennes);

            foreach ($randomQuestionsMoyenne as $qm) {
                $duree = $duree + $qm->duree;

                $RandomQuestions[] = [
                    'question' => $qm,
                    'reponses' => Reponse::where('questionID', $qm->_id)->where('etat', 'active')->get(),
                    'reponsecorrecte' => DB::collection('reponses')->where('questionID', $qm->_id)->where('reponseCorrecte', 'Oui')->where('etat', 'active')->get(),

                ];
            }

            $randomQuestionsDifficile = Question::where('niveau', 'difficile')->get()->random($nbrquestionsdifficiles);

            foreach ($randomQuestionsDifficile as $qd) {
                $duree = $duree + $qd->duree;
                $RandomQuestions[] = [
                    'question' => $qd,
                    'reponses' => Reponse::where('questionID', $qd->_id)->where('etat', 'active')->get(),
                    'reponsecorrecte' => DB::collection('reponses')->where('questionID', $qd->_id)->where('reponseCorrecte', 'Oui')->where('etat', 'active')->get(),


                ];
            }



            $id = auth()->user()->_id;
            $stagiaire = Stagiaire::find($id);

            if ($RandomQuestions && $stagiaire) {
                return response()->json([
                    'status' => 200,
                    'questionsreponses' => $RandomQuestions,
                    'duree' => $duree,
                    'notetotale' => $notetotale,
                    'pourcentage' => $pourcentage,
                    'stagiaire' => $stagiaire,
                    'postid' => $postID,


                ]);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'Pas de questions trouvées ou stagiaire'
                ]);
            }
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Pas de critère trouvé'
            ]);
        }
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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

        $post = DemandeStage::find($id);

        if ($post) {


            if ($request->hasFile('demandestage') && $request->hasFile('cv') && $request->hasFile('cin')) {
                $pathFiche = $post->demandestage;
                $pathCV = $post->cv;
                $pathCIN = $post->cin;

                if (File::exists($pathFiche) || File::exists($pathCV) || File::exists($pathCIN)) {

                    File::delete($pathFiche);
                    File::delete($pathCV);
                    File::delete($pathCIN);
                }
                $fileFiche = $request->file('demandestage');
                $fileCV = $request->file('cv');
                $fileCIN = $request->file('cin');
                $extensionFiche = $fileFiche->getClientOriginalExtension();
                $extensionCV = $fileCV->getClientOriginalExtension();
                $extensionCIN = $fileCIN->getClientOriginalExtension();

                $filenameFiche = Str::random(5) . '.' . $extensionFiche;
                $filenameCV = Str::random(5) . '.' . $extensionCV;
                $filenameCIN = Str::random(5) . '.' . $extensionCIN;

                $fileFiche->move('img/post/', $filenameFiche);
                $fileCV->move('img/post/', $filenameCV);
                $fileCIN->move('img/post/', $filenameCIN);


                $post->demandestage = 'img/post/' . $filenameFiche;
                $post->cv = 'img/post/' . $filenameCV;
                $post->cin = 'img/post/' . $filenameCIN;

                $post->date = Carbon::now()->toDateTimeString();
            }

            $post->etatpost = 'published';
            $post->etatdemande = 'Nouvellement créé';
            $post->etatprise = 'faux';



            $post->update();

            return response()->json([
                'status' => 200,
                'message' => 'Vous avez postulé avec succès',
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Post non trouvé',
            ]);
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


    public function monPost($id)
    {
        $post =  DemandeStage::where('_id', $id)->first();
        return response()->json([
            'status' => 200,
            'post' => $post
        ]);
    }

    public function SuivreMonDossier()
    {
        $id = auth()->user()->_id;
        $currentuser = Stagiaire::find($id);

        $demandesdestages = DemandeStage::where('stagiaire.stagiaireId', $currentuser->_id)->where('etatpost', 'published')->get();

        return response()->json([
            'status' => 200,
            'dossier' => $demandesdestages,

        ]);
    }


    public function MessagesDocuments()
    {
        $id = auth()->user()->_id;
        $currentuser = Stagiaire::find($id);

        $notif = NotificationDocuments::where('Stagiaire_id', $currentuser->_id)->latest()->first();

        if ($notif) {
            return response()->json([
                'status' => 200,
                'notif' => $notif,

            ]);
        } else {
            return response()->json([
                'status' => 401,

            ]);
        }
    }

    public function UpdateDocuments(Request $request, $id)
    {
        $post = DemandeStage::find($id);

        if ($post) {


            if ($request->hasFile('demandestage')) {
                $pathFiche = $post->demandestage;

                if (File::exists($pathFiche)) {

                    File::delete($pathFiche);
                }
                $fileFiche = $request->file('demandestage');
                $extensionFiche = $fileFiche->getClientOriginalExtension();

                $filenameFiche = Str::random(5) . '.' . $extensionFiche;
                $fileFiche->move('img/post/', $filenameFiche);

                $post->demandestage = 'img/post/' . $filenameFiche;
                $post->date = Carbon::now()->toDateTimeString();
            }

            if ($request->hasFile('cv')) {
                $pathCV = $post->cv;

                if (File::exists($pathCV)) {

                    File::delete($pathCV);
                }
                $fileCV = $request->file('cv');
                $extensionCV = $fileCV->getClientOriginalExtension();

                $filenameCV = Str::random(5) . '.' . $extensionCV;
                $fileCV->move('img/post/', $filenameCV);

                $post->cv = 'img/post/' . $filenameCV;
                $post->date = Carbon::now()->toDateTimeString();
            }

            if ($request->hasFile('cin')) {
                $pathCIN = $post->cin;

                if (File::exists($pathCIN)) {

                    File::delete($pathCIN);
                }
                $fileCIN = $request->file('cin');
                $extensionCIN = $fileCIN->getClientOriginalExtension();

                $filenameCIN = Str::random(5) . '.' . $extensionCIN;
                $fileCIN->move('img/post/', $filenameCIN);

                $post->cin = 'img/post/' . $filenameCIN;
                $post->date = Carbon::now()->toDateTimeString();
            }



            $id = auth()->user()->_id;
            $currentuser = Stagiaire::find($id);


            $notif = NotificationDocuments::where('Stagiaire_id', $currentuser->_id)->latest()->first();

            if ($notif) {
                $notif->delete();
            }

            $emetteur = auth()->user()->nom . ' ' . auth()->user()->prenom;
            $emetteurID = auth()->user()->_id;
            $emetteurImage = auth()->user()->image;
            $newnotif = new NotificationDocuments;
            $newnotif->emetteurID = $emetteurID;
            $newnotif->emetteur = $emetteur;
            $newnotif->emetteurImage = $emetteurImage;
            $newnotif->date = Carbon::now()->toDateTimeString();


            $newnotif->save();

            $post->update();

            return response()->json([
                'status' => 200,
                'message' => 'Mise à jour effectuée avec succès',
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Post non trouvé',
            ]);
        }
    }

    public function Mademande()
    {
        $id = auth()->user()->_id;
        $currentuser = Stagiaire::find($id);
        $demande =  DemandeStage::where('etatprise', 'vrai')->where('stagiaire.stagiaireId', $currentuser->_id)->first();

        if ($demande) {
            return response()->json([
                'status' => 200,
                'demande' => $demande
            ]);
        } else {
            return response()->json([
                'status' => 401,
            ]);
        }
    }
}
