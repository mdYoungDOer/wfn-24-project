import React from 'react'
import { Link } from '@inertiajs/react'

export default function LeagueCard({ league }) {
  return (
    <Link href={`/leagues/${league.id}`} className="block">
      <div className="bg-white rounded-lg shadow-sm p-3 hover:shadow-md transition-shadow border border-gray-200">
        {/* League Logo */}
        <div className="flex items-center justify-center mb-3">
          <div className="w-12 h-12 bg-gray-200 rounded-lg overflow-hidden flex items-center justify-center">
            {league.logo ? (
              <img
                src={league.logo}
                alt={league.name}
                className="w-full h-full object-cover"
              />
            ) : (
              <span className="text-2xl">🏆</span>
            )}
          </div>
        </div>

        {/* League Info */}
        <div className="text-center">
          <h4 className="font-semibold text-neutral text-sm mb-1 line-clamp-2">
            {league.name}
          </h4>
          <p className="text-xs text-gray-500 mb-2">
            {league.country}
          </p>
          
          {/* League Type Badge */}
          <span className="inline-block px-2 py-1 text-xs font-medium text-gray-600 bg-gray-100 rounded">
            {league.type || 'League'}
          </span>
        </div>
      </div>
    </Link>
  )
}
