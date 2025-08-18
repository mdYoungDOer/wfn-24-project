import React from 'react'
import { Link } from '@inertiajs/react'

export default function FeaturedArticleCard({ article, isMain = false }) {
  return (
    <div className={`bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow ${isMain ? 'md:col-span-2' : ''}`}>
      {/* Article Image */}
      <div className={`relative overflow-hidden ${isMain ? 'h-64' : 'h-48'}`}>
        <img
          src={article.featured_image || '/images/placeholder-article.jpg'}
          alt={article.title}
          className="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
        />
        <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent" />
        
        {/* Category Badge */}
        <div className="absolute top-4 left-4">
          <span
            className="px-3 py-1 text-xs font-medium text-white rounded-full"
            style={{ backgroundColor: article.category?.color || '#e41e5b' }}
          >
            {article.category?.name}
          </span>
        </div>

        {/* Featured Badge */}
        {article.is_featured && (
          <div className="absolute top-4 right-4">
            <span className="px-3 py-1 text-xs font-medium text-white bg-highlight rounded-full">
              FEATURED
            </span>
          </div>
        )}
      </div>

      {/* Article Content */}
      <div className="p-4">
        {/* Meta Information */}
        <div className="flex items-center gap-3 text-sm text-gray-500 mb-3">
          <span>By {article.author_name || 'WFN24 Staff'}</span>
          <span>•</span>
          <span>{new Date(article.published_at).toLocaleDateString()}</span>
          <span>•</span>
          <span>{article.view_count} views</span>
        </div>

        {/* Title */}
        <h3 className={`font-bold text-neutral mb-3 line-clamp-2 ${isMain ? 'text-xl' : 'text-lg'}`}>
          <Link href={`/news/${article.slug}`} className="hover:text-primary">
            {article.title}
          </Link>
        </h3>

        {/* Excerpt */}
        <p className="text-gray-600 text-sm mb-4 line-clamp-3">
          {article.excerpt}
        </p>

        {/* Read More Button */}
        <Link
          href={`/news/${article.slug}`}
          className="inline-flex items-center text-primary hover:text-primary/80 font-medium text-sm"
        >
          Read More
          <svg className="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
          </svg>
        </Link>
      </div>
    </div>
  )
}
