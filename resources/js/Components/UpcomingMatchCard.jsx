import React from 'react'
import { Link } from '@inertiajs/react'

export default function UpcomingMatchCard({ match }) {
  const formatDate = (dateString) => {
    const date = new Date(dateString)
    const now = new Date()
    const diffTime = date - now
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    const diffHours = Math.ceil(diffTime / (1000 * 60 * 60))
    const diffMinutes = Math.ceil(diffTime / (1000 * 60))

    if (diffDays > 1) {
      return `${diffDays} days`
    } else if (diffHours > 1) {
      return `${diffHours} hours`
    } else if (diffMinutes > 0) {
      return `${diffMinutes} minutes`
    } else {
      return 'Starting soon'
    }
  }

  const formatTime = (dateString) => {
    return new Date(dateString).toLocaleTimeString([], {
      hour: '2-digit',
      minute: '2-digit'
    })
  }

  return (
    <div className="bg-white rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow">
      {/* Match Header */}
      <div className="flex items-center justify-between mb-4">
        <div className="flex items-center gap-2">
          <span className="text-xs font-medium text-blue-600 bg-blue-100 px-3 py-1 rounded-full">
            UPCOMING
          </span>
          <span className="text-xs text-gray-500">
            {formatDate(match.match_date)}
          </span>
        </div>
        <span className="text-sm text-gray-500">
          {match.league?.name}
        </span>
      </div>

      {/* Teams */}
      <div className="space-y-4">
        {/* Home Team */}
        <div className="flex items-center gap-3">
          <div className="w-8 h-8 bg-gray-200 rounded overflow-hidden">
            <img
              src={match.home_team?.logo || '/images/placeholder-team.png'}
              alt={match.home_team?.name}
              className="w-full h-full object-cover"
            />
          </div>
          <span className="font-semibold text-neutral flex-1">
            {match.home_team?.name}
          </span>
          <span className="text-lg font-bold text-neutral">
            -
          </span>
        </div>

        {/* Away Team */}
        <div className="flex items-center gap-3">
          <div className="w-8 h-8 bg-gray-200 rounded overflow-hidden">
            <img
              src={match.away_team?.logo || '/images/placeholder-team.png'}
              alt={match.away_team?.name}
              className="w-full h-full object-cover"
            />
          </div>
          <span className="font-semibold text-neutral flex-1">
            {match.away_team?.name}
          </span>
          <span className="text-lg font-bold text-neutral">
            -
          </span>
        </div>
      </div>

      {/* Match Info */}
      <div className="mt-4 pt-4 border-t border-gray-200">
        <div className="flex items-center justify-between text-sm text-gray-500 mb-2">
          <span>{formatTime(match.match_date)}</span>
          <span>{match.venue}</span>
        </div>

        {/* League Info */}
        <div className="flex items-center justify-between text-xs text-gray-400 mb-3">
          <span>{match.league?.country}</span>
          <span>{match.league?.type}</span>
        </div>

        {/* Action Button */}
        <Link
          href={`/matches/${match.id}`}
          className="w-full btn-primary text-sm py-2 text-center block"
        >
          View Details
        </Link>
      </div>
    </div>
  )
}
