<?php

namespace App\Http\Controllers;

use Exception;
use App\Repositories\Repository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use App\Repositories\Data;
use Illuminate\Support\Facades\DB;

use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\ConvertEmptyStringsToNull;
use App\Mail\ContactMessageCreated;
use App\Models\User as ModelsUser;
use Facade\FlareClient\Http\Response;
use Illuminate\Foundation\Auth\User;
use PhpParser\Builder\Use_;
use PhpParser\Node\Stmt\Use_ as StmtUse_;
use Symfony\Component\HttpFoundation\Cookie;
use Sentinel;
use Reminder;
//use Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }



    function showPageAccueil(Request $request)
    {
        $hasKey = $request->session()->has('student');
        $hasKey1 = $request->session()->has('teacher');
        if($hasKey){
            return view('tableau_de_bord_etudiant');
        }
       else if($hasKey1){
            return view('tableau_de_bord_enseignant');
       }

        return view('page_accueil');
    }
    function showItablissement()
    {
        return view('itablissement');
    }

    function showContact()
    {
        return view('contact');
    }
//----------------------------------------------------------------------------------------------------------------------------------
    function showConnexionEtudiant()
    {
        return view('page_principale_Etudiant');
    }
//--------------------------------------------------------------------------------------------------------------------------------
    function showInscriptionEtudiant()
    {
        return view('page_Inscription_Etudiant');
    }

    function storeInscription(Request $request)
    {
        $rules = [
            'nom' => ['required'],
            'prénom' => ['required'],
            'email' => ['required', 'unique:Etudiant,Email_Etudiant'],
            'tel' => ['required', 'unique:Etudiant,Email_Etudiant'],
            'password' => ['required'],
            'passwordconfirmation' => ['required'],
            'niveau' => ['required'],
            'date' => ['required', 'date'],
            'scales' => ['required'],

            ];
            $messages = [
                'nom.required' => 'Vous devez choisir un nom.',
                'prénom.required' => 'Vous devez choisir un prénom.',
                'email.required' => 'Vous devez choisir une adresse mail.',
                'email.unique' => 'Cet Email existe déja.',
                'tel.required' => 'Vous devez choisir un numéro de telephone.',
                'tel.unique' => 'Ce numéro existe déja.',
                'password.required' => 'champs obligatoire',
                'passwordConfirmation.required' => 'champs obligatoire',
                'niveau.required' => 'champs obligatoire',
                'date.required' => 'Vous devez choisir une date de naissance.',
                'date.date' => 'Vous devez choisir une date de naissance valide.',
                'scales.required'=>'vous devez cocher pour continuer'
            ];

            $validatedData = $request->validate($rules,$messages);
            try{

                $this->repository->insertEtudiant(    ['NomEtudiant' => $validatedData['nom'],
                'PrénomEtudiant' => $validatedData['prénom'],
                'Email_Etudiant' => $validatedData['email'],
                'NumTelephone' => $validatedData['tel'],
                'Date_Naissance' => $validatedData['date'],
                'Niveau_Etude' => $validatedData['niveau'],
                'NomImage'=> 'imagesDefault.png'
                ]);

                $this->repository->addUser($validatedData['email'], $validatedData['password']);
            }catch (Exception $exception) {

                var_dump($exception);
                return redirect()->route('InscriptionEtudiant.show')->withInput()->withErrors("Impossible de rajouter l'étudiant");
            }
        //return redirect()->route('tableauDeBordEtudiant.show');
        return redirect()->route('PageAccueil.show');
    }
