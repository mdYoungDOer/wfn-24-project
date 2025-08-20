// CRUD Operations for Admin Dashboard
class AdminCRUD {
    constructor() {
        this.currentArticle = null;
        this.currentMatch = null;
        this.currentTeam = null;
        this.currentUser = null;
        this.categories = [];
        this.leagues = [];
        this.teams = [];
        this.init();
    }

    async init() {
        try {
            await this.loadCategories();
            await this.loadLeagues();
            await this.loadTeams();
        } catch (error) {
            console.error('Error initializing CRUD:', error);
        }
    }

    // Articles CRUD
    async createArticle() {
        try {
            const modal = this.createArticleModal();
            document.body.appendChild(modal);
            this.setupArticleForm(modal);
        } catch (error) {
            this.showError('Error creating article form: ' + error.message);
        }
    }

    async editArticle(id) {
        try {
            const response = await fetch(`/api/admin/articles/${id}`);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.currentArticle = data.data;
                const modal = this.createArticleModal();
                document.body.appendChild(modal);
                this.setupArticleForm(modal, data.data);
            } else {
                this.showError(data.error || 'Failed to load article');
            }
        } catch (error) {
            this.showError('Error loading article: ' + error.message);
        }
    }

    async deleteArticle(id) {
        if (confirm('Are you sure you want to delete this article?')) {
            try {
                const response = await fetch(`/api/admin/articles/${id}`, {
                    method: 'DELETE'
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    this.showSuccess('Article deleted successfully');
                    if (adminDashboard && typeof adminDashboard.loadArticles === 'function') {
                        adminDashboard.loadArticles();
                    }
                } else {
                    this.showError(data.error || 'Failed to delete article');
                }
            } catch (error) {
                this.showError('Error deleting article: ' + error.message);
            }
        }
    }

    // Matches CRUD
    async createMatch() {
        try {
            const modal = this.createMatchModal();
            document.body.appendChild(modal);
            this.setupMatchForm(modal);
        } catch (error) {
            this.showError('Error creating match form: ' + error.message);
        }
    }

    async editMatch(id) {
        try {
            const response = await fetch(`/api/admin/matches/${id}`);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.currentMatch = data.data;
                const modal = this.createMatchModal();
                document.body.appendChild(modal);
                this.setupMatchForm(modal, data.data);
            } else {
                this.showError(data.error || 'Failed to load match');
            }
        } catch (error) {
            this.showError('Error loading match: ' + error.message);
        }
    }

    async deleteMatch(id) {
        if (confirm('Are you sure you want to delete this match?')) {
            try {
                const response = await fetch(`/api/admin/matches/${id}`, {
                    method: 'DELETE'
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    this.showSuccess('Match deleted successfully');
                    if (adminDashboard && typeof adminDashboard.loadMatches === 'function') {
                        adminDashboard.loadMatches();
                    }
                } else {
                    this.showError(data.error || 'Failed to delete match');
                }
            } catch (error) {
                this.showError('Error deleting match: ' + error.message);
            }
        }
    }

    // Teams CRUD
    async createTeam() {
        try {
            const modal = this.createTeamModal();
            document.body.appendChild(modal);
            this.setupTeamForm(modal);
        } catch (error) {
            this.showError('Error creating team form: ' + error.message);
        }
    }

    async editTeam(id) {
        try {
            const response = await fetch(`/api/admin/teams/${id}`);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.currentTeam = data.data;
                const modal = this.createTeamModal();
                document.body.appendChild(modal);
                this.setupTeamForm(modal, data.data);
            } else {
                this.showError(data.error || 'Failed to load team');
            }
        } catch (error) {
            this.showError('Error loading team: ' + error.message);
        }
    }

    async deleteTeam(id) {
        if (confirm('Are you sure you want to delete this team?')) {
            try {
                const response = await fetch(`/api/admin/teams/${id}`, {
                    method: 'DELETE'
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    this.showSuccess('Team deleted successfully');
                    if (adminDashboard && typeof adminDashboard.loadTeams === 'function') {
                        adminDashboard.loadTeams();
                    }
                } else {
                    this.showError(data.error || 'Failed to delete team');
                }
            } catch (error) {
                this.showError('Error deleting team: ' + error.message);
            }
        }
    }

    // Form Setup Methods
    setupArticleForm(modal, article = null) {
        const form = modal.querySelector('#articleForm');
        const title = modal.querySelector('#articleModalTitle');
        const titleInput = modal.querySelector('#articleTitle');
        const excerptInput = modal.querySelector('#articleExcerpt');
        const categorySelect = modal.querySelector('#articleCategory');
        const contentInput = modal.querySelector('#articleContent');
        const imageUrlInput = modal.querySelector('#articleImageUrl');
        const publishedCheckbox = modal.querySelector('#articlePublished');
        const featuredCheckbox = modal.querySelector('#articleFeatured');

        // Populate form if editing
        if (article) {
            title.textContent = 'Edit Article';
            titleInput.value = article.title || '';
            excerptInput.value = article.excerpt || '';
            contentInput.value = article.content || '';
            imageUrlInput.value = article.featured_image || '';
            publishedCheckbox.checked = article.is_published || false;
            featuredCheckbox.checked = article.is_featured || false;
            
            if (article.category_id) {
                categorySelect.value = article.category_id;
            }
        } else {
            title.textContent = 'Create New Article';
        }

        // Setup form submission
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.saveArticle(form, article?.id);
        });
    }

    setupMatchForm(modal, match = null) {
        const form = modal.querySelector('#matchForm');
        const title = modal.querySelector('#matchModalTitle');
        const leagueSelect = modal.querySelector('#matchLeague');
        const homeTeamSelect = modal.querySelector('#matchHomeTeam');
        const awayTeamSelect = modal.querySelector('#matchAwayTeam');
        const dateInput = modal.querySelector('#matchDate');
        const statusSelect = modal.querySelector('#matchStatus');
        const homeScoreInput = modal.querySelector('#matchHomeScore');
        const awayScoreInput = modal.querySelector('#matchAwayScore');
        const venueInput = modal.querySelector('#matchVenue');
        const refereeInput = modal.querySelector('#matchReferee');

        // Populate form if editing
        if (match) {
            title.textContent = 'Edit Match';
            if (match.league_id) leagueSelect.value = match.league_id;
            if (match.home_team_id) homeTeamSelect.value = match.home_team_id;
            if (match.away_team_id) awayTeamSelect.value = match.away_team_id;
            if (match.match_date) dateInput.value = match.match_date;
            if (match.status) statusSelect.value = match.status;
            if (match.home_score !== null) homeScoreInput.value = match.home_score;
            if (match.away_score !== null) awayScoreInput.value = match.away_score;
            venueInput.value = match.venue || '';
            refereeInput.value = match.referee || '';
        } else {
            title.textContent = 'Create New Match';
        }

        // Setup form submission
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.saveMatch(form, match?.id);
        });
    }

    setupTeamForm(modal, team = null) {
        const form = modal.querySelector('#teamForm');
        const title = modal.querySelector('#teamModalTitle');
        const nameInput = modal.querySelector('#teamName');
        const leagueSelect = modal.querySelector('#teamLeague');
        const countryInput = modal.querySelector('#teamCountry');
        const cityInput = modal.querySelector('#teamCity');
        const stadiumInput = modal.querySelector('#teamStadium');
        const foundedInput = modal.querySelector('#teamFounded');
        const logoInput = modal.querySelector('#teamLogo');

        // Populate form if editing
        if (team) {
            title.textContent = 'Edit Team';
            nameInput.value = team.name || '';
            if (team.league_id) leagueSelect.value = team.league_id;
            countryInput.value = team.country || '';
            cityInput.value = team.city || '';
            stadiumInput.value = team.stadium || '';
            if (team.founded_year) foundedInput.value = team.founded_year;
            logoInput.value = team.logo_url || '';
        } else {
            title.textContent = 'Create New Team';
        }

        // Setup form submission
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.saveTeam(form, team?.id);
        });
    }

    // Save Methods
    async saveArticle(form, articleId = null) {
        const formData = new FormData(form);
        const data = {
            title: formData.get('title'),
            excerpt: formData.get('excerpt'),
            content: formData.get('content'),
            category_id: formData.get('category_id') ? parseInt(formData.get('category_id')) : 1,
            featured_image: formData.get('featured_image'),
            is_published: formData.get('is_published') === 'on',
            is_featured: formData.get('is_featured') === 'on'
        };

        try {
            const url = articleId ? `/api/admin/articles/${articleId}` : '/api/admin/articles';
            const method = articleId ? 'PUT' : 'POST';
            
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            
            if (result.success) {
                this.showSuccess(articleId ? 'Article updated successfully' : 'Article created successfully');
                form.closest('.fixed').remove();
                if (adminDashboard && typeof adminDashboard.loadArticles === 'function') {
                    adminDashboard.loadArticles();
                }
            } else {
                this.showError(result.error || 'Failed to save article');
            }
        } catch (error) {
            this.showError('Error saving article: ' + error.message);
        }
    }

    async saveMatch(form, matchId = null) {
        const formData = new FormData(form);
        const data = {
            league_id: formData.get('league_id') ? parseInt(formData.get('league_id')) : null,
            home_team_id: formData.get('home_team_id') ? parseInt(formData.get('home_team_id')) : null,
            away_team_id: formData.get('away_team_id') ? parseInt(formData.get('away_team_id')) : null,
            match_date: formData.get('match_date'),
            status: formData.get('status'),
            home_score: formData.get('home_score') ? parseInt(formData.get('home_score')) : null,
            away_score: formData.get('away_score') ? parseInt(formData.get('away_score')) : null,
            venue: formData.get('venue'),
            referee: formData.get('referee'),
            attendance: formData.get('attendance') ? parseInt(formData.get('attendance')) : null
        };

        try {
            const url = matchId ? `/api/admin/matches/${matchId}` : '/api/admin/matches';
            const method = matchId ? 'PUT' : 'POST';
            
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            
            if (result.success) {
                this.showSuccess(matchId ? 'Match updated successfully' : 'Match created successfully');
                form.closest('.fixed').remove();
                if (adminDashboard && typeof adminDashboard.loadMatches === 'function') {
                    adminDashboard.loadMatches();
                }
            } else {
                this.showError(result.error || 'Failed to save match');
            }
        } catch (error) {
            this.showError('Error saving match: ' + error.message);
        }
    }

    async saveTeam(form, teamId = null) {
        const formData = new FormData(form);
        const data = {
            name: formData.get('name'),
            league_id: formData.get('league_id') ? parseInt(formData.get('league_id')) : null,
            country: formData.get('country'),
            city: formData.get('city'),
            stadium: formData.get('stadium'),
            founded_year: formData.get('founded_year') ? parseInt(formData.get('founded_year')) : null,
            logo_url: formData.get('logo_url')
        };

        try {
            const url = teamId ? `/api/admin/teams/${teamId}` : '/api/admin/teams';
            const method = teamId ? 'PUT' : 'POST';
            
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            
            if (result.success) {
                this.showSuccess(teamId ? 'Team updated successfully' : 'Team created successfully');
                form.closest('.fixed').remove();
                if (adminDashboard && typeof adminDashboard.loadTeams === 'function') {
                    adminDashboard.loadTeams();
                }
            } else {
                this.showError(result.error || 'Failed to save team');
            }
        } catch (error) {
            this.showError('Error saving team: ' + error.message);
        }
    }

    // Utility Methods
    async loadCategories() {
        try {
            const response = await fetch('/api/admin/categories');
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            const data = await response.json();
            if (data.success) {
                this.categories = data.categories || [];
            }
        } catch (error) {
            console.error('Error loading categories:', error);
            this.categories = [];
        }
    }

    async loadLeagues() {
        try {
            const response = await fetch('/api/admin/leagues');
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            const data = await response.json();
            if (data.success) {
                this.leagues = data.leagues || [];
            }
        } catch (error) {
            console.error('Error loading leagues:', error);
            this.leagues = [];
        }
    }

    async loadTeams() {
        try {
            const response = await fetch('/api/admin/teams');
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            const data = await response.json();
            if (data.success) {
                this.teams = data.teams || [];
            }
        } catch (error) {
            console.error('Error loading teams:', error);
            this.teams = [];
        }
    }

    // Category Management Methods
    async createCategory() {
        const modal = this.createCategoryModal();
        document.body.appendChild(modal);
        this.setupCategoryForm(modal);
    }

    createCategoryModal() {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Create New Category</h2>
                    <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form id="categoryForm" class="p-6 space-y-6">
                    <div>
                        <label for="categoryName" class="block text-sm font-medium text-gray-700 mb-2">Category Name *</label>
                        <input type="text" id="categoryName" name="name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="Enter category name">
                    </div>
                    <div>
                        <label for="categoryDescription" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea id="categoryDescription" name="description" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="Enter category description"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="categoryColor" class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                            <input type="color" id="categoryColor" name="color" value="#e41e5b" class="w-full h-12 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label for="categoryIcon" class="block text-sm font-medium text-gray-700 mb-2">Icon</label>
                            <select id="categoryIcon" name="icon" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="folder">📁 Folder</option>
                                <option value="newspaper">📰 Newspaper</option>
                                <option value="trophy">🏆 Trophy</option>
                                <option value="star">⭐ Star</option>
                                <option value="fire">🔥 Fire</option>
                                <option value="heart">❤️ Heart</option>
                                <option value="bolt">⚡ Bolt</option>
                                <option value="flag">🏁 Flag</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="categoryActive" name="is_active" checked class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                        <label for="categoryActive" class="ml-2 text-sm text-gray-700">Active</label>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="this.closest('.fixed').remove()" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors duration-200">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-gradient-to-r from-primary to-secondary text-white rounded-lg hover:opacity-90 transition-opacity duration-200">Create Category</button>
                    </div>
                </form>
            </div>
        `;
        return modal;
    }

    setupCategoryForm(modal) {
        const form = modal.querySelector('#categoryForm');
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.saveCategory(form);
        });
    }

    async saveCategory(form, categoryId = null) {
        const formData = new FormData(form);
        const data = {
            name: formData.get('name'),
            description: formData.get('description'),
            color: formData.get('color'),
            icon: formData.get('icon'),
            is_active: formData.get('is_active') === 'on'
        };

        try {
            const url = categoryId ? `/api/admin/categories/${categoryId}` : '/api/admin/categories';
            const method = categoryId ? 'PUT' : 'POST';
            
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            
            if (result.success) {
                this.showSuccess(categoryId ? 'Category updated successfully' : 'Category created successfully');
                form.closest('.fixed').remove();
                if (adminDashboard && typeof adminDashboard.loadCategories === 'function') {
                    adminDashboard.loadCategories();
                }
            } else {
                this.showError(result.error || 'Failed to save category');
            }
        } catch (error) {
            this.showError('Error saving category: ' + error.message);
        }
    }

    async editCategory(id) {
        try {
            const response = await fetch(`/api/admin/categories/${id}`);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            const data = await response.json();
            
            if (data.success) {
                const category = data.data;
                const modal = this.createCategoryModal();
                modal.querySelector('h2').textContent = 'Edit Category';
                modal.querySelector('button[type="submit"]').textContent = 'Update Category';
                
                // Populate form fields
                modal.querySelector('#categoryName').value = category.name;
                modal.querySelector('#categoryDescription').value = category.description || '';
                modal.querySelector('#categoryColor').value = category.color || '#e41e5b';
                modal.querySelector('#categoryIcon').value = category.icon || 'folder';
                modal.querySelector('#categoryActive').checked = category.is_active;
                
                document.body.appendChild(modal);
                this.setupCategoryForm(modal, id);
            } else {
                this.showError(data.error || 'Failed to load category');
            }
        } catch (error) {
            this.showError('Error loading category: ' + error.message);
        }
    }

    async deleteCategory(id) {
        if (!confirm('Are you sure you want to delete this category? This action cannot be undone.')) {
            return;
        }

        try {
            const response = await fetch(`/api/admin/categories/${id}`, {
                method: 'DELETE'
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            
            if (result.success) {
                this.showSuccess('Category deleted successfully');
                if (adminDashboard && typeof adminDashboard.loadCategories === 'function') {
                    adminDashboard.loadCategories();
                }
            } else {
                this.showError(result.error || 'Failed to delete category');
            }
        } catch (error) {
            this.showError('Error deleting category: ' + error.message);
        }
    }

    handleImageUpload(input) {
        const file = input.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('image', file);
        formData.append('content_type', 'articles');

        fetch('/api/admin/upload', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('articleImageUrl').value = data.data.url;
                this.showImagePreview(data.data.url);
            } else {
                this.showError('Upload failed: ' + data.error);
            }
        })
        .catch(error => {
            this.showError('Upload error: ' + error.message);
        });
    }

    showImagePreview(url) {
        const preview = document.getElementById('imagePreview');
        const img = document.getElementById('previewImg');
        if (preview && img) {
            img.src = url;
            preview.classList.remove('hidden');
        }
    }

    showSuccess(message) {
        // Create a toast notification
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    showError(message) {
        // Create a toast notification
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 5000);
    }

    // Modal Creation Methods
    createArticleModal() {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-900" id="articleModalTitle">Create New Article</h3>
                        <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <form id="articleForm" class="p-6 space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                                <input type="text" id="articleTitle" name="title" required 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Excerpt</label>
                                <textarea id="articleExcerpt" name="excerpt" rows="3"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                                <select id="articleCategory" name="category_id" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">Select Category</option>
                                    ${this.categories.map(cat => `<option value="${cat.id}">${cat.name}</option>`).join('')}
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Featured Image</label>
                                <div class="flex items-center space-x-4">
                                    <input type="file" id="articleImage" name="image" accept="image/*" 
                                           class="hidden" onchange="adminCRUD.handleImageUpload(this)">
                                    <button type="button" onclick="document.getElementById('articleImage').click()"
                                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                        Choose Image
                                    </button>
                                    <div id="imagePreview" class="hidden">
                                        <img id="previewImg" class="w-16 h-16 object-cover rounded-lg" src="" alt="Preview">
                                    </div>
                                </div>
                                <input type="hidden" id="articleImageUrl" name="featured_image">
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Content *</label>
                                <textarea id="articleContent" name="content" rows="10" required 
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" 
                                          placeholder="Enter article content here..."></textarea>
                            </div>
                            <div class="flex space-x-4">
                                <label class="flex items-center">
                                    <input type="checkbox" id="articlePublished" name="is_published" class="mr-2">
                                    <span class="text-sm text-gray-700">Published</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" id="articleFeatured" name="is_featured" class="mr-2">
                                    <span class="text-sm text-gray-700">Featured</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                        <button type="button" onclick="this.closest('.fixed').remove()" 
                                class="px-6 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-6 py-2 bg-gradient-to-r from-primary to-secondary text-white rounded-lg hover:opacity-90 transition-opacity">
                            Save Article
                        </button>
                    </div>
                </form>
            </div>
        `;
        return modal;
    }

    createMatchModal() {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-900" id="matchModalTitle">Create New Match</h3>
                        <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <form id="matchForm" class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">League</label>
                            <select id="matchLeague" name="league_id" required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select League</option>
                                ${this.leagues.map(league => `<option value="${league.id}">${league.name}</option>`).join('')}
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Match Date</label>
                            <input type="datetime-local" id="matchDate" name="match_date" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Home Team</label>
                            <select id="matchHomeTeam" name="home_team_id" required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select Home Team</option>
                                ${this.teams.map(team => `<option value="${team.id}">${team.name}</option>`).join('')}
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Away Team</label>
                            <select id="matchAwayTeam" name="away_team_id" required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select Away Team</option>
                                ${this.teams.map(team => `<option value="${team.id}">${team.name}</option>`).join('')}
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select id="matchStatus" name="status" required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="SCHEDULED">Scheduled</option>
                                <option value="LIVE">Live</option>
                                <option value="FINISHED">Finished</option>
                                <option value="CANCELLED">Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Venue</label>
                            <input type="text" id="matchVenue" name="venue" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Home Score</label>
                            <input type="number" id="matchHomeScore" name="home_score" min="0" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Away Score</label>
                            <input type="number" id="matchAwayScore" name="away_score" min="0" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Referee</label>
                            <input type="text" id="matchReferee" name="referee" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>
                    <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                        <button type="button" onclick="this.closest('.fixed').remove()" 
                                class="px-6 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-6 py-2 bg-gradient-to-r from-primary to-secondary text-white rounded-lg hover:opacity-90 transition-opacity">
                            Save Match
                        </button>
                    </div>
                </form>
            </div>
        `;
        return modal;
    }

    createTeamModal() {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-900" id="teamModalTitle">Create New Team</h3>
                        <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <form id="teamForm" class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Team Name *</label>
                            <input type="text" id="teamName" name="name" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">League</label>
                            <select id="teamLeague" name="league_id" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select League</option>
                                ${this.leagues.map(league => `<option value="${league.id}">${league.name}</option>`).join('')}
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                            <input type="text" id="teamCountry" name="country" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                            <input type="text" id="teamCity" name="city" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Stadium</label>
                            <input type="text" id="teamStadium" name="stadium" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Founded Year</label>
                            <input type="number" id="teamFounded" name="founded_year" min="1800" max="2024" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Logo URL</label>
                            <input type="url" id="teamLogo" name="logo_url" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="https://example.com/logo.png">
                        </div>
                    </div>
                    <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                        <button type="button" onclick="this.closest('.fixed').remove()" 
                                class="px-6 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-6 py-2 bg-gradient-to-r from-primary to-secondary text-white rounded-lg hover:opacity-90 transition-opacity">
                            Save Team
                        </button>
                    </div>
                </form>
            </div>
        `;
        return modal;
    }
}

