@extends('base')
@section('title')
Messages reçus
@endsection
@section('assets')
    <link rel="stylesheet" href="/css/style.css">
@endsection
@section('content')
<div class="big-box">
    <h2> Messages reçus</h2>


    <div class="bar_iden">
        <div>
            <ul>
                <li>

                    <img class="photo_profil3" src="/icon/profil1.jpeg" alt="">
                    <div class="prof">
                        <span class="nom_prof">WALID SIYOUCEF</span>
                        <span class="matière">MATHS</span>
                    </div>
                </li>
                <li>

                    <img class="photo_profil3" src="/icon/profil1.jpeg" alt="">
                    <div class="prof">
                        <span class="nom_prof">WALID SIYOUCEF</span>
                        <span class="matière">MATHS</span>
                    </div>
                </li>
                <li>

                    <img class="photo_profil3" src="/icon/profil1.jpeg" alt="">
                    <div class="prof">
                        <span class="nom_prof">WALID SIYOUCEF</span>
                        <span class="matière">MATHS</span>
                    </div>
                </li>



            </ul>
        </div>
    </div>
    <form action="">
        <br><br><br><br>
        <h3> Message reçu :</h3><br>

        <div class="Message">
            <p></p>
        </div>
    </form>





    <br>


</div>

@endsection
