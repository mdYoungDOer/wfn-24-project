import React from 'react'
import { Link } from '@inertiajs/react'

export default function FeaturedArticleCard({ article, isMain = false }) {
  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    })
  }

  return (
    <Link href={`/news/${article.slug}`} className="block">
      <div className={`card-hover ${isMain ? 'h-full' : ''}`}>
        {/* Article Image */}
        <div className={`relative overflow-hidden rounded-lg mb-4 ${isMain ? 'h-64' : 'h-48'}`}>
          {article.featured_image ? (
            <img
              src={article.featured_image}
              alt={article.title}
              className="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
            />
          ) : (
            <div className="w-full h-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center">
              <span className="text-white text-4xl">⚽</span>
            </div>
          )}
          
          {/* Category Badge */}
          {article.category_name && (
            <div className="absolute top-3 left-3">
              <span className="badge-primary">
                {article.category_name}
              </span>
            </div>
          )}
          
          {/* Featured Badge */}
          {article.is_featured && (
            <div className="absolute top-3 right-3">
              <span className="badge-warning">
                Featured
              </span>
            </div>
          )}
        </div>

        {/* Article Content */}
        <div className="space-y-3">
          {/* Title */}
          <h3 className={`font-bold text-neutral hover:text-primary transition-colors duration-200 ${
            isMain ? 'text-2xl' : 'text-lg'
          }`}>
            {article.title}
          </h3>

          {/* Excerpt */}
          {article.excerpt && (
            <p className="text-gray-600 line-clamp-3">
              {article.excerpt}
            </p>
          )}

          {/* Meta Information */}
          <div className="flex items-center justify-between text-sm text-gray-500">
            <div className="flex items-center space-x-4">
              {/* Author */}
              {article.first_name && (
                <span>
                  By {article.first_name} {article.last_name}
                </span>
              )}
              
              {/* Published Date */}
              {article.published_at && (
                <span>
                  {formatDate(article.published_at)}
                </span>
              )}
            </div>

            {/* View Count */}
            {article.view_count > 0 && (
              <div className="flex items-center space-x-1">
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span>{article.view_count}</span>
              </div>
            )}
          </div>
        </div>
      </div>
    </Link>
  )
}
