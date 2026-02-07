// dashboard.js - Light Theme Version

let allOrders = [];
let allDrinks = [];
let allTables = [];

const imgUrl = (path) => path ? '../' + path : null;
const FALLBACK_IMG = 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=300&h=200&fit=crop';
let allPhotos = [];
let selectedPhotos = new Set();
let pendingDeleteIds = [];

window.switchTab = (tabId) => {
    document.getElementById('orders').style.display = tabId === 'orders' ? 'block' : 'none';
    document.getElementById('menu').style.display = tabId === 'menu' ? 'block' : 'none';
    document.getElementById('photos').style.display = tabId === 'photos' ? 'block' : 'none';
    document.getElementById('messages').style.display = tabId === 'messages' ? 'block' : 'none';

    if (tabId === 'menu') loadMenu();
    if (tabId === 'photos') loadDashPhotos();
    if (tabId === 'messages') loadMessages();
};

// --- Orders ---
function loadOrders() {
    fetch('../api/orders.php').then(res => res.json()).then(data => { allOrders = data; renderOrders(); });
}

function renderOrders() {
    const list = document.getElementById('ordersList');
    if (allOrders.length === 0) {
        list.innerHTML = '<div class="col-span-full flex flex-col items-center justify-center text-slate-300 py-20"><div class="text-6xl mb-4">☕</div><p class="font-medium">All caught up!</p></div>';
        return;
    }
    list.innerHTML = allOrders.map(o => `
        <div class="bg-white rounded-3xl p-6 border ${o.status === 'ready' ? 'border-green-400 shadow-xl shadow-green-100 ring-2 ring-green-100' : 'border-slate-100 shadow-sm'} relative overflow-hidden transition hover:shadow-md">
            <div class="flex justify-between items-start mb-4">
                <span class="font-bold text-lg text-slate-800 bg-slate-50 px-3 py-1 rounded-lg border border-slate-100">${o.table_name}</span>
                <span class="text-xs font-bold text-slate-400">#${o.id} • ${o.created_at.substring(11, 16)}</span>
            </div>
            
            <div class="mb-5">
                 ${o.summary.split(', ').map(item => `<div class="font-medium text-slate-700 text-lg py-1 border-b border-dashed border-slate-100 last:border-0">${item}</div>`).join('')}
            </div>
            
            <div class="text-sm text-slate-500 mb-6 bg-slate-50 p-3 rounded-2xl border border-slate-100 flex items-center gap-2">
                 <span class="w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center text-xs">🕒</span>
                 Updated: ${o.updated_at ? o.updated_at.substring(11, 16) : 'Just now'}
            </div>
            
            <div class="grid grid-cols-1 gap-2">
                ${o.status === 'pending' ?
            `<button class="bg-amber-400 hover:bg-amber-500 text-amber-950 font-bold py-3 rounded-2xl transition w-full shadow-lg shadow-amber-100" onclick="updateStatus(${o.id}, 'ready')">Mark Ready</button>` :
            `<button class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 rounded-2xl transition w-full shadow-lg shadow-green-200" onclick="updateStatus(${o.id}, 'done')">Complete</button>`
        }
            </div>
        </div>
    `).join('');
}

window.updateStatus = (id, status) => {
    fetch('../api/orders.php', { method: 'PUT', body: JSON.stringify({ id, status }) }).then(() => loadOrders());
};

// --- Messages ---
let allMessages = [];
function loadMessages() {
    fetch('../api/messages.php?t=' + Date.now()).then(res => res.json()).then(data => { allMessages = data; renderMessages(); });
}

function renderMessages() {
    const list = document.getElementById('messagesList');
    if (allMessages.length === 0) {
        list.innerHTML = '<div class="col-span-full py-20 text-center text-slate-400 font-medium">No messages yet.</div>';
        return;
    }
    list.innerHTML = allMessages.map(m => `
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-pink-100 flex flex-col relative overflow-hidden group hover:shadow-md transition">
             <div class="absolute top-0 right-0 p-4 opacity-10 font-black text-6xl text-pink-500 -mt-2 -mr-2">❞</div>
             
             <div class="flex items-center gap-3 mb-4">
                 <div class="w-10 h-10 rounded-full bg-pink-100 flex items-center justify-center text-pink-500 font-bold border border-pink-200">
                    ${m.guest_name.charAt(0).toUpperCase()}
                 </div>
                 <div>
                     <h3 class="font-bold text-slate-800">${m.guest_name}</h3>
                     <p class="text-xs text-slate-400 font-bold uppercase tracking-wide">${m.created_at || 'Just now'}</p>
                 </div>
             </div>
             
             <div class="text-slate-600 font-medium leading-relaxed italic relative z-10">
                 "${m.message}"
             </div>
        </div>
    `).join('');
}

// --- Menu ---
function loadMenu() {
    fetch('../api/drinks.php').then(res => res.json()).then(data => { allDrinks = data; renderMenu(); });
    fetch('../api/tables.php').then(res => res.json()).then(data => { allTables = data; renderTables(); });
}

function renderMenu() {
    const list = document.getElementById('menuList');
    list.innerHTML = allDrinks.map(d => `
        <div class="bg-white p-3 rounded-2xl flex items-center gap-4 group border border-slate-100 shadow-sm hover:shadow-md transition">
            <img src="${d.image_url ? imgUrl(d.image_url) : FALLBACK_IMG}"
                 class="w-14 h-14 rounded-xl object-cover bg-slate-100"
                 onerror="this.src='${FALLBACK_IMG}'">
            <div class="flex-1">
                <div class="font-bold text-slate-700 flex items-center gap-2 text-lg">
                    ${d.name}
                    ${d.is_active == 0 ? '<span class="text-[10px] bg-red-50 text-red-500 px-2 py-0.5 rounded-full font-bold border border-red-100">HIDDEN</span>' : ''}
                </div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wide">${d.category}</div>
            </div>
            <div class="flex items-center gap-2">
               <button onclick="toggleDrink(${d.id}, ${d.is_active == 1 ? 0 : 1})" class="p-2 rounded-xl border font-bold text-xs transition ${d.is_active == 1 ? 'text-green-600 bg-green-50 border-green-100 hover:bg-green-100' : 'text-slate-400 bg-slate-50 border-slate-100 hover:bg-slate-100'}">
                   ${d.is_active == 1 ? 'ON' : 'OFF'}
               </button>
               <button onclick="editDrink(${d.id})" class="w-8 h-8 flex items-center justify-center rounded-xl bg-blue-50 text-blue-500 hover:bg-blue-100 transition">✎</button>
               <button onclick="deleteDrink(${d.id})" class="w-8 h-8 flex items-center justify-center rounded-xl bg-red-50 text-red-500 hover:bg-red-100 transition">×</button>
            </div>
        </div>
    `).join('');
}

window.toggleDrink = (id, status) => {
    fetch('../api/drinks.php', { method: 'PUT', body: JSON.stringify({ id, is_active: status }) }).then(loadMenu);
};

window.deleteDrink = (id) => {
    if (confirm("Delete?")) fetch('../api/drinks.php', { method: 'DELETE', body: JSON.stringify({ id }) }).then(loadMenu);
}

window.editDrink = (id) => {
    const d = allDrinks.find(i => i.id == id);
    document.getElementById('drinkId').value = d.id;
    document.getElementById('drinkName').value = d.name;
    document.getElementById('drinkCat').value = d.category;
    document.getElementById('drinkDesc').value = d.description;

    // Check if image is a URL (starts with http) or local path
    if (d.image_url && d.image_url.startsWith('http')) {
        document.getElementById('drinkImageLink').value = d.image_url;
    } else {
        document.getElementById('drinkImageLink').value = '';
    }

    document.getElementById('formTitle').textContent = "Edit " + d.name;
    document.getElementById('saveDrinkBtn').textContent = "Update Drink";
    document.getElementById('saveDrinkBtn').classList.add('bg-indigo-600');
    window.scrollTo(0, 0);
};

window.resetForm = () => {
    document.getElementById('drinkForm').reset();
    document.getElementById('drinkId').value = '';
    // Also clear hidden/custom inputs if any, though form.reset handles most.
    if (document.getElementById('drinkImageLink')) document.getElementById('drinkImageLink').value = '';

    document.getElementById('formTitle').textContent = "Add New Drink";
    document.getElementById('saveDrinkBtn').textContent = "Save Drink";
    document.getElementById('saveDrinkBtn').classList.remove('bg-indigo-600');
};

document.getElementById('drinkForm').addEventListener('submit', function (e) {
    e.preventDefault();
    fetch('../api/drinks.php', { method: 'POST', body: new FormData(this) }).then(() => {
        resetForm(); loadMenu();
    });
});

// Tables
function renderTables() {
    document.getElementById('tablesList').innerHTML = allTables.map(t => `
        <div class="bg-white border border-slate-200 text-slate-600 text-sm pl-4 pr-1 py-1 rounded-full flex items-center gap-3 font-bold shadow-sm hover:shadow transition group">
            <span class="cursor-pointer" onclick="openEditTable(${t.id}, '${t.name}')">${t.name}</span>
            <span onclick="deleteTable(${t.id})" class="w-6 h-6 rounded-full bg-slate-50 text-slate-400 hover:bg-red-50 hover:text-red-500 flex items-center justify-center cursor-pointer transition">×</span>
        </div>
    `).join('');
}

window.addTable = () => {
    const name = document.getElementById('newTableName').value;
    if (name) fetch('../api/tables.php', { method: 'POST', body: JSON.stringify({ name }) }).then(loadMenu);
    document.getElementById('newTableName').value = '';
};

window.deleteTable = (id) => {
    if (confirm("Delete Table?")) fetch('../api/tables.php', { method: 'DELETE', body: JSON.stringify({ id }) }).then(loadMenu);
}

window.openEditTable = (id, name) => {
    document.getElementById('editTableOverlay').style.display = 'flex';
    document.getElementById('editTableId').value = id;
    document.getElementById('editTableName').value = name;
}

window.updateTable = () => {
    const id = document.getElementById('editTableId').value;
    const name = document.getElementById('editTableName').value;
    fetch('../api/tables.php', { method: 'PUT', body: JSON.stringify({ id, name }) }).then(() => {
        document.getElementById('editTableOverlay').style.display = 'none';
        loadMenu();
    });
}

// --- Photos ---
function loadDashPhotos() {
    fetch('../api/photos.php').then(res => res.json()).then(photos => {
        allPhotos = photos.map(p => ({ ...p, id: Number(p.id) })); // Ensure ID is Number

        // Filter selection against current
        selectedPhotos = new Set([...selectedPhotos].filter(id => allPhotos.find(p => p.id === id)));
        updatePhotoSelectionUI();

        const grid = document.getElementById('dashPhotosGrid');
        if (allPhotos.length === 0) {
            grid.innerHTML = '<p class="col-span-full text-center text-slate-400">No photos uploaded yet.</p>';
            return;
        }

        grid.innerHTML = allPhotos.map(p => {
            const isSel = selectedPhotos.has(p.id);
            return `
            <div class="relative group rounded-xl overflow-hidden bg-white shadow-sm border ${isSel ? 'border-blue-500 ring-2 ring-blue-500' : 'border-slate-100'} cursor-pointer" onclick="togglePhotoSelect(${p.id})">
                <img src="${imgUrl(p.filename)}" class="w-full h-40 object-cover">
                
                <div class="absolute top-2 left-2">
                    <div class="w-6 h-6 rounded-full border-2 ${isSel ? 'bg-blue-500 border-blue-500' : 'bg-black/30 border-white'} flex items-center justify-center transition">
                        ${isSel ? '<span class="text-white text-xs">✓</span>' : ''}
                    </div>
                </div>
                
                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2">
                    <!-- Overlay actions -->
                </div>
            </div>
        `}).join('');
    });
}

window.togglePhotoSelect = (idStr) => {
    const id = Number(idStr); // Ensure Number
    if (selectedPhotos.has(id)) selectedPhotos.delete(id);
    else selectedPhotos.add(id);
    loadDashPhotos();
};

window.selectAllPhotos = () => {
    if (selectedPhotos.size === allPhotos.length) {
        selectedPhotos.clear();
    } else {
        selectedPhotos.clear(); // Reset to ensure no dupes
        allPhotos.forEach(p => selectedPhotos.add(p.id));
    }
    loadDashPhotos();
};

function updatePhotoSelectionUI() {
    document.getElementById('selCountDown').textContent = selectedPhotos.size;
    document.getElementById('selCountDel').textContent = selectedPhotos.size;
}

window.downloadSelected = () => {
    if (selectedPhotos.size === 0) return alert("Select photos first.");

    // We can't really zip client side easily without a lib.
    // We'll just trigger multiple downloads or one by one.
    // Browsers might block multiple.
    // For now, let's just loop.

    selectedPhotos.forEach(id => {
        const p = allPhotos.find(i => i.id == id);
        if (p) {
            const a = document.createElement('a');
            a.href = imgUrl(p.filename);
            a.download = p.filename.split('/').pop();
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }
    });
};

window.deleteSelected = () => {
    if (selectedPhotos.size === 0) return alert("Select photos first.");
    pendingDeleteIds = [...selectedPhotos];

    // Show PIN Modal
    document.getElementById('pinModal').classList.remove('hidden');
    document.getElementById('pinModal').classList.add('flex');
    document.getElementById('pinInput').value = '';
    document.getElementById('pinInput').focus();
};

window.closePinModal = () => {
    document.getElementById('pinModal').classList.add('hidden');
    document.getElementById('pinModal').classList.remove('flex');
    pendingDeleteIds = [];
};

window.confirmPin = () => {
    const pin = document.getElementById('pinInput').value;
    if (pin === '1234') { // HARDCODED for now
        closePinModal();
        performDelete();
    } else {
        alert("Incorrect PIN");
    }
};

async function performDelete() {
    for (const id of pendingDeleteIds) {
        await fetch('../api/photos.php', {
            method: 'DELETE',
            body: JSON.stringify({ id })
        });
    }
    selectedPhotos.clear();
    loadDashPhotos();
}

setInterval(() => {
    if (document.getElementById('orders').style.display !== 'none') loadOrders();
    if (document.getElementById('messages').style.display !== 'none') loadMessages();
}, 5000);

loadOrders();
