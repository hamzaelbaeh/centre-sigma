<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['date','categorie','fournisseur','description','montant','mode','reference'];
    protected function casts(): array
    {
        return ['date'=>'date','montant'=>'decimal:2'];
    }
}
