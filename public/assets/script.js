// script.js

document.addEventListener('DOMContentLoaded', () => {
    // State
    let allDrinks = [];
    let cart = [];
    let activeCategory = 'All';

    // Elements
    const drinksGrid = document.getElementById('drinksGrid');
    const searchInput = document.getElementById('searchInput');
    const modal = document.getElementById('orderModal');
    const checkoutModal = document.getElementById('checkoutModal');

    const modalImg = document.getElementById('modalImg');
    const modalTitle = document.getElementById('modalTitle');
    // const modalDesc = document.getElementById('modalDesc'); // Removed
    const modalCat = document.getElementById('modalCat');
    const modalQtyVal = document.getElementById('modalQtyVal');
    const addToCartBtn = document.getElementById('addToCartBtn');

    const cartItemsList = document.getElementById('cartItemsList');
    const tableSelect = document.getElementById('tableSelect');
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    const openCartBtn = document.getElementById('openCartBtn');
    const cartCountBadge = document.getElementById('cartCountBadge');

    const statusBar = document.getElementById('orderStatus');
    const statusMessage = document.getElementById('statusMessage');
    const statusBadge = document.getElementById('statusBadge');

    // Photo Preview Elements
    const photoPreviewModal = document.getElementById('photoPreviewModal');
    const previewImage = document.getElementById('previewImage');

    let selectedDrink = null;
    let selectedQty = 1;

    // --- Init ---
    renderSkeletons(); // Show skeletons immediately
    fetchDrinks();
    fetchTables();
    checkActiveOrder();
    setInterval(checkActiveOrder, 5000);

    // --- Listeners ---
    if (searchInput) searchInput.addEventListener('input', (e) => filterDrinks(e.target.value));

    // CLICK OUTSIDE TO CLOSE
    window.addEventListener('click', (e) => {
        if (e.target === modal) closeModal(modal);
        if (e.target === checkoutModal) closeModal(checkoutModal);
        if (e.target === photoPreviewModal) closePhotoPreview();
    });

    document.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', () => {
            closeModal(btn.closest('.fixed'));
        });
    });

    function closeModal(el) {
        if (!el) return;
        const popup = el.querySelector('.popup-content');
        if (popup && !el.classList.contains('hidden')) {
            popup.classList.add('translate-y-full');
            setTimeout(() => el.classList.add('hidden'), 300);
        } else {
            el.classList.add('hidden');
        }
    }

    if (document.getElementById('mQtyMinus')) {
        document.getElementById('mQtyMinus').addEventListener('click', () => {
            if (selectedQty > 1) { selectedQty--; modalQtyVal.textContent = selectedQty; }
        });
        document.getElementById('mQtyPlus').addEventListener('click', () => {
            if (selectedQty < 2) { selectedQty++; modalQtyVal.textContent = selectedQty; }
        });
    }

    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', () => {
            addToCart(selectedDrink, selectedQty);
            closeModal(modal);
        });
    }

    if (openCartBtn) openCartBtn.addEventListener('click', openCheckout);
    if (placeOrderBtn) placeOrderBtn.addEventListener('click', submitOrder);

    // --- DRAG TO DISMISS (Mobile Sheet) ---
    const sheet = document.querySelector('#orderModal .popup-content');
    if (sheet) {
        let startY = 0;
        let currentY = 0;
        let isDragging = false;

        sheet.addEventListener('touchstart', (e) => {
            // Only drag if scrolled to top
            if (sheet.querySelector('.overflow-y-auto').scrollTop > 0) return;
            startY = e.touches[0].clientY;
            isDragging = true;
        }, { passive: true });

        sheet.addEventListener('touchmove', (e) => {
            if (!isDragging) return;
            currentY = e.touches[0].clientY;
            const diff = currentY - startY;
            if (diff > 0) { // Dragging down
                sheet.style.transform = `translateY(${diff}px)`;
                e.preventDefault();
            }
        }, { passive: false }); // Non-passive to allow preventDefault

        sheet.addEventListener('touchend', (e) => {
            if (!isDragging) return;
            isDragging = false;
            const diff = currentY - startY;
            if (diff > 150) { // Threshold to close
                closeModal(modal);
                setTimeout(() => { sheet.style.transform = ''; }, 300);
            } else {
                sheet.style.transform = ''; // Bounce back
            }
        });
    }

    // --- TAB SWITCHING ---
    // --- TAB SWITCHING ---
    window.switchTab = (tab) => {
        window.scrollTo(0, 0);
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.getElementById('tab-' + tab).classList.remove('hidden');

        document.querySelectorAll('.tab-btn').forEach(btn => {
            if (btn.dataset.target === tab) {
                btn.classList.add('bg-blue-600', 'text-white', 'shadow-lg');
                btn.classList.remove('text-gray-500', 'hover:bg-white/40');
            } else {
                btn.classList.remove('bg-blue-600', 'text-white', 'shadow-lg');
                btn.classList.add('text-gray-500', 'hover:bg-white/40');
            }
        });

        if (tab === 'photos') fetchPhotos();
        if (tab === 'messages') {
            setTimeout(fetchGuestMessages, 50);
        }
    };

    // Polling for messages
    setInterval(() => {
        const msgTab = document.getElementById('tab-messages');
        if (msgTab && !msgTab.classList.contains('hidden')) {
            fetchGuestMessages();
        }
    }, 3000);

    // --- MESSAGES LOGIC ---
    const messageForm = document.getElementById('messageForm');
    const guestMessagesList = document.getElementById('guestMessagesList');
    let lastMessageCount = 0;

    if (messageForm) {
        messageForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const nameInput = document.getElementById('chatName');
            const msgInput = document.getElementById('chatInput');

            // Validate Name
            if (!nameInput.value.trim()) {
                showAlert("Please enter your name.", "Name Required");
                nameInput.focus();
                return;
            }

            // Validate Message
            if (!msgInput.value.trim()) {
                msgInput.focus();
                return;
            }

            // Save Name
            localStorage.setItem('wedding_guest_name', nameInput.value.trim());
            let chatterName = nameInput.value.trim(); // Assuming chatterName is defined or will be used locally

            const btn = document.getElementById('sendMessageBtn');
            const originalContent = btn.innerHTML;

            btn.disabled = true;
            btn.textContent = "Sending...";

            const formData = new FormData(messageForm);
            const data = Object.fromEntries(formData.entries());

            if (!data.message.trim()) { // This check might be redundant due to msgInput validation above
                showAlert("Please write a message first.", "Empty Message");
                btn.disabled = false;
                btn.innerHTML = originalContent;
                return;
            }

            try {
                const res = await fetch('../api/messages.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                if (res.ok) {
                    msgInput.value = ''; // Clear message only
                    fetchGuestMessages(); // Refresh immediately
                    if (window.confetti) {
                        // Smaller burst for chat
                        confetti({ particleCount: 30, spread: 50, origin: { y: 0.8 }, colors: ['#FFC0CB', '#FF69B4'] });
                    }
                } else {
                    showAlert("Failed to send message.", "Error");
                }
            } catch (err) {
                showAlert("Network error.", "Error");
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalContent;
                msgInput.focus();
            }
        });
    }

    // --- PHOTO LOGIC ---
    const photoInput = document.getElementById('photoUpload');
    if (photoInput) {
        photoInput.addEventListener('change', async (e) => {
            if (e.target.files.length === 0) return;
            const file = e.target.files[0];
            const formData = new FormData();
            formData.append('photo', file);

            showAlert("Uploading photo...", "Please Wait");

            try {
                const res = await fetch('../api/photos.php', { method: 'POST', body: formData });
                if (res.ok) {
                    showAlert("Photo added to gallery!", "Success");
                    fetchPhotos();
                } else {
                    showAlert("Upload failed.", "Error");
                }
            } catch (e) { showAlert("Error uploading.", "Error"); }
        });
    }

    async function fetchGuestMessages() {
        if (!guestMessagesList || guestMessagesList.closest('.hidden')) return;

        try {
            const res = await fetch('../api/messages.php?t=' + Date.now());
            const msgs = await res.json();

            // API returns Newest First (ORDER BY created_at DESC)
            // We want Oldest First for chat (Top to Bottom)
            const chatMsgs = msgs.slice().reverse();

            if (chatMsgs.length === 0) {
                guestMessagesList.innerHTML = '<div class="text-center text-gray-400 text-sm py-10">No messages yet. Start the conversation!</div>';
                lastMessageCount = 0;
                return;
            }

            // Only re-render if count changed to avoid jumpiness (basic check)
            if (chatMsgs.length !== lastMessageCount) {
                const myName = localStorage.getItem('wedding_guest_name');

                guestMessagesList.innerHTML = chatMsgs.map(m => {
                    const isMe = myName && m.guest_name === myName;
                    return `
                        <div class="flex flex-col ${isMe ? 'items-end' : 'items-start'}">
                            <span class="text-[10px] text-gray-400 px-2 mb-1">${m.guest_name}</span>
                            <div class="max-w-[80%] rounded-2xl px-4 py-3 shadow-sm text-sm ${isMe ? 'bg-blue-600 text-white rounded-tr-none' : 'bg-white text-gray-800 rounded-tl-none'}">
                                ${m.message}
                            </div>
                        </div>
                    `;
                }).join('');

                // Scroll to bottom
                setTimeout(() => {
                    guestMessagesList.scrollTop = guestMessagesList.scrollHeight;
                }, 10);
                lastMessageCount = chatMsgs.length;
            }
        } catch (e) {
            console.error(e);
        }
    }

    async function fetchPhotos() {
        const grid = document.getElementById('photosGrid');
        try {
            const res = await fetch('../api/photos.php');
            const photos = await res.json();

            if (photos.length === 0) {
                grid.innerHTML = '<div class="col-span-full py-20 text-center text-gray-400 font-medium">No photos yet. Be the first!</div>';
                return;
            }

            grid.innerHTML = photos.map(p => `
                <div class="break-inside-avoid mb-4 rounded-xl overflow-hidden cursor-pointer group relative shadow-md" onclick="openPhotoPreview('../public/${p.filename}')">
                    <img src="../public/${p.filename}" class="w-full h-auto object-cover transform transition duration-500 group-hover:scale-105" loading="lazy">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition"></div>
                </div>
            `).join('');

        } catch (e) {
            grid.innerHTML = '<div class="text-center text-red-500">Failed to load photos</div>';
        }
    }

    // Preview logic
    window.openPhotoPreview = (src) => {
        if (photoPreviewModal && previewImage) {
            previewImage.src = src;
            photoPreviewModal.classList.remove('hidden');
        }
    };

    window.closePhotoPreview = () => {
        if (photoPreviewModal) photoPreviewModal.classList.add('hidden');
    };

    // --- Functions ---
    async function fetchDrinks() {
        try {
            const res = await fetch('../api/drinks.php');
            allDrinks = await res.json();
            renderCategories(allDrinks);
            renderDrinks(allDrinks);
        } catch (e) { console.error(e); }
    }

    async function fetchTables() {
        try {
            const res = await fetch('../api/tables.php');
            const tables = await res.json();
            tableSelect.innerHTML = '<option value="">Select Table...</option>' + tables.map(t => `<option value="${t.id}">${t.name}</option>`).join('');
        } catch (e) { }
    }

    function renderSkeletons() {
        if (!drinksGrid) return;
        drinksGrid.innerHTML = Array(6).fill(0).map(() => `
            <div class="rounded-[20px] overflow-hidden bg-white shadow-sm border border-gray-100 animate-pulse">
                <div class="aspect-[3/4] bg-slate-200"></div>
                <div class="p-4 space-y-2">
                    <div class="h-3 bg-slate-200 rounded w-1/3"></div>
                    <div class="h-5 bg-slate-200 rounded w-2/3"></div>
                </div>
            </div>
        `).join('');
    }

    function renderCategories(drinks) {
        const container = document.getElementById('categoryFilters');
        if (!container) return;

        const categories = ['All', ...new Set(drinks.filter(d => d.is_active == 1).map(d => d.category))].filter(Boolean);

        container.innerHTML = categories.map(cat => `
            <button onclick="setCategory('${cat}')" 
                class="px-5 py-2 rounded-full text-sm font-bold whitespace-nowrap transition shadow-sm border 
                ${activeCategory === cat ? 'bg-iosBlue text-white border-transparent shadow-blue-500/30' : 'bg-white text-gray-500 border-gray-200'}"
            >
                ${cat}
            </button>
        `).join('');
    }

    window.setCategory = (cat) => {
        activeCategory = cat;
        renderCategories(allDrinks); // Re-render to update active state styles
        filterDrinks(searchInput.value || '');
    };

    function renderDrinks(drinks) {
        if (!drinksGrid) return;

        if (drinks.length === 0) {
            drinksGrid.innerHTML = `
                <div class="col-span-2 flex flex-col items-center justify-center py-20 text-center">
                    <div class="w-16 h-16 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center text-3xl mb-4">🍹</div>
                    <p class="text-gray-500 font-medium">No drinks found.</p>
                    <p class="text-xs text-gray-400 mt-1">Try a different category or search term.</p>
                </div>
            `;
            return;
        }

        drinksGrid.innerHTML = drinks.map((d, index) => `
            <div class="glass-card rounded-[20px] overflow-hidden relative group cursor-pointer animate-fade-in-up" 
                 style="animation-delay: ${index * 50}ms; animation-fill-mode: both;"
                 onclick="openDrinkModal(${d.id})">
                <div class="aspect-[3/4] bg-gray-100 relative">
                    <img src="${d.image_url ? '../public/' + d.image_url : 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=300&h=200&fit=crop'}" 
                         class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" 
                         onerror="this.src='https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=300&h=200&fit=crop'">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-60"></div>
                </div>
                <div class="absolute bottom-0 left-0 right-0 p-4">
                    <span class="text-[10px] uppercase font-bold text-blue-200 tracking-wider mb-1 block">${d.category}</span>
                    <h3 class="text-white font-bold text-lg leading-tight shadow-black drop-shadow-md">${d.name}</h3>
                </div>
            </div>
        `).join('');
    }

    function filterDrinks(term) {
        const lower = term.toLowerCase();
        let filtered = allDrinks.filter(d => d.is_active == 1);

        // Filter by Category
        if (activeCategory !== 'All') {
            filtered = filtered.filter(d => d.category === activeCategory);
        }

        // Filter by Search
        if (lower) {
            filtered = filtered.filter(d => d.name.toLowerCase().includes(lower) || d.category.toLowerCase().includes(lower));
        }

        renderDrinks(filtered);
    }

    window.openDrinkModal = (id) => {
        if (isBlocked()) return;

        selectedDrink = allDrinks.find(d => d.id == id);
        if (!selectedDrink) return;

        modalImg.src = selectedDrink.image_url ? '../public/' + selectedDrink.image_url : 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=300&h=200&fit=crop';
        modalTitle.textContent = selectedDrink.name;
        // modalDesc removed as per request
        if (modalCat) modalCat.textContent = selectedDrink.category.toUpperCase();

        const existing = cart.find(i => i.id === id);
        selectedQty = existing ? existing.quantity : 1;
        modalQtyVal.textContent = selectedQty;

        addToCartBtn.textContent = existing ? "Update Tray" : "Add to Tray";

        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.querySelector('.popup-content').classList.remove('translate-y-full');
        }, 10); // slightly delayed to allow display:block to apply
    };

    function addToCart(drink, qty) {
        const existingIndex = cart.findIndex(i => i.id === drink.id);
        if (existingIndex > -1) {
            cart[existingIndex].quantity = qty;
        } else {
            if (cart.length >= 2) {
                showAlert("Tray Full: Max 2 types of drinks allowed.");
                return;
            }
            cart.push({
                id: drink.id,
                name: drink.name,
                image: drink.image_url,
                quantity: qty
            });
        }
        updateCartUI();
    }

    function removeFromCart(id) {
        cart = cart.filter(i => i.id !== id);
        updateCartUI();
        if (cart.length > 0) openCheckout();
        else checkoutModal.classList.add('hidden');
    }

    function updateCartUI() {
        cartCountBadge.textContent = cart.length;
        if (cart.length > 0) openCartBtn.classList.remove('hidden');
        else openCartBtn.classList.add('hidden');
    }

    function openCheckout() {
        if (cart.length === 0) return;

        cartItemsList.innerHTML = cart.map(item => `
            <div class="flex items-center gap-4 bg-gray-50 p-4 rounded-2xl">
                <img src="${item.image ? '../public/' + item.image : 'assets/placeholder.jpg'}" class="w-16 h-16 rounded-xl object-cover">
                <div class="flex-1">
                    <h4 class="font-bold text-gray-900">${item.name}</h4>
                    <span class="text-sm text-gray-500">Qty: ${item.quantity}</span>
                </div>
                <button onclick="removeFromCart(${item.id})" class="text-gray-400 hover:text-red-500 p-2">✕</button>
            </div>
        `).join('');

        checkoutModal.classList.remove('hidden');
    }

    window.removeFromCart = removeFromCart;

    async function submitOrder() {
        const tableId = tableSelect.value;
        const guestName = document.getElementById('guestName').value;
        const guestNote = document.getElementById('guestNote').value;

        if (!tableId || cart.length === 0) {
            showAlert("Please select a table.");
            return;
        }

        placeOrderBtn.disabled = true;
        placeOrderBtn.textContent = "Sending...";

        try {
            const res = await fetch('../api/orders.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    table_id: tableId,
                    guest_name: guestName,
                    guest_note: guestNote,
                    items: cart.map(i => ({ id: i.id, quantity: i.quantity }))
                })
            });

            if (res.ok) {
                cart = [];
                updateCartUI();
                checkoutModal.classList.add('hidden');
                checkActiveOrder();
                showAlert("Order Placed Successfully!");

                // CONFETTI BURST
                if (window.confetti) {
                    confetti({
                        particleCount: 100,
                        spread: 70,
                        origin: { y: 0.6 }
                    });
                }
            } else {
                const d = await res.json();
                showAlert(d.error || "Error", "Oops");
            }
        } catch (e) { showAlert("Error connecting", "Network Error"); }
        finally {
            placeOrderBtn.disabled = false;
            placeOrderBtn.textContent = "Confirm Order";
        }
    }

    function isBlocked() {
        if (statusBar.classList.contains('translate-y-0') && statusBadge.textContent !== "DONE") {
            showAlert("You already have an active order. Please wait for it to be completed.", "Order In Progress");
            return true;
        }
        return false;
    }

    async function checkActiveOrder() {
        try {
            const res = await fetch('../api/orders.php?scope=me');
            if (res.ok) {
                const data = await res.json();
                const active = data.find(o => ['pending', 'ready'].includes(o.status));
                if (active) {
                    statusBar.classList.remove('translate-y-full');
                    statusBar.classList.add('translate-y-0');

                    if (active.status === 'ready') {
                        statusMessage.textContent = "Your drink is ready!";
                        statusBadge.textContent = "READY";
                        statusBadge.className = "px-4 py-2 rounded-full bg-green-100 text-green-700 text-xs font-bold border border-green-200 animate-pulse";
                        // Pulsing global container
                        statusBar.classList.add('shadow-[0_0_30px_rgba(34,197,94,0.3)]', 'border-green-200');
                    } else {
                        statusMessage.textContent = "Mixing your drink...";
                        statusBadge.textContent = "PENDING";
                        statusBadge.className = "px-4 py-2 rounded-full bg-blue-100 text-blue-700 text-xs font-bold border border-blue-200";
                        statusBar.classList.remove('shadow-[0_0_30px_rgba(34,197,94,0.3)]', 'border-green-200');
                    }
                } else {
                    statusBar.classList.add('translate-y-full');
                    statusBar.classList.remove('translate-y-0');
                }
            }
        } catch (e) { }
    }

    // Custom Alert Logic
    window.showAlert = (msg, title = "Notice") => {
        const overlay = document.getElementById('customAlert');
        const box = document.getElementById('customAlertBox');

        if (!overlay) return alert(msg); // Fallback

        document.getElementById('alertTitle').textContent = title;
        document.getElementById('alertMessage').textContent = msg;

        overlay.classList.remove('hidden');
        overlay.classList.add('flex');

        setTimeout(() => {
            box.classList.remove('scale-90', 'opacity-0');
            box.classList.add('scale-100', 'opacity-100');
        }, 10);
    };

    window.closeAlert = () => {
        const overlay = document.getElementById('customAlert');
        const box = document.getElementById('customAlertBox');

        box.classList.remove('scale-100', 'opacity-100');
        box.classList.add('scale-90', 'opacity-0');

        setTimeout(() => {
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
        }, 200);
    };
});
