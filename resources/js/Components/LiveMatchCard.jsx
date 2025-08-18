import React from 'react'
import { Link } from '@inertiajs/react'

export default function LiveMatchCard({ match }) {
  const formatTime = (timeString) => {
    if (!timeString) return ''
    return new Date(`2000-01-01T${timeString}`).toLocaleTimeString('en-US', {
      hour: '2-digit',
      minute: '2-digit',
      hour12: true
    })
  }

  const getMatchStatus = (status) => {
    switch (status) {
      case 'live':
        return { text: 'LIVE', color: 'text-red-600', bg: 'bg-red-100' }
      case 'finished':
        return { text: 'FT', color: 'text-gray-600', bg: 'bg-gray-100' }
      case 'scheduled':
        return { text: 'SCHEDULED', color: 'text-blue-600', bg: 'bg-blue-100' }
      default:
        return { text: status?.toUpperCase(), color: 'text-gray-600', bg: 'bg-gray-100' }
    }
  }

  const status = getMatchStatus(match.status)

  return (
    <Link href={`/match/${match.id}`} className="block">
      <div className="card-hover p-4">
        {/* Match Header */}
        <div className="flex items-center justify-between mb-3">
          <div className="flex items-center space-x-2">
            <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${status.bg} ${status.color}`}>
              {status.text}
            </span>
            {match.status === 'live' && (
              <span className="live-indicator">LIVE</span>
            )}
          </div>
          
          {match.league_name && (
            <span className="text-xs text-gray-500">
              {match.league_name}
            </span>
          )}
        </div>

        {/* Teams and Score */}
        <div className="space-y-3">
          {/* Home Team */}
          <div className="flex items-center justify-between">
            <div className="flex items-center space-x-2 flex-1">
              {match.home_team_logo && (
                <img
                  src={match.home_team_logo}
                  alt={match.home_team_name}
                  className="team-logo"
                />
              )}
              <span className="font-medium text-sm truncate">
                {match.home_team_name}
              </span>
            </div>
            <div className="text-lg font-bold text-neutral">
              {match.home_score !== null ? match.home_score : '-'}
            </div>
          </div>

          {/* Away Team */}
          <div className="flex items-center justify-between">
            <div className="flex items-center space-x-2 flex-1">
              {match.away_team_logo && (
                <img
                  src={match.away_team_logo}
                  alt={match.away_team_name}
                  className="team-logo"
                />
              )}
              <span className="font-medium text-sm truncate">
                {match.away_team_name}
              </span>
            </div>
            <div className="text-lg font-bold text-neutral">
              {match.away_score !== null ? match.away_score : '-'}
            </div>
          </div>
        </div>

        {/* Match Details */}
        <div className="mt-3 pt-3 border-t border-gray-100">
          <div className="flex items-center justify-between text-xs text-gray-500">
            <div className="flex items-center space-x-2">
              {match.kickoff_time && (
                <span>
                  {formatTime(match.kickoff_time)}
                </span>
              )}
              {match.stadium && (
                <span className="truncate max-w-24">
                  {match.stadium}
                </span>
              )}
            </div>
            
            {match.status === 'live' && (
              <div className="flex items-center space-x-1 text-red-600">
                <div className="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                <span>Live</span>
              </div>
            )}
          </div>
        </div>

        {/* Match Statistics (if available) */}
        {match.status === 'live' && (match.home_possession || match.away_possession) && (
          <div className="mt-3 pt-3 border-t border-gray-100">
            <div className="flex items-center justify-between text-xs">
              <span className="text-gray-500">Possession</span>
              <div className="flex items-center space-x-2">
                <span className="font-medium">{match.home_possession}%</span>
                <div className="w-16 h-1 bg-gray-200 rounded-full overflow-hidden">
                  <div 
                    className="h-full bg-primary rounded-full"
                    style={{ width: `${match.home_possession}%` }}
                  ></div>
                </div>
                <span className="font-medium">{match.away_possession}%</span>
              </div>
            </div>
          </div>
        )}
      </div>
    </Link>
  )
}
