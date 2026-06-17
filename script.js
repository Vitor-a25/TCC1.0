
const input  = document.getElementById('searchInput');
const filter = document.getElementById('categoryFilter');
const grid   = document.getElementById('servicesGrid');



function filterCards() {
    const term = input ? input.value.toLowerCase() : '';
    const cat  = filter ? filter.value : 'todos';
    const cards = grid ? grid.querySelectorAll('.service-card') : [];
    let visible = 0;
    cards.forEach(card => {
        const name = card.querySelector('h3')?.textContent.toLowerCase() || '';
        const desc = card.querySelector('p')?.textContent.toLowerCase()  || '';
        const cardCat = card.dataset.cat || '';
        const matchTerm = !term || name.includes(term) || desc.includes(term);
        const matchCat  = cat === 'todos' || cardCat === cat;
        card.style.display = (matchTerm && matchCat) ? '' : 'none';
        if (matchTerm && matchCat) visible++;
    });
    
    let empty = grid ? grid.querySelector('.empty-msg') : null;
    if (!empty && grid) {
        empty = document.createElement('p');
        empty.className = 'empty-msg empty';
        grid.appendChild(empty);
    }
    if (empty) empty.style.display = visible === 0 ? '' : 'none';
}



if (document.getElementById('searchBtn')) {
    document.getElementById('searchBtn').addEventListener('click', filterCards);
}
if (input)  input.addEventListener('input', filterCards);
if (filter) filter.addEventListener('change', filterCards);




const menuBtn = document.getElementById('menuBtn');
if (menuBtn) {
    menuBtn.addEventListener('click', () => document.getElementById('navMenu').classList.toggle('active'));
}