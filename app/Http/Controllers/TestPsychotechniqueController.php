<?php

namespace App\Http\Controllers;

use App\Models\Critere;
use App\Models\Reponse;
use App\Models\Question;
use App\Models\Stagiaire;
use App\Models\Testpsychotechnique;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;



class TestPsychotechniqueController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexQuestionsReponses(Request $request)
    {

        $domaine = Testpsychotechnique::get()->last()->domaine;
        $type = Testpsychotechnique::get()->last()->type;

        $critere = Critere::where('typestage', $type)->where('domainestage', $domaine)->where('etat', 'active')->first();

        if ($critere) {
            $duree = 0;
            $RandomQuestions = [];

            $nbrquestionsfaciles = $critere->nombrequestionsfaciles;
            $nbrquestionsmoyennes = $critere->nombrequestionsmoyennes;
            $nbrquestionsdifficiles = $critere->nombrequestionsdifficiles;

            $notequestionfacile = $critere->notequestionfacile * $nbrquestionsfaciles;
            $notequestionmoyenne = $critere->notequestionmoyenne * $nbrquestionsmoyennes;
            $notequestiondifficile = $critere->notequestiondifficile * $nbrquestionsdifficiles;

            $notetotale = $notequestionfacile + $notequestionmoyenne + $notequestiondifficile;


            $randomQuestionsFacile = Question::where('niveau', 'Facile')->get()->random($nbrquestionsfaciles);

            foreach ($randomQuestionsFacile as $qf) {
                $duree = $duree + $qf->duree;

                $RandomQuestions[] = [
                    'question' => $qf,
                    'reponses' => Reponse::where('questionID', $qf->_id)->get(),
                    'reponsecorrecte' => DB::collection('reponses')->where('questionID', $qf->_id)->where('reponseCorrecte', 'Oui')->get(),


                ];
            }

            $randomQuestionsMoyenne = Question::where('niveau', 'Moyenne')->get()->random($nbrquestionsmoyennes);

            foreach ($randomQuestionsMoyenne as $qm) {
                $duree = $duree + $qm->duree;

                $RandomQuestions[] = [
                    'question' => $qm,
                    'reponses' => Reponse::where('questionID', $qm->_id)->get(),
                    'reponsecorrecte' => DB::collection('reponses')->where('questionID', $qm->_id)->where('reponseCorrecte', 'Oui')->get(),

                ];
            }

            $randomQuestionsDifficile = Question::where('niveau', 'difficile')->get()->random($nbrquestionsdifficiles);

            foreach ($randomQuestionsDifficile as $qd) {
                $duree = $duree + $qd->duree;
                $RandomQuestions[] = [
                    'question' => $qd,
                    'reponses' => Reponse::where('questionID', $qd->_id)->get(),
                    'reponsecorrecte' => DB::collection('reponses')->where('questionID', $qd->_id)->where('reponseCorrecte', 'Oui')->get(),


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
                    'stagiaire' => $stagiaire

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
        //
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
}