//-----------------------------------------------------------------------------------------------------------------------------------
    function showLoginEtudiant()
    {
        return view('LoginEtudiant');
    }

    function storeLoginEtudiant(Request $request)
    {
        $rules = [
        'email' => ['required', 'email'],
        'password' => ['required']
         ];
        $messages = [
        'email.required' => 'Vous devez saisir un e-mail.',
        'email.email' => 'Vous devez saisir un e-mail valide.',
        'email.exists' => "Cet utilisateur n'existe pas.",
        'password.required' => "Vous devez saisir un mot de passe.",
         ];
        $validatedData = $request->validate($rules, $messages);
        $email=$validatedData['email'];
        $password=$validatedData['password'];


        try {
      # TODO 1 : lever une exception si le mot de passe de l'utilisateur n'est pas correct
        $student=$this->repository->getStudent($email,$password);
      # TODO 2 : se souvenir de l'authentification de l'utilisateur

        //$value = $request->session()->get($user['email']);
        $request->session()->put('student', $student);
        //return view('tableau_de_bord_etudiant');

        } catch (Exception $e) {
        return redirect()->back()->withInput()->withErrors("Impossible de vous authentifier.");
    }



    //return redirect()->route('tableauDeBordEtudiant.show');
    return redirect()->route('PageAccueil.show');
    }

//-----------------------------------------------------------------------------------------------------------------------------------
function showLoginEnseignant()
{
    return view('page_autentification_enseignant');
}

function storeLoginEnseignant(Request $request)
{

    $rules = [
        'email_teacher' => ['required', 'email'],
        'password_teacher' => ['required']
         ];
        $messages = [
        'email_teacher.required' => 'Vous devez saisir un e-mail.',
        'email_teacher.email' => 'Vous devez saisir un e-mail valide.',
        'email_teacher.exists' => "Cet utilisateur n'existe pas.",
        'password_teacher.required' => "Vous devez saisir un mot de passe.",
         ];
        $validatedData = $request->validate($rules, $messages);
        $email=$validatedData['email_teacher'];
        $password=$validatedData['password_teacher'];


        try {
      # TODO 1 : lever une exception si le mot de passe de l'utilisateur n'est pas correct
        $teacher=$this->repository->getTeacher($email,$password);
      # TODO 2 : se souvenir de l'authentification de l'utilisateur

        //$value = $request->session()->get($user['email']);
        $request->session()->put('teacher', $teacher);

        } catch (Exception $e) {
        return redirect()->back()->withInput()->withErrors("Impossible de vous authentifier.");
        }
        //return redirect()->route('tableauDeBordEnseignant.show');
        return redirect()->route('PageAccueil.show');

}
      function logout(Request $request) {
        if($request->session()->has('student')){
        $request->session()->forget('student');
        $value=$request->session()->get('student');
        //dd($value);
        }
        else{
        $request->session()->forget('teacher');
        }

        return redirect()->route('PageAccueil.show');
      }
//-----------------------------------------------------------------------------------------------------------------------

    function motDePasseOublieForm()
    {
        return view('mot_de_passe_oublie');
    }
    function storemotDePasseOublie(Request $request, Repository $repository)
    {
        $messages = ['email.required' => "Vous devez saisir votre mail"];

        $rules = [
                    'email' => ['required']
                 ];

        $validateData = $request->validate($rules, $messages);

        $email = $validateData['email'];
        $repository->tableEtudiant($email);

        $reponse = $repository->etudiantExiste($email);
        if($reponse==true)
        {
            $code = rand(100000,999999);
            //dump($code);
            $tablEtudiant = $repository->tableEtudiant($email);
            $prenom = $tablEtudiant[0]->PrénomEtudiant;

            $mailable = new ContactMessageCreated($email,$prenom,$code);
            Mail::to($email)->send($mailable);
            $repository->changeCodeConfirmation($email,$code);
            return redirect()->back()->with('message', 'Un message a été envoyé dans votre boite mail');
        }


        return redirect()->back()->withErrors('message : La réinitialisation de votre mot de passe a échoué');

    }
