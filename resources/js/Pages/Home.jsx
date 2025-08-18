import React from 'react'

export default function Home({ featuredArticles, latestNews, liveMatches, upcomingMatches, majorLeagues }) {
  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-gradient-to-r from-primary to-secondary text-white py-6">
        <div className="container mx-auto px-4">
          <h1 className="text-3xl font-bold">WFN24</h1>
          <p className="text-lg opacity-90">World Football News 24</p>
        </div>
      </header>

      {/* Main Content */}
      <main className="container mx-auto px-4 py-8">
        {/* Hero Section */}
        <section className="mb-12">
          <div className="bg-white rounded-lg shadow-md p-8">
            <h2 className="text-2xl font-bold text-gray-800 mb-4">Welcome to WFN24</h2>
            <p className="text-gray-600 mb-6">
              Your comprehensive source for football news, live scores, match updates, and everything football.
            </p>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div className="text-center p-4 bg-blue-50 rounded-lg">
                <div className="text-2xl font-bold text-blue-600 mb-2">📰</div>
                <h3 className="font-semibold text-gray-800">Latest News</h3>
                <p className="text-sm text-gray-600">Breaking football news and updates</p>
              </div>
              <div className="text-center p-4 bg-green-50 rounded-lg">
                <div className="text-2xl font-bold text-green-600 mb-2">⚽</div>
                <h3 className="font-semibold text-gray-800">Live Matches</h3>
                <p className="text-sm text-gray-600">Real-time scores and commentary</p>
              </div>
              <div className="text-center p-4 bg-purple-50 rounded-lg">
                <div className="text-2xl font-bold text-purple-600 mb-2">🏆</div>
                <h3 className="font-semibold text-gray-800">League Tables</h3>
                <p className="text-sm text-gray-600">Standings and statistics</p>
              </div>
            </div>
          </div>
        </section>

        {/* Stats Section */}
        <section className="mb-12">
          <div className="bg-white rounded-lg shadow-md p-8">
            <h2 className="text-2xl font-bold text-gray-800 mb-6">WFN24 Stats</h2>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
              <div className="text-center">
                <div className="text-3xl font-bold text-primary mb-2">
                  {latestNews?.length || 0}+
                </div>
                <div className="text-gray-600">News Articles</div>
              </div>
              <div className="text-center">
                <div className="text-3xl font-bold text-primary mb-2">
                  {liveMatches?.length || 0}
                </div>
                <div className="text-gray-600">Live Matches</div>
              </div>
              <div className="text-center">
                <div className="text-3xl font-bold text-primary mb-2">
                  {majorLeagues?.length || 0}
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

        {/* Features Section */}
        <section className="mb-12">
          <div className="bg-white rounded-lg shadow-md p-8">
            <h2 className="text-2xl font-bold text-gray-800 mb-6">Features</h2>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div className="p-4 border border-gray-200 rounded-lg">
                <h3 className="font-semibold text-gray-800 mb-2">📱 Mobile-First Design</h3>
                <p className="text-sm text-gray-600">Optimized for all devices and screen sizes</p>
              </div>
              <div className="p-4 border border-gray-200 rounded-lg">
                <h3 className="font-semibold text-gray-800 mb-2">🔔 Real-Time Updates</h3>
                <p className="text-sm text-gray-600">Live scores and instant notifications</p>
              </div>
              <div className="p-4 border border-gray-200 rounded-lg">
                <h3 className="font-semibold text-gray-800 mb-2">🔍 Advanced Search</h3>
                <p className="text-sm text-gray-600">Find news, teams, players, and matches</p>
              </div>
              <div className="p-4 border border-gray-200 rounded-lg">
                <h3 className="font-semibold text-gray-800 mb-2">📊 Statistics</h3>
                <p className="text-sm text-gray-600">Comprehensive stats and analytics</p>
              </div>
              <div className="p-4 border border-gray-200 rounded-lg">
                <h3 className="font-semibold text-gray-800 mb-2">👥 Team Profiles</h3>
                <p className="text-sm text-gray-600">Detailed team and player information</p>
              </div>
              <div className="p-4 border border-gray-200 rounded-lg">
                <h3 className="font-semibold text-gray-800 mb-2">📧 Email Alerts</h3>
                <p className="text-sm text-gray-600">Customizable notification preferences</p>
              </div>
            </div>
          </div>
        </section>
      </main>

      {/* Footer */}
      <footer className="bg-gray-800 text-white py-8">
        <div className="container mx-auto px-4">
          <div className="text-center">
            <h3 className="text-xl font-bold mb-2">WFN24</h3>
            <p className="text-gray-400">World Football News 24 - Your Ultimate Football Destination</p>
            <p className="text-sm text-gray-500 mt-4">© 2024 WFN24. All rights reserved.</p>
          </div>
        </div>
      </footer>
    </div>
  )
}
