<div class="px-2 sm:px-5">

    <div class="w-full m-1 border-b-2 border-gray-300 dark:border-gray-600 pb-3 mb-4">
        <h1 class="font-semibold text-lg sm:text-xl pl-1 text-gray-800 dark:text-gray-200">
            Total Downline: {{ count(json_decode($downline)) }}
        </h1>
    </div>

    <div class="m-1 w-full" style="min-height: 400px;">
        <div id="chart-container"></div>
    </div>

</div>

<!-- Compact Tabbed Modal - Mobile First -->
<div id="userInfoModal" class="hidden fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm z-50 flex items-center justify-center p-4" onclick="closeModal(event)">
    <div class="relative w-full max-w-md max-h-[50vh] bg-white dark:bg-gray-900 rounded-2xl shadow-2xl overflow-hidden flex flex-col" onclick="event.stopPropagation()">

        <!-- Modal Header - Compact -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-3 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center space-x-2 min-w-0">
                <div class="w-8 h-8 bg-white/20 backdrop-blur rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-bold text-white truncate" id="modalTitle">Member Profile</h3>
                    <p class="text-xs text-white/80 truncate" id="modalSubtitle">View details</p>
                </div>
            </div>
            <button onclick="closeModal()" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 transition flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
            <div class="flex space-x-1 px-4" role="tablist">
                <button onclick="switchTab('overview')" id="tab-overview" class="tab-btn px-3 py-2 text-xs font-semibold border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400">
                    Overview
                </button>
                <button onclick="switchTab('network')" id="tab-network" class="tab-btn px-3 py-2 text-xs font-semibold border-b-2 border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                    Network
                </button>
                <button onclick="switchTab('actions')" id="tab-actions" class="tab-btn px-3 py-2 text-xs font-semibold border-b-2 border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                    Actions
                </button>
            </div>
        </div>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto" id="modalContent">
            <!-- Content injected here -->
        </div>

    </div>
</div>

