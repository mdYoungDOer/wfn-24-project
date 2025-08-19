import React, { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import { Bar } from 'react-chartjs-2';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
} from 'chart.js';

ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
);

const LeagueDetail = () => {
  const { id } = useParams();
  const [leagueData, setLeagueData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [activeTab, setActiveTab] = useState('standings');

  useEffect(() => {
    fetchLeagueDetails();
  }, [id]);

  const fetchLeagueDetails = async () => {
    try {
      setLoading(true);
      const response = await fetch(`/api/leagues/${id}`);
      const data = await response.json();
      
      if (data.error) {
        setError(data.error);
      } else {
        setLeagueData(data);
      }
    } catch (err) {
      setError('Failed to load league details');
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
          <p className="mt-4 text-gray-600">Loading league details...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="text-red-500 text-6xl mb-4">🏆</div>
          <h2 className="text-2xl font-bold text-gray-800 mb-2">League Not Found</h2>
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

  const { league, standings, top_scorers, recent_matches, upcoming_matches } = leagueData;

  const standingsData = {
    labels: standings.slice(0, 10).map(team => team.name),
    datasets: [
      {
        label: 'Points',
        data: standings.slice(0, 10).map(team => team.points),
        backgroundColor: 'rgba(228, 30, 91, 0.8)',
        borderColor: '#e41e5b',
        borderWidth: 1,
      }
    ]
  };

  const getPositionColor = (position) => {
    if (position <= 4) return 'bg-green-100 text-green-800'; // Champions League
    if (position <= 6) return 'bg-blue-100 text-blue-800';   // Europa League
    if (position >= 18) return 'bg-red-100 text-red-800';    // Relegation
    return 'bg-gray-100 text-gray-800';
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
              <div className="flex items-center space-x-4">
                <img 
                  src={league.logo_url || '/placeholder-league.png'} 
                  alt={league.name}
                  className="w-12 h-12"
                />
                <div>
                  <h1 className="text-2xl font-bold text-gray-900">{league.name}</h1>
                  <p className="text-gray-600">{league.country}</p>
                </div>
              </div>
            </div>
            <div className="text-right">
              <div className="text-sm text-gray-600">Season {league.season}</div>
              <div className="text-sm text-gray-600">{league.type}</div>
            </div>
          </div>
        </div>
      </div>

      {/* Tabs */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="bg-white rounded-lg shadow-lg mb-8">
          <div className="border-b border-gray-200">
            <nav className="flex space-x-8 px-6">
              {['standings', 'top-scorers', 'fixtures', 'recent-matches'].map((tab) => (
                <button
                  key={tab}
                  onClick={() => setActiveTab(tab)}
                  className={`py-4 px-1 border-b-2 font-medium text-sm capitalize ${
                    activeTab === tab
                      ? 'border-primary text-primary'
                      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                  }`}
                >
                  {tab.replace('-', ' ')}
                </button>
              ))}
            </nav>
          </div>

          <div className="p-6">
            {/* Standings Tab */}
            {activeTab === 'standings' && (
              <div>
                <div className="mb-6">
                  <h3 className="text-lg font-semibold mb-4">League Table</h3>
                  <Bar data={standingsData} options={{ responsive: true }} />
                </div>
                
                <div className="overflow-x-auto">
                  <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                      <tr>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pos</th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Team</th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">P</th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">W</th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">D</th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">L</th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GF</th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GA</th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GD</th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pts</th>
                      </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                      {standings.map((team, index) => (
                        <tr key={team.id} className="hover:bg-gray-50">
                          <td className="px-6 py-4 whitespace-nowrap">
                            <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getPositionColor(team.position)}`}>
                              {team.position}
                            </span>
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap">
                            <div className="flex items-center">
                              <img 
                                src={team.logo_url || '/placeholder-team.png'} 
                                alt={team.name}
                                className="w-8 h-8 mr-3"
                              />
                              <div className="text-sm font-medium text-gray-900">{team.name}</div>
                            </div>
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{team.played}</td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{team.won}</td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{team.drawn}</td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{team.lost}</td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{team.goals_for}</td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{team.goals_against}</td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{team.goal_difference}</td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{team.points}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            )}

            {/* Top Scorers Tab */}
            {activeTab === 'top-scorers' && (
              <div>
                <h3 className="text-lg font-semibold mb-6">Top Scorers</h3>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <h4 className="text-md font-medium mb-4">Goals</h4>
                    <div className="space-y-3">
                      {top_scorers
                        .filter(player => player.goals > 0)
                        .slice(0, 10)
                        .map((player, index) => (
                          <div key={player.id} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div className="flex items-center space-x-3">
                              <span className="text-lg font-bold text-primary">{index + 1}</span>
                              <img 
                                src={player.team_logo || '/placeholder-team.png'} 
                                alt={player.team_name}
                                className="w-8 h-8"
                              />
                              <div>
                                <div className="font-medium">{player.name}</div>
                                <div className="text-sm text-gray-600">{player.team_name}</div>
                              </div>
                            </div>
                            <div className="text-right">
                              <div className="text-lg font-bold text-primary">{player.goals}</div>
                              <div className="text-sm text-gray-600">goals</div>
                            </div>
                          </div>
                        ))}
                    </div>
                  </div>
                  
                  <div>
                    <h4 className="text-md font-medium mb-4">Assists</h4>
                    <div className="space-y-3">
                      {top_scorers
                        .filter(player => player.assists > 0)
                        .slice(0, 10)
                        .map((player, index) => (
                          <div key={player.id} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div className="flex items-center space-x-3">
                              <span className="text-lg font-bold text-secondary">{index + 1}</span>
                              <img 
                                src={player.team_logo || '/placeholder-team.png'} 
                                alt={player.team_name}
                                className="w-8 h-8"
                              />
                              <div>
                                <div className="font-medium">{player.name}</div>
                                <div className="text-sm text-gray-600">{player.team_name}</div>
                              </div>
                            </div>
                            <div className="text-right">
                              <div className="text-lg font-bold text-secondary">{player.assists}</div>
                              <div className="text-sm text-gray-600">assists</div>
                            </div>
                          </div>
                        ))}
                    </div>
                  </div>
                </div>
              </div>
            )}

            {/* Fixtures Tab */}
            {activeTab === 'fixtures' && (
              <div>
                <h3 className="text-lg font-semibold mb-6">Upcoming Fixtures</h3>
                <div className="space-y-4">
                  {upcoming_matches.length > 0 ? (
                    upcoming_matches.map((match) => (
                      <div key={match.id} className="bg-gray-50 rounded-lg p-4">
                        <div className="flex items-center justify-between">
                          <div className="flex items-center space-x-4 flex-1">
                            <div className="text-center flex-1">
                              <div className="flex items-center justify-center space-x-3">
                                <img 
                                  src={match.home_team_logo || '/placeholder-team.png'} 
                                  alt={match.home_team_name}
                                  className="w-8 h-8"
                                />
                                <span className="font-medium">{match.home_team_name}</span>
                              </div>
                            </div>
                            
                            <div className="text-center">
                              <div className="text-lg font-bold text-gray-900">
                                {new Date(match.kickoff_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                              </div>
                              <div className="text-sm text-gray-600">
                                {new Date(match.kickoff_time).toLocaleDateString()}
                              </div>
                            </div>
                            
                            <div className="text-center flex-1">
                              <div className="flex items-center justify-center space-x-3">
                                <span className="font-medium">{match.away_team_name}</span>
                                <img 
                                  src={match.away_team_logo || '/placeholder-team.png'} 
                                  alt={match.away_team_name}
                                  className="w-8 h-8"
                                />
                              </div>
                            </div>
                          </div>
                          
                          <button className="ml-4 bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary transition">
                            View Details
                          </button>
                        </div>
                      </div>
                    ))
                  ) : (
                    <div className="text-center py-8">
                      <div className="text-gray-400 text-6xl mb-4">📅</div>
                      <p className="text-gray-600">No upcoming fixtures</p>
                    </div>
                  )}
                </div>
              </div>
            )}

            {/* Recent Matches Tab */}
            {activeTab === 'recent-matches' && (
              <div>
                <h3 className="text-lg font-semibold mb-6">Recent Matches</h3>
                <div className="space-y-4">
                  {recent_matches.length > 0 ? (
                    recent_matches.map((match) => (
                      <div key={match.id} className="bg-gray-50 rounded-lg p-4">
                        <div className="flex items-center justify-between">
                          <div className="flex items-center space-x-4 flex-1">
                            <div className="text-center flex-1">
                              <div className="flex items-center justify-center space-x-3">
                                <img 
                                  src={match.home_team_logo || '/placeholder-team.png'} 
                                  alt={match.home_team_name}
                                  className="w-8 h-8"
                                />
                                <span className="font-medium">{match.home_team_name}</span>
                              </div>
                            </div>
                            
                            <div className="text-center">
                              <div className="text-2xl font-bold text-gray-900">
                                {match.home_score} - {match.away_score}
                              </div>
                              <div className="text-sm text-gray-600">
                                {new Date(match.kickoff_time).toLocaleDateString()}
                              </div>
                            </div>
                            
                            <div className="text-center flex-1">
                              <div className="flex items-center justify-center space-x-3">
                                <span className="font-medium">{match.away_team_name}</span>
                                <img 
                                  src={match.away_team_logo || '/placeholder-team.png'} 
                                  alt={match.away_team_name}
                                  className="w-8 h-8"
                                />
                              </div>
                            </div>
                          </div>
                          
                          <button className="ml-4 bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary transition">
                            View Details
                          </button>
                        </div>
                      </div>
                    ))
                  ) : (
                    <div className="text-center py-8">
                      <div className="text-gray-400 text-6xl mb-4">⚽</div>
                      <p className="text-gray-600">No recent matches</p>
                    </div>
                  )}
                </div>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default LeagueDetail;
