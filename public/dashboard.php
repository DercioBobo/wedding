<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barman Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #f8fafc; color: #1e293b; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col bg-slate-50 text-slate-800">

    <div class="sticky top-0 z-20 glass-panel border-b border-slate-200 p-4 flex justify-between items-center shadow-sm">
        <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">Bar Dashboard</h1>
        <div class="flex gap-2">
            <button onclick="switchTab('orders')" class="nav-btn px-5 py-2 rounded-full font-bold text-sm transition shadow-sm" data-target="orders">Orders</button>
            <button onclick="switchTab('messages')" class="nav-btn px-5 py-2 rounded-full font-bold text-sm transition shadow-sm" data-target="messages">Messages</button>
            <button onclick="switchTab('menu')" class="nav-btn px-5 py-2 rounded-full font-bold text-sm transition shadow-sm" data-target="menu">Menu</button>
            <button onclick="switchTab('photos')" class="nav-btn px-5 py-2 rounded-full font-bold text-sm transition shadow-sm" data-target="photos">Photos</button>
        </div>
    </div>

    <div id="orders" class="p-4 md:p-8 flex-1">
        <div id="ordersList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <!-- Loaded -->
        </div>
    </div>

    <!-- Messages Tab -->
    <div id="messages" class="hidden p-4 md:p-8 max-w-6xl mx-auto w-full">
        <h2 class="text-2xl font-bold mb-6 text-slate-800">Guest Messages</h2>
        <div id="messagesList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Loaded -->
        </div>
    </div>

    <!-- Menu Tab -->
    <div id="menu" class="hidden p-4 md:p-8 max-w-5xl mx-auto w-full">
        
        <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-xl shadow-slate-200/50 mb-10">
            <h2 class="text-2xl font-bold mb-6 flex items-center gap-3 text-slate-800">
                <span class="bg-blue-100 text-blue-600 p-2 rounded-xl">📝</span>
                <span id="formTitle">Add New Drink</span>
            </h2>
            <form id="drinkForm" class="space-y-6">
                <input type="hidden" name="id" id="drinkId">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase text-slate-400 tracking-wider">Name</label>
                        <input name="name" id="drinkName" placeholder="e.g. Mojito" class="bg-slate-50 border border-slate-200 rounded-2xl p-4 w-full focus:ring-4 focus:ring-blue-100 outline-none transition font-medium" required>
                    </div>
                    <div class="space-y-2">
                         <label class="text-xs font-bold uppercase text-slate-400 tracking-wider">Category</label>
                        <input name="category" id="drinkCat" placeholder="e.g. Cocktail" class="bg-slate-50 border border-slate-200 rounded-2xl p-4 w-full focus:ring-4 focus:ring-blue-100 outline-none transition font-medium">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold uppercase text-slate-400 tracking-wider">Description</label>
                    <textarea name="description" id="drinkDesc" placeholder="Description..." class="bg-slate-50 border border-slate-200 rounded-2xl p-4 w-full focus:ring-4 focus:ring-blue-100 outline-none h-24 transition font-medium resize-none"></textarea>
                </div>
                
                <div class="flex items-center gap-4 pt-2">
                    <label class="cursor-pointer bg-slate-100 hover:bg-slate-200 transition px-6 py-3 rounded-xl font-bold text-slate-600 flex items-center gap-2">
                        📷 Upload Image
                        <input type="file" name="image" class="hidden">
                    </label>
                    <div class="flex-1"></div>
                    <button type="button" onclick="resetForm()" class="text-slate-400 hover:text-slate-600 font-bold px-4">Cancel</button>
                    <button type="submit" id="saveDrinkBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-8 py-3 rounded-xl transition shadow-lg shadow-blue-200">Save Drink</button>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Tables Column -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-lg shadow-slate-200/50">
                    <h3 class="font-black text-slate-300 uppercase text-xs tracking-widest mb-6">Tables</h3>
                    <div class="flex gap-2 mb-6">
                        <input id="newTableName" placeholder="New Table" class="bg-slate-50 border border-slate-200 rounded-xl p-3 flex-1 text-sm outline-none focus:ring-2 focus:ring-blue-100 font-bold text-slate-600">
                        <button onclick="addTable()" class="bg-slate-800 text-white p-3 rounded-xl font-bold hover:bg-slate-700 transition">+</button>
                    </div>
                    <div id="tablesList" class="flex flex-wrap gap-2">
                        <!-- Loaded -->
                    </div>
                </div>
            </div>

            <!-- Drinks List Column -->
            <div class="lg:col-span-2 space-y-4">
                <h3 class="font-black text-slate-300 uppercase text-xs tracking-widest px-2">Active Menu</h3>
                <div id="menuList" class="space-y-3">
                    <!-- Loaded -->
                </div>
            </div>
        </div>

    </div>

    <!-- Photos Tab -->
    <div id="photos" class="hidden p-4 md:p-8 max-w-6xl mx-auto w-full">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-slate-800">Photo Gallery</h2>
            <div class="flex gap-2">
                <button onclick="selectAllPhotos()" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold px-4 py-2 rounded-xl transition text-sm">Select All</button>
                <button onclick="downloadSelected()" class="bg-blue-100 hover:bg-blue-200 text-blue-700 font-bold px-4 py-2 rounded-xl transition text-sm flex items-center gap-1">
                    <span>⬇</span> Download (<span id="selCountDown">0</span>)
                </button>
                <button onclick="deleteSelected()" class="bg-red-100 hover:bg-red-200 text-red-700 font-bold px-4 py-2 rounded-xl transition text-sm flex items-center gap-1">
                     <span>×</span> Delete (<span id="selCountDel">0</span>)
                </button>
            </div>
        </div>
        
        <div id="dashPhotosGrid" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
            <!-- JS Loaded -->
        </div>
    </div>

    <!-- Edit Table Modal -->
    <div id="editTableOverlay" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
        <div class="bg-white p-8 rounded-3xl max-w-sm w-full shadow-2xl">
            <h3 class="font-bold text-xl mb-6 text-slate-800">Rename Table</h3>
            <input type="hidden" id="editTableId">
            <input id="editTableName" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 mb-6 outline-none focus:ring-4 focus:ring-blue-100 font-bold text-lg text-slate-700">
            <div class="flex gap-3">
                <button onclick="document.getElementById('editTableOverlay').style.display='none'" class="flex-1 bg-slate-100 text-slate-500 font-bold py-3 rounded-xl hover:bg-slate-200 transition">Cancel</button>
                <button onclick="updateTable()" class="flex-1 bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-200 transition">Update</button>
            </div>
        </div>
    </div>
    
    <!-- PIN Modal -->
    <div id="pinModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4 z-[60]">
        <div class="bg-white p-8 rounded-3xl max-w-xs w-full shadow-2xl text-center transform scale-95 transition-all">
            <h3 class="font-bold text-xl mb-6 text-slate-800">Enter Security PIN</h3>
            <p class="text-slate-500 mb-6 text-sm">Please confirm authorization to delete.</p>
            <div class="flex justify-center gap-2 mb-6" id="pinInputs">
                <!-- Inputs generated by JS or simple input -->
                <input type="password" id="pinInput" maxlength="4" class="text-center text-3xl font-bold tracking-[0.5em] w-full p-4 bg-slate-50 border-2 border-slate-200 rounded-2xl focus:border-blue-500 outline-none transition" placeholder="••••">
            </div>
            <div class="flex gap-3">
                <button onclick="closePinModal()" class="flex-1 bg-slate-100 text-slate-500 font-bold py-3 rounded-xl hover:bg-slate-200 transition">Cancel</button>
                <button onclick="confirmPin()" class="flex-1 bg-red-500 text-white font-bold py-3 rounded-xl hover:bg-red-600 shadow-lg shadow-red-200 transition">Confirm</button>
            </div>
        </div>
    </div>

    <script src="assets/dashboard.js"></script>
    <script>
        // Update nav logic for tailwind light theme
        const originalSwitchTab = window.switchTab;
        window.switchTab = (tab) => {
            originalSwitchTab(tab);
            document.querySelectorAll('.nav-btn').forEach(b => {
                if(b.dataset.target === tab) {
                    b.classList.add('bg-white', 'text-blue-600', 'shadow-md', 'border', 'border-slate-100');
                    b.classList.remove('text-slate-400', 'hover:bg-white/50');
                } else {
                    b.classList.remove('bg-white', 'text-blue-600', 'shadow-md', 'border', 'border-slate-100');
                    b.classList.add('text-slate-400', 'hover:bg-white/50');
                }
            });
        };
        window.switchTab('orders');
    </script>
</body>
</html>