@push('scripts')

    <script src="{{ asset('js/d3/d3.v7.min.js') }}"></script>
    <script src="{{ asset('js/d3/d3-org-chart@3.0.1') }}"></script>
    <script src="{{ asset('js/d3/d3-flextree.js') }}"></script>

    <script>
        let currentUserData = null;
        let currentTab = 'overview';

        // Tab Switching
        function switchTab(tabName) {
            currentTab = tabName;

            // Update tab buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('border-indigo-600', 'text-indigo-600', 'dark:text-indigo-400');
                btn.classList.add('border-transparent', 'text-gray-600', 'dark:text-gray-400');
            });

            document.getElementById('tab-' + tabName).classList.remove('border-transparent', 'text-gray-600', 'dark:text-gray-400');
            document.getElementById('tab-' + tabName).classList.add('border-indigo-600', 'text-indigo-600', 'dark:text-indigo-400');

            // Render tab content
            renderTabContent(tabName, currentUserData);
        }

        // Render Tab Content
        function renderTabContent(tab, userData) {
            const modalContent = document.getElementById('modalContent');
            const imageUrl = userData.image || `https://ui-avatars.com/api/?name=${encodeURIComponent(userData.name)}&size=80&background=6366F1&color=fff&bold=true`;

            if (tab === 'overview') {
                modalContent.innerHTML = `
                    <div class="p-4 space-y-4">
                        <!-- Profile Card -->
                        <div class="flex items-center space-x-3 p-3 rounded-xl bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-gray-800 dark:to-gray-700">
                            <img src="${imageUrl}"
                                 alt="${userData.name}"
                                 class="w-16 h-16 rounded-xl border-2 border-white dark:border-gray-600 shadow-lg object-cover"
                                 onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(userData.name)}&size=80&background=6366F1&color=fff&bold=true'">
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-gray-900 dark:text-white text-base truncate">${userData.name}</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-300 truncate">${userData.email || 'No email'}</p>
                                <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-600 text-white">
                                    ${userData.level}
                                </span>
                            </div>
                        </div>

                        <!-- Quick Info -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-3 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center space-x-2 mb-1">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                    </svg>
                                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Code</span>
                                </div>
                                <p class="text-sm font-mono font-bold text-indigo-600 dark:text-indigo-400 truncate">${userData.referral_code || 'N/A'}</p>
                            </div>

                            <div class="p-3 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center space-x-2 mb-1">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Joined</span>
                                </div>
                                <p class="text-sm font-bold text-gray-900 dark:text-white truncate">${userData.joinedOn}</p>
                            </div>
                        </div>

                        <!-- Expandable Sections -->
                        <div class="space-y-2">
                            <button onclick="toggleSection('details')" class="w-full flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">More Details</span>
                                <svg id="icon-details" class="w-4 h-4 text-gray-600 dark:text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div id="section-details" class="hidden px-3 space-y-2">
                                <div class="flex justify-between py-2 border-b border-gray-200 dark:border-gray-700">
                                    <span class="text-xs text-gray-600 dark:text-gray-400">Email</span>
                                    <span class="text-xs font-medium text-gray-900 dark:text-white truncate ml-2">${userData.email || 'Not provided'}</span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-gray-200 dark:border-gray-700">
                                    <span class="text-xs text-gray-600 dark:text-gray-400">Level</span>
                                    <span class="text-xs font-medium text-gray-900 dark:text-white">${userData.level}</span>
                                </div>
                                <div class="flex justify-between py-2">
                                    <span class="text-xs text-gray-600 dark:text-gray-400">Network Depth</span>
                                    <span class="text-xs font-medium text-gray-900 dark:text-white">Level ${userData.depth}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            } else if (tab === 'network') {
                modalContent.innerHTML = `
                    <div class="p-4 space-y-4">
                        <!-- Network Stats -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-4 rounded-xl ${userData.hasChildren ? 'bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20' : 'bg-gray-50 dark:bg-gray-800'}">
                                <div class="w-10 h-10 ${userData.hasChildren ? 'bg-green-500' : 'bg-gray-400'} rounded-lg flex items-center justify-center mb-2">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <p class="text-xs font-medium ${userData.hasChildren ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-400'}">Downline</p>
                                <p class="text-lg font-bold ${userData.hasChildren ? 'text-green-700 dark:text-green-300' : 'text-gray-700 dark:text-gray-300'}">${userData.hasChildren ? 'Active' : 'None'}</p>
                            </div>

                            <div class="p-4 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20">
                                <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center mb-2">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                </div>
                                <p class="text-xs font-medium text-blue-600 dark:text-blue-400">Depth</p>
                                <p class="text-lg font-bold text-blue-700 dark:text-blue-300">Level ${userData.depth}</p>
                            </div>
                        </div>

                        <!-- Network Info -->
                        <div class="p-4 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 border-l-4 border-indigo-600">
                            <div class="flex items-start space-x-2">
                                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="text-xs font-semibold text-indigo-900 dark:text-indigo-200">Network Position</p>
                                    <p class="text-xs text-indigo-700 dark:text-indigo-300 mt-1">
                                        This member is at <strong>Level ${userData.depth}</strong> in your network tree.
                                        ${userData.hasChildren ? 'They have an active downline.' : 'No downline members yet.'}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            } else if (tab === 'actions') {
                modalContent.innerHTML = `
                    <div class="p-4 space-y-3">
                        <a href="{{ url('') }}/admin/users/${userData.referral_code}"
                           class="flex items-center space-x-3 p-3 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white transition shadow-lg">
                            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-sm">View Full Profile</p>
                                <p class="text-xs text-white/80">See complete account details</p>
                            </div>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>

                        <a href="{{ url('') }}/admin/users/${userData.referral_code}/community"
                           class="flex items-center space-x-3 p-3 rounded-xl bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white transition shadow-lg">
                            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-sm">View Network Tree</p>
                                <p class="text-xs text-white/80">See their downline structure</p>
                            </div>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>

                        <a href="{{ url('') }}/admin/users/${userData.referral_code}/stats"
                           class="flex items-center space-x-3 p-3 rounded-xl bg-gradient-to-r from-pink-600 to-pink-700 hover:from-pink-700 hover:to-pink-800 text-white transition shadow-lg">
                            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-sm">View Statistics</p>
                                <p class="text-xs text-white/80">Performance & analytics</p>
                            </div>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>

                        <a href="{{ url('') }}/admin/users/${userData.referral_code}/edit"
                           class="flex items-center space-x-3 p-3 rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-900 dark:text-white transition border border-gray-300 dark:border-gray-600">
                            <div class="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-sm">Edit Account</p>
                                <p class="text-xs opacity-70">Modify user information</p>
                            </div>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                `;
            }
        }

        // Toggle Expandable Section
        function toggleSection(sectionId) {
            const section = document.getElementById('section-' + sectionId);
            const icon = document.getElementById('icon-' + sectionId);

            if (section.classList.contains('hidden')) {
                section.classList.remove('hidden');
                icon.style.transform = 'rotate(180deg)';
            } else {
                section.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
            }
        }

        // Open Modal
        function openUserModal(userData) {
            currentUserData = userData;
            currentTab = 'overview';

            const modal = document.getElementById('userInfoModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalSubtitle = document.getElementById('modalSubtitle');

            modalTitle.textContent = userData.name;
            modalSubtitle.textContent = userData.referral_code;

            renderTabContent('overview', userData);

            // Reset tabs
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('border-indigo-600', 'text-indigo-600', 'dark:text-indigo-400');
                btn.classList.add('border-transparent', 'text-gray-600', 'dark:text-gray-400');
            });
            document.getElementById('tab-overview').classList.remove('border-transparent', 'text-gray-600', 'dark:text-gray-400');
            document.getElementById('tab-overview').classList.add('border-indigo-600', 'text-indigo-600', 'dark:text-indigo-400');

            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        // Close Modal
        function closeModal(event) {
            if (!event || event.target.id === 'userInfoModal') {
                document.getElementById('userInfoModal').classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        }

        // D3.js Chart
        document.addEventListener("DOMContentLoaded", function() {
            const chartContainer = document.getElementById("chart-container");
            if (!chartContainer) return;

            let downlineData = @json(json_decode($downline));

            if (!downlineData || downlineData.length === 0) {
                chartContainer.innerHTML = '<p class="text-center text-gray-500 py-10 text-sm">No downline data</p>';
                return;
            }

            try {
                let chart = new d3.OrgChart()
                    .container('#chart-container')
                    .data(downlineData)
                    .nodeHeight((d) => 140)
                    .nodeWidth((d) => 200)
                    .childrenMargin((d) => 50)
                    .compactMarginBetween((d) => 35)
                    .compactMarginPair((d) => 30)
                    .neighbourMargin((a, b) => 20)
                    .nodeContent(function (d, i, arr, state) {
                        const imageUrl = d.data.image || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(d.data.name) + '&size=40&background=6366F1&color=fff&bold=true';
                        const userDataStr = JSON.stringify(d.data).replace(/"/g, '&quot;');

                        return `
                            <div style="width:${d.width}px; height:${d.height}px; padding:3px;">
                                <div style="background:#FFF; width:100%; height:100%; border-radius:10px; border:1px solid #E5E7EB; box-shadow:0 2px 4px rgba(0,0,0,0.1); position:relative; padding:10px;">
                                    <div style="display:flex; align-items:center; margin-bottom:8px;">
                                        <img src="${imageUrl}" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid #fff; box-shadow:0 2px 4px rgba(0,0,0,0.1);"
                                             onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(d.data.name)}&size=40&background=6366F1&color=fff&bold=true'"/>
                                        <div style="margin-left:8px; flex:1; min-width:0;">
                                            <div style="font-size:12px; font-weight:600; color:#111827; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="${d.data.name}">${d.data.name}</div>
                                            <div style="font-size:10px; color:#6B7280;">${d.data.level}</div>
                                        </div>
                                    </div>
                                    <div style="font-size:9px; color:#9CA3AF; margin-bottom:8px;">Joined: ${d.data.joinedOn}</div>
                                    <div style="display:flex; gap:4px;">
                                        <a href="{{ url('') }}/admin/users/${d.data.referral_code}/community"
                                           style="flex:1; background:linear-gradient(135deg,#6366F1,#8B5CF6); color:#fff; padding:5px; border-radius:6px; font-size:9px; font-weight:600; text-align:center; text-decoration:none; transition:all 0.2s;"
                                           onmouseover="this.style.transform='translateY(-1px)'"
                                           onmouseout="this.style.transform='translateY(0)'">
                                            📊
                                        </a>
                                        <button onclick='openUserModal(${userDataStr})'
                                                style="flex:1; background:linear-gradient(135deg,#EC4899,#F43F5E); color:#fff; padding:5px; border-radius:6px; font-size:9px; font-weight:600; border:none; cursor:pointer; transition:all 0.2s;"
                                                onmouseover="this.style.transform='translateY(-1px)'"
                                                onmouseout="this.style.transform='translateY(0)'">
                                            ℹ️
                                        </button>
                                    </div>
                                    ${d.data.depth > 0 ? `<div style="position:absolute; top:8px; right:8px; background:#EEF2FF; color:#4F46E5; padding:2px 6px; border-radius:10px; font-size:9px; font-weight:600;">L${d.data.depth}</div>` : ''}
                                    ${d.data.hasChildren ? `<div style="position:absolute; top:8px; left:8px; background:#10B981; color:#fff; padding:2px 6px; border-radius:10px; font-size:9px; font-weight:600;">${d.children ? d.children.length : 0}↓</div>` : ''}
                                </div>
                            </div>
                        `;
                    })
                    .render();

            } catch (error) {
                console.error("Error:", error);
                chartContainer.innerHTML = `<p class="text-center text-red-500 py-10 text-sm">Error: ${error.message}</p>`;
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') closeModal();
        });
    </script>

@endpush