// Initialize CRUD operations
let adminCRUD;
document.addEventListener('DOMContentLoaded', function() {
    adminCRUD = new AdminCRUD();
});

// Global functions for CRUD operations
function createArticle() {
    if (adminCRUD) {
        adminCRUD.createArticle();
    }
}

function editArticle(id) {
    if (adminCRUD) {
        adminCRUD.editArticle(id);
    }
}

function deleteArticle(id) {
    if (adminCRUD) {
        adminCRUD.deleteArticle(id);
    }
}

function createMatch() {
    if (adminCRUD) {
        adminCRUD.createMatch();
    }
}

function editMatch(id) {
    if (adminCRUD) {
        adminCRUD.editMatch(id);
    }
}

function deleteMatch(id) {
    if (adminCRUD) {
        adminCRUD.deleteMatch(id);
    }
}

function createTeam() {
    if (adminCRUD) {
        adminCRUD.createTeam();
    }
}

function editTeam(id) {
    if (adminCRUD) {
        adminCRUD.editTeam(id);
    }
}

function deleteTeam(id) {
    if (adminCRUD) {
        adminCRUD.deleteTeam(id);
    }
}

// Category CRUD functions
function createCategory() {
    if (adminCRUD) {
        adminCRUD.createCategory();
    }
}

function editCategory(id) {
    if (adminCRUD) {
        adminCRUD.editCategory(id);
    }
}

function deleteCategory(id) {
    if (adminCRUD) {
        adminCRUD.deleteCategory(id);
    }
}