//---------------------------------------------------------------------------------------------------------------------------------

    function reinitialisationMotDePasseForm()
    {
        return view('reinitialisation_mot_de_passe');
    }

    function storereinitialisationMotDePasse(Request $request, Repository $repository)
    {
        $messages = [
                    'email.required' => "Vous devez saisir votre mail",
                    'code.required' => "Saisissez le code reçu sur votre boite mail",
                    'password1.required' => "Vous devez saisir un mot de passe",
                    'password2.required' => "Vous devez saisir un mot de passe"
                    ];
        $rules = [
                    'email' => ['required','email'],
                    'code' => ['required','integer'],
                    'password1' => ['required'],
                    'password2' => ['required']
                 ];
        $validateData = $request->validate($rules, $messages);
        //dd($validateData['email']);
        $emailSaisi = $validateData['email'];
        $codeSaisi = $validateData['code'];
        $password1Saisi = $validateData['password1'];
        $password2Saisi = $validateData['password2'];
        $tabUser = $repository->tableUtilisateurEtudiant($emailSaisi);
        if(count($tabUser)!==0){
            $emailExistant = $tabUser[0]->Email_Etudiant;
            $codeExistant =  $tabUser[0]->codeReinitialisation;
            if($codeSaisi=== $codeExistant && $password1Saisi ===$password2Saisi)
            {
                $repository->changeMotDePasseOublier($emailExistant,$password1Saisi);
                return view('page_principale_Etudiant')->with('message','Votre mot de passe a bien été changé');
            }
        }
        return redirect()->back()->withErrors('echeque, vérifiez bien vos informations');
    }
//--------------------------------------------------------------------------------------------------------------------------------
   /*function showTableauDeBordEtudiant(Request $request)
    {
        $hasKey = $request->session()->has('student');
        if(!$hasKey){
            return redirect()->route('PageAccueil.show');
        }
        return view('tableau_de_bord_etudiant');
    }*/
//--------------------------------------------------------------------------------------------------------------------------------
    /*function showTableauDeBordEnseignant(Request $request)
    {
        $hasKey = $request->session()->has('teacher');
        if(!$hasKey){
            return redirect()->route('PageAccueil.show');
        }
        return view('tableau_de_bord_enseignant');
    }*/
//--------------------------------------------------------------------------------------------------------------------------------
    function showProfil(Request $request)
    {
        $hasKey = $request->session()->has('student');
        //dd($hasKey);
       //dump($hasKey);
        if(!$hasKey){
            return redirect()->route('PageAccueil.show');
        }

        $value = $request->session()->get('student');
        $email = $value[1];

        try{

        $tableEtudiant = $this->repository->tableEtudiant($email);
        $nom = $tableEtudiant[0]->NomEtudiant;
        //dump($nom);
        $prenom = $tableEtudiant[0]->PrénomEtudiant;
        $dateNaissance = $tableEtudiant[0]->Date_Naissance;
        $phone = $tableEtudiant[0]->NumTelephone;
        $classe = $tableEtudiant[0]->Niveau_Etude;
        $NomImage = $tableEtudiant[0]->NomImage;
        //dd($NomImage);

        $tableInformations = ['NomImage'=>$NomImage ,'nom'=>$nom , 'prenom'=>$prenom , 'dateNaissance'=>$dateNaissance , 'phone'=>$phone , 'classe'=>$classe];


        } catch(Exception $e){
            return redirect()->back()->withErrors("il y a eu un probleme");
        }
        return view('profil_etudiant',['tab' => $tableInformations]);
    }
