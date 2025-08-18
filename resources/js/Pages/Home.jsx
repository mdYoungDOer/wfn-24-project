import React from 'react'
import { Link } from '@inertiajs/react'
import MainLayout from '../Layouts/MainLayout'
import FeaturedArticleCard from '../Components/FeaturedArticleCard'
import LiveMatchCard from '../Components/LiveMatchCard'
import UpcomingMatchCard from '../Components/UpcomingMatchCard'
import LeagueCard from '../Components/LeagueCard'
import TopStoriesCarousel from '../Components/TopStoriesCarousel'
import MatchCenter from '../Components/MatchCenter'

export default function Home({ featuredArticles, latestNews, liveMatches, upcomingMatches, majorLeagues }) {
  return (
    <MainLayout title="WFN24 - World Football News 24">
      {/* Hero Section with Top Stories Carousel */}
      <section className="mb-8">
        <TopStoriesCarousel articles={featuredArticles} />
      </section>

      {/* Live Match Center */}
      <section className="mb-8">
        <MatchCenter liveMatches={liveMatches} />
      </section>

      {/* Main Content Grid */}
      <div className="grid grid-cols-1 lg:grid-cols-4 gap-8">
        {/* Main Content - 3 columns */}
        <div className="lg:col-span-3">
          {/* Featured Articles */}
          <section className="mb-8">
            <div className="flex justify-between items-center mb-6">
              <h2 className="text-2xl font-bold text-neutral">Featured Stories</h2>
              <Link href="/news" className="text-primary hover:text-primary/80 font-medium">
                View All News →
              </Link>
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {featuredArticles.slice(0, 4).map((article, index) => (
                <FeaturedArticleCard 
                  key={article.id} 
                  article={article} 
                  isMain={index === 0}
                />
              ))}
            </div>
          </section>

          {/* Latest News */}
          <section className="mb-8">
            <div className="flex justify-between items-center mb-6">
              <h2 className="text-2xl font-bold text-neutral">Latest News</h2>
              <Link href="/news" className="text-primary hover:text-primary/80 font-medium">
                View All →
              </Link>
            </div>
            
            <div className="space-y-4">
              {latestNews.slice(0, 8).map((article) => (
                <div key={article.id} className="flex gap-4 p-4 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                  <div className="flex-shrink-0 w-24 h-24 bg-gray-200 rounded-lg overflow-hidden">
                    <img 
                      src={article.featured_image || '/images/placeholder-article.jpg'} 
                      alt={article.title}
                      className="w-full h-full object-cover"
                    />
                  </div>
                  <div className="flex-1">
                    <div className="flex items-center gap-2 mb-2">
                      <span 
                        className="px-2 py-1 text-xs font-medium text-white rounded"
                        style={{ backgroundColor: article.category?.color || '#e41e5b' }}
                      >
                        {article.category?.name}
                      </span>
                      <span className="text-sm text-gray-500">
                        {new Date(article.published_at).toLocaleDateString()}
                      </span>
                    </div>
                    <h3 className="font-semibold text-neutral mb-2 line-clamp-2">
                      <Link href={`/news/${article.slug}`} className="hover:text-primary">
                        {article.title}
                      </Link>
                    </h3>
                    <p className="text-gray-600 text-sm line-clamp-2">
                      {article.excerpt}
                    </p>
                  </div>
                </div>
              ))}
            </div>
          </section>

          {/* Upcoming Matches */}
          <section className="mb-8">
            <div className="flex justify-between items-center mb-6">
              <h2 className="text-2xl font-bold text-neutral">Upcoming Matches</h2>
              <Link href="/matches" className="text-primary hover:text-primary/80 font-medium">
                View All Matches →
              </Link>
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              {upcomingMatches.map((match) => (
                <UpcomingMatchCard key={match.id} match={match} />
              ))}
            </div>
          </section>
        </div>

        {/* Sidebar - 1 column */}
        <div className="lg:col-span-1">
          {/* Live Matches Sidebar */}
          <div className="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h3 className="text-lg font-bold text-neutral mb-4 flex items-center">
              <span className="live-indicator mr-2">LIVE</span>
              Live Matches
            </h3>
            {liveMatches.length > 0 ? (
              <div className="space-y-4">
                {liveMatches.map((match) => (
                  <LiveMatchCard key={match.id} match={match} compact />
                ))}
              </div>
            ) : (
              <p className="text-gray-500 text-center py-4">No live matches at the moment</p>
            )}
          </div>

          {/* League Tables */}
          <div className="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h3 className="text-lg font-bold text-neutral mb-4">League Tables</h3>
            <div className="space-y-4">
              {majorLeagues.slice(0, 3).map((league) => (
                <div key={league.id} className="p-3 border border-gray-200 rounded-lg">
                  <h4 className="font-medium text-neutral mb-2">{league.name}</h4>
                  <p className="text-sm text-gray-500">{league.country}</p>
                  <Link href={`/leagues/${league.id}`} className="text-primary text-sm hover:underline">
                    View Table →
                  </Link>
                </div>
              ))}
            </div>
          </div>

          {/* Major Leagues */}
          <div className="bg-white rounded-lg shadow-sm p-6">
            <h3 className="text-lg font-bold text-neutral mb-4">Major Leagues</h3>
            <div className="grid grid-cols-2 gap-3">
              {majorLeagues.map((league) => (
                <LeagueCard key={league.id} league={league} />
              ))}
            </div>
          </div>
        </div>
      </div>

      {/* Stats Section */}
      <section className="mt-12">
        <div className="bg-gradient-to-r from-primary to-secondary text-white rounded-lg p-8">
          <h2 className="text-2xl font-bold mb-6 text-center">WFN24 Stats</h2>
          
          <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div className="text-center">
              <div className="text-3xl font-bold mb-2">
                {latestNews.length}+
              </div>
              <div className="text-white/80">News Articles</div>
            </div>
            
            <div className="text-center">
              <div className="text-3xl font-bold mb-2">
                {liveMatches.length}
              </div>
              <div className="text-white/80">Live Matches</div>
            </div>
            
            <div className="text-center">
              <div className="text-3xl font-bold mb-2">
                {majorLeagues.length}
              </div>
              <div className="text-white/80">Leagues Covered</div>
            </div>
            
            <div className="text-center">
              <div className="text-3xl font-bold mb-2">
                24/7
              </div>
              <div className="text-white/80">News Coverage</div>
            </div>
          </div>
        </div>
      </section>
    </MainLayout>
  )
}
