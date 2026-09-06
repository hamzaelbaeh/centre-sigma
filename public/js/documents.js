
document.querySelectorAll('.doc-link').forEach(a => {
  a.addEventListener('click', e => {
    e.preventDefault();
    const sid = document.getElementById('docStudent').value;
    if (!sid) { alert('Choisir un élève'); return; }
    window.open('/documents/print/' + a.dataset.type + '?student_id=' + sid, '_blank');
  });
});
document.querySelectorAll('.class-link').forEach(a => {
  a.addEventListener('click', e => {
    e.preventDefault();
    const cid = document.getElementById('docClass').value;
    if (!cid) { alert('Choisir une classe'); return; }
    window.open('/documents/print/' + a.dataset.type + '?class_id=' + cid, '_blank');
  });
});
