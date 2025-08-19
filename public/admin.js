// Admin Dashboard JavaScript
class AdminDashboard {
    constructor() {
        this.isAuthenticated = false;
        this.currentUser = null;
        this.activeTab = 'dashboard';
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.checkAuthStatus();
        this.updateTimeAndDate();
        setInterval(() => this.updateTimeAndDate(), 1000);
    }

    setupEventListeners() {
        // Login form submission
        const loginForm = document.getElementById('adminLoginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => this.handleLogin(e));
        }

        // Logout button
        const logoutBtn = document.querySelector('button[onclick="logout()"]');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', (e) => this.handleLogout(e));
        }
    }

    async checkAuthStatus() {
        try {
            const response = await fetch('/auth/user');
            const data = await response.json();
            
            if (data.user && data.user.is_admin) {
                this.isAuthenticated = true;
                this.currentUser = data.user;
                this.showDashboard();
                this.loadUserInfo();
                this.loadTabData('dashboard');
            } else {
                this.showLoginForm();
            }
        } catch (error) {
            console.error('Error checking auth status:', error);
            this.showLoginForm();
        }
    }

    showLoginForm() {
        document.getElementById('loginForm').classList.remove('hidden');
        document.getElementById('adminDashboard').classList.add('hidden');
        this.isAuthenticated = false;
        this.currentUser = null;
    }

    showDashboard() {
        document.getElementById('loginForm').classList.add('hidden');
        document.getElementById('adminDashboard').classList.remove('hidden');
        document.getElementById('userInfo').classList.remove('hidden');
        this.isAuthenticated = true;
    }

    loadUserInfo() {
        if (!this.currentUser) return;

        const userName = this.currentUser.first_name || this.currentUser.username || 'Administrator';
        const userInitials = userName.split(' ').map(n => n.charAt(0)).join('').toUpperCase();

        document.getElementById('userName').textContent = userName;
        document.getElementById('userInitials').textContent = userInitials;
        document.getElementById('userRole').textContent = 'Administrator';
    }

    async handleLogin(e) {
        e.preventDefault();
        
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const errorDiv = document.getElementById('loginError');
        
        try {
            const response = await fetch('/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email, password })
            });
            
            const data = await response.json();
            
            if (data.success) {
                errorDiv.classList.add('hidden');
                await this.checkAuthStatus();
            } else {
                errorDiv.textContent = data.message || 'Login failed';
                errorDiv.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Login error:', error);
            errorDiv.textContent = 'Login failed. Please try again.';
            errorDiv.classList.remove('hidden');
        }
    }

    async handleLogout(e) {
        e.preventDefault();
        
        try {
            await fetch('/auth/logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            });
            
            this.showLoginForm();
        } catch (error) {
            console.error('Logout error:', error);
        }
    }

    showTab(tabName) {
        // Hide all tab contents
        const tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(tab => tab.classList.remove('active'));
        
        // Show selected tab
        const selectedTab = document.getElementById(`${tabName}-tab`);
        if (selectedTab) {
            selectedTab.classList.add('active');
        }
        
        // Update navigation highlighting
        const navLinks = document.querySelectorAll('.nav-link, .sidebar-link');
        navLinks.forEach(link => {
            link.classList.remove('text-primary');
            link.classList.add('text-gray-700');
        });
        
        // Highlight active nav link
        const activeNavLinks = document.querySelectorAll(`[onclick="showTab('${tabName}')"]`);
        activeNavLinks.forEach(link => {
            link.classList.remove('text-gray-700');
            link.classList.add('text-primary');
        });
        
        this.activeTab = tabName;
        this.loadTabData(tabName);
    }

    async loadTabData(tabName) {
        switch (tabName) {
            case 'dashboard':
                await this.loadDashboardData();
                break;
            case 'articles':
                await this.loadArticles();
                break;
            case 'matches':
                await this.loadMatches();
                break;
            case 'teams':
                await this.loadTeams();
                break;
            case 'users':
                await this.loadUsers();
                break;
        }
    }

    async loadDashboardData() {
        try {
            const response = await fetch('/api/admin/dashboard');
            const data = await response.json();
            
            if (data.success) {
                this.updateDashboardStats(data.stats);
                this.updateRecentArticles(data.recent_articles);
                this.updateRecentActivity(data.recent_activity);
            }
        } catch (error) {
            console.error('Error loading dashboard data:', error);
        }
    }

    updateDashboardStats(stats) {
        if (stats) {
            document.getElementById('totalArticles').textContent = stats.total_articles || 0;
            document.getElementById('liveMatches').textContent = stats.live_matches || 0;
            document.getElementById('totalUsers').textContent = stats.total_users || 0;
            document.getElementById('totalTeams').textContent = stats.total_teams || 0;
        }
    }

    updateRecentArticles(articles) {
        const tableBody = document.getElementById('recentArticlesTable');
        if (!tableBody) return;

        if (!articles || articles.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-8 text-gray-500">
                        No articles found
                    </td>
                </tr>
            `;
            return;
        }

        tableBody.innerHTML = articles.map(article => `
            <tr class="border-b border-gray-100 hover:bg-gray-50">
                <td class="py-4 px-4">
                    <div class="font-medium text-gray-900">${article.title}</div>
                    <div class="text-sm text-gray-500 line-clamp-2">${article.excerpt || 'No excerpt'}</div>
                </td>
                <td class="py-4 px-4 text-sm text-gray-600">${article.author_name || 'Admin'}</td>
                <td class="py-4 px-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${article.is_published ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">
                        ${article.is_published ? 'Published' : 'Draft'}
                    </span>
                </td>
                <td class="py-4 px-4 text-sm text-gray-600">${new Date(article.published_at).toLocaleDateString()}</td>
                <td class="py-4 px-4">
                    <div class="flex space-x-2">
                        <button onclick="editArticle(${article.id})" class="text-blue-600 hover:text-blue-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <button onclick="deleteArticle(${article.id})" class="text-red-600 hover:text-red-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    updateRecentActivity(activities) {
        const container = document.getElementById('recentActivity');
        if (!container) return;

        if (!activities || activities.length === 0) {
            container.innerHTML = `
                <div class="text-center py-4 text-gray-500">
                    No recent activity
                </div>
            `;
            return;
        }

        container.innerHTML = activities.map(activity => `
            <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">${activity.title}</p>
                    <p class="text-xs text-gray-600">${activity.description}</p>
                </div>
                <span class="text-xs text-gray-500">${activity.time_ago}</span>
            </div>
        `).join('');
    }

    async loadArticles() {
        try {
            const response = await fetch('/api/admin/articles');
            const data = await response.json();
            
            if (data.success) {
                this.displayArticles(data.articles);
            }
        } catch (error) {
            console.error('Error loading articles:', error);
        }
    }

    displayArticles(articles) {
        const container = document.getElementById('articles-container');
        if (!container) return;

        container.innerHTML = `
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-gray-900">Articles Management</h2>
                    <button onclick="createArticle()" class="bg-primary hover:bg-secondary text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                        Create New Article
                    </button>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left py-4 px-6 font-medium text-gray-600">Title</th>
                                <th class="text-left py-4 px-6 font-medium text-gray-600">Author</th>
                                <th class="text-left py-4 px-6 font-medium text-gray-600">Status</th>
                                <th class="text-left py-4 px-6 font-medium text-gray-600">Date</th>
                                <th class="text-left py-4 px-6 font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${articles && articles.length > 0 ? articles.map(article => `
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-4 px-6">
                                        <div class="font-medium text-gray-900">${article.title}</div>
                                        <div class="text-sm text-gray-500 line-clamp-2">${article.excerpt || 'No excerpt'}</div>
                                    </td>
                                    <td class="py-4 px-6 text-sm text-gray-600">${article.author_name || 'Admin'}</td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${article.is_published ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">
                                            ${article.is_published ? 'Published' : 'Draft'}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-sm text-gray-600">${new Date(article.published_at).toLocaleDateString()}</td>
                                    <td class="py-4 px-6">
                                        <div class="flex space-x-2">
                                            <button onclick="editArticle(${article.id})" class="text-blue-600 hover:text-blue-800">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            <button onclick="deleteArticle(${article.id})" class="text-red-600 hover:text-red-800">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `).join('') : `
                                <tr>
                                    <td colspan="5" class="text-center py-8 text-gray-500">
                                        No articles found
                                    </td>
                                </tr>
                            `}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    async loadMatches() {
        try {
            const response = await fetch('/api/admin/matches');
            const data = await response.json();
            
            if (data.success) {
                this.displayMatches(data.matches);
            }
        } catch (error) {
            console.error('Error loading matches:', error);
        }
    }

    displayMatches(matches) {
        const container = document.getElementById('matches-container');
        if (!container) return;

        container.innerHTML = `
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-gray-900">Matches Management</h2>
                    <button onclick="createMatch()" class="bg-primary hover:bg-secondary text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                        Add New Match
                    </button>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left py-4 px-6 font-medium text-gray-600">Home Team</th>
                                <th class="text-left py-4 px-6 font-medium text-gray-600">Away Team</th>
                                <th class="text-left py-4 px-6 font-medium text-gray-600">Score</th>
                                <th class="text-left py-4 px-6 font-medium text-gray-600">Status</th>
                                <th class="text-left py-4 px-6 font-medium text-gray-600">Date</th>
                                <th class="text-left py-4 px-6 font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${matches && matches.length > 0 ? matches.map(match => `
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-4 px-6 font-medium text-gray-900">${match.home_team_name || 'TBD'}</td>
                                    <td class="py-4 px-6 font-medium text-gray-900">${match.away_team_name || 'TBD'}</td>
                                    <td class="py-4 px-6">
                                        <span class="font-bold text-lg">${match.home_score || 0} - ${match.away_score || 0}</span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${match.status === 'live' ? 'bg-red-100 text-red-800' : match.status === 'finished' ? 'bg-gray-100 text-gray-800' : 'bg-blue-100 text-blue-800'}">
                                            ${match.status || 'Scheduled'}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-sm text-gray-600">${new Date(match.match_date).toLocaleDateString()}</td>
                                    <td class="py-4 px-6">
                                        <div class="flex space-x-2">
                                            <button onclick="editMatch(${match.id})" class="text-blue-600 hover:text-blue-800">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            <button onclick="deleteMatch(${match.id})" class="text-red-600 hover:text-red-800">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `).join('') : `
                                <tr>
                                    <td colspan="6" class="text-center py-8 text-gray-500">
                                        No matches found
                                    </td>
                                </tr>
                            `}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    async loadTeams() {
        try {
            const response = await fetch('/api/admin/teams');
            const data = await response.json();
            
            if (data.success) {
                this.displayTeams(data.teams);
            }
        } catch (error) {
            console.error('Error loading teams:', error);
        }
    }

    displayTeams(teams) {
        const container = document.getElementById('teams-container');
        if (!container) return;

        container.innerHTML = `
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-gray-900">Teams Management</h2>
                    <button onclick="createTeam()" class="bg-primary hover:bg-secondary text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                        Add New Team
                    </button>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left py-4 px-6 font-medium text-gray-600">Team Name</th>
                                <th class="text-left py-4 px-6 font-medium text-gray-600">League</th>
                                <th class="text-left py-4 px-6 font-medium text-gray-600">Country</th>
                                <th class="text-left py-4 px-6 font-medium text-gray-600">Founded</th>
                                <th class="text-left py-4 px-6 font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${teams && teams.length > 0 ? teams.map(team => `
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                                                <span class="text-xs font-bold text-gray-600">${team.name.charAt(0)}</span>
                                            </div>
                                            <div class="font-medium text-gray-900">${team.name}</div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-sm text-gray-600">${team.league_name || 'N/A'}</td>
                                    <td class="py-4 px-6 text-sm text-gray-600">${team.country || 'N/A'}</td>
                                    <td class="py-4 px-6 text-sm text-gray-600">${team.founded_year || 'N/A'}</td>
                                    <td class="py-4 px-6">
                                        <div class="flex space-x-2">
                                            <button onclick="editTeam(${team.id})" class="text-blue-600 hover:text-blue-800">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            <button onclick="deleteTeam(${team.id})" class="text-red-600 hover:text-red-800">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `).join('') : `
                                <tr>
                                    <td colspan="5" class="text-center py-8 text-gray-500">
                                        No teams found
                                    </td>
                                </tr>
                            `}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    async loadUsers() {
        try {
            const response = await fetch('/api/admin/users');
            const data = await response.json();
            
            if (data.success) {
                this.displayUsers(data.users);
            }
        } catch (error) {
            console.error('Error loading users:', error);
        }
    }

    displayUsers(users) {
        const container = document.getElementById('users-container');
        if (!container) return;

        container.innerHTML = `
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-gray-900">Users Management</h2>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left py-4 px-6 font-medium text-gray-600">User</th>
                                <th class="text-left py-4 px-6 font-medium text-gray-600">Email</th>
                                <th class="text-left py-4 px-6 font-medium text-gray-600">Role</th>
                                <th class="text-left py-4 px-6 font-medium text-gray-600">Status</th>
                                <th class="text-left py-4 px-6 font-medium text-gray-600">Joined</th>
                                <th class="text-left py-4 px-6 font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${users && users.length > 0 ? users.map(user => `
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 bg-gradient-to-r from-primary to-secondary rounded-full flex items-center justify-center">
                                                <span class="text-xs font-bold text-white">${user.username.charAt(0).toUpperCase()}</span>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900">${user.username}</div>
                                                <div class="text-sm text-gray-500">${user.first_name || ''} ${user.last_name || ''}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-sm text-gray-600">${user.email}</td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${user.is_admin ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'}">
                                            ${user.is_admin ? 'Admin' : 'User'}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                            ${user.is_active ? 'Active' : 'Inactive'}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-sm text-gray-600">${new Date(user.created_at).toLocaleDateString()}</td>
                                    <td class="py-4 px-6">
                                        <div class="flex space-x-2">
                                            <button onclick="toggleUserStatus(${user.id})" class="text-blue-600 hover:text-blue-800">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </button>
                                            <button onclick="deleteUser(${user.id})" class="text-red-600 hover:text-red-800">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `).join('') : `
                                <tr>
                                    <td colspan="6" class="text-center py-8 text-gray-500">
                                        No users found
                                    </td>
                                </tr>
                            `}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    updateTimeAndDate() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', { 
            hour12: false, 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        const dateString = now.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        const dateTimeElement = document.getElementById('currentDateTime');
        if (dateTimeElement) {
            dateTimeElement.textContent = `${timeString} • ${dateString}`;
        }
    }

    showError(message) {
        // You can implement a toast notification system here
        console.error(message);
        alert(message);
    }

    showSuccess(message) {
        // You can implement a toast notification system here
        console.log(message);
        alert(message);
    }
}

// Initialize the admin dashboard
let adminDashboard;

document.addEventListener('DOMContentLoaded', function() {
    adminDashboard = new AdminDashboard();
});

// Global functions for HTML onclick handlers
function showTab(tabName) {
    if (adminDashboard) {
        adminDashboard.showTab(tabName);
    }
}

function logout() {
    if (adminDashboard) {
        adminDashboard.handleLogout();
    }
}

// Placeholder functions for CRUD operations
function createArticle() {
    adminDashboard.showError('Create article functionality coming soon!');
}

function editArticle(id) {
    adminDashboard.showError(`Edit article ${id} functionality coming soon!`);
}

function deleteArticle(id) {
    if (confirm('Are you sure you want to delete this article?')) {
        adminDashboard.showError(`Delete article ${id} functionality coming soon!`);
    }
}

function createMatch() {
    adminDashboard.showError('Create match functionality coming soon!');
}

function editMatch(id) {
    adminDashboard.showError(`Edit match ${id} functionality coming soon!`);
}

function deleteMatch(id) {
    if (confirm('Are you sure you want to delete this match?')) {
        adminDashboard.showError(`Delete match ${id} functionality coming soon!`);
    }
}

function createTeam() {
    adminDashboard.showError('Create team functionality coming soon!');
}

function editTeam(id) {
    adminDashboard.showError(`Edit team ${id} functionality coming soon!`);
}

function deleteTeam(id) {
    if (confirm('Are you sure you want to delete this team?')) {
        adminDashboard.showError(`Delete team ${id} functionality coming soon!`);
    }
}

function toggleUserStatus(id) {
    adminDashboard.showError(`Toggle user status ${id} functionality coming soon!`);
}

function deleteUser(id) {
    if (confirm('Are you sure you want to delete this user?')) {
        adminDashboard.showError(`Delete user ${id} functionality coming soon!`);
    }
}
