<?php

namespace Database\Seeders;



use App\Repositories\Repository;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();



        touch('database/database.sqlite');
        $repository = new Repository();
        $repository->createDatabase();
        $repository->insertEtudiant(
                                    ['NomEtudiant' => 'BELKHOUS',
                                    'PrénomEtudiant' => 'Lyes',
                                    'Date_Naissance' => '1989-01-18',
                                    'Email_Etudiant' => 'lyes@hotmail.com',
                                    'Niveau_Etude' => 'Troisième_année',
                                    'NumTelephone'=> '06 14 62 33 44',
                                    'NomImage'=> 'imagesDefault.png']

                                    );

                                    $repository->insertEtudiant(
                                        ['NomEtudiant' => 'SIYOUCEF',
                                        'PrénomEtudiant' => 'Walid',
                                        'Date_Naissance' => '1992-01-18',
                                        'Email_Etudiant' => 'walid@hotmail.com',
                                        'Niveau_Etude' => 'Troisième_année',
                                        'NumTelephone'=> '06 20 20 20',
                                        'NomImage'=> 'imagesDefault.png']

                                        );
        $repository->addUser('walid@hotmail.com','se');
        $repository->addUser('lyes@hotmail.com','se');
        $repository->addTeacher('nabil@hotmail.com', 'secret');
        $repository->addTeacher('aissa@hotmail.com', 'secret');


    }
}
