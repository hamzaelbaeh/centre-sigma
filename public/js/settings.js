
const side = document.getElementById('previewSidebar');
const gold = document.getElementById('c_gold');
const cside = document.getElementById('c_side');
const prevActive = document.getElementById('prevActive');
const prevNom = document.getElementById('prevNom');
const prevSous = document.getElementById('prevSous');
const prevLogo = document.getElementById('prevLogo');
gold.addEventListener('input', () => { prevActive.style.background = gold.value; prevLogo.style.background = gold.value; });
cside.addEventListener('input', () => { side.style.background = cside.value; });
document.getElementById('nom_etab').addEventListener('input', e => prevNom.textContent = e.target.value);
document.getElementById('sous_titre').addEventListener('input', e => prevSous.textContent = e.target.value);