//-------------------------------------------------------------------------------------------------------------------------------
    function modificationEtudiantForm(Request $request)
    {
        /*
        verifier si l'utilisateur est connecté ou pas si non rediriger 'utlisateur vers la page
        d'autentification. avec un message vous "devez vous authentifier d'abord".
        */
        $hasKey = $request->session()->has('student');
        if(!$hasKey){
            return redirect()->route('PageAccueil.show');
        }
        $value = $request->session()->get('student');
        $email = $value[1];
        $nomImage = $this->repository->tableEtudiant($email)[0]->NomImage;

        return view('page_modification_etudiant',['nomImage'=>$nomImage]);
    }

    function storeModificationEtudiant(Request $request, Repository $repository)
    {
        $messages = [
            'nom.String' => "Vous devez saisir un nom.",
            'prenom.String' => "Vous devez saisir un Pres nom.",
            'ancienEmail.required' => "Vous devez saisir votre ancien email.",
            'nouveauEmail.email' => "Vous devez saisir un email.",
            'date.date' => "Vous devez selectionner une date valide."

          ];

        $rules = [  'nom' => ['String','nullable'],
                    'prenom' => ['String','nullable'],
                    'phone' => ['String','nullable'],
                    'ancienEmail' => ['required','email'],
                    'nouveauEmail' => ['email','nullable'],
                    'date' => ['date','nullable']

                ];
        //verification des champs saisi
        //dump($request->all());
        $validatedData = $request->validate($rules,$messages);
        $ancienEmail = $validatedData['ancienEmail'];




        //dd($nom . $prenom . $ancienEmail . $nouveauEmail . $date);
        $hasKey = $request->session()->has('student');
        if(!$hasKey){
            return redirect()->route('PageAccueil.show');
        }
        //dump(gettype($tableEtudiant));
        //dd(count($tableEtudiant));
        try{
        //dd($request->file('image'));
        $tableEtudiant = $repository->tableEtudiant($ancienEmail);
        $nom = $tableEtudiant[0]->NomEtudiant;
        $prenom = $tableEtudiant[0]->PrénomEtudiant;
        $phone = $tableEtudiant[0]->NumTelephone;
        $ancienEmail = $tableEtudiant[0]->Email_Etudiant;
        $nouveauEmail = $tableEtudiant[0]->Email_Etudiant;
        $date = $tableEtudiant[0]->Date_Naissance;
        $NomImage = $tableEtudiant[0]->NomImage;


        if($validatedData['nom']!= null)
            $nom = $validatedData['nom'];

        if($validatedData['prenom']!== null)
            $prenom = $validatedData['prenom'];
        if($validatedData['phone']!=null)
            $phone = $validatedData['phone'];

        if($validatedData['ancienEmail']!= null)
            $ancienEmail = $validatedData['ancienEmail'];

        if($validatedData['nouveauEmail']!= null)
            $nouveauEmail = $validatedData['nouveauEmail'];

        if($validatedData['date']!= null)
            $date = $validatedData['date'];
        //dd(gettype($phone));
        $repository->modifInfoEtudiant($ancienEmail,$nouveauEmail,$nom,$prenom,$date,$phone);


        return redirect()->route('profil.show')->with('message','Modiffications enrigistrées');
        }catch(Exception $e)
        {
            return redirect()->back()->withErrors("Modifs non enrigistrées");
        }
    }
//-------------------------------------------------------------------------------------------------------------------------
function storePhoto(Request $request)
{
    //23/03/2021
    $hasKey = $request->session()->has('student');
    if(!$hasKey){
        return redirect()->route('PageAccueil.show');
    }
    $value = $request->session()->get('student');
        $email = $value[1];
    //dd($request->file('image'));
    //dd($request->hasFile('image'));
    if($request->hasFile('image'))
    {
        $fileName = $request->image->getClientOriginalName();
        try
        {
            $this->repository->verificationExtensionImage($fileName);
        }catch(Exception $e)
        {
            return redirect()->back()->withErrors("Extension de photos autorisée => (png,PNG,jpg,JPG,jpeg,GPEG) uniquement");
        }
        //$request->image->storeAs('image',$fileName,'public');
        $nomFichierTmp = $_FILES['image']['tmp_name'];
        //dd($nomFichierTmp);
        $fichierDestination = 'C:\Users\hp\OneDrive\Bureau\25-03-21-00..56\contact\public\storage\image/'.$fileName;
        $this->repository->envoiPhoto($nomFichierTmp, $fichierDestination);
        DB::table('Etudiant')
        ->where('Email_Etudiant', $email)
        ->update(['NomImage'=> $fileName]);
        return redirect()->back()->with("message","Photo de profil changé avec succé");
    }
    return redirect()->back()->withErrors("aucune photo selectionné");


}
//-------------------------------------------------------------------------------------------------------------------------------
    function showMesRendezVousEtudiant(Request $request)
    {

        $hasKey = $request->session()->has('student');
        if(!$hasKey){
            return redirect()->route('PageAccueil.show');
        }
        $IdEtudiant = $request->session()->get('student')[0];

        $msg_count=$this->repository->msg_count($IdEtudiant);

        // $ms=$this->repository->getIdtech($IdEtudiant);
        $CC=$this->repository->msg_rdv($IdEtudiant);


        if($CC==0) return view('mes_rendez_vous_etudiant',['msg_count' => $msg_count ,'CC'=>$CC]);

         $Names=$this->repository->info($IdEtudiant);

        return view('mes_rendez_vous_etudiant',['msg_count' => $msg_count, 'Names' => $Names,'CC'=>$CC]);
    }
