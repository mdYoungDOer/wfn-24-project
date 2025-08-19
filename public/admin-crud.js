// CRUD Operations for Admin Dashboard
class AdminCRUD {
    constructor() {
        this.currentArticle = null;
        this.currentMatch = null;
        this.currentTeam = null;
        this.currentUser = null;
        this.categories = [];
        this.leagues = [];
        this.init();
    }

    async init() {
        await this.loadCategories();
        await this.loadLeagues();
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
