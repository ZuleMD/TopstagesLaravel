<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReponseController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\DepartmentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



//Route pour s'inscrire (Stagiaire)
Route::post('/register', [AuthController::class, 'register']);

//Route pour s'authentifier (stagiaire)
Route::post('/login-locale', [AuthController::class, 'LocalLogin'])->middleware('throttle:login');

//Route pour s'authentifier (coordinateur/service formation/encadrant/chef département)
Route::post('/login-stagiaire', [AuthController::class, 'StagiaireLogin'])->middleware('throttle:login');



//Route pour l'envoi d'un lien de vérification par e-mail
Route::post('/forgot-password', [AuthController::class, 'forgotpassword']);
//Route pour récupérer le mot de passe oublié
Route::post('/reset-forgottenpassword', [AuthController::class, 'resetforgottenpassword']);


Route::post('/stagiaire-forgot-password', [AuthController::class, 'Stagiaireforgotpassword']);
Route::post('/stagiaire-reset-forgottenpassword', [AuthController::class, 'Stagiaireresetforgottenpassword']);



//Route pour changer le mot de passe lors de la première connexion
Route::post('/reset-firstloginpassword/{id}', [AuthController::class, 'resetfirstloginpassword']);


//Routes privés pour le Coordinateur:
Route::group(['middleware' => ['auth:sanctum', 'isCoordinateur']], function () {


    //Route pour vérifier que l'utilisateur qui est connecté est coordinateur
    Route::get('/checkingCoordinateur', function () {
        return response()->json(['message' => 'Vous êtes coordinateur', 'status' => 200], 200);
    });

    //Route pour consulter les utilisateurs
    Route::get('/users', [UserController::class, 'index']);
    //Routes pour modifier l'utilisateur
    Route::post('/users/{id}', [UserController::class, 'update']);
    Route::get('/edit-user/{id}', [UserController::class, 'edit']);
    //Route pour créer l'utilisateur
    Route::post('/users', [UserController::class, 'store']);
    //Route pour obtenir la liste des rôles
    Route::get('/roles', [UserController::class, 'GetRoles']);
    //Route pour obtenir la liste des départements
    Route::get('/departements', [UserController::class, 'GetDepartements']);
});



//Routes privés pour le Servie formation
Route::group(['middleware' => ['auth:sanctum', 'isServiceFormation']], function () {

    //Route pour vérifier que l'utilisateur qui est connecté est service formation
    Route::get('/checkingServiceFormation', function () {
        return response()->json(['message' => 'Vous êtes service formation', 'status' => 200], 200);
    });


    //Route pour créer un département
    Route::post('/departments', [DepartmentController::class, 'store']);
    //Route pour consulter les départements
    Route::get('/departments', [DepartmentController::class, 'index']);
    //Route pour modifier un département
    Route::post('/departments/{id}', [DepartmentController::class, 'update']);
    Route::get('/edit-department/{id}', [DepartmentController::class, 'edit']);

    //Route pour créer une question
    Route::post('/questions', [QuestionController::class, 'store']);
    //Route pour consulter les questions
    Route::get('/questions', [QuestionController::class, 'index']);
    //Route pour modifier une question
    Route::post('/questions/{id}', [QuestionController::class, 'update']);
    Route::get('/edit-question/{id}', [QuestionController::class, 'edit']);

    //Route pour obtenir la liste des réponses de la question spécifié par son id
    Route::get('/reponses/{id}', [QuestionController::class, 'GetReponses']);
    //Route pour supprimer une réponses
    Route::delete('/delete-reponse/{id}', [ReponseController::class, 'destroy']);
    //Route pour créer une réponse
    Route::post('/reponses', [ReponseController::class, 'store']);
    //Routes pour modifier une réponse
    Route::post('/reponses/{id}', [ReponseController::class, 'update']);
    Route::get('/edit-reponse/{id}', [ReponseController::class, 'edit']);
});





//Routes pour tous les utilisateurs authentifiés
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/checkingAuthenticated', function () {
        return response()->json(['message' => 'You are in', 'status' => 200], 200);
    });

    //Route pour obtenir l'utilisateur actuellement connecté
    Route::get('/currentuser', [AuthController::class, 'getCurrentUser']);
    //Route pour mettre à jour le profil
    Route::get('/edit-profil/{id}', [ProfileController::class, 'editProfil']);
    Route::post('/profil/{id}', [ProfileController::class, 'updateProfil']);

    //Route pour se déconnecter
    Route::post('/logout', [AuthController::class, 'logout']);
});
