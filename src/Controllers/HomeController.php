<?php

namespace WFN24\Controllers;

use Inertia\Inertia;
use WFN24\Models\NewsArticle;
use WFN24\Models\FootballMatch;
use WFN24\Models\League;
use WFN24\Services\FootballApiService;

class HomeController
{
    private $newsArticle;
    private $match;
    private $league;
    private $footballApi;

    public function __construct()
    {
        $this->newsArticle = new NewsArticle();
        $this->match = new FootballMatch();
        $this->league = new League();
        $this->footballApi = new FootballApiService();
    }

    public function index()
    {
        // Get featured articles
        $featuredArticles = $this->newsArticle->getFeaturedArticles(5);
        
        // Get latest news
        $latestNews = $this->newsArticle->getPublishedArticles(1, 10);
        
        // Get live matches
        $liveMatches = $this->match->getLiveMatches();
        
        // Get upcoming matches
        $upcomingMatches = $this->match->getUpcomingMatches(5);
        
        // Get major leagues
        $majorLeagues = $this->league->getMajorLeagues();

        return Inertia::render('Home', [
            'featuredArticles' => $featuredArticles,
            'latestNews' => $latestNews['data'],
            'liveMatches' => $liveMatches,
            'upcomingMatches' => $upcomingMatches,
            'majorLeagues' => $majorLeagues,
        ]);
    }

    public function search()
    {
        $query = $_GET['q'] ?? '';
        $type = $_GET['type'] ?? 'all';
        
        if (empty($query)) {
            return Inertia::render('Search', [
                'query' => '',
                'type' => $type,
                'results' => [],
                'total' => 0
            ]);
        }

        $results = [];
        $total = 0;

        switch ($type) {
            case 'news':
                $newsResults = $this->newsArticle->searchArticles($query, 1, 20);
                $results = $newsResults['data'];
                $total = $newsResults['total'];
                break;
                
            case 'teams':
                $teamModel = new \WFN24\Models\Team();
                $results = $teamModel->searchTeams($query);
                $total = count($results);
                break;
                
            case 'players':
                $playerModel = new \WFN24\Models\Player();
                $results = $playerModel->searchPlayers($query);
                $total = count($results);
                break;
                
            case 'leagues':
                $results = $this->league->searchLeagues($query);
                $total = count($results);
                break;
                
            default: // all
                $newsResults = $this->newsArticle->searchArticles($query, 1, 10);
                $teamModel = new \WFN24\Models\Team();
                $playerModel = new \WFN24\Models\Player();
                
                $results = [
                    'news' => $newsResults['data'],
                    'teams' => $teamModel->searchTeams($query),
                    'players' => $playerModel->searchPlayers($query),
                    'leagues' => $this->league->searchLeagues($query)
                ];
                $total = $newsResults['total'] + count($results['teams']) + count($results['players']) + count($results['leagues']);
                break;
        }

        return Inertia::render('Search', [
            'query' => $query,
            'type' => $type,
            'results' => $results,
            'total' => $total
        ]);
    }
}