//--------------------------------------------------------------------------------------------------------------------------------
    function priseRendezVousForm(Request $request)
    {
        $hasKey = $request->session()->has('student');
        if(!$hasKey){
            return redirect()->route('PageAccueil.show');
        }
        //dd($request->all());
        //dd($hasKey);
        // dd($request->profs['NomEnseignant']);
        // $request->profs['NomEnseignant'];
        // $request->profs['PrénomEnseignant'];
        $email = $request->profs['Email_Enseignant'];
        $tab = ['10:00','10:30','11:00','11:30','12:00','12:30','14:00','14:30','15:00'];
        $tableDispoEnseignant = $this->repository->tabDispoEnseignant($email);
        $tableDispoEnseignant = json_decode(json_encode($tableDispoEnseignant), true);
        for($i=0; $i<9 ;$i++)
        {
            $tabDispoLundi[$i] = $tableDispoEnseignant[$i];
            $tabDispoLundi[$i]['H'] = $tab[$i];
        }

        for($i=9; $i<18 ;$i++)
        {
            $tabDispoMardi[$i] = $tableDispoEnseignant[$i];
            $tabDispoMardi[$i]['H'] = $tab[$i-9];
        }
        for($i=18; $i<27 ;$i++)
        {
            $tabDispoMercredi[$i] = $tableDispoEnseignant[$i];
            $tabDispoMercredi[$i]['H'] = $tab[$i-18];
        }
        for($i=27; $i<36 ;$i++)
        {
            $tabDispoJeudi[$i] = $tableDispoEnseignant[$i];
            $tabDispoJeudi[$i]['H'] = $tab[$i-27];
        }
        for($i=36; $i<45 ;$i++)
        {
            $tabDispoVendredi[$i] = $tableDispoEnseignant[$i];
            $tabDispoVendredi[$i]['H'] = $tab[$i-36];
        }

        //dump("okkkkkkk");
        $arrayUrl = DB::table('RendezVous')->get('urlFichier')->toArray();
        //$url = $arrayUrl[0]->urlFichier;
        //$nonFichier = $this->repository->nonFichier();
        //$nomFichierHache = $nonFichier[0]->nomFichierHache;
        return view('prise_rendez_vous_etudiant',[  'PrénomEnseignant'=>$request->profs['NomEnseignant'],
                                                    'NomEnseignant'=>$request->profs['PrénomEnseignant'],
                                                    'Matière'=>$request->profs['Matière'],
                                                    'tabDispoLundi'=>$tabDispoLundi,
                                                    'tabDispoMardi'=>$tabDispoMardi,
                                                    'tabDispoMercredi'=>$tabDispoMercredi,
                                                    'tabDispoJeudi'=>$tabDispoJeudi,
                                                    'tabDispoVendredi'=>$tabDispoVendredi,
                                                    'email'=>$email
                                                    //'nomFichierHache'=>$nomFichierHache
                                                ]);

    }
