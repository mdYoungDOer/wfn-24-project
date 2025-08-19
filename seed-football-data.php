<?php
/**
 * WFN24 Football Data Seeder
 * Populates the app with real football data from API-Football
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    echo "❌ .env file not found. Please ensure your environment variables are set.\n";
    exit(1);
}

use WFN24\Services\FootballApiService;
use WFN24\Config\Database;

class FootballDataSeeder
{
    private $apiService;
    private $db;
    private $logger;

    public function __construct()
    {
        $this->apiService = new FootballApiService();
        $this->db = Database::getInstance();
        
        // Create logs directory if it doesn't exist
        if (!is_dir(__DIR__ . '/logs')) {
            mkdir(__DIR__ . '/logs', 0755, true);
        }
        
        $this->logger = new \Monolog\Logger('seeder');
        $this->logger->pushHandler(new \Monolog\Handler\StreamHandler(__DIR__ . '/logs/seeder.log', \Monolog\Logger::INFO));
    }

    public function run()
    {
        echo "🚀 Starting WFN24 Football Data Seeding...\n\n";
        
        try {
            // 1. Seed Major Leagues
            $this->seedMajorLeagues();
            
            // 2. Seed Live Matches
            $this->seedLiveMatches();
            
            // 3. Seed Upcoming Matches
            $this->seedUpcomingMatches();
            
            // 4. Seed Sample News Articles
            $this->seedNewsArticles();
            
            // 5. Seed Top Scorers
            $this->seedTopScorers();
            
            echo "✅ Football data seeding completed successfully!\n";
            echo "🌐 Visit your app: https://wfn24-project-qrml7.ondigitalocean.app\n";
            
        } catch (Exception $e) {
            echo "❌ Seeding failed: " . $e->getMessage() . "\n";
            $this->logger->error('Seeding failed: ' . $e->getMessage());
        }
    }

    private function seedMajorLeagues()
    {
        echo "📊 Seeding major leagues...\n";
        
        $leagues = $this->apiService->getMajorLeagues();
        
        if (empty($leagues)) {
            echo "⚠️  No leagues found from API, using fallback data...\n";
            $leagues = $this->getFallbackLeagues();
        }
        
        foreach ($leagues as $league) {
            try {
                $stmt = $this->db->getConnection()->prepare(
                    "INSERT INTO leagues (api_league_id, name, country, logo_url, type, season, is_active) 
                     VALUES (?, ?, ?, ?, ?, ?, TRUE)
                     ON CONFLICT (api_league_id) 
                     DO UPDATE SET name = EXCLUDED.name, country = EXCLUDED.country, logo_url = EXCLUDED.logo_url"
                );
                
                $stmt->execute([
                    $league['api_league_id'],
                    $league['name'],
                    $league['country'],
                    $league['logo_url'],
                    $league['type'],
                    $league['season']
                ]);
                
                echo "✅ Added/Updated: {$league['name']} ({$league['country']})\n";
                
            } catch (Exception $e) {
                echo "❌ Failed to seed league {$league['name']}: " . $e->getMessage() . "\n";
            }
        }
        
        echo "📊 Leagues seeding completed.\n\n";
    }

    private function seedLiveMatches()
    {
        echo "⚽ Seeding live matches...\n";
        
        $liveMatches = $this->apiService->getLiveMatches();
        
        if (empty($liveMatches)) {
            echo "ℹ️  No live matches currently available.\n";
            return;
        }
        
        foreach ($liveMatches as $match) {
            try {
                // First, ensure teams exist
                $homeTeamId = $this->ensureTeamExists($match['home_team']);
                $awayTeamId = $this->ensureTeamExists($match['away_team']);
                
                // Then, ensure league exists
                $leagueId = $this->ensureLeagueExists($match['league']);
                
                // Finally, create/update match
                $stmt = $this->db->getConnection()->prepare(
                    "INSERT INTO matches (api_match_id, home_team_id, away_team_id, league_id, match_date, status, home_score, away_score, venue, is_live) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, TRUE)
                     ON CONFLICT (api_match_id) 
                     DO UPDATE SET home_score = EXCLUDED.home_score, away_score = EXCLUDED.away_score, status = EXCLUDED.status, is_live = TRUE"
                );
                
                $stmt->execute([
                    $match['api_match_id'],
                    $homeTeamId,
                    $awayTeamId,
                    $leagueId,
                    $match['match_date'],
                    $match['status'],
                    $match['home_score'],
                    $match['away_score'],
                    $match['venue']
                ]);
                
                echo "✅ Live Match: {$match['home_team']['name']} {$match['home_score']} - {$match['away_score']} {$match['away_team']['name']}\n";
                
            } catch (Exception $e) {
                echo "❌ Failed to seed live match: " . $e->getMessage() . "\n";
            }
        }
        
        echo "⚽ Live matches seeding completed.\n\n";
    }

    private function seedUpcomingMatches()
    {
        echo "📅 Seeding upcoming matches...\n";
        
        $upcomingMatches = $this->apiService->getUpcomingMatches(20);
        
        if (empty($upcomingMatches)) {
            echo "ℹ️  No upcoming matches found.\n";
            return;
        }
        
        foreach ($upcomingMatches as $match) {
            try {
                // First, ensure teams exist
                $homeTeamId = $this->ensureTeamExists($match['home_team']);
                $awayTeamId = $this->ensureTeamExists($match['away_team']);
                
                // Then, ensure league exists
                $leagueId = $this->ensureLeagueExists($match['league']);
                
                // Finally, create/update match
                $stmt = $this->db->getConnection()->prepare(
                    "INSERT INTO matches (api_match_id, home_team_id, away_team_id, league_id, match_date, status, venue, is_live) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, FALSE)
                     ON CONFLICT (api_match_id) 
                     DO UPDATE SET match_date = EXCLUDED.match_date, status = EXCLUDED.status, venue = EXCLUDED.venue"
                );
                
                $stmt->execute([
                    $match['api_match_id'],
                    $homeTeamId,
                    $awayTeamId,
                    $leagueId,
                    $match['match_date'],
                    $match['status'],
                    $match['venue']
                ]);
                
                echo "✅ Upcoming: {$match['home_team']['name']} vs {$match['away_team']['name']} ({$match['league']['name']})\n";
                
            } catch (Exception $e) {
                echo "❌ Failed to seed upcoming match: " . $e->getMessage() . "\n";
            }
        }
        
        echo "📅 Upcoming matches seeding completed.\n\n";
    }

    private function seedNewsArticles()
    {
        echo "📰 Seeding news articles...\n";
        
        $articles = $this->getSampleNewsArticles();
        
        foreach ($articles as $article) {
            try {
                $stmt = $this->db->getConnection()->prepare(
                    "INSERT INTO news_articles (title, slug, excerpt, content, featured_image, category_id, author_name, is_featured, is_published, published_at, view_count) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, TRUE, ?, ?)
                     ON CONFLICT (slug) DO NOTHING"
                );
                
                $stmt->execute([
                    $article['title'],
                    $article['slug'],
                    $article['excerpt'],
                    $article['content'],
                    $article['featured_image'],
                    $article['category_id'],
                    $article['author_name'],
                    $article['is_featured'],
                    $article['published_at'],
                    $article['view_count']
                ]);
                
                echo "✅ News: {$article['title']}\n";
                
            } catch (Exception $e) {
                echo "❌ Failed to seed news article: " . $e->getMessage() . "\n";
            }
        }
        
        echo "📰 News articles seeding completed.\n\n";
    }

    private function seedTopScorers()
    {
        echo "🥅 Seeding top scorers...\n";
        
        // Major league IDs: Premier League (39), La Liga (140), Serie A (135), Bundesliga (78), Ligue 1 (61)
        $leagueIds = [39, 140, 135, 78, 61];
        
        foreach ($leagueIds as $leagueId) {
            try {
                $scorers = $this->apiService->getTopScorers($leagueId);
                
                if (!empty($scorers)) {
                    foreach (array_slice($scorers, 0, 5) as $scorer) { // Top 5 scorers
                        $this->ensurePlayerExists($scorer);
                    }
                    echo "✅ Top scorers for league ID {$leagueId}: " . count($scorers) . " players\n";
                }
                
            } catch (Exception $e) {
                echo "❌ Failed to seed top scorers for league {$leagueId}: " . $e->getMessage() . "\n";
            }
        }
        
        echo "🥅 Top scorers seeding completed.\n\n";
    }

    private function ensureTeamExists($teamData)
    {
        $stmt = $this->db->getConnection()->prepare(
            "INSERT INTO teams (name, logo_url, is_active) 
             VALUES (?, ?, TRUE)
             ON CONFLICT (name) 
             DO UPDATE SET logo_url = EXCLUDED.logo_url
             RETURNING id"
        );
        
        $stmt->execute([$teamData['name'], $teamData['logo']]);
        $result = $stmt->fetch();
        
        return $result['id'];
    }

    private function ensureLeagueExists($leagueData)
    {
        $stmt = $this->db->getConnection()->prepare(
            "INSERT INTO leagues (name, country, is_active) 
             VALUES (?, ?, TRUE)
             ON CONFLICT (name) 
             DO UPDATE SET country = EXCLUDED.country
             RETURNING id"
        );
        
        $stmt->execute([$leagueData['name'], $leagueData['country']]);
        $result = $stmt->fetch();
        
        return $result['id'];
    }

    private function ensurePlayerExists($playerData)
    {
        $stmt = $this->db->getConnection()->prepare(
            "INSERT INTO players (name, photo_url, is_active) 
             VALUES (?, ?, TRUE)
             ON CONFLICT (name) 
             DO UPDATE SET photo_url = EXCLUDED.photo_url
             RETURNING id"
        );
        
        $stmt->execute([$playerData['name'], $playerData['photo']]);
        $result = $stmt->fetch();
        
        return $result['id'];
    }

    private function getFallbackLeagues()
    {
        return [
            [
                'api_league_id' => 39,
                'name' => 'Premier League',
                'country' => 'England',
                'logo_url' => 'https://media.api-sports.io/football/leagues/39.png',
                'type' => 'League',
                'season' => date('Y')
            ],
            [
                'api_league_id' => 140,
                'name' => 'La Liga',
                'country' => 'Spain',
                'logo_url' => 'https://media.api-sports.io/football/leagues/140.png',
                'type' => 'League',
                'season' => date('Y')
            ],
            [
                'api_league_id' => 135,
                'name' => 'Serie A',
                'country' => 'Italy',
                'logo_url' => 'https://media.api-sports.io/football/leagues/135.png',
                'type' => 'League',
                'season' => date('Y')
            ],
            [
                'api_league_id' => 78,
                'name' => 'Bundesliga',
                'country' => 'Germany',
                'logo_url' => 'https://media.api-sports.io/football/leagues/78.png',
                'type' => 'League',
                'season' => date('Y')
            ],
            [
                'api_league_id' => 61,
                'name' => 'Ligue 1',
                'country' => 'France',
                'logo_url' => 'https://media.api-sports.io/football/leagues/61.png',
                'type' => 'League',
                'season' => date('Y')
            ]
        ];
    }

    private function getSampleNewsArticles()
    {
        return [
            [
                'title' => 'Premier League Title Race Reaches Climax as Arsenal and Manchester City Battle for Glory',
                'slug' => 'premier-league-title-race-climax-arsenal-manchester-city',
                'excerpt' => 'The Premier League title race is reaching its most dramatic conclusion in years, with Arsenal and Manchester City separated by just a single point heading into the final weeks of the season.',
                'content' => '<p>The Premier League title race has delivered one of the most thrilling conclusions in recent memory, with Arsenal and Manchester City locked in a battle that could go down to the final day of the season.</p><p>Arsenal, under the guidance of Mikel Arteta, have shown remarkable consistency throughout the campaign, while Pep Guardiola\'s Manchester City are chasing an unprecedented fourth consecutive Premier League title.</p><p>With just three games remaining for both teams, every point becomes crucial. The Gunners currently hold a slender one-point advantage, but City have a game in hand that could prove decisive.</p><p>"It\'s the most exciting title race I\'ve seen in years," said former Arsenal legend Thierry Henry. "Both teams deserve credit for the quality of football they\'ve produced this season."</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1579952363873-27f3bade9f55?w=800',
                'category_id' => 1, // Breaking News
                'author_name' => 'WFN24 Staff',
                'is_featured' => true,
                'published_at' => date('Y-m-d H:i:s'),
                'view_count' => 15420
            ],
            [
                'title' => 'Champions League Semi-Finals: Real Madrid vs Bayern Munich Preview',
                'slug' => 'champions-league-semi-finals-real-madrid-bayern-munich-preview',
                'excerpt' => 'Two European giants clash in what promises to be an epic Champions League semi-final showdown between Real Madrid and Bayern Munich.',
                'content' => '<p>The Santiago Bernabéu will host one of the most anticipated Champions League semi-finals in recent memory as Real Madrid welcome Bayern Munich for the first leg of their epic showdown.</p><p>Real Madrid, the record 14-time European champions, are looking to add another trophy to their illustrious collection. Carlo Ancelotti\'s side have been in scintillating form, with Vinícius Júnior and Jude Bellingham leading their attacking charge.</p><p>Bayern Munich, under the guidance of Thomas Tuchel, are determined to bounce back from their Bundesliga disappointment by securing European glory. Harry Kane\'s goalscoring form has been crucial to their Champions League campaign.</p><p>"This is what Champions League football is all about," said Real Madrid captain Nacho. "Two great teams, two great histories, and everything to play for."</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1552318965-6e6be7484ada?w=800',
                'category_id' => 3, // Match Reports
                'author_name' => 'WFN24 Staff',
                'is_featured' => false,
                'published_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'view_count' => 8920
            ],
            [
                'title' => 'Transfer News: Kylian Mbappé\'s Future Remains Uncertain as Real Madrid Links Intensify',
                'slug' => 'transfer-news-kylian-mbappe-future-real-madrid-links',
                'excerpt' => 'The football world waits with bated breath as Kylian Mbappé\'s future remains shrouded in uncertainty, with Real Madrid reportedly leading the race for his signature.',
                'content' => '<p>The biggest transfer saga of the summer is reaching its climax as Kylian Mbappé\'s future continues to dominate football headlines across Europe.</p><p>The French superstar has confirmed his departure from Paris Saint-Germain at the end of the season, but his next destination remains officially unconfirmed. Real Madrid have long been considered the frontrunners for his signature, with reports suggesting a deal is close to completion.</p><p>"Mbappé is one of the best players in the world, and his next move will have a significant impact on the football landscape," said football analyst Jamie Carragher. "Real Madrid have been pursuing him for years, and this could finally be the summer they get their man."</p><p>Liverpool and Manchester City have also been linked with the 25-year-old, but sources close to the player suggest Madrid is his preferred destination.</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800',
                'category_id' => 2, // Transfer News
                'author_name' => 'WFN24 Staff',
                'is_featured' => false,
                'published_at' => date('Y-m-d H:i:s', strtotime('-4 hours')),
                'view_count' => 12340
            ],
            [
                'title' => 'Erling Haaland Breaks Premier League Goal Record in Manchester City Victory',
                'slug' => 'erling-haaland-breaks-premier-league-goal-record-manchester-city',
                'excerpt' => 'Erling Haaland continues to rewrite the record books as he breaks another Premier League scoring record in Manchester City\'s dominant victory.',
                'content' => '<p>Erling Haaland has once again proven why he\'s considered one of the most lethal strikers in world football, breaking another Premier League record in Manchester City\'s emphatic 4-0 victory over Brighton.</p><p>The Norwegian striker scored a hat-trick to take his season tally to 35 goals, breaking the previous record for most goals in a Premier League season by a foreign player. His clinical finishing and predatory instincts in front of goal have been nothing short of remarkable.</p><p>"Haaland is a phenomenon," said Manchester City manager Pep Guardiola. "His ability to find space and finish chances is extraordinary. He\'s rewriting what we thought was possible in terms of goalscoring."</p><p>The 23-year-old\'s incredible form has been crucial to City\'s title challenge, and his partnership with Kevin De Bruyne continues to terrorize Premier League defenses.</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800',
                'category_id' => 3, // Match Reports
                'author_name' => 'WFN24 Staff',
                'is_featured' => false,
                'published_at' => date('Y-m-d H:i:s', strtotime('-6 hours')),
                'view_count' => 9870
            ],
            [
                'title' => 'Jude Bellingham Named La Liga Player of the Season in Debut Campaign',
                'slug' => 'jude-bellingham-named-la-liga-player-season-debut-campaign',
                'excerpt' => 'Real Madrid\'s Jude Bellingham has been named La Liga Player of the Season in his debut campaign, capping off a remarkable first year in Spain.',
                'content' => '<p>Jude Bellingham\'s incredible debut season in La Liga has been officially recognized as the English midfielder was named Player of the Season at the annual La Liga awards ceremony.</p><p>The 20-year-old has been nothing short of sensational since joining Real Madrid from Borussia Dortmund last summer, scoring 18 goals and providing 12 assists in his first La Liga campaign. His performances have been crucial to Real Madrid\'s title challenge and Champions League success.</p><p>"Bellingham has exceeded all expectations in his first season in Spain," said Real Madrid manager Carlo Ancelotti. "His maturity, technical ability, and leadership qualities are remarkable for a player of his age."</p><p>The award caps off a remarkable year for Bellingham, who has also been instrumental in England\'s international success and is widely considered one of the best midfielders in world football.</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800',
                'category_id' => 1, // Breaking News
                'author_name' => 'WFN24 Staff',
                'is_featured' => false,
                'published_at' => date('Y-m-d H:i:s', strtotime('-8 hours')),
                'view_count' => 7650
            ]
        ];
    }
}

// Run the seeder
$seeder = new FootballDataSeeder();
$seeder->run();
