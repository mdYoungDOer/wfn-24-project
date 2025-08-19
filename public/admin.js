// Admin Dashboard JavaScript
class AdminDashboard {
    constructor() {
        this.currentTab = 'dashboard';
        this.currentPage = 1;
        this.searchTerm = '';
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.checkAuthStatus();
        this.loadDashboardData();
        this.updateTimeAndDate();
        setInterval(() => this.updateTimeAndDate(), 1000);
    }

    setupEventListeners() {
        // Tab switching
        document.querySelectorAll('.nav-link, .sidebar-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const tab = e.currentTarget.getAttribute('onclick')?.match(/showTab\('(.+?)'\)/)?.[1];
                if (tab) this.showTab(tab);
            });
        });

        // Login form
        const loginForm = document.getElementById('adminLoginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => this.handleLogin(e));
        }

        // Logout
        const logoutBtn = document.querySelector('[onclick="logout()"]');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', (e) => this.handleLogout(e));
        }
    }

    async checkAuthStatus() {
        try {
            const response = await fetch('/auth/user');
            const data = await response.json();
            
            if (data.user && data.user.is_admin) {
                this.showDashboard();
                this.loadUserInfo(data.user);
            } else {
                this.showLoginForm();
            }
        } catch (error) {
            console.error('Auth check failed:', error);
            this.showLoginForm();
        }
    }

    showLoginForm() {
        document.getElementById('loginForm').classList.remove('hidden');
        document.getElementById('dashboard').classList.add('hidden');
    }

    showDashboard() {
        document.getElementById('loginForm').classList.add('hidden');
        document.getElementById('dashboard').classList.remove('hidden');
        document.getElementById('userInfo').classList.remove('hidden');
    }

    loadUserInfo(user) {
        const initials = user.username ? user.username.charAt(0).toUpperCase() : 'A';
        document.getElementById('userInitials').textContent = initials;
        document.getElementById('userName').textContent = user.username || 'Admin User';
        document.getElementById('userRole').textContent = user.is_admin ? 'Administrator' : 'User';
    }

    async handleLogin(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const email = formData.get('email');
        const password = formData.get('password');

        try {
            const response = await fetch('/auth/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });

            const data = await response.json();
            if (data.success) {
                this.checkAuthStatus();
            } else {
                this.showError(data.message || 'Login failed');
            }
        } catch (error) {
            this.showError('Login failed. Please try again.');
        }
    }

    async handleLogout(e) {
        e.preventDefault();
        try {
            await fetch('/auth/logout', { method: 'POST' });
            this.showLoginForm();
        } catch (error) {
            console.error('Logout failed:', error);
        }
    }

    showTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });

        // Show selected tab
        const selectedTab = document.getElementById(`${tabName}-tab`);
        if (selectedTab) {
            selectedTab.classList.add('active');
        }

        // Update navigation
        document.querySelectorAll('.nav-link, .sidebar-link').forEach(link => {
            link.classList.remove('text-primary');
            link.classList.add('text-gray-700');
        });

        // Highlight active tab
        const activeLink = document.querySelector(`[onclick="showTab('${tabName}')"]`);
        if (activeLink) {
            activeLink.classList.remove('text-gray-700');
            activeLink.classList.add('text-primary');
        }

        this.currentTab = tabName;
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
                this.updateDashboardStats(data.data);
                this.updateRecentArticles(data.data.recent_articles);
                this.updateRecentActivity(data.data.recent_activity);
            }
        } catch (error) {
            console.error('Failed to load dashboard data:', error);
        }
    }

    updateDashboardStats(stats) {
        document.getElementById('totalArticles').textContent = stats.total_articles || 0;
        document.getElementById('liveMatches').textContent = stats.live_matches || 0;
        document.getElementById('totalUsers').textContent = stats.total_users || 0;
        document.getElementById('totalTeams').textContent = stats.total_teams || 0;
    }

    updateRecentArticles(articles) {
        const container = document.getElementById('recentArticlesTable');
        if (!container || !articles) return;

        container.innerHTML = articles.map(article => `
            <tr class="border-b border-gray-100 hover:bg-gray-50">
                <td class="py-3 px-4">
                    <div class="font-medium text-gray-900">${article.title}</div>
                    <div class="text-sm text-gray-500">${article.excerpt || ''}</div>
                </td>
                <td class="py-3 px-4 text-sm text-gray-600">${article.author_name || 'Admin'}</td>
                <td class="py-3 px-4">
                    <span class="px-2 py-1 text-xs rounded-full ${article.is_published ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">
                        ${article.is_published ? 'Published' : 'Draft'}
                    </span>
                </td>
                <td class="py-3 px-4 text-sm text-gray-500">${new Date(article.created_at).toLocaleDateString()}</td>
                <td class="py-3 px-4">
                    <div class="flex space-x-2">
                        <button onclick="adminDashboard.editArticle(${article.id})" class="text-blue-600 hover:text-blue-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <button onclick="adminDashboard.deleteArticle(${article.id})" class="text-red-600 hover:text-red-800">
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
        if (!container || !activities) return;

        container.innerHTML = activities.map(activity => `
            <div class="flex items-start space-x-3">
                <div class="w-2 h-2 bg-primary rounded-full mt-2"></div>
                <div class="flex-1">
                    <p class="text-sm text-gray-900">${activity.action}</p>
                    <p class="text-xs text-gray-500">${activity.description}</p>
                    <p class="text-xs text-gray-400">${new Date(activity.created_at).toLocaleString()}</p>
                </div>
            </div>
        `).join('');
    }

    async loadArticles(page = 1, search = '') {
        try {
            const response = await fetch(`/api/admin/articles?page=${page}&search=${search}`);
            const data = await response.json();
            
            if (data.success) {
                this.displayArticles(data.data);
            }
        } catch (error) {
            console.error('Failed to load articles:', error);
        }
    }

    displayArticles(data) {
        const container = document.getElementById('articles-container');
        if (!container) return;

        container.innerHTML = `
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Articles</h2>
                <button onclick="adminDashboard.openCreateModal('article')" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary">
                    Create Article
                </button>
            </div>
            <div class="bg-white rounded-lg shadow">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Author</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            ${data.articles.map(article => `
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">${article.title}</div>
                                        <div class="text-sm text-gray-500">${article.excerpt || ''}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">${article.author_name || 'Admin'}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs rounded-full ${article.is_published ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">
                                            ${article.is_published ? 'Published' : 'Draft'}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">${new Date(article.created_at).toLocaleDateString()}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex space-x-2">
                                            <button onclick="adminDashboard.editArticle(${article.id})" class="text-blue-600 hover:text-blue-800">Edit</button>
                                            <button onclick="adminDashboard.deleteArticle(${article.id})" class="text-red-600 hover:text-red-800">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    async loadMatches(page = 1, status = '') {
        try {
            const response = await fetch(`/api/admin/matches?page=${page}&status=${status}`);
            const data = await response.json();
            
            if (data.success) {
                this.displayMatches(data.data);
            }
        } catch (error) {
            console.error('Failed to load matches:', error);
        }
    }

    displayMatches(data) {
        const container = document.getElementById('matches-container');
        if (!container) return;

        container.innerHTML = `
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Matches</h2>
                <button onclick="adminDashboard.openCreateModal('match')" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary">
                    Create Match
                </button>
            </div>
            <div class="bg-white rounded-lg shadow">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teams</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">League</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Score</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            ${data.matches.map(match => `
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">${match.home_team_name} vs ${match.away_team_name}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">${match.league_name || 'Unknown'}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">${new Date(match.match_date).toLocaleDateString()}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs rounded-full ${match.status === 'live' ? 'bg-red-100 text-red-800' : match.status === 'finished' ? 'bg-gray-100 text-gray-800' : 'bg-blue-100 text-blue-800'}">
                                            ${match.status}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">${match.home_score || 0} - ${match.away_score || 0}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex space-x-2">
                                            <button onclick="adminDashboard.editMatch(${match.id})" class="text-blue-600 hover:text-blue-800">Edit</button>
                                            <button onclick="adminDashboard.deleteMatch(${match.id})" class="text-red-600 hover:text-red-800">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    async loadTeams(page = 1, search = '') {
        try {
            const response = await fetch(`/api/admin/teams?page=${page}&search=${search}`);
            const data = await response.json();
            
            if (data.success) {
                this.displayTeams(data.data);
            }
        } catch (error) {
            console.error('Failed to load teams:', error);
        }
    }

    displayTeams(data) {
        const container = document.getElementById('teams-container');
        if (!container) return;

        container.innerHTML = `
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Teams</h2>
                <button onclick="adminDashboard.openCreateModal('team')" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary">
                    Create Team
                </button>
            </div>
            <div class="bg-white rounded-lg shadow">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Team</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">League</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Country</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stadium</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            ${data.teams.map(team => `
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <img src="${team.logo_url || '/placeholder-team.png'}" alt="${team.name}" class="w-8 h-8 rounded-full mr-3">
                                            <div>
                                                <div class="font-medium text-gray-900">${team.name}</div>
                                                <div class="text-sm text-gray-500">${team.short_name}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">${team.league_name || 'Unknown'}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">${team.country || 'Unknown'}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">${team.stadium || 'Unknown'}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex space-x-2">
                                            <button onclick="adminDashboard.editTeam(${team.id})" class="text-blue-600 hover:text-blue-800">Edit</button>
                                            <button onclick="adminDashboard.deleteTeam(${team.id})" class="text-red-600 hover:text-red-800">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    async loadUsers(page = 1, search = '') {
        try {
            const response = await fetch(`/api/admin/users?page=${page}&search=${search}`);
            const data = await response.json();
            
            if (data.success) {
                this.displayUsers(data.data);
            }
        } catch (error) {
            console.error('Failed to load users:', error);
        }
    }

    displayUsers(data) {
        const container = document.getElementById('users-container');
        if (!container) return;

        container.innerHTML = `
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Users</h2>
            </div>
            <div class="bg-white rounded-lg shadow">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            ${data.users.map(user => `
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white font-bold">
                                                ${user.username ? user.username.charAt(0).toUpperCase() : 'U'}
                                            </div>
                                            <div class="ml-3">
                                                <div class="font-medium text-gray-900">${user.username}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">${user.email}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs rounded-full ${user.is_admin ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'}">
                                            ${user.is_admin ? 'Admin' : 'User'}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs rounded-full ${user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                            ${user.is_active ? 'Active' : 'Inactive'}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">${new Date(user.created_at).toLocaleDateString()}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex space-x-2">
                                            <button onclick="adminDashboard.toggleUserStatus(${user.id}, ${!user.is_active})" class="text-blue-600 hover:text-blue-800">
                                                ${user.is_active ? 'Deactivate' : 'Activate'}
                                            </button>
                                            <button onclick="adminDashboard.deleteUser(${user.id})" class="text-red-600 hover:text-red-800">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    updateTimeAndDate() {
        const now = new Date();
        const timeElement = document.getElementById('currentDateTime');
        const dateElement = document.getElementById('currentDate');
        
        if (timeElement) {
            timeElement.textContent = now.toLocaleTimeString();
        }
        if (dateElement) {
            dateElement.textContent = now.toLocaleDateString();
        }
    }

    showError(message) {
        const errorElement = document.getElementById('loginError');
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.classList.remove('hidden');
        }
    }

    // CRUD Operations
    async createArticle(data) {
        try {
            const response = await fetch('/api/admin/articles', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (result.success) {
                this.loadArticles();
                this.showSuccess('Article created successfully');
            } else {
                this.showError(result.error);
            }
        } catch (error) {
            this.showError('Failed to create article');
        }
    }

    async editArticle(id) {
        // Implementation for editing article
        console.log('Edit article:', id);
    }

    async deleteArticle(id) {
        if (confirm('Are you sure you want to delete this article?')) {
            try {
                const response = await fetch(`/api/admin/articles/${id}`, {
                    method: 'DELETE'
                });
                const result = await response.json();
                if (result.success) {
                    this.loadArticles();
                    this.showSuccess('Article deleted successfully');
                } else {
                    this.showError(result.error);
                }
            } catch (error) {
                this.showError('Failed to delete article');
            }
        }
    }

    async toggleUserStatus(id, status) {
        try {
            const response = await fetch(`/api/admin/users/${id}/status`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ status: status ? 'active' : 'inactive' })
            });
            const result = await response.json();
            if (result.success) {
                this.loadUsers();
                this.showSuccess('User status updated successfully');
            } else {
                this.showError(result.error);
            }
        } catch (error) {
            this.showError('Failed to update user status');
        }
    }

    showSuccess(message) {
        // Implementation for success notifications
        console.log('Success:', message);
    }

    openCreateModal(type) {
        // Implementation for opening create modals
        console.log('Open create modal for:', type);
    }
}

// Initialize admin dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.adminDashboard = new AdminDashboard();
});

// Global functions for onclick handlers
function showTab(tabName) {
    if (window.adminDashboard) {
        window.adminDashboard.showTab(tabName);
    }
}

function logout() {
    if (window.adminDashboard) {
        window.adminDashboard.handleLogout();
    }
}