//-------------------------------------------------------------------------------------------------------------------------

    function storePriseRendezVous(Request $request)
    {

        /*
        verification des champs saisis
        verification de la disponibilité de l'enseignant*/

        $hasKey = $request->session()->has('student');

        if(!$hasKey){
            return redirect()->route('PageAccueil.show');
        }

        //dd($request->all());


        $emailProf = $request->input()["emailprofs"];
        $tabCoche = $request->input('choix');
        //dd($tabCoche);
        if($tabCoche==null) return redirect()->back()->withErrors('selectionner au moins un créneau');
        $Heure = $tabCoche[0];
        $idEnseignant = intval($this->repository->tableEnseignant($emailProf)[0]["Id_Enseignant"]);
        $IdEtudiant = intval($request->session()->get('student')[0]);
        //$jour = $this->repository->getJour($Heure);
        $message = $request->input('message');
        if($message==null) $message ="aucun message";
        $objet = $request->input('motif') ;

        $this->repository->envoiFichier($Heure, $message, $objet, $IdEtudiant, $idEnseignant);
        //dd($tabCoché[0]);
        //dd( $tabCoché);
        //$taille = count($tabCoché);
        $this->repository->modificationDispoParEtudiant($emailProf, $Heure);
        $nonFichier = $this->repository->nonFichier();
        $nomFichierHache = $nonFichier[0]->nomFichierHache;
        return redirect()->back()->with('message', 'Rendez-vous pris avec sucée');
        //return view('mes_rendez_vous_etudiant');
    }
//----------------------------------------------------------------------------------------------------------------------------

    function annulationRendezVous(Request $request)
    {
        /*
        verification que la requette a été faite apres connexion
        affichafge d'un message "voulez vous confirmez l'annulation du rdv"
        retirer le rdv de la base de donnée et actualiser l'affichage des rdv
        */
        $hasKey = $request->session()->has('student');
        if(!$hasKey){
            return redirect()->route('PageAccueil.show');
        }
        return view('mes_rendez_vous_etudiant');
    }

    function showMessageReçu(Request $request)
    {
        $hasKey = $request->session()->has('student');
        if(!$hasKey){
            return redirect()->route('PageAccueil.show');
        }

        $IdEtudiant = $request->session()->get('student')[0];
$CC=$this->repository->msg_count($IdEtudiant);
if($CC==0) return view('message_reçu_etudiant',['CC'=>$CC]);



        $Names=$this->repository->getMessageIdtechNames($IdEtudiant);
        // dd($Names);
              //  dd($Names);
        return view('message_reçu_etudiant',['Names'=>$Names, 'CC'=>$CC]);
    }

    function showMessageReçu_msg(Request $request)
    {
        $hasKey = $request->session()->has('student');
        if(!$hasKey){
            return redirect()->route('PageAccueil.show');
        }
        $IdEtudiant = $request->session()->get('student')[0];
        // dd($IdEtudiant);
        // $Idprof=$request->Id_Enseignant;

        // dd($request->Id_Enseignant);
        $MSG=$this->repository->getMessage2($request->Id_msg);
        $Name=$this->repository->getMessageIdtechName($request->Id_Enseignant);
        // dd($Name);
        return view('message_reçu_etudiant-msg',['Name'=>$Name , 'MSG'=>$MSG]);
    }
