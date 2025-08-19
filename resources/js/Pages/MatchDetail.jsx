import React, { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import { Line, Doughnut } from 'react-chartjs-2';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  ArcElement,
} from 'chart.js';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  ArcElement
);

const MatchDetail = () => {
  const { id } = useParams();
  const [matchData, setMatchData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [activeTab, setActiveTab] = useState('overview');

  useEffect(() => {
    fetchMatchDetails();
  }, [id]);

  const fetchMatchDetails = async () => {
    try {
      setLoading(true);
      const response = await fetch(`/api/matches/${id}`);
      const data = await response.json();
      
      if (data.error) {
        setError(data.error);
      } else {
        setMatchData(data);
      }
    } catch (err) {
      setError('Failed to load match details');
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
          <p className="mt-4 text-gray-600">Loading match details...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="text-red-500 text-6xl mb-4">⚽</div>
          <h2 className="text-2xl font-bold text-gray-800 mb-2">Match Not Found</h2>
          <p className="text-gray-600 mb-4">{error}</p>
          <button 
            onClick={() => window.history.back()}
            className="bg-primary text-white px-6 py-2 rounded-lg hover:bg-secondary transition"
          >
            Go Back
          </button>
        </div>
      </div>
    );
  }

  const { match, statistics, lineups, events } = matchData;

  const getStatusColor = (status) => {
    switch (status) {
      case 'LIVE': return 'text-red-600 bg-red-100';
      case 'FINISHED': return 'text-green-600 bg-green-100';
      case 'SCHEDULED': return 'text-blue-600 bg-blue-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const possessionData = {
    labels: [match.home_team_name, match.away_team_name],
    datasets: [{
      data: [statistics.possession_home, statistics.possession_away],
      backgroundColor: ['#e41e5b', '#9a0864'],
      borderWidth: 0,
    }]
  };

  const shotsData = {
    labels: ['Shots', 'Shots on Target'],
    datasets: [
      {
        label: match.home_team_name,
        data: [statistics.shots_home, statistics.shots_on_target_home],
        borderColor: '#e41e5b',
        backgroundColor: 'rgba(228, 30, 91, 0.1)',
      },
      {
        label: match.away_team_name,
        data: [statistics.shots_away, statistics.shots_on_target_away],
        borderColor: '#9a0864',
        backgroundColor: 'rgba(154, 8, 100, 0.1)',
      }
    ]
  };

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <div className="bg-white shadow-sm border-b">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div className="flex items-center justify-between">
            <div className="flex items-center space-x-4">
              <button 
                onClick={() => window.history.back()}
                className="text-gray-600 hover:text-gray-800"
              >
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                </svg>
              </button>
              <div>
                <h1 className="text-2xl font-bold text-gray-900">Match Details</h1>
                <p className="text-gray-600">{match.league_name}</p>
              </div>
            </div>
            <div className={`px-3 py-1 rounded-full text-sm font-semibold ${getStatusColor(match.status)}`}>
              {match.status}
            </div>
          </div>
        </div>
      </div>

      {/* Match Score Card */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="bg-white rounded-lg shadow-lg p-8 mb-8">
          <div className="text-center mb-6">
            <h2 className="text-3xl font-bold text-gray-900 mb-2">
              {match.home_team_name} vs {match.away_team_name}
            </h2>
            <p className="text-gray-600">{new Date(match.kickoff_time).toLocaleDateString()}</p>
          </div>

          <div className="flex items-center justify-center space-x-8">
            {/* Home Team */}
            <div className="text-center flex-1">
              <img 
                src={match.home_team_logo || '/placeholder-team.png'} 
                alt={match.home_team_name}
                className="w-20 h-20 mx-auto mb-4"
              />
              <h3 className="text-xl font-semibold text-gray-900">{match.home_team_name}</h3>
            </div>

            {/* Score */}
            <div className="text-center">
              <div className="text-6xl font-bold text-gray-900 mb-2">
                {match.home_score} - {match.away_score}
              </div>
              {match.status === 'LIVE' && (
                <div className="text-red-600 font-semibold animate-pulse">
                  LIVE - {match.elapsed_time || '0'}'
                </div>
              )}
            </div>

            {/* Away Team */}
            <div className="text-center flex-1">
              <img 
                src={match.away_team_logo || '/placeholder-team.png'} 
                alt={match.away_team_name}
                className="w-20 h-20 mx-auto mb-4"
              />
              <h3 className="text-xl font-semibold text-gray-900">{match.away_team_name}</h3>
            </div>
          </div>
        </div>

        {/* Tabs */}
        <div className="bg-white rounded-lg shadow-lg mb-8">
          <div className="border-b border-gray-200">
            <nav className="flex space-x-8 px-6">
              {['overview', 'statistics', 'lineups', 'events'].map((tab) => (
                <button
                  key={tab}
                  onClick={() => setActiveTab(tab)}
                  className={`py-4 px-1 border-b-2 font-medium text-sm capitalize ${
                    activeTab === tab
                      ? 'border-primary text-primary'
                      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                  }`}
                >
                  {tab}
                </button>
              ))}
            </nav>
          </div>

          <div className="p-6">
            {/* Overview Tab */}
            {activeTab === 'overview' && (
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div className="bg-gray-50 rounded-lg p-6">
                  <h3 className="text-lg font-semibold mb-4">Possession</h3>
                  <div className="w-48 h-48 mx-auto">
                    <Doughnut data={possessionData} options={{ cutout: '60%' }} />
                  </div>
                </div>
                <div className="bg-gray-50 rounded-lg p-6">
                  <h3 className="text-lg font-semibold mb-4">Shots Comparison</h3>
                  <Line data={shotsData} options={{ responsive: true }} />
                </div>
              </div>
            )}

            {/* Statistics Tab */}
            {activeTab === 'statistics' && (
              <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                {[
                  { label: 'Shots', home: statistics.shots_home, away: statistics.shots_away },
                  { label: 'Shots on Target', home: statistics.shots_on_target_home, away: statistics.shots_on_target_away },
                  { label: 'Corners', home: statistics.corners_home, away: statistics.corners_away },
                  { label: 'Fouls', home: statistics.fouls_home, away: statistics.fouls_away },
                  { label: 'Yellow Cards', home: statistics.yellow_cards_home, away: statistics.yellow_cards_away },
                  { label: 'Red Cards', home: statistics.red_cards_home, away: statistics.red_cards_away },
                ].map((stat, index) => (
                  <div key={index} className="bg-gray-50 rounded-lg p-4 text-center">
                    <h4 className="text-sm font-medium text-gray-600 mb-2">{stat.label}</h4>
                    <div className="flex justify-between items-center">
                      <span className="text-lg font-bold text-primary">{stat.home}</span>
                      <span className="text-lg font-bold text-secondary">{stat.away}</span>
                    </div>
                  </div>
                ))}
              </div>
            )}

            {/* Lineups Tab */}
            {activeTab === 'lineups' && (
              <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                {/* Home Team Lineup */}
                <div>
                  <h3 className="text-lg font-semibold mb-4">{match.home_team_name}</h3>
                  <div className="bg-gray-50 rounded-lg p-4">
                    <h4 className="font-medium text-gray-700 mb-3">Starting XI</h4>
                    {lineups.home.starting.map((player, index) => (
                      <div key={index} className="flex items-center justify-between py-2 border-b border-gray-200 last:border-b-0">
                        <div className="flex items-center space-x-3">
                          <span className="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center text-sm font-bold">
                            {player.number}
                          </span>
                          <span className="font-medium">{player.name}</span>
                        </div>
                        <span className="text-sm text-gray-600">{player.position}</span>
                      </div>
                    ))}
                    
                    <h4 className="font-medium text-gray-700 mb-3 mt-6">Substitutes</h4>
                    {lineups.home.substitutes.map((player, index) => (
                      <div key={index} className="flex items-center justify-between py-2 border-b border-gray-200 last:border-b-0">
                        <div className="flex items-center space-x-3">
                          <span className="w-8 h-8 bg-gray-300 text-gray-700 rounded-full flex items-center justify-center text-sm font-bold">
                            {player.number}
                          </span>
                          <span className="font-medium">{player.name}</span>
                        </div>
                        <span className="text-sm text-gray-600">{player.position}</span>
                      </div>
                    ))}
                  </div>
                </div>

                {/* Away Team Lineup */}
                <div>
                  <h3 className="text-lg font-semibold mb-4">{match.away_team_name}</h3>
                  <div className="bg-gray-50 rounded-lg p-4">
                    <h4 className="font-medium text-gray-700 mb-3">Starting XI</h4>
                    {lineups.away.starting.map((player, index) => (
                      <div key={index} className="flex items-center justify-between py-2 border-b border-gray-200 last:border-b-0">
                        <div className="flex items-center space-x-3">
                          <span className="w-8 h-8 bg-secondary text-white rounded-full flex items-center justify-center text-sm font-bold">
                            {player.number}
                          </span>
                          <span className="font-medium">{player.name}</span>
                        </div>
                        <span className="text-sm text-gray-600">{player.position}</span>
                      </div>
                    ))}
                    
                    <h4 className="font-medium text-gray-700 mb-3 mt-6">Substitutes</h4>
                    {lineups.away.substitutes.map((player, index) => (
                      <div key={index} className="flex items-center justify-between py-2 border-b border-gray-200 last:border-b-0">
                        <div className="flex items-center space-x-3">
                          <span className="w-8 h-8 bg-gray-300 text-gray-700 rounded-full flex items-center justify-center text-sm font-bold">
                            {player.number}
                          </span>
                          <span className="font-medium">{player.name}</span>
                        </div>
                        <span className="text-sm text-gray-600">{player.position}</span>
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            )}

            {/* Events Tab */}
            {activeTab === 'events' && (
              <div className="space-y-4">
                {events.length > 0 ? (
                  events.map((event, index) => (
                    <div key={index} className="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                      <div className="text-center min-w-[60px]">
                        <span className="text-lg font-bold text-gray-900">{event.minute}'</span>
                      </div>
                      <div className="flex-1">
                        <div className="flex items-center space-x-2">
                          <span className={`px-2 py-1 rounded text-xs font-semibold ${
                            event.event_type === 'goal' ? 'bg-green-100 text-green-800' :
                            event.event_type === 'card' ? 'bg-yellow-100 text-yellow-800' :
                            'bg-blue-100 text-blue-800'
                          }`}>
                            {event.event_type.toUpperCase()}
                          </span>
                          <span className="font-medium">{event.player_name}</span>
                        </div>
                        {event.description && (
                          <p className="text-sm text-gray-600 mt-1">{event.description}</p>
                        )}
                      </div>
                    </div>
                  ))
                ) : (
                  <div className="text-center py-8">
                    <div className="text-gray-400 text-6xl mb-4">⚽</div>
                    <p className="text-gray-600">No events recorded yet</p>
                  </div>
                )}
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default MatchDetail;
