<?php

namespace Database\Seeders;

use App\Models\Fee;
use App\Models\ParentGuardian;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Setting;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimetableSlot;
use App\Models\Training;
use App\Models\User;
use App\Models\Expense;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Setting::create([
            'nom_etablissement' => 'NOOR ACADEMY',
            'sous_titre' => 'SOUTIEN SCOLAIRE',
            'adresse' => 'Casablanca, Maroc',
            'telephone' => '0522000000',
            'email' => 'contact@noor-academy.ma',
            'couleur_principale' => '#f7be1d',
            'couleur_sidebar' => '#081f52',
        ]);

        User::create([
            'username' => 'admin',
            'name' => 'Administrateur',
            'email' => 'admin@noor-academy.ma',
            'password' => Hash::make('admin123'),
            'role' => 'Administrateur',
            'is_active' => true,
        ]);

        $year = SchoolYear::create([
            'nom' => '2026/2027',
            'date_debut' => '2026-09-01',
            'date_fin' => '2027-06-30',
            'is_active' => true,
        ]);

        $teachers = [];
        foreach ([
            ['Alaoui','Fatima','Mathématiques','Mensuel',4500],
            ['Benjelloun','Karim','Français','Mensuel',4200],
            ['Idrissi','Sara','Physique','Horaire',120],
            ['Tazi','Youssef','Anglais','Mensuel',4000],
        ] as $i => [$nom,$prenom,$spec,$mode,$val]) {
            $teachers[] = Teacher::create([
                'matricule' => 'ENS'.str_pad((string)($i+1),4,'0',STR_PAD_LEFT),
                'nom'=>$nom,'prenom'=>$prenom,'specialite'=>$spec,
                'telephone'=>'06'.rand(10000000,99999999),
                'email'=>strtolower($prenom).'@noor.ma',
                'date_embauche'=>'2024-09-01','statut'=>'Actif',
                'mode_paiement'=>$mode,'valeur_dh'=>$val,
            ]);
        }

        $classesData = [
            ['1ère primaire','Primaire','Salle A',0,30],
            ['2ème & 3ème primaire','Primaire','Salle B',1,30],
            ['collège','Collège','Salle C',2,35],
            ['Soutien lycée','Lycée','Salle D',3,25],
        ];
        $classes = [];
        foreach ($classesData as $i => [$nom,$niv,$salle,$ti,$cap]) {
            $classes[] = SchoolClass::create([
                'nom'=>$nom,'niveau'=>$niv,'salle'=>$salle,
                'teacher_id'=>$teachers[$ti]->id,'capacite'=>$cap,'school_year_id'=>$year->id,
            ]);
        }

        $subjects = [];
        foreach ([
            ['MATH','Mathématiques','Primaire',4,400],
            ['FR','Français','Primaire',4,350],
            ['AR','Arabe','Primaire',3,300],
            ['PHY','Physique','Collège',3,400],
            ['ANG','Anglais','Collège',3,350],
            ['SVT','SVT','Lycée',2,300],
        ] as [$code,$nom,$niv,$h,$prix]) {
            $subjects[] = Subject::create(['code'=>$code,'nom'=>$nom,'niveau'=>$niv,'heures_semaine'=>$h,'prix'=>$prix]);
        }
        foreach ($subjects as $i => $sub) {
            $sub->teachers()->attach($teachers[$i % 4]->id);
            $sub->classes()->attach($classes[$i % 4]->id);
        }

        $parents = [];
        foreach ([
            ['Chraibi','Hassan','Ingénieur','0611111111'],
            ['Berrada','Nadia','Médecin','0622222222'],
            ['Amrani','Omar','Commerçant','0633333333'],
        ] as [$nom,$prenom,$prof,$tel]) {
            $parents[] = ParentGuardian::create([
                'nom'=>$nom,'prenom'=>$prenom,'profession'=>$prof,'telephone'=>$tel,
                'email'=>strtolower($nom).'@mail.ma','adresse'=>'Casablanca',
            ]);
        }

        $firstNames = ['Lina','Aya','Yassine','Sara','Adam','Ines','Mehdi','Nour','Rania','Omar','Hiba','Anas','Salma','Yassine','Imane','Zakaria','Maryam','Rayan','Amina','Karim','Sofia','Amine','Leila'];
        $lastNames = ['Chraibi','Berrada','Amrani','Alaoui','Tazi','Idrissi','Fassi','Mansouri','Benali','Zahra'];
        $students = [];
        for ($i = 0; $i < 22; $i++) {
            $class = $classes[$i % 4];
            $st = Student::create([
                'matricule' => 'EL'.str_pad((string)($i+1),5,'0',STR_PAD_LEFT),
                'nom' => $lastNames[$i % count($lastNames)],
                'prenom' => $firstNames[$i % count($firstNames)],
                'date_naissance' => sprintf('20%02d-%02d-%02d', 10+($i%8), 1+($i%12), 1+($i%28)),
                'lieu_naissance' => 'Casablanca',
                'sexe' => $i % 2 ? 'F' : 'M',
                'telephone' => '06'.rand(10000000,99999999),
                'class_id' => $class->id,
                'date_inscription' => '2026-09-01',
                'statut' => 'Actif',
                'school_year_id' => $year->id,
            ]);
            $students[] = $st;
            $parents[$i % 3]->students()->attach($st->id);
        }

        // Fees per class
        $feeMap = [
            0 => ['inscription'=>1000,'mensualite'=>0,'transport'=>300,'formation'=>200],
            1 => ['inscription'=>1000,'mensualite'=>0,'transport'=>300],
            2 => ['inscription'=>1000,'mensualite'=>0,'transport'=>300],
            3 => ['inscription'=>800,'mensualite'=>0,'transport'=>0],
        ];
        foreach ($classes as $i => $c) {
            $vals = array_merge(['inscription'=>0,'mensualite'=>0,'transport'=>0,'cantine'=>0,'activites'=>0,'formation'=>0,'autres'=>0], $feeMap[$i]);
            Fee::create(array_merge($vals, ['class_id'=>$c->id,'school_year_id'=>$year->id]));
        }

        $periode = '2026-09';
        foreach ($students as $i => $st) {
            $fee = Fee::where('class_id', $st->class_id)->first();
            $st->loadMissing('schoolClass.subjects');
            $montant = (float) ($st->schoolClass?->subjects?->sum('prix') ?? 0);
            if ($montant <= 0) $montant = 400;
            $paye = $i < 5 ? $montant : ($i < 10 ? round($montant/2,2) : 0);
            $pay = Payment::create([
                'student_id'=>$st->id,'type'=>'Mensualité','montant'=>$montant,'paye'=>$paye,
                'periode'=>$periode,'statut'=>$paye<=0?'Non payé':($paye>=$montant?'Soldé':'Partiel'),
                'school_year_id'=>$year->id,
            ]);
            if ($paye > 0) {
                PaymentTransaction::create([
                    'payment_id'=>$pay->id,'date'=>'2026-09-05','montant'=>$paye,'mode'=>'Espèces',
                ]);
            }
            if ($i < 8) {
                Payment::create([
                    'student_id'=>$st->id,'type'=>'Inscription','montant'=>$fee->inscription ?? 1000,
                    'paye'=>$fee->inscription ?? 1000,'periode'=>$periode,'statut'=>'Soldé','school_year_id'=>$year->id,
                ]);
            }
        }

        foreach ([
            ['Directeur','Alaoui','Rachid',8000],
            ['Secrétaire','Bennani','Salma',4500],
            ['Comptable','Kettani','Hicham',5500],
            ['Surveillant','Naciri','Said',3500],
        ] as $i => [$poste,$nom,$prenom,$sal]) {
            Staff::create([
                'matricule'=>'EMP'.str_pad((string)($i+1),4,'0',STR_PAD_LEFT),
                'nom'=>$nom,'prenom'=>$prenom,'poste'=>$poste,'salaire'=>$sal,
                'telephone'=>'06'.rand(10000000,99999999),'date_embauche'=>'2023-01-15',
                'mode_paiement'=>'Virement','statut'=>'Actif',
            ]);
        }

        Expense::create(['date'=>'2026-09-02','categorie'=>'Loyer','fournisseur'=>'Agence Immo','description'=>'Loyer septembre','montant'=>2500,'mode'=>'Virement']);
        Expense::create(['date'=>'2026-09-03','categorie'=>'Électricité','fournisseur'=>'LYDEC','description'=>'Facture','montant'=>600,'mode'=>'Espèces']);
        Expense::create(['date'=>'2026-08-15','categorie'=>'Fournitures scolaires','fournisseur'=>'Papeterie','description'=>'Cahiers','montant'=>400,'mode'=>'Espèces']);

        TimetableSlot::create(['class_id'=>$classes[0]->id,'subject_id'=>$subjects[0]->id,'teacher_id'=>$teachers[0]->id,'jour'=>'Lundi','debut'=>'13:30','fin'=>'14:30','salle'=>'Salle A']);
        TimetableSlot::create(['class_id'=>$classes[0]->id,'subject_id'=>$subjects[1]->id,'teacher_id'=>$teachers[1]->id,'jour'=>'Mardi','debut'=>'14:30','fin'=>'15:30','salle'=>'Salle A']);
        TimetableSlot::create(['class_id'=>$classes[2]->id,'subject_id'=>$subjects[3]->id,'teacher_id'=>$teachers[2]->id,'jour'=>'Mercredi','debut'=>'15:30','fin'=>'16:30','salle'=>'Salle C']);

        $training = Training::create([
            'nom'=>'Renforcement Maths Collège','formateur'=>'Alaoui Fatima',
            'description'=>'Sessions de soutien','duree'=>'8 semaines',
            'date_debut'=>'2026-10-01','date_fin'=>'2026-11-30','prix'=>800,'salle'=>'Salle C',
            'classe_niveau'=>'Collège','nombre_seances'=>16,
        ]);
        $training->subjects()->attach($subjects[0]->id);
        $training->participants()->create(['student_id'=>$students[0]->id,'montant_paye'=>400,'telephone'=>$students[0]->telephone]);

        $this->command?->info('Centre Sigma demo seed complete. Login: admin / admin123');
    }
}
