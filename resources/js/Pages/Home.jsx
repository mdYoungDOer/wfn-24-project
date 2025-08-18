import React from 'react'
import { Link } from '@inertiajs/react'
import MainLayout from '@/Layouts/MainLayout'
import FeaturedArticleCard from '@/Components/FeaturedArticleCard'
import LiveMatchCard from '@/Components/LiveMatchCard'
import UpcomingMatchCard from '@/Components/UpcomingMatchCard'
import LeagueCard from '@/Components/LeagueCard'

export default function Home({ featuredArticles, latestNews, liveMatches, upcomingMatches, majorLeagues }) {
  return (
    <MainLayout title="WFN24 - World Football News 24">
      {/* Hero Section with Featured Articles */}
      <section className="mb-12">
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Main Featured Article */}
          {featuredArticles.length > 0 && (
            <div className="lg:col-span-2">
              <FeaturedArticleCard article={featuredArticles[0]} isMain={true} />
            </div>
          )}
          
          {/* Sidebar with Live Matches */}
          <div className="space-y-6">
            <div className="card">
              <h2 className="text-xl font-bold text-neutral mb-4 flex items-center">
                <span className="live-indicator mr-2">LIVE</span>
                Live Matches
              </h2>
              {liveMatches.length > 0 ? (
                <div className="space-y-4">
                  {liveMatches.slice(0, 3).map((match) => (
                    <LiveMatchCard key={match.id} match={match} />
                  ))}
                </div>
              ) : (
                <p className="text-gray-500 text-center py-4">No live matches at the moment</p>
              )}
            </div>
          </div>
        </div>
      </section>

      {/* Latest News Section */}
      <section className="mb-12">
        <div className="flex justify-between items-center mb-6">
          <h2 className="text-2xl font-bold text-neutral">Latest News</h2>
          <Link href="/news" className="btn-outline">
            View All News
          </Link>
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {latestNews.slice(0, 6).map((article) => (
            <FeaturedArticleCard key={article.id} article={article} />
          ))}
        </div>
      </section>

      {/* Upcoming Matches Section */}
      <section className="mb-12">
        <div className="flex justify-between items-center mb-6">
          <h2 className="text-2xl font-bold text-neutral">Upcoming Matches</h2>
          <Link href="/matches" className="btn-outline">
            View All Matches
          </Link>
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {upcomingMatches.map((match) => (
            <UpcomingMatchCard key={match.id} match={match} />
          ))}
        </div>
      </section>

      {/* Major Leagues Section */}
      <section className="mb-12">
        <h2 className="text-2xl font-bold text-neutral mb-6">Major Leagues</h2>
        
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          {majorLeagues.map((league) => (
            <LeagueCard key={league.id} league={league} />
          ))}
        </div>
      </section>

      {/* Stats Section */}
      <section className="mb-12">
        <div className="card">
          <h2 className="text-2xl font-bold text-neutral mb-6">WFN24 Stats</h2>
          
          <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div className="text-center">
              <div className="text-3xl font-bold text-primary mb-2">
                {latestNews.length}+
              </div>
              <div className="text-gray-600">News Articles</div>
            </div>
            
            <div className="text-center">
              <div className="text-3xl font-bold text-primary mb-2">
                {liveMatches.length}
              </div>
              <div className="text-gray-600">Live Matches</div>
            </div>
            
            <div className="text-center">
              <div className="text-3xl font-bold text-primary mb-2">
                {majorLeagues.length}
              </div>
              <div className="text-gray-600">Leagues Covered</div>
            </div>
            
            <div className="text-center">
              <div className="text-3xl font-bold text-primary mb-2">
                24/7
              </div>
              <div className="text-gray-600">News Coverage</div>
            </div>
          </div>
        </div>
      </section>
    </MainLayout>
  )
}
