<div id="children-modal" class="cm-backdrop" hidden>
  <div class="cm-box card">
    <div class="cm-head"><h3 id="cm-title" style="margin:0"></h3><button type="button" class="btn btn-sm btn-ghost" id="cm-close">{{ __('Fermer') }}</button></div>
    <div class="table-wrap" style="margin-top:10px"><table class="data">
      <thead><tr><th>{{ __('ÉLÈVE') }}</th><th>{{ __('MATRICULE') }}</th><th>{{ __('Niveau scolaire') }}</th><th>{{ __('CLASSE') }}</th><th>{{ __('STATUT') }}</th></tr></thead>
      <tbody id="cm-body"></tbody>
    </table></div>
  </div>
</div>
<style>
.cm-backdrop{position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:200;display:flex;align-items:center;justify-content:center;padding:16px}
.cm-backdrop[hidden]{display:none}
.cm-box{max-width:760px;width:100%;max-height:85vh;overflow:auto}
.cm-head{display:flex;justify-content:space-between;align-items:center;gap:10px}
a.js-children{font-weight:700;text-decoration:underline;cursor:pointer}
</style>
<script>
(function(){
  const modal = document.getElementById('children-modal');
  const body = document.getElementById('cm-body');
  const title = document.getElementById('cm-title');
  const close = () => { modal.hidden = true; };
  document.getElementById('cm-close').addEventListener('click', close);
  modal.addEventListener('click', e => { if (e.target === modal) close(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
  const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  document.addEventListener('click', function(e){
    const a = e.target.closest('.js-children');
    if (!a) return;
    e.preventDefault();
    fetch(a.dataset.url, {headers:{'Accept':'application/json'}}).then(r => r.json()).then(d => {
      title.innerHTML = '<span dir="ltr">' + esc(d.parent.display_id) + '</span> — ' + esc(d.parent.nom);
      body.innerHTML = d.students.length ? d.students.map(s =>
        '<tr><td><a href="' + esc(s.url) + '"><strong>' + esc(s.nom) + '</strong></a></td><td><span dir="ltr">' + esc(s.matricule) + '</span></td><td>' + esc(s.niveau) + '</td><td>' + esc(s.classe) + '</td><td>' + esc(s.statut) + '</td></tr>'
      ).join('') : '<tr><td colspan="5" class="muted">—</td></tr>';
      modal.hidden = false;
    });
  });
})();
</script>