//---------------------------------------------------------------------------------------------------------------------------------
    function rendezVousMessageForm(Request $request)
    {
        $hasKey1 = $request->session()->has('teacher');
        if(!$hasKey1){
            return view('page_accueil');
        }

        $Id_Enseignant = $request->session()->get('teacher')[0];

        $CC=$this->repository->msg_rdv2($Id_Enseignant);

        if($CC==0) return view('mes_rendez_vous_enseignant',['CC'=>$CC]);
        $Names=$this->repository->info2($Id_Enseignant);

        return view('mes_rendez_vous_enseignant',['Names'=>$Names, 'CC'=>$CC]);








    }
    function RendezVousMessage(Request $request)
    {
        $hasKey1 = $request->session()->has('teacher');
        //dd($hasKey1);

        if(!$hasKey1){
            return view('page_accueil');
        }
        // $Id_Enseignant = $request->session()->get('teacher')[0];

        // , 'CC'=>$CC
//  dd($request->etuds);
// $request->etuds['NomEtudiant'];
// $request->etuds['PrénomEtudiant'];
// $request->etuds['Niveau'];

        return view('message_enseignant_etudiant',['etuds'=>$request->etuds,'PrénomEtudiant'=>$request->etuds['PrénomEtudiant'],'NomEtudiant'=>$request->etuds['NomEtudiant'],'Niveau_Etude'=>$request->etuds['Niveau_Etude']]);
    }

    function storeRendezVousMessage(Request $request, Repository $repository)
    {
        $hasKey1 = $request->session()->has('teacher');
        //dd($hasKey1);

        if(!$hasKey1){
            return view('page_accueil');
        }

        $IdEnseignant = $request->session()->get('teacher')[0];
        $IdEtudiant = $request->etuds['IdEtudiant'];
        // dd($IdEnseignant);
        //  dd($request->etuds);
        $rules = [
            'msgEnEt' => ['required'],
            ];
            $messages = [
                'msgEnEt.required' => 'Vous devez saisir un message.'
            ];
            $validatedData = $request->validate($rules,$messages);
            $Message = $validatedData['msgEnEt'];
            $this->repository->sendMessage($Message, $IdEtudiant , $IdEnseignant);
            $CC=$this->repository->msg_rdv2($IdEnseignant);

            if($CC==0) return view('mes_rendez_vous_enseignant',['CC'=>$CC]);
            $Names=$this->repository->info2($IdEnseignant);

            return view('mes_rendez_vous_enseignant',['Names'=>$Names, 'CC'=>$CC]);


    }

    function storeannulationRendezVousEnseignant()
    {
        /*
        verifications habituelles + message "etes vous sur de vouloir annuler ce rdv"
        actualisation de la liste des rdv
        */
        return view('mes_rendez_vous_etudiant');
    }

    function RendezVous(Request $request)
    {
        $hasKey1 = $request->session()->has('teacher');
        if(!$hasKey1){
            return view('page_accueil');
        }

        $Id_Enseignant = $request->session()->get('teacher')[0];
        // dd($request->Name['Id_RDV']);
        $Id_RDV=intval($request->Name['Id_RDV']);
        //  dd($request->all());
        $Msg= json_decode(json_encode($this->repository->getmmj($Id_RDV)), true);

        // dd($Msg[0]['Message']);

        $obj= json_decode(json_encode($this->repository->getobj($Id_RDV)), true);
        // dd($obj[0]);
        $doc= json_decode(json_encode($this->repository->getdoc($Id_RDV)), true);
        // dd($doc[0]['nomFichierHache']);
            $etudiant=["NomEtudiant" => "SIYOUCEF","PrénomEtudiant" => "Walid","Niveau_Etude" => "Troisième_année"];
        return view('rendez_vous',['etudiant'=>$etudiant,'obj'=>$obj ,'Msg'=>$Msg ,'nomFichierHache'=>$doc[0]['nomFichierHache']] );
    }
