import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';

const Dashboard = () => {
    const [dashboardData, setDashboardData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        fetchDashboardData();
    }, []);

    const fetchDashboardData = async () => {
        try {
            const response = await fetch('/admin');
            if (!response.ok) {
                throw new Error('Failed to fetch dashboard data');
            }
            const data = await response.json();
            setDashboardData(data);
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return (
            <div className="min-h-screen bg-gray-50 flex items-center justify-center">
                <div className="text-center">
                    <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
                    <p className="mt-4 text-gray-600">Loading dashboard...</p>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="min-h-screen bg-gray-50 flex items-center justify-center">
                <div className="text-center">
                    <div className="text-red-500 text-6xl mb-4">⚠️</div>
                    <h2 className="text-2xl font-bold text-gray-800 mb-2">Error Loading Dashboard</h2>
                    <p className="text-gray-600 mb-4">{error}</p>
                    <button 
                        onClick={fetchDashboardData}
                        className="bg-primary text-white px-4 py-2 rounded-lg hover:bg-red-700 transition"
                    >
                        Try Again
                    </button>
                </div>
            </div>
        );
    }

    if (!dashboardData) {
        return null;
    }

    const { stats, recent_articles, recent_users, live_matches } = dashboardData;

    return (
        <div className="min-h-screen bg-gray-50">
            {/* Header */}
            <header className="bg-white shadow-sm border-b border-gray-200">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between items-center h-16">
                        <div className="flex items-center space-x-3">
                            <div className="w-10 h-10 bg-gradient-to-r from-primary to-secondary rounded-lg flex items-center justify-center">
                                <span className="text-white font-bold text-lg">W</span>
                            </div>
                            <div>
                                <h1 className="text-2xl font-bold text-gray-800">WFN24 Admin</h1>
                                <p className="text-xs text-gray-500">Content Management System</p>
                            </div>
                        </div>
                        
                        <nav className="flex space-x-8">
                            <Link to="/admin" className="text-primary font-medium">Dashboard</Link>
                            <Link to="/admin/articles" className="text-gray-700 hover:text-primary font-medium">Articles</Link>
                            <Link to="/admin/users" className="text-gray-700 hover:text-primary font-medium">Users</Link>
                            <Link to="/admin/matches" className="text-gray-700 hover:text-primary font-medium">Matches</Link>
                            <Link to="/" className="text-gray-700 hover:text-primary font-medium">View Site</Link>
                        </nav>
                    </div>
                </div>
            </header>

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                {/* Stats Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div className="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                        <div className="flex items-center">
                            <div className="p-3 rounded-full bg-blue-100 text-blue-600">
                                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div className="ml-4">
                                <p className="text-sm font-medium text-gray-600">Total Articles</p>
                                <p className="text-2xl font-semibold text-gray-900">{stats.total_articles}</p>
                            </div>
                        </div>
                    </div>

                    <div className="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                        <div className="flex items-center">
                            <div className="p-3 rounded-full bg-green-100 text-green-600">
                                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                            </div>
                            <div className="ml-4">
                                <p className="text-sm font-medium text-gray-600">Total Users</p>
                                <p className="text-2xl font-semibold text-gray-900">{stats.total_users}</p>
                            </div>
                        </div>
                    </div>

                    <div className="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
                        <div className="flex items-center">
                            <div className="p-3 rounded-full bg-red-100 text-red-600">
                                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div className="ml-4">
                                <p className="text-sm font-medium text-gray-600">Live Matches</p>
                                <p className="text-2xl font-semibold text-gray-900">{stats.live_matches}</p>
                            </div>
                        </div>
                    </div>

                    <div className="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                        <div className="flex items-center">
                            <div className="p-3 rounded-full bg-purple-100 text-purple-600">
                                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                            </div>
                            <div className="ml-4">
                                <p className="text-sm font-medium text-gray-600">Total Teams</p>
                                <p className="text-2xl font-semibold text-gray-900">{stats.total_teams}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Content Grid */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    {/* Recent Articles */}
                    <div className="bg-white rounded-lg shadow-md">
                        <div className="px-6 py-4 border-b border-gray-200">
                            <h3 className="text-lg font-semibold text-gray-800">Recent Articles</h3>
                        </div>
                        <div className="p-6">
                            {recent_articles.length > 0 ? (
                                <div className="space-y-4">
                                    {recent_articles.map((article) => (
                                        <div key={article.id} className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                            <div className="flex-1">
                                                <h4 className="font-medium text-gray-800 truncate">{article.title}</h4>
                                                <p className="text-sm text-gray-500">By {article.author_name}</p>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <span className={`px-2 py-1 text-xs rounded-full ${
                                                    article.is_published 
                                                        ? 'bg-green-100 text-green-800' 
                                                        : 'bg-yellow-100 text-yellow-800'
                                                }`}>
                                                    {article.is_published ? 'Published' : 'Draft'}
                                                </span>
                                                <span className="text-sm text-gray-500">{article.view_count} views</span>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-gray-500 text-center py-8">No articles found</p>
                            )}
                            <div className="mt-6">
                                <Link 
                                    to="/admin/articles"
                                    className="block w-full text-center bg-primary text-white py-2 px-4 rounded-lg hover:bg-red-700 transition"
                                >
                                    View All Articles
                                </Link>
                            </div>
                        </div>
                    </div>

                    {/* Recent Users */}
                    <div className="bg-white rounded-lg shadow-md">
                        <div className="px-6 py-4 border-b border-gray-200">
                            <h3 className="text-lg font-semibold text-gray-800">Recent Users</h3>
                        </div>
                        <div className="p-6">
                            {recent_users.length > 0 ? (
                                <div className="space-y-4">
                                    {recent_users.map((user) => (
                                        <div key={user.id} className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                            <div className="flex items-center space-x-3">
                                                <div className="w-10 h-10 bg-gradient-to-r from-primary to-secondary rounded-full flex items-center justify-center">
                                                    <span className="text-white font-semibold">
                                                        {user.first_name ? user.first_name[0] : user.username[0]}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h4 className="font-medium text-gray-800">
                                                        {user.first_name} {user.last_name}
                                                    </h4>
                                                    <p className="text-sm text-gray-500">@{user.username}</p>
                                                </div>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                {user.is_admin && (
                                                    <span className="px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded-full">
                                                        Admin
                                                    </span>
                                                )}
                                                <span className="text-sm text-gray-500">
                                                    {new Date(user.created_at).toLocaleDateString()}
                                                </span>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-gray-500 text-center py-8">No users found</p>
                            )}
                            <div className="mt-6">
                                <Link 
                                    to="/admin/users"
                                    className="block w-full text-center bg-secondary text-white py-2 px-4 rounded-lg hover:bg-purple-700 transition"
                                >
                                    View All Users
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Live Matches */}
                {live_matches.length > 0 && (
                    <div className="mt-8 bg-white rounded-lg shadow-md">
                        <div className="px-6 py-4 border-b border-gray-200">
                            <h3 className="text-lg font-semibold text-gray-800">Live Matches</h3>
                        </div>
                        <div className="p-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                {live_matches.map((match) => (
                                    <div key={match.id} className="p-4 bg-red-50 border border-red-200 rounded-lg">
                                        <div className="flex items-center justify-between mb-3">
                                            <span className="text-xs font-semibold text-red-600 bg-red-100 px-2 py-1 rounded">LIVE</span>
                                            <span className="text-xs text-gray-500">{match.league_name}</span>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <div className="text-center">
                                                <div className="text-sm font-medium">{match.home_team_name}</div>
                                                <div className="text-xs text-gray-500">Home</div>
                                            </div>
                                            <div className="text-center">
                                                <div className="text-xl font-bold text-gray-800">
                                                    {match.home_score} - {match.away_score}
                                                </div>
                                                <div className="text-xs text-gray-500">{match.status}</div>
                                            </div>
                                            <div className="text-center">
                                                <div className="text-sm font-medium">{match.away_team_name}</div>
                                                <div className="text-xs text-gray-500">Away</div>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                )}

                {/* Quick Actions */}
                <div className="mt-8 bg-white rounded-lg shadow-md">
                    <div className="px-6 py-4 border-b border-gray-200">
                        <h3 className="text-lg font-semibold text-gray-800">Quick Actions</h3>
                    </div>
                    <div className="p-6">
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <Link 
                                to="/admin/articles/new"
                                className="flex items-center p-4 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition"
                            >
                                <div className="p-2 bg-blue-100 text-blue-600 rounded-lg mr-3">
                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 className="font-medium text-gray-800">Create Article</h4>
                                    <p className="text-sm text-gray-500">Write a new news article</p>
                                </div>
                            </Link>

                            <Link 
                                to="/admin/users"
                                className="flex items-center p-4 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition"
                            >
                                <div className="p-2 bg-green-100 text-green-600 rounded-lg mr-3">
                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 className="font-medium text-gray-800">Manage Users</h4>
                                    <p className="text-sm text-gray-500">View and manage user accounts</p>
                                </div>
                            </Link>

                            <Link 
                                to="/admin/matches"
                                className="flex items-center p-4 bg-purple-50 border border-purple-200 rounded-lg hover:bg-purple-100 transition"
                            >
                                <div className="p-2 bg-purple-100 text-purple-600 rounded-lg mr-3">
                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 className="font-medium text-gray-800">Match Center</h4>
                                    <p className="text-sm text-gray-500">Manage matches and results</p>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Dashboard;
