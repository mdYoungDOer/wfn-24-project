import React from 'react'
import { Link } from '@inertiajs/react'

export default function UpcomingMatchCard({ match }) {
  const formatDate = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', {
      weekday: 'short',
      month: 'short',
      day: 'numeric'
    })
  }

  const formatTime = (timeString) => {
    if (!timeString) return ''
    return new Date(`2000-01-01T${timeString}`).toLocaleTimeString('en-US', {
      hour: '2-digit',
      minute: '2-digit',
      hour12: true
    })
  }

  const getTimeUntilMatch = (dateString, timeString) => {
    if (!dateString || !timeString) return ''
    
    const matchDateTime = new Date(`${dateString}T${timeString}`)
    const now = new Date()
    const diff = matchDateTime - now

    if (diff <= 0) return 'Starting soon'

    const days = Math.floor(diff / (1000 * 60 * 60 * 24))
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))

    if (days > 0) return `${days}d ${hours}h`
    if (hours > 0) return `${hours}h ${minutes}m`
    return `${minutes}m`
  }

  return (
    <Link href={`/match/${match.id}`} className="block">
      <div className="card-hover p-4">
        {/* Match Header */}
        <div className="flex items-center justify-between mb-3">
          <div className="flex items-center space-x-2">
            <span className="badge-secondary">
              UPCOMING
            </span>
          </div>
          
          {match.league_name && (
            <span className="text-xs text-gray-500">
              {match.league_name}
            </span>
          )}
        </div>

        {/* Teams */}
        <div className="space-y-3">
          {/* Home Team */}
          <div className="flex items-center space-x-2">
            {match.home_team_logo && (
              <img
                src={match.home_team_logo}
                alt={match.home_team_name}
                className="team-logo"
              />
            )}
            <span className="font-medium text-sm flex-1">
              {match.home_team_name}
            </span>
          </div>

          {/* VS */}
          <div className="text-center text-xs text-gray-500 font-medium">
            VS
          </div>

          {/* Away Team */}
          <div className="flex items-center space-x-2">
            {match.away_team_logo && (
              <img
                src={match.away_team_logo}
                alt={match.away_team_name}
                className="team-logo"
              />
            )}
            <span className="font-medium text-sm flex-1">
              {match.away_team_name}
            </span>
          </div>
        </div>

        {/* Match Details */}
        <div className="mt-4 pt-3 border-t border-gray-100 space-y-2">
          {/* Date and Time */}
          <div className="flex items-center justify-between text-xs">
            <span className="text-gray-500">Date</span>
            <span className="font-medium">
              {formatDate(match.match_date)}
            </span>
          </div>
          
          <div className="flex items-center justify-between text-xs">
            <span className="text-gray-500">Time</span>
            <span className="font-medium">
              {formatTime(match.kickoff_time)}
            </span>
          </div>

          {/* Countdown */}
          <div className="flex items-center justify-between text-xs">
            <span className="text-gray-500">Starts in</span>
            <span className="font-medium text-primary">
              {getTimeUntilMatch(match.match_date, match.kickoff_time)}
            </span>
          </div>

          {/* Venue */}
          {match.stadium && (
            <div className="flex items-center justify-between text-xs">
              <span className="text-gray-500">Venue</span>
              <span className="font-medium truncate max-w-24">
                {match.stadium}
              </span>
            </div>
          )}
        </div>

        {/* League Info */}
        {match.league_country && (
          <div className="mt-3 pt-3 border-t border-gray-100">
            <div className="flex items-center justify-between text-xs">
              <span className="text-gray-500">Country</span>
              <span className="font-medium">
                {match.league_country}
              </span>
            </div>
          </div>
        )}
      </div>
    </Link>
  )
}
