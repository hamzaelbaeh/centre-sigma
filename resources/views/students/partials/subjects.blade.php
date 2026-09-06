@php
    $selectedIds = collect(old('subjects', isset($student) ? $student->subjects->pluck('id')->all() : []))->map(fn($id)=>(int)$id);
    $oldPrix = old('subject_prix', []);
@endphp
<div class="form-group" id="student-subjects-section">
  <label>{{ __('Matières') }}</label>
  <div class="sub" style="margin-bottom:8px">{{ __('Cochez les matières que l\'élève étudie et indiquez le prix facturé (prérempli avec le prix suggéré de la matière).') }}</div>
  <div class="checkboxes" style="display:flex;flex-direction:column;gap:8px">
    @forelse($subjects as $sub)
      @php
        $checked = $selectedIds->contains((int)$sub->id);
        $pivotPrix = isset($student) ? optional($student->subjects->firstWhere('id', $sub->id))->pivot->prix : null;
        $defaultPrix = $oldPrix[$sub->id] ?? $oldPrix[(string)$sub->id] ?? ($pivotPrix !== null && $pivotPrix !== '' ? $pivotPrix : $sub->prix);
      @endphp
      <div class="subject-enroll-row" style="display:flex;flex-wrap:wrap;align-items:center;gap:10px;padding:6px 0;border-bottom:1px solid #eee">
        <label style="display:flex;align-items:center;gap:8px;min-width:220px;margin:0">
          <input type="checkbox" class="subject-check" name="subjects[]" value="{{ $sub->id }}" data-default-prix="{{ $sub->prix }}" @checked($checked)>
          <span>{{ $sub->nom }} <span class="muted">({{ number_format((float)$sub->prix, 2, ',', ' ') }} DH)</span></span>
        </label>
        <label style="display:flex;align-items:center;gap:6px;margin:0">
          <span class="muted">{{ __('Prix facturé') }}</span>
          <input class="form-input subject-prix" style="width:110px" type="number" step="0.01" min="0"
                 name="subject_prix[{{ $sub->id }}]" value="{{ $defaultPrix }}"
                 data-subject-id="{{ $sub->id }}" @disabled(!$checked)>
          <span class="muted">DH</span>
        </label>
      </div>
    @empty
      <div class="muted">{{ __('Aucune matière') }}</div>
    @endforelse
  </div>
  <div style="margin-top:10px;font-weight:600">{{ __('Total mensuel estimé') }} : <span id="subjects-total">0,00</span> DH</div>
</div>
<script>
(function(){
  const section = document.getElementById('student-subjects-section');
  if (!section) return;
  const totalEl = document.getElementById('subjects-total');
  function fmt(n){
    return n.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
  }
  function recalc(){
    let sum = 0;
    section.querySelectorAll('.subject-enroll-row').forEach(row => {
      const check = row.querySelector('.subject-check');
      const prixInput = row.querySelector('.subject-prix');
      if (!check || !prixInput) return;
      prixInput.disabled = !check.checked;
      if (check.checked) {
        const v = parseFloat(prixInput.value);
        if (!isNaN(v)) sum += v;
      }
    });
    if (totalEl) totalEl.textContent = fmt(sum);
    const payeHint = document.getElementById('payment-total-hint');
    if (payeHint) payeHint.textContent = fmt(sum);
    const payeInput = document.getElementById('payment_paye');
    const statut = document.getElementById('payment_statut');
    if (payeInput && statut && statut.value === 'Soldé') {
      payeInput.value = sum.toFixed(2);
    }
  }
  section.addEventListener('change', function(e){
    if (e.target.classList.contains('subject-check')) {
      const row = e.target.closest('.subject-enroll-row');
      const prixInput = row && row.querySelector('.subject-prix');
      if (prixInput && e.target.checked && (prixInput.value === '' || prixInput.disabled)) {
        prixInput.value = e.target.dataset.defaultPrix || '0';
      }
    }
    recalc();
  });
  section.addEventListener('input', function(e){
    if (e.target.classList.contains('subject-prix')) recalc();
  });
  recalc();
})();
</script>
