(function () {
    const list = document.getElementById('materiaux-list');
    const addBtn = document.getElementById('add-materiau-btn');

    if (!list || !addBtn) {
        return;
    }

    const prototype = list.dataset.prototype;
    if (!prototype) {
        return;
    }

    let index = parseInt(list.dataset.index || '0', 10);

    addBtn.addEventListener('click', () => {
        const newForm = prototype.replace(/__name__/g, index);

        const div = document.createElement('div');
        div.className = 'materiau-item';
        div.innerHTML = newForm;

        list.appendChild(div);
        index += 1;
        list.dataset.index = String(index);
    });
})();
