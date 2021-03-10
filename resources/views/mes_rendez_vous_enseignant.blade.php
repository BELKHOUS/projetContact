
@extends('base')
@section('title')
Mes rendez-vous enseignant
@endsection


@section('content')
<div class="big-box2">
    <h2> Rendez-vous</h2>
    <h3>Mes rendez-vous</h3><br>


    <div class="bar_iden">
        <div>
            <ul>
                <li>
                    <div class="RDV">
                        <span class="date">Lundi 15 fevrier 15:30</span>
                        <span class="annuler"><a href="#">Annuler</a></span>
                    </div>
                    <img class="photo_profil3" src="/icon/profil1.jpeg" alt="">
                    <div class="prof">
                        <span class="nom_prof">CAMIILE DINAZ</span>
                        <span class="matière">1ère année</span>
                    </div>
                </li>
                <li>
                    <div class="RDV">
                        <span class="date">Lundi 15 fevrier 15:30</span>
                        <span class="annuler"><a href="#">Annuler</a></span>
                    </div>
                    <img class="photo_profil3" src="/icon/profil1.jpeg" alt="">
                    <div class="prof">
                        <span class="nom_prof">CAMIILE DINAZ</span>
                        <span class="matière">1ère année</span>
                    </div>
                </li>
                <li>
                    <div class="RDV">
                        <span class="date">Lundi 15 fevrier 15:30</span>
                        <span class="annuler"><a href="#">Annuler</a></span>
                    </div>
                    <img class="photo_profil3" src="/icon/profil1.jpeg" alt="">
                    <div class="prof">
                        <span class="nom_prof">CAMIILE DINAZ</span>
                        <span class="matière">1ère année</span>
                    </div>
                </li>



            </ul>
        </div>
    </div>
    <h2></h2>
    <div class="bar_rech">
        <h3>Etudiant</h3><br>
        <div class="search_box">
            <input type="text" placeholder="Trouvez votre etudiant?">
        </div>
        <br>
        <br>
        <br>
        <br>
        <br>
        <div>
            <ul>
                <li>
                    <img class="photo_profil3" src="/icon/profil1.jpeg" alt=""> <span class="nom_prof">CAMIILE DINAZ</span>
                    <span class="matière">1ère année</span>
                    <span class="prendre_rendez_vous"> <a href="#">Envoyer un message</a></span>
                </li>


            </ul>
        </div>
    </div>
    <h2></h2>



    <br>


</div>
@endsection