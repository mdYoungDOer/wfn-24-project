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
        await this.loadCategories();
        await this.loadLeagues();
        await this.loadTeams();
    }

    // Articles CRUD
    async createArticle() {
        const modal = this.createArticleModal();
        document.body.appendChild(modal);
        this.setupArticleForm(modal);
    }

    async editArticle(id) {
        try {
            const response = await fetch(`/api/admin/articles/${id}`);
            const data = await response.json();
            
            if (data.success) {
                this.currentArticle = data.data;
                const modal = this.createArticleModal();
                document.body.appendChild(modal);
                this.setupArticleForm(modal, data.data);
            } else {
                this.showError('Failed to load article');
            }
        } catch (error) {
            this.showError('Error loading article');
        }
    }

    async deleteArticle(id) {
        if (confirm('Are you sure you want to delete this article?')) {
            try {
                const response = await fetch(`/api/admin/articles/${id}`, {
                    method: 'DELETE'
                });
                const data = await response.json();
                
                if (data.success) {
                    this.showSuccess('Article deleted successfully');
                    adminDashboard.loadArticles();
                } else {
                    this.showError(data.error || 'Failed to delete article');
                }
            } catch (error) {
                this.showError('Error deleting article');
            }
        }
    }

    // Matches CRUD
    async createMatch() {
        const modal = this.createMatchModal();
        document.body.appendChild(modal);
        this.setupMatchForm(modal);
    }

    async editMatch(id) {
        try {
            const response = await fetch(`/api/admin/matches/${id}`);
            const data = await response.json();
            
            if (data.success) {
                this.currentMatch = data.data;
                const modal = this.createMatchModal();
                document.body.appendChild(modal);
                this.setupMatchForm(modal, data.data);
            } else {
                this.showError('Failed to load match');
            }
        } catch (error) {
            this.showError('Error loading match');
        }
    }

    async deleteMatch(id) {
        if (confirm('Are you sure you want to delete this match?')) {
            try {
                const response = await fetch(`/api/admin/matches/${id}`, {
                    method: 'DELETE'
                });
                const data = await response.json();
                
                if (data.success) {
                    this.showSuccess('Match deleted successfully');
                    adminDashboard.loadMatches();
                } else {
                    this.showError(data.error || 'Failed to delete match');
                }
            } catch (error) {
                this.showError('Error deleting match');
            }
        }
    }

    createMatchModal() {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
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
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">League *</label>
                                <select id="matchLeague" name="league_id" required 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">Select League</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Home Team *</label>
                                <select id="matchHomeTeam" name="home_team_id" required 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">Select Home Team</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Away Team *</label>
                                <select id="matchAwayTeam" name="away_team_id" required 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">Select Away Team</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Match Date & Time *</label>
                                <input type="datetime-local" id="matchDate" name="match_date" required 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select id="matchStatus" name="status" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="scheduled">Scheduled</option>
                                    <option value="live">Live</option>
                                    <option value="finished">Finished</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="postponed">Postponed</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
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
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Venue</label>
                                <input type="text" id="matchVenue" name="venue" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Referee</label>
                                <input type="text" id="matchReferee" name="referee" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Attendance</label>
                                <input type="number" id="matchAttendance" name="attendance" min="0" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                        <button type="button" onclick="this.closest('.fixed').remove()"
                                class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-secondary transition-colors">
                            Save Match
                        </button>
                    </div>
                </form>
            </div>
        `;
        return modal;
    }

    setupMatchForm(modal, match = null) {
        const form = modal.querySelector('#matchForm');
        const league = modal.querySelector('#matchLeague');
        const homeTeam = modal.querySelector('#matchHomeTeam');
        const awayTeam = modal.querySelector('#matchAwayTeam');
        const matchDate = modal.querySelector('#matchDate');
        const status = modal.querySelector('#matchStatus');
        const homeScore = modal.querySelector('#matchHomeScore');
        const awayScore = modal.querySelector('#matchAwayScore');
        const venue = modal.querySelector('#matchVenue');
        const referee = modal.querySelector('#matchReferee');
        const attendance = modal.querySelector('#matchAttendance');
        const modalTitle = modal.querySelector('#matchModalTitle');

        // Populate leagues
        this.leagues.forEach(leagueItem => {
            const option = document.createElement('option');
            option.value = leagueItem.id;
            option.textContent = leagueItem.name;
            league.appendChild(option);
        });

        // Populate teams
        this.teams.forEach(team => {
            const homeOption = document.createElement('option');
            homeOption.value = team.id;
            homeOption.textContent = team.name;
            homeTeam.appendChild(homeOption);

            const awayOption = document.createElement('option');
            awayOption.value = team.id;
            awayOption.textContent = team.name;
            awayTeam.appendChild(awayOption);
        });

        // If editing, populate form
        if (match) {
            modalTitle.textContent = 'Edit Match';
            league.value = match.league_id || '';
            homeTeam.value = match.home_team_id || '';
            awayTeam.value = match.away_team_id || '';
            matchDate.value = match.match_date ? match.match_date.replace(' ', 'T') : '';
            status.value = match.status || 'scheduled';
            homeScore.value = match.home_score || '';
            awayScore.value = match.away_score || '';
            venue.value = match.venue || '';
            referee.value = match.referee || '';
            attendance.value = match.attendance || '';
        }

        // Setup form submission
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.saveMatch(form, match?.id);
        });
    }

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
                                <div id="articleEditor" class="border border-gray-300 rounded-lg min-h-[300px]"></div>
                                <input type="hidden" id="articleContent" name="content">
                            </div>
                            <div class="flex items-center space-x-4">
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
                                class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-secondary transition-colors">
                            Save Article
                        </button>
                    </div>
                </form>
            </div>
        `;
        return modal;
    }

    setupArticleForm(modal, article = null) {
        const form = modal.querySelector('#articleForm');
        const title = modal.querySelector('#articleTitle');
        const excerpt = modal.querySelector('#articleExcerpt');
        const category = modal.querySelector('#articleCategory');
        const content = modal.querySelector('#articleContent');
        const published = modal.querySelector('#articlePublished');
        const featured = modal.querySelector('#articleFeatured');
        const imageUrl = modal.querySelector('#articleImageUrl');
        const modalTitle = modal.querySelector('#articleModalTitle');

        // Populate categories
        this.categories.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = cat.name;
            category.appendChild(option);
        });

        // If editing, populate form
        if (article) {
            modalTitle.textContent = 'Edit Article';
            title.value = article.title;
            excerpt.value = article.excerpt || '';
            category.value = article.category_id || '';
            content.value = article.content || '';
            published.checked = article.is_published;
            featured.checked = article.is_featured;
            imageUrl.value = article.featured_image || '';

            if (article.featured_image) {
                this.showImagePreview(article.featured_image);
            }
        }

        // Setup form submission
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.saveArticle(form, article?.id);
        });
    }

    async saveArticle(form, articleId = null) {
        const formData = new FormData(form);
        const data = {
            title: formData.get('title'),
            excerpt: formData.get('excerpt'),
            content: document.getElementById('articleContent').value,
            category_id: formData.get('category_id'),
            featured_image: document.getElementById('articleImageUrl').value,
            is_published: document.getElementById('articlePublished').checked,
            is_featured: document.getElementById('articleFeatured').checked
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

            const result = await response.json();
            
            if (result.success) {
                this.showSuccess(articleId ? 'Article updated successfully' : 'Article created successfully');
                form.closest('.fixed').remove();
                adminDashboard.loadArticles();
            } else {
                this.showError(result.error || 'Failed to save article');
            }
        } catch (error) {
            this.showError('Error saving article');
        }
    }

    async handleImageUpload(input) {
        const file = input.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('image', file);
        formData.append('content_type', 'articles');

        try {
            const response = await fetch('/api/admin/upload', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                document.getElementById('articleImageUrl').value = result.data.url;
                this.showImagePreview(result.data.url);
                this.showSuccess('Image uploaded successfully');
            } else {
                this.showError(result.error || 'Failed to upload image');
            }
        } catch (error) {
            this.showError('Error uploading image');
        }
    }

    showImagePreview(url) {
        const preview = document.getElementById('imagePreview');
        const img = document.getElementById('previewImg');
        img.src = url;
        preview.classList.remove('hidden');
    }

    // Helper methods
    async loadCategories() {
        try {
            const response = await fetch('/api/admin/categories');
            const data = await response.json();
            if (data.success) {
                this.categories = data.data;
            }
        } catch (error) {
            console.error('Error loading categories:', error);
        }
    }

    async loadLeagues() {
        try {
            const response = await fetch('/api/admin/leagues');
            const data = await response.json();
            if (data.success) {
                this.leagues = data.data;
            }
        } catch (error) {
            console.error('Error loading leagues:', error);
        }
    }

    async loadTeams() {
        try {
            const response = await fetch('/api/admin/teams');
            const data = await response.json();
            if (data.success) {
                this.teams = data.teams;
            }
        } catch (error) {
            console.error('Error loading teams:', error);
        }
    }

    // Teams CRUD
    async createTeam() {
        const modal = this.createTeamModal();
        document.body.appendChild(modal);
        this.setupTeamForm(modal);
    }

    async editTeam(id) {
        try {
            const response = await fetch(`/api/admin/teams/${id}`);
            const data = await response.json();
            
            if (data.success) {
                this.currentTeam = data.data;
                const modal = this.createTeamModal();
                document.body.appendChild(modal);
                this.setupTeamForm(modal, data.data);
            } else {
                this.showError('Failed to load team');
            }
        } catch (error) {
            this.showError('Error loading team');
        }
    }

    async deleteTeam(id) {
        if (confirm('Are you sure you want to delete this team?')) {
            try {
                const response = await fetch(`/api/admin/teams/${id}`, {
                    method: 'DELETE'
                });
                const data = await response.json();
                
                if (data.success) {
                    this.showSuccess('Team deleted successfully');
                    adminDashboard.loadTeams();
                } else {
                    this.showError(data.error || 'Failed to delete team');
                }
            } catch (error) {
                this.showError('Error deleting team');
            }
        }
    }

    createTeamModal() {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
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
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Team Name *</label>
                                <input type="text" id="teamName" name="name" required 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Short Name</label>
                                <input type="text" id="teamShortName" name="short_name" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">League</label>
                                <select id="teamLeague" name="league_id" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">Select League</option>
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
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Stadium</label>
                                <input type="text" id="teamStadium" name="stadium" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Founded Year</label>
                                <input type="number" id="teamFoundedYear" name="founded_year" min="1800" max="2025" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Logo URL</label>
                                <input type="url" id="teamLogoUrl" name="logo_url" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                                <input type="url" id="teamWebsite" name="website" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea id="teamDescription" name="description" rows="4"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                        <button type="button" onclick="this.closest('.fixed').remove()"
                                class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-secondary transition-colors">
                            Save Team
                        </button>
                    </div>
                </form>
            </div>
        `;
        return modal;
    }

    setupTeamForm(modal, team = null) {
        const form = modal.querySelector('#teamForm');
        const name = modal.querySelector('#teamName');
        const shortName = modal.querySelector('#teamShortName');
        const league = modal.querySelector('#teamLeague');
        const country = modal.querySelector('#teamCountry');
        const city = modal.querySelector('#teamCity');
        const stadium = modal.querySelector('#teamStadium');
        const foundedYear = modal.querySelector('#teamFoundedYear');
        const logoUrl = modal.querySelector('#teamLogoUrl');
        const website = modal.querySelector('#teamWebsite');
        const description = modal.querySelector('#teamDescription');
        const modalTitle = modal.querySelector('#teamModalTitle');

        // Populate leagues
        this.leagues.forEach(leagueItem => {
            const option = document.createElement('option');
            option.value = leagueItem.id;
            option.textContent = leagueItem.name;
            league.appendChild(option);
        });

        // If editing, populate form
        if (team) {
            modalTitle.textContent = 'Edit Team';
            name.value = team.name || '';
            shortName.value = team.short_name || '';
            league.value = team.league_id || '';
            country.value = team.country || '';
            city.value = team.city || '';
            stadium.value = team.stadium || '';
            foundedYear.value = team.founded_year || '';
            logoUrl.value = team.logo_url || '';
            website.value = team.website || '';
            description.value = team.description || '';
        }

        // Setup form submission
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.saveTeam(form, team?.id);
        });
    }

    async saveTeam(form, teamId = null) {
        const formData = new FormData(form);
        const data = {
            name: formData.get('name'),
            short_name: formData.get('short_name'),
            league_id: formData.get('league_id'),
            country: formData.get('country'),
            city: formData.get('city'),
            stadium: formData.get('stadium'),
            founded_year: formData.get('founded_year') ? parseInt(formData.get('founded_year')) : null,
            logo_url: formData.get('logo_url'),
            website: formData.get('website'),
            description: formData.get('description')
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

            const result = await response.json();
            
            if (result.success) {
                this.showSuccess(teamId ? 'Team updated successfully' : 'Team created successfully');
                form.closest('.fixed').remove();
                adminDashboard.loadTeams();
            } else {
                this.showError(result.error || 'Failed to save team');
            }
        } catch (error) {
            this.showError('Error saving team');
        }
    }

    showSuccess(message) {
        // You can implement a toast notification system here
        alert(message);
    }

    showError(message) {
        // You can implement a toast notification system here
        alert('Error: ' + message);
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

// Teams CRUD
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
