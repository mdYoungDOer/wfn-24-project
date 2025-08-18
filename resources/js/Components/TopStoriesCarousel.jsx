import React, { useState, useEffect } from 'react'
import { Link } from '@inertiajs/react'

export default function TopStoriesCarousel({ articles }) {
  const [currentSlide, setCurrentSlide] = useState(0)

  useEffect(() => {
    const timer = setInterval(() => {
      setCurrentSlide((prev) => (prev + 1) % articles.length)
    }, 5000)
    return () => clearInterval(timer)
  }, [articles.length])

  if (!articles || articles.length === 0) {
    return null
  }

  return (
    <div className="relative bg-white rounded-lg shadow-sm overflow-hidden">
      {/* Main Carousel */}
      <div className="relative h-96 md:h-[500px]">
        {articles.map((article, index) => (
          <div
            key={article.id}
            className={`absolute inset-0 transition-opacity duration-500 ${
              index === currentSlide ? 'opacity-100' : 'opacity-0'
            }`}
          >
            {/* Background Image */}
            <div className="absolute inset-0">
              <img
                src={article.featured_image || '/images/placeholder-article.jpg'}
                alt={article.title}
                className="w-full h-full object-cover"
              />
              <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent" />
            </div>

            {/* Content */}
            <div className="absolute bottom-0 left-0 right-0 p-6 md:p-8">
              <div className="max-w-4xl">
                {/* Category Badge */}
                <div className="flex items-center gap-2 mb-3">
                  <span
                    className="px-3 py-1 text-xs font-medium text-white rounded-full"
                    style={{ backgroundColor: article.category?.color || '#e41e5b' }}
                  >
                    {article.category?.name}
                  </span>
                  {article.is_featured && (
                    <span className="px-3 py-1 text-xs font-medium text-white bg-highlight rounded-full">
                      FEATURED
                    </span>
                  )}
                  <span className="text-white/80 text-sm">
                    {new Date(article.published_at).toLocaleDateString()}
                  </span>
                </div>

                {/* Title */}
                <h1 className="text-2xl md:text-4xl font-bold text-white mb-3 line-clamp-2">
                  <Link href={`/news/${article.slug}`} className="hover:text-primary/80">
                    {article.title}
                  </Link>
                </h1>

                {/* Excerpt */}
                <p className="text-white/90 text-sm md:text-base mb-4 line-clamp-2">
                  {article.excerpt}
                </p>

                {/* Author and Stats */}
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-3">
                    <span className="text-white/80 text-sm">
                      By {article.author_name || 'WFN24 Staff'}
                    </span>
                    <span className="text-white/60 text-sm">
                      {article.view_count} views
                    </span>
                  </div>
                  <Link
                    href={`/news/${article.slug}`}
                    className="btn-primary text-sm px-4 py-2"
                  >
                    Read More
                  </Link>
                </div>
              </div>
            </div>
          </div>
        ))}

        {/* Navigation Arrows */}
        <button
          onClick={() => setCurrentSlide((prev) => (prev - 1 + articles.length) % articles.length)}
          className="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-black/50 hover:bg-black/70 text-white rounded-full flex items-center justify-center transition-colors"
        >
          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <button
          onClick={() => setCurrentSlide((prev) => (prev + 1) % articles.length)}
          className="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-black/50 hover:bg-black/70 text-white rounded-full flex items-center justify-center transition-colors"
        >
          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>

      {/* Dots Indicator */}
      <div className="absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2">
        {articles.map((_, index) => (
          <button
            key={index}
            onClick={() => setCurrentSlide(index)}
            className={`w-2 h-2 rounded-full transition-colors ${
              index === currentSlide ? 'bg-white' : 'bg-white/50'
            }`}
          />
        ))}
      </div>

      {/* Sidebar Articles */}
      <div className="absolute top-4 right-4 w-80 bg-white/95 backdrop-blur-sm rounded-lg p-4 max-h-96 overflow-y-auto">
        <h3 className="text-lg font-bold text-neutral mb-3">Top Stories</h3>
        <div className="space-y-3">
          {articles.slice(0, 4).map((article, index) => (
            <div
              key={article.id}
              className={`flex gap-3 p-2 rounded cursor-pointer transition-colors ${
                index === currentSlide ? 'bg-primary/10' : 'hover:bg-gray-50'
              }`}
              onClick={() => setCurrentSlide(index)}
            >
              <div className="flex-shrink-0 w-16 h-12 bg-gray-200 rounded overflow-hidden">
                <img
                  src={article.featured_image || '/images/placeholder-article.jpg'}
                  alt={article.title}
                  className="w-full h-full object-cover"
                />
              </div>
              <div className="flex-1 min-w-0">
                <h4 className="font-medium text-neutral text-sm line-clamp-2">
                  {article.title}
                </h4>
                <p className="text-gray-500 text-xs mt-1">
                  {new Date(article.published_at).toLocaleDateString()}
                </p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  )
}
