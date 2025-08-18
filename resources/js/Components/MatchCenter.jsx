import React from 'react'
import { Link } from '@inertiajs/react'

export default function MatchCenter({ liveMatches }) {
  if (!liveMatches || liveMatches.length === 0) {
    return (
      <div className="bg-white rounded-lg shadow-sm p-6">
        <div className="flex items-center justify-between mb-4">
          <h2 className="text-xl font-bold text-neutral flex items-center">
            <span className="live-indicator mr-2">LIVE</span>
            Match Center
          </h2>
          <Link href="/matches" className="text-primary hover:text-primary/80 font-medium">
            View All →
          </Link>
        </div>
        <div className="text-center py-8">
          <div className="text-4xl mb-4">⚽</div>
          <p className="text-gray-500">No live matches at the moment</p>
          <p className="text-gray-400 text-sm mt-2">Check back later for live action</p>
        </div>
      </div>
    )
  }

  return (
    <div className="bg-white rounded-lg shadow-sm overflow-hidden">
      {/* Header */}
      <div className="bg-gradient-to-r from-primary to-secondary text-white p-4">
        <div className="flex items-center justify-between">
          <h2 className="text-xl font-bold flex items-center">
            <span className="live-indicator mr-2">LIVE</span>
            Match Center
          </h2>
          <Link href="/matches" className="text-white/80 hover:text-white font-medium">
            View All →
          </Link>
        </div>
      </div>

      {/* Live Matches Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
        {liveMatches.map((match) => (
          <div key={match.id} className="bg-gray-50 rounded-lg p-4 hover:shadow-md transition-shadow">
            {/* Match Header */}
            <div className="flex items-center justify-between mb-3">
              <span className="text-xs font-medium text-red-600 bg-red-100 px-2 py-1 rounded">
                LIVE
              </span>
              <span className="text-xs text-gray-500">
                {match.league?.name}
              </span>
            </div>

            {/* Teams and Score */}
            <div className="space-y-3">
              {/* Home Team */}
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-2 flex-1">
                  <div className="w-6 h-6 bg-gray-200 rounded overflow-hidden">
                    <img
                      src={match.home_team?.logo || '/images/placeholder-team.png'}
                      alt={match.home_team?.name}
                      className="w-full h-full object-cover"
                    />
                  </div>
                  <span className="font-medium text-sm text-neutral">
                    {match.home_team?.name}
                  </span>
                </div>
                <span className="text-xl font-bold text-neutral">
                  {match.home_score}
                </span>
              </div>

              {/* Away Team */}
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-2 flex-1">
                  <div className="w-6 h-6 bg-gray-200 rounded overflow-hidden">
                    <img
                      src={match.away_team?.logo || '/images/placeholder-team.png'}
                      alt={match.away_team?.name}
                      className="w-full h-full object-cover"
                    />
                  </div>
                  <span className="font-medium text-sm text-neutral">
                    {match.away_team?.name}
                  </span>
                </div>
                <span className="text-xl font-bold text-neutral">
                  {match.away_score}
                </span>
              </div>
            </div>

            {/* Match Info */}
            <div className="mt-4 pt-3 border-t border-gray-200">
              <div className="flex items-center justify-between text-xs text-gray-500">
                <span>
                  {new Date(match.match_date).toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit'
                  })}
                </span>
                <span>{match.venue}</span>
              </div>

              {/* Possession Stats */}
              {match.home_possession && match.away_possession && (
                <div className="mt-2">
                  <div className="flex items-center gap-2 text-xs">
                    <span className="text-gray-500">Possession:</span>
                    <div className="flex-1 bg-gray-200 rounded-full h-1">
                      <div
                        className="bg-primary h-1 rounded-full"
                        style={{ width: `${match.home_possession}%` }}
                      />
                    </div>
                    <span className="text-gray-500">
                      {match.home_possession}% - {match.away_possession}%
                    </span>
                  </div>
                </div>
              )}

              {/* Action Button */}
              <div className="mt-3">
                <Link
                  href={`/matches/${match.id}`}
                  className="w-full btn-primary text-xs py-2 text-center block"
                >
                  Follow Live
                </Link>
              </div>
            </div>
          </div>
        ))}
      </div>

      {/* Quick Stats */}
      <div className="bg-gray-50 px-4 py-3 border-t border-gray-200">
        <div className="flex items-center justify-between text-sm">
          <span className="text-gray-600">
            {liveMatches.length} live match{liveMatches.length !== 1 ? 'es' : ''}
          </span>
          <div className="flex items-center gap-4">
            <span className="text-gray-600">
              Total Goals: {liveMatches.reduce((sum, match) => sum + (match.home_score || 0) + (match.away_score || 0), 0)}
            </span>
            <span className="text-gray-600">
              Avg. Goals: {liveMatches.length > 0 ? (liveMatches.reduce((sum, match) => sum + (match.home_score || 0) + (match.away_score || 0), 0) / liveMatches.length).toFixed(1) : 0}
            </span>
          </div>
        </div>
      </div>
    </div>
  )
}
