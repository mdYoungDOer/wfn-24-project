import React from 'react'
import { Link } from '@inertiajs/react'

export default function LeagueCard({ league }) {
  return (
    <Link href={`/league/${league.id}`} className="block">
      <div className="card-hover p-4 text-center">
        {/* League Logo */}
        <div className="mb-3">
          {league.logo ? (
            <img
              src={league.logo}
              alt={league.name}
              className="w-16 h-16 mx-auto object-contain"
            />
          ) : (
            <div className="w-16 h-16 mx-auto bg-gradient-to-br from-primary to-secondary rounded-full flex items-center justify-center">
              <span className="text-white text-2xl font-bold">
                {league.name.charAt(0)}
              </span>
            </div>
          )}
        </div>

        {/* League Name */}
        <h3 className="font-bold text-neutral text-sm mb-1 line-clamp-2">
          {league.name}
        </h3>

        {/* League Country */}
        {league.country && (
          <p className="text-gray-500 text-xs mb-2">
            {league.country}
          </p>
        )}

        {/* League Type */}
        {league.type && (
          <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${
            league.type === 'cup' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800'
          }`}>
            {league.type === 'cup' ? 'Cup' : 'League'}
          </span>
        )}

        {/* Season */}
        {league.season && (
          <p className="text-gray-400 text-xs mt-2">
            {league.season}
          </p>
        )}
      </div>
    </Link>
  )
}
