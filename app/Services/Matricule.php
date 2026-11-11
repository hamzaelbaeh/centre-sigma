<?php
namespace App\Services;

use App\Models\ParentGuardian;
use App\Models\Student;

/**
 * Student matricule: <level letter><3-digit per-level counter>[-<parent number>]
 * P = primaire, C = collège, L = lycée (tronc commun / 1ère bac / 2ème bac), G = non scolarisé.
 * Example: P001-012
 */
class Matricule
{
    public const PATTERN = '/^([PCLG])(\d{3,})(?:-(\d+))?$/';

    public static function letter(?string $niveau): string
    {
        $niveau = (string) $niveau;
        if (str_contains($niveau, 'primaire')) return 'P';
        if (str_contains($niveau, 'collège')) return 'C';
        if (in_array($niveau, ['Tronc commun', '1ère bac', '2ème bac'], true)) return 'L';
        return 'G'; // Non scolarisé (and students without a level)
    }

    public static function parse(?string $matricule): ?array
    {
        if ($matricule && preg_match(self::PATTERN, $matricule, $m)) {
            return ['letter' => $m[1], 'counter' => (int) $m[2], 'suffix' => $m[3] ?? null];
        }
        return null;
    }

    public static function format(string $letter, int $counter): string
    {
        return $letter.str_pad((string) $counter, 3, '0', STR_PAD_LEFT);
    }

    /** Next free base matricule (without parent suffix) for a level: max existing counter for the letter + 1. */
    public static function next(?string $niveau): string
    {
        $letter = self::letter($niveau);
        $max = 0;
        foreach (Student::pluck('matricule') as $m) {
            $p = self::parse($m);
            if ($p && $p['letter'] === $letter) $max = max($max, $p['counter']);
        }
        return self::format($letter, $max + 1);
    }

    public static function parentNumero(int|string|null $numero): string
    {
        return $numero ? str_pad((string) $numero, 3, '0', STR_PAD_LEFT) : '';
    }

    /** Add / update / remove the "-<parent number>" suffix from the first linked parent. */
    public static function refresh(Student $student): void
    {
        $p = self::parse($student->matricule);
        if (!$p) return; // custom / legacy matricule: leave untouched
        $first = $student->parents()->orderBy('parent_student.id')->first();
        $new = self::format($p['letter'], $p['counter']);
        if ($first && $first->numero) $new .= '-'.self::parentNumero($first->numero);
        if ($new !== $student->matricule) $student->forceFill(['matricule' => $new])->save();
    }

    public static function refreshMany(iterable $studentIds): void
    {
        foreach (Student::whereIn('id', collect($studentIds)->unique()->all())->get() as $s) self::refresh($s);
    }

    /** Digits-only phone, 212… / 00212… normalised to 0… */
    public static function phoneKey(?string $phone): string
    {
        $d = preg_replace('/\D+/', '', latin_digits((string) $phone));
        if (str_starts_with($d, '00212')) $d = '0'.substr($d, 5);
        elseif (str_starts_with($d, '212')) $d = '0'.substr($d, 3);
        return $d;
    }

    public static function findParentByPhone(?string $phone, ?int $exceptId = null): ?ParentGuardian
    {
        $key = self::phoneKey($phone);
        if ($key === '') return null;
        return ParentGuardian::whereNotNull('telephone')
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->get()->first(fn ($p) => self::phoneKey($p->telephone) === $key);
    }

    /** One-off: parent numbers in id order, students renumbered per level in id order. Returns counts. */
    public static function renumberAll(): array
    {
        $n = 0;
        foreach (ParentGuardian::orderBy('id')->get() as $p) {
            $p->numero = ++$n;
            $p->saveQuietly();
        }
        $perLetter = ['P' => 0, 'C' => 0, 'L' => 0, 'G' => 0];
        foreach (Student::orderBy('id')->get() as $s) {
            $l = self::letter($s->niveau_scolaire);
            $s->matricule = self::format($l, ++$perLetter[$l]);
            $s->saveQuietly();
            self::refresh($s);
        }
        return ['parents' => $n, 'students' => $perLetter];
    }
}