//-----------------------------------------------------------------------------------------------------------------------------------------------

    function showdisponibilites(Request $request)
    {


        $hasKey1 = $request->session()->has('teacher');
        //dd($hasKey1);

        if(!$hasKey1){
            return view('page_accueil');
        }

        $email = $request->session()->get('teacher')[1];
        $tab = ['10:00','10:30','11:00','11:30','12:00','12:30','14:00','14:30','15:00'];

        $tabEns = $this->repository->tableEnseignant($email);
        //dd($tabEns);
        $nomPrenom = $tabEns[0][ "NomEnseignant"]."  ".$tabEns[0][ "PrénomEnseignant"];
        $nomPrenom = strtoupper($nomPrenom);
        $matiere = $tabEns[0]["Matière"];
        $matiere = strtoupper($matiere);


        $tableDispoEnseignant = $this->repository->tabDispoEnseignant($email);
        $tableDispoEnseignant = json_decode(json_encode($tableDispoEnseignant), true);
        for($i=0; $i<9 ;$i++)
        {
            $tabDispoLundi[$i] = $tableDispoEnseignant[$i];
            $tabDispoLundi[$i]['H'] = $tab[$i];
        }
        for($i=9; $i<18 ;$i++)
        {
            $tabDispoMardi[$i] = $tableDispoEnseignant[$i];
            $tabDispoMardi[$i]['H'] = $tab[$i-9];
        }
        for($i=18; $i<27 ;$i++)
        {
            $tabDispoMercredi[$i] = $tableDispoEnseignant[$i];
            $tabDispoMercredi[$i]['H'] = $tab[$i-18];
        }
        for($i=27; $i<36 ;$i++)
        {
            $tabDispoJeudi[$i] = $tableDispoEnseignant[$i];
            $tabDispoJeudi[$i]['H'] = $tab[$i-27];
        }
        for($i=36; $i<45 ;$i++)
        {
            $tabDispoVendredi[$i] = $tableDispoEnseignant[$i];
            $tabDispoVendredi[$i]['H'] = $tab[$i-36];
        }


        return view('disponibilites_enseignant',[
                                                    'tabDispoLundi'=>$tabDispoLundi,
                                                    'tabDispoMardi'=>$tabDispoMardi,
                                                    'tabDispoMercredi'=>$tabDispoMercredi,
                                                    'tabDispoJeudi'=>$tabDispoJeudi,
                                                    'tabDispoVendredi'=>$tabDispoVendredi,
                                                    'nomPrenom'=>$nomPrenom,
                                                    'matiere'=> $matiere
                                                ]);
    }

    function storeDisponibilites(Request $request)
    {
        $hasKey1 = $request->session()->has('teacher');
        //dd($hasKey1);

        if(!$hasKey1){
            return view('page_accueil');
        }

        $email = $request->session()->get('teacher')[1];
        //dd($email);
        $tabCoché = $request->input('choix');
        //dd( $tabCoché);
        //$taille = count($tabCoché);
        $this->repository->modificationDispo($email, $tabCoché);

        return redirect()->back()->with('message', 'Disponibilitées mises a jour');
    }
//---------------------------------------------------------------------BARRE DE RECHERCHE-------------------------------------------------------

function showSearchBarre(Request $request)
{
    $hasKey = $request->session()->has('student');
    if(!$hasKey){
        return redirect()->route('PageAccueil.show');
    }
    $IdEtudiant = $request->session()->get('student')[0];


$q = request()->input('q');
// dd($q);
if(empty($q)){
    return redirect()->route('MesRendezVousEtudiant');
}
$profss= $this->repository->searchProf($q);

$msg_count=$this->repository->msg_count($IdEtudiant);

$CC=$this->repository->msg_rdv($IdEtudiant);

if($CC==0) return view('mes_rendez_vous_etudiant-research',['profss' => $profss,'msg_count' => $msg_count ,'CC'=>$CC]);

$Names=$this->repository->info($IdEtudiant);


return view('mes_rendez_vous_etudiant-research', ['profss' => $profss, 'msg_count' => $msg_count,'Names' => $Names ,'CC'=>$CC]);
}

function showSearchBarre2(Request $request)
{
    $hasKey1 = $request->session()->has('teacher');
    if(!$hasKey1){
        return view('page_accueil');
    }

    $Id_Enseignant = $request->session()->get('teacher')[0];
$q = request()->input('q');
if(empty($q)){
    return redirect()->route('rendezVousMessage');
}
$etudss= $this->repository->searchEtud($q);
$CC=$this->repository->msg_rdv2($Id_Enseignant);

if($CC==0) return view('mes_rendez_vous_enseignant-research',['etudss' => $etudss ,'CC'=>$CC]);


$Names=$this->repository->info2($Id_Enseignant);

return view('mes_rendez_vous_enseignant-research', ['etudss' => $etudss ,'Names'=>$Names ,'CC'=>$CC]);
}

}
