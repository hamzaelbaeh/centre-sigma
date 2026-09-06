<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('nom_etablissement')->default('NOOR ACADEMY');
            $table->string('sous_titre')->nullable()->default('SOUTIEN SCOLAIRE');
            $table->string('adresse')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->string('logo')->nullable();
            $table->string('couleur_principale')->default('#f7be1d');
            $table->string('couleur_sidebar')->default('#081f52');
            $table->timestamps();
        });

        Schema::create('school_years', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('matricule')->nullable()->unique();
            $table->string('nom');
            $table->string('prenom');
            $table->string('cin')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->text('adresse')->nullable();
            $table->date('date_embauche')->nullable();
            $table->string('specialite')->nullable();
            $table->string('statut')->default('Actif');
            $table->string('mode_paiement')->default('Mensuel');
            $table->decimal('valeur_dh', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('niveau')->nullable();
            $table->string('salle')->nullable();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->unsignedInteger('capacite')->nullable()->default(30);
            $table->foreignId('school_year_id')->nullable()->constrained('school_years')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('code')->nullable();
            $table->string('niveau')->nullable();
            $table->decimal('heures_semaine', 5, 2)->nullable()->default(0);
            $table->timestamps();
        });

        Schema::create('class_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->unique(['class_id', 'subject_id']);
        });

        Schema::create('subject_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->unique(['subject_id', 'teacher_id']);
        });

        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom')->nullable();
            $table->string('cin')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->text('adresse')->nullable();
            $table->string('profession')->nullable();
            $table->timestamps();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('matricule')->nullable()->unique();
            $table->string('nom');
            $table->string('prenom');
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance')->nullable();
            $table->string('sexe', 1)->nullable();
            $table->string('cin')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->text('adresse')->nullable();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->date('date_inscription')->nullable();
            $table->string('statut')->default('Actif');
            $table->string('code_massar')->nullable();
            $table->string('photo')->nullable();
            $table->foreignId('school_year_id')->nullable()->constrained('school_years')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('parent_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('parents')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->unique(['parent_id', 'student_id']);
        });

        Schema::create('timetable_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->string('jour')->nullable();
            $table->time('debut');
            $table->time('fin');
            $table->string('salle')->nullable();
            $table->timestamps();
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->date('date');
            $table->string('status')->default('present'); // present, absent, retard
            $table->boolean('justifie')->default(false);
            $table->string('motif')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'date', 'subject_id'], 'attendance_unique');
        });

        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('school_year_id')->nullable()->constrained('school_years')->nullOnDelete();
            $table->decimal('inscription', 12, 2)->default(0);
            $table->decimal('mensualite', 12, 2)->default(0);
            $table->decimal('transport', 12, 2)->default(0);
            $table->decimal('cantine', 12, 2)->default(0);
            $table->decimal('activites', 12, 2)->default(0);
            $table->decimal('formation', 12, 2)->default(0);
            $table->decimal('autres', 12, 2)->default(0);
            $table->timestamps();
            $table->unique(['class_id', 'school_year_id']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('type'); // Mensualité, Inscription, Transport, etc.
            $table->decimal('montant', 12, 2)->default(0);
            $table->decimal('paye', 12, 2)->default(0);
            $table->string('periode')->nullable(); // YYYY-MM
            $table->string('statut')->default('Non payé'); // Non payé, Partiel, Soldé
            $table->foreignId('school_year_id')->nullable()->constrained('school_years')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('montant', 12, 2);
            $table->string('mode')->nullable();
            $table->string('reference')->nullable();
            $table->timestamps();
        });

        Schema::create('teacher_payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->string('periode'); // YYYY-MM
            $table->string('mode_paiement')->nullable();
            $table->decimal('base', 12, 2)->default(0);
            $table->decimal('taux', 12, 2)->default(0);
            $table->decimal('brut', 12, 2)->default(0);
            $table->decimal('primes', 12, 2)->default(0);
            $table->decimal('avances', 12, 2)->default(0);
            $table->decimal('retenues', 12, 2)->default(0);
            $table->decimal('net', 12, 2)->default(0);
            $table->string('statut')->default('En attente');
            $table->timestamps();
            $table->unique(['teacher_id', 'periode']);
        });

        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('matricule')->nullable()->unique();
            $table->string('nom');
            $table->string('prenom');
            $table->string('cin')->nullable();
            $table->string('telephone')->nullable();
            $table->string('poste')->nullable();
            $table->date('date_embauche')->nullable();
            $table->decimal('salaire', 12, 2)->default(0);
            $table->string('mode_paiement')->nullable();
            $table->string('statut')->default('Actif');
            $table->timestamps();
        });

        Schema::create('staff_payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->string('periode');
            $table->decimal('brut', 12, 2)->default(0);
            $table->decimal('primes', 12, 2)->default(0);
            $table->decimal('avances', 12, 2)->default(0);
            $table->decimal('retenues', 12, 2)->default(0);
            $table->decimal('net', 12, 2)->default(0);
            $table->string('statut')->default('En attente');
            $table->timestamps();
            $table->unique(['staff_id', 'periode']);
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('categorie');
            $table->string('fournisseur')->nullable();
            $table->text('description')->nullable();
            $table->decimal('montant', 12, 2);
            $table->string('mode')->nullable();
            $table->string('reference')->nullable();
            $table->timestamps();
        });

        Schema::create('departures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->string('nom_externe')->nullable();
            $table->date('date_sortie');
            $table->string('statut');
            $table->string('raison')->nullable();
            $table->string('destination')->nullable();
            $table->string('documents_remis')->nullable();
            $table->text('observation')->nullable();
            $table->decimal('solde_du', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('formateur')->nullable();
            $table->text('description')->nullable();
            $table->string('duree')->nullable();
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->decimal('prix', 12, 2)->default(0);
            $table->string('salle')->nullable();
            $table->string('classe_niveau')->nullable();
            $table->unsignedInteger('nombre_seances')->nullable();
            $table->timestamps();
        });

        Schema::create('subject_training', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->constrained('trainings')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->unique(['training_id', 'subject_id']);
        });

        Schema::create('training_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->constrained('trainings')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->string('nom_externe')->nullable();
            $table->string('telephone')->nullable();
            $table->decimal('montant_paye', 12, 2)->default(0);
            $table->string('resultat')->nullable();
            $table->boolean('certificat')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $tables = [
            'training_participants','subject_training','trainings','departures','expenses',
            'staff_payrolls','staff','teacher_payrolls','payment_transactions','payments','fees',
            'attendances','timetable_slots','parent_student','students','parents',
            'subject_teacher','class_subject','subjects','classes','teachers','school_years','settings'
        ];
        foreach ($tables as $t) {
            Schema::dropIfExists($t);
        }
    }
};
