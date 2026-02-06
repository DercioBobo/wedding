<!-- index.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Wedding Bar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; -webkit-tap-highlight-color: transparent; }
        
        .liquid-bg {
            background: radial-gradient(circle at 0% 0%, #eef2ff 0%, #fdfbf7 50%, #eff6ff 100%);
            background-size: 200% 200%;
            animation: liquidMove 20s ease infinite;
        }
        @keyframes liquidMove { 0% { background-position: 0% 0% } 50% { background-position: 100% 100% } 100% { background-position: 0% 0% } }
        
        .glass-panel {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card:active { transform: scale(0.96); }

        .orb { position: fixed; border-radius: 50%; filter: blur(80px); z-index: -1; opacity: 0.6; }
        .orb-1 { top: -10%; left: -10%; width: 50vw; height: 50vw; background: #bfdbfe; }
        .orb-2 { bottom: 10%; right: -10%; width: 60vw; height: 60vw; background: #e9d5ff; }
        
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* Custom Alert Animation */
        .ios-alert-enter { transform: scale(1.1); opacity: 0; }
        .ios-alert-enter-active { transform: scale(1); opacity: 1; transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        iosBlue: '#007AFF',
                        iosText: '#1D1D1F',
                        iosGray: '#F5F5F7'
                    },
                    boxShadow: {
                        'glow': '0 0 20px rgba(0, 122, 255, 0.3)'
                    },
                    animation: {
                        'fade-in-up': 'fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards'
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="liquid-bg min-h-screen text-iosText pb-24 overflow-x-hidden selection:bg-iosBlue selection:text-white">

    <div class="orb orb-1 animate-pulse"></div>
    <div class="orb orb-2 animate-pulse" style="animation-delay: 2s"></div>

    <!-- Tab Content Container -->
    <div id="tab-drinks" class="tab-content block">
        <div class="max-w-md mx-auto min-h-screen flex flex-col pt-6 px-5 pb-32">
            
            <header class="mb-6 flex justify-between items-end">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1 uppercase tracking-wider">Welcome</p>
                    <h1 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600">
                        The Wedding Bar
                    </h1>
                </div>
                <div id="statusIndicator" class="hidden">
                     <span class="relative flex h-3 w-3">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-3 w-3 bg-sky-500"></span>
                    </span>
                </div>
            </header>

            <div class="sticky top-4 z-40 mb-8">
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                         <svg class="h-5 w-5 text-gray-400 group-focus-within:text-iosBlue transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                         </svg>
                    </div>
                    <input type="text" id="searchInput" 
                        class="block w-full pl-11 pr-4 py-4 glass-panel rounded-[24px] text-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-iosBlue/50 transition-all shadow-sm"
                        placeholder="Search cocktails...">
                </div>
            </div>

            <!-- Categories -->
            <div class="mb-6 -mx-5 overflow-x-auto no-scrollbar pl-5 pr-5 flex gap-3" id="categoryFilters">
                <!-- JS Loaded -->
            </div>

            <div id="drinksGrid" class="grid grid-cols-2 gap-4 pb-10">
                <div class="col-span-2 text-center py-10 text-gray-400">Loading menu...</div>
            </div>
            
        </div>
    </div>

    <!-- PHOTOS TAB -->
    <div id="tab-photos" class="tab-content hidden">
        <div class="max-w-md mx-auto min-h-screen flex flex-col pt-6 px-5 pb-32">
            
            <header class="mb-6 flex justify-between items-end">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1 uppercase tracking-wider">Gallery</p>
                    <h1 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-pink-500 to-orange-500">
                        Wedding Moments
                    </h1>
                </div>
                <!-- Upload Button -->
                <label class="cursor-pointer bg-white/80 backdrop-blur text-blue-600 rounded-full w-12 h-12 flex items-center justify-center shadow-lg border border-white active:scale-95 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <input type="file" id="photoUpload" class="hidden" accept="image/*">
                </label>
            </header>
            
            <!-- Photos Grid -->
            <div id="photosGrid" class="columns-2 gap-4 space-y-4">
                <!-- JS Loaded -->
                <div class="bg-white/50 rounded-2xl p-4 text-center break-inside-avoid">
                    <p class="text-sm text-gray-500">Loading photos...</p>
                </div>
            </div>
            
        </div>
    </div>
    
    <!-- PHOTO PREVIEW MODAL -->
    <div id="photoPreviewModal" class="fixed inset-0 z-[70] hidden bg-black/95 flex items-center justify-center p-4">
        <button onclick="closePhotoPreview()" class="absolute top-6 right-6 text-white/50 hover:text-white w-10 h-10 flex items-center justify-center rounded-full bg-white/10 text-2xl">&times;</button>
        <img id="previewImage" src="" class="max-w-full max-h-full rounded-lg shadow-2xl">
    </div>

    <!-- BOTTOM TAB BAR -->
    <div class="fixed bottom-6 left-5 right-5 z-[60]">
        <div class="glass-panel mx-auto max-w-sm rounded-[32px] p-2 flex justify-between shadow-2xl border border-white/60">
            <button onclick="switchTab('drinks')" class="tab-btn w-1/2 py-3 rounded-[24px] flex items-center justify-center gap-2 font-bold transition-all text-white bg-blue-600 shadow-lg" data-target="drinks">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <span>Drinks</span>
            </button>
            <button onclick="switchTab('photos')" class="tab-btn w-1/2 py-3 rounded-[24px] flex items-center justify-center gap-2 font-bold transition-all text-gray-500 hover:bg-white/40" data-target="photos">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span>Photos</span>
            </button>
        </div>
    </div>
    
    <!-- Status Bar for Drinks (Adjusted z-index and position) -->
    <div id="orderStatus" 
         class="fixed bottom-0 left-0 w-full glass-panel border-t border-white/50 rounded-t-[32px] p-6 pb-28 transform translate-y-full transition-transform duration-500 z-50 flex items-center justify-between shadow-[0_-10px_40px_rgba(0,0,0,0.1)]">
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Status</p>
            <p id="statusMessage" class="text-lg font-bold text-gray-800">Mixing your drink...</p>
        </div>
        <span id="statusBadge" class="px-4 py-2 rounded-full bg-blue-100 text-blue-700 text-xs font-bold border border-blue-200">
            PENDING
        </span>
    </div>

    <!-- Cart Button (Adjusted Position) -->
    <button id="openCartBtn" class="fixed bottom-28 right-5 z-40 hidden glass-panel bg-white/90 px-5 py-4 rounded-full flex flex-col items-center justify-center gap-1 shadow-xl border border-white active:scale-95 transition-all hover:shadow-glow w-16 h-16">
        <span id="cartCountBadge" class="flex items-center justify-center bg-iosBlue text-white text-xs font-bold h-6 w-6 rounded-full shadow-md">0</span>
        <span class="text-[10px] font-bold text-iosBlue uppercase">Order</span>
    </button>

    <!-- Modal: Drink Detail -->
    <div id="orderModal" class="fixed inset-0 z-[65] hidden">
        <div class="absolute inset-0 bg-black/20 backdrop-blur-sm transition-opacity close-modal"></div>
        <div class="absolute bottom-0 inset-x-0 h-[85vh] bg-white rounded-t-[40px] shadow-2xl overflow-hidden flex flex-col transition-transform duration-300 transform translate-y-full popup-content">
            <div class="w-full flex justify-center pt-4 pb-2 bg-white z-10 close-modal cursor-pointer">
                <div class="w-12 h-1.5 rounded-full bg-gray-200"></div>
            </div>
            <div class="overflow-y-auto flex-1 pb-10 no-scrollbar relative">
                <div class="relative h-64 w-full">
                    <img id="modalImg" src="" class="absolute inset-0 w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-b from-transparent to-white/90"></div>
                </div>
                <div class="px-8 -mt-10 relative z-10">
                    <span id="modalCat" class="inline-block px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-bold tracking-wider mb-3 border border-blue-100">COCKTAIL</span>
                    <h2 id="modalTitle" class="text-3xl font-bold text-gray-900 mb-4 leading-tight">Mojito</h2>
                    <p id="modalDesc" class="text-gray-500 leading-relaxed mb-8">Refreshing mint and lime...</p>
                    <div class="flex items-center justify-between mb-8 p-1 bg-gray-100 rounded-full w-40 mx-auto">
                        <button id="mQtyMinus" class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center text-gray-600 active:scale-90 transition">-</button>
                        <span id="modalQtyVal" class="font-bold text-xl text-gray-800">1</span>
                        <button id="mQtyPlus" class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center text-gray-800 active:scale-90 transition">+</button>
                    </div>
                </div>
            </div>
            <div class="p-6 bg-white border-t border-gray-100">
                <button id="addToCartBtn" class="w-full py-4 rounded-[20px] bg-black text-white font-bold text-lg shadow-xl active:scale-95 transition-transform flex items-center justify-center gap-2">
                    Add to Order
                </button>
            </div>
        </div>
    </div>

    <!-- Modal: Checkout -->
    <div id="checkoutModal" class="fixed inset-0 z-[65] hidden">
        <div class="absolute inset-0 bg-white/90 backdrop-blur-md transition-opacity"></div>
        <div class="absolute inset-0 flex flex-col p-6">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold">Your Tray</h2>
                <button class="close-modal w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">&times;</button>
            </div>
            <div id="cartItemsList" class="flex-1 overflow-y-auto space-y-4"></div>
            <div class="mt-auto pt-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Your Table</label>
                    <div class="relative">
                        <select id="tableSelect" class="w-full p-4 bg-gray-50 rounded-2xl border-none text-lg font-medium appearance-none focus:ring-2 focus:ring-blue-500/20 outline-none">
                            <option value="">Select Table...</option>
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">▼</div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" id="guestName" placeholder="Your Name" class="p-4 bg-gray-50 rounded-2xl border-none outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition">
                    <input type="text" id="guestNote" placeholder="Notes?" class="p-4 bg-gray-50 rounded-2xl border-none outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition">
                </div>
                <button id="placeOrderBtn" class="w-full py-4 rounded-[20px] bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold text-lg shadow-lg active:scale-95 transition-transform">
                    Confirm Order
                </button>
            </div>
        </div>
    </div>
    
    <!-- CUSTOM ALERT MODAL -->
    <div id="customAlert" class="fixed inset-0 z-[100] hidden items-center justify-center px-6">
        <div class="absolute inset-0 bg-black/30 backdrop-blur-sm transition-opacity"></div>
        <div id="customAlertBox" class="bg-white/90 backdrop-blur-xl w-full max-w-[300px] rounded-2xl shadow-2xl relative z-10 transform scale-90 opacity-0 transition-all duration-200">
            <div class="p-6 text-center">
                <h3 id="alertTitle" class="text-lg font-bold mb-2">Message</h3>
                <p id="alertMessage" class="text-gray-600 text-[15px] leading-relaxed">Alert Content</p>
            </div>
            <div class="border-t border-gray-300/50">
                <button onclick="closeAlert()" class="w-full py-3 text-blue-600 font-bold text-lg active:bg-gray-100 rounded-b-2xl transition-colors">OK</button>
            </div>
        </div>
    </div>

    <script src="assets/script.js"></script>
</body>
</html>
