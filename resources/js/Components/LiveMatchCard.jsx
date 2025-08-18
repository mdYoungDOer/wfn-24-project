import React from 'react'
import { Link } from '@inertiajs/react'

export default function LiveMatchCard({ match, compact = false }) {
  const getStatusColor = (status) => {
    switch (status) {
      case 'LIVE':
        return 'text-red-600 bg-red-100'
      case 'FT':
        return 'text-gray-600 bg-gray-100'
      case 'HT':
        return 'text-yellow-600 bg-yellow-100'
      default:
        return 'text-gray-600 bg-gray-100'
    }
  }

  const formatTime = (dateString) => {
    return new Date(dateString).toLocaleTimeString([], {
      hour: '2-digit',
      minute: '2-digit'
    })
  }

  if (compact) {
    return (
      <div className="bg-gray-50 rounded-lg p-3 hover:bg-gray-100 transition-colors">
        {/* Match Header */}
        <div className="flex items-center justify-between mb-2">
          <span className={`text-xs font-medium px-2 py-1 rounded ${getStatusColor(match.status)}`}>
            {match.status}
          </span>
          <span className="text-xs text-gray-500">
            {match.league?.name}
          </span>
        </div>

        {/* Teams and Score */}
        <div className="space-y-2">
          {/* Home Team */}
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-2 flex-1">
              <div className="w-5 h-5 bg-gray-200 rounded overflow-hidden">
                <img
                  src={match.home_team?.logo || '/images/placeholder-team.png'}
                  alt={match.home_team?.name}
                  className="w-full h-full object-cover"
                />
              </div>
              <span className="font-medium text-sm text-neutral truncate">
                {match.home_team?.name}
              </span>
            </div>
            <span className="text-lg font-bold text-neutral">
              {match.home_score}
            </span>
          </div>

          {/* Away Team */}
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-2 flex-1">
              <div className="w-5 h-5 bg-gray-200 rounded overflow-hidden">
                <img
                  src={match.away_team?.logo || '/images/placeholder-team.png'}
                  alt={match.away_team?.name}
                  className="w-full h-full object-cover"
                />
              </div>
              <span className="font-medium text-sm text-neutral truncate">
                {match.away_team?.name}
              </span>
            </div>
            <span className="text-lg font-bold text-neutral">
              {match.away_score}
            </span>
          </div>
        </div>

        {/* Match Info */}
        <div className="mt-2 pt-2 border-t border-gray-200">
          <div className="flex items-center justify-between text-xs text-gray-500">
            <span>{formatTime(match.match_date)}</span>
            <Link href={`/matches/${match.id}`} className="text-primary hover:underline">
              Follow →
            </Link>
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="bg-white rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow">
      {/* Match Header */}
      <div className="flex items-center justify-between mb-4">
        <div className="flex items-center gap-2">
          <span className={`text-xs font-medium px-3 py-1 rounded-full ${getStatusColor(match.status)}`}>
            {match.status}
          </span>
          {match.status === 'LIVE' && (
            <span className="live-indicator text-xs font-medium text-red-600">
              LIVE
            </span>
          )}
        </div>
        <span className="text-sm text-gray-500">
          {match.league?.name}
        </span>
      </div>

      {/* Teams and Score */}
      <div className="space-y-4">
        {/* Home Team */}
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-3 flex-1">
            <div className="w-8 h-8 bg-gray-200 rounded overflow-hidden">
              <img
                src={match.home_team?.logo || '/images/placeholder-team.png'}
                alt={match.home_team?.name}
                className="w-full h-full object-cover"
              />
            </div>
            <span className="font-semibold text-neutral">
              {match.home_team?.name}
            </span>
          </div>
          <span className="text-2xl font-bold text-neutral">
            {match.home_score}
          </span>
        </div>

        {/* Away Team */}
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-3 flex-1">
            <div className="w-8 h-8 bg-gray-200 rounded overflow-hidden">
              <img
                src={match.away_team?.logo || '/images/placeholder-team.png'}
                alt={match.away_team?.name}
                className="w-full h-full object-cover"
              />
            </div>
            <span className="font-semibold text-neutral">
              {match.away_team?.name}
            </span>
          </div>
          <span className="text-2xl font-bold text-neutral">
            {match.away_score}
          </span>
        </div>
      </div>

      {/* Match Info */}
      <div className="mt-4 pt-4 border-t border-gray-200">
        <div className="flex items-center justify-between text-sm text-gray-500 mb-2">
          <span>{formatTime(match.match_date)}</span>
          <span>{match.venue}</span>
        </div>

        {/* Possession Stats */}
        {match.home_possession && match.away_possession && (
          <div className="mb-3">
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
        <Link
          href={`/matches/${match.id}`}
          className="w-full btn-primary text-sm py-2 text-center block"
        >
          Follow Live
        </Link>
      </div>
    </div>
  )
}
