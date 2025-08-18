<?php

namespace WFN24\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private $mailer;
    private $fromEmail;
    private $fromName;

    public function __construct()
    {
        $this->fromEmail = $_ENV['SENDGRID_FROM_EMAIL'] ?? 'noreply@wfn24.com';
        $this->fromName = $_ENV['SENDGRID_FROM_NAME'] ?? 'WFN24';
        
        $this->mailer = new PHPMailer(true);
        $this->setupMailer();
    }

    private function setupMailer(): void
    {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = 'smtp.sendgrid.net';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = 'apikey';
            $this->mailer->Password = $_ENV['SENDGRID_API_KEY'] ?? '';
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = 587;

            // Default settings
            $this->mailer->setFrom($this->fromEmail, $this->fromName);
            $this->mailer->isHTML(true);
            $this->mailer->CharSet = 'UTF-8';
        } catch (Exception $e) {
            error_log("Email Service Setup Error: " . $e->getMessage());
        }
    }

    public function sendWelcomeEmail(string $email, string $name): bool
    {
        try {
            $this->mailer->addAddress($email, $name);
            $this->mailer->Subject = 'Welcome to WFN24 - World Football News 24';
            
            $body = $this->getWelcomeEmailTemplate($name);
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Welcome Email Error: " . $e->getMessage());
            return false;
        }
    }

    public function sendMatchAlert(string $email, string $name, array $matchData): bool
    {
        try {
            $this->mailer->addAddress($email, $name);
            $this->mailer->Subject = "Match Alert: {$matchData['home_team']} vs {$matchData['away_team']}";
            
            $body = $this->getMatchAlertTemplate($name, $matchData);
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Match Alert Email Error: " . $e->getMessage());
            return false;
        }
    }

    public function sendNewsletter(string $email, string $name, array $articles): bool
    {
        try {
            $this->mailer->addAddress($email, $name);
            $this->mailer->Subject = 'WFN24 Weekly Newsletter';
            
            $body = $this->getNewsletterTemplate($name, $articles);
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Newsletter Email Error: " . $e->getMessage());
            return false;
        }
    }

    public function sendPasswordReset(string $email, string $name, string $resetToken): bool
    {
        try {
            $this->mailer->addAddress($email, $name);
            $this->mailer->Subject = 'Password Reset Request - WFN24';
            
            $resetUrl = $_ENV['APP_URL'] . "/reset-password?token=" . $resetToken;
            $body = $this->getPasswordResetTemplate($name, $resetUrl);
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Password Reset Email Error: " . $e->getMessage());
            return false;
        }
    }

    public function sendCustomEmail(string $email, string $name, string $subject, string $message): bool
    {
        try {
            $this->mailer->addAddress($email, $name);
            $this->mailer->Subject = $subject;
            
            $body = $this->getCustomEmailTemplate($name, $message);
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Custom Email Error: " . $e->getMessage());
            return false;
        }
    }

    private function getWelcomeEmailTemplate(string $name): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Welcome to WFN24</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h1 style='color: #e41e5b; margin: 0;'>Welcome to WFN24</h1>
                    <p style='color: #666; margin: 5px 0;'>World Football News 24</p>
                </div>
                
                <div style='background: #f9f9f9; padding: 20px; border-radius: 8px;'>
                    <h2>Hello {$name}!</h2>
                    <p>Welcome to WFN24, your ultimate destination for world football news, live scores, and comprehensive match coverage.</p>
                    
                    <h3>What you can expect:</h3>
                    <ul>
                        <li>Breaking football news and transfer updates</li>
                        <li>Live match scores and commentary</li>
                        <li>Detailed match reports and analysis</li>
                        <li>Player and team statistics</li>
                        <li>League standings and fixtures</li>
                    </ul>
                    
                    <p>Stay tuned for the latest updates from the world of football!</p>
                    
                    <div style='text-align: center; margin-top: 30px;'>
                        <a href='" . $_ENV['APP_URL'] . "' style='background: #e41e5b; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Visit WFN24</a>
                    </div>
                </div>
                
                <div style='text-align: center; margin-top: 30px; color: #666; font-size: 12px;'>
                    <p>© 2024 WFN24. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    private function getMatchAlertTemplate(string $name, array $matchData): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Match Alert</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h1 style='color: #e41e5b; margin: 0;'>Match Alert</h1>
                </div>
                
                <div style='background: #f9f9f9; padding: 20px; border-radius: 8px;'>
                    <h2>Hello {$name}!</h2>
                    <p>Your match is about to begin!</p>
                    
                    <div style='background: white; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                        <h3 style='text-align: center; margin: 0 0 15px 0;'>{$matchData['home_team']} vs {$matchData['away_team']}</h3>
                        <p style='text-align: center; margin: 5px 0;'><strong>League:</strong> {$matchData['league']}</p>
                        <p style='text-align: center; margin: 5px 0;'><strong>Time:</strong> {$matchData['kickoff_time']}</p>
                        <p style='text-align: center; margin: 5px 0;'><strong>Venue:</strong> {$matchData['stadium']}</p>
                    </div>
                    
                    <div style='text-align: center; margin-top: 30px;'>
                        <a href='" . $_ENV['APP_URL'] . "/match/{$matchData['id']}' style='background: #e41e5b; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Follow Live</a>
                    </div>
                </div>
                
                <div style='text-align: center; margin-top: 30px; color: #666; font-size: 12px;'>
                    <p>© 2024 WFN24. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    private function getNewsletterTemplate(string $name, array $articles): string
    {
        $articlesHtml = '';
        foreach ($articles as $article) {
            $articlesHtml .= "
            <div style='margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #eee;'>
                <h3 style='margin: 0 0 10px 0;'><a href='" . $_ENV['APP_URL'] . "/article/{$article['slug']}' style='color: #e41e5b; text-decoration: none;'>{$article['title']}</a></h3>
                <p style='margin: 0; color: #666;'>{$article['excerpt']}</p>
            </div>";
        }

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>WFN24 Newsletter</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h1 style='color: #e41e5b; margin: 0;'>WFN24 Newsletter</h1>
                    <p style='color: #666; margin: 5px 0;'>This Week in Football</p>
                </div>
                
                <div style='background: #f9f9f9; padding: 20px; border-radius: 8px;'>
                    <h2>Hello {$name}!</h2>
                    <p>Here are the top stories from this week in football:</p>
                    
                    {$articlesHtml}
                    
                    <div style='text-align: center; margin-top: 30px;'>
                        <a href='" . $_ENV['APP_URL'] . "/news' style='background: #e41e5b; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Read More News</a>
                    </div>
                </div>
                
                <div style='text-align: center; margin-top: 30px; color: #666; font-size: 12px;'>
                    <p>© 2024 WFN24. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    private function getPasswordResetTemplate(string $name, string $resetUrl): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Password Reset</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h1 style='color: #e41e5b; margin: 0;'>Password Reset</h1>
                </div>
                
                <div style='background: #f9f9f9; padding: 20px; border-radius: 8px;'>
                    <h2>Hello {$name}!</h2>
                    <p>You have requested to reset your password for your WFN24 account.</p>
                    
                    <p>Click the button below to reset your password:</p>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$resetUrl}' style='background: #e41e5b; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password</a>
                    </div>
                    
                    <p>If you didn't request this password reset, please ignore this email.</p>
                    
                    <p>This link will expire in 1 hour for security reasons.</p>
                </div>
                
                <div style='text-align: center; margin-top: 30px; color: #666; font-size: 12px;'>
                    <p>© 2024 WFN24. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    private function getCustomEmailTemplate(string $name, string $message): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>WFN24 Message</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h1 style='color: #e41e5b; margin: 0;'>WFN24</h1>
                </div>
                
                <div style='background: #f9f9f9; padding: 20px; border-radius: 8px;'>
                    <h2>Hello {$name}!</h2>
                    
                    <div style='background: white; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                        {$message}
                    </div>
                    
                    <div style='text-align: center; margin-top: 30px;'>
                        <a href='" . $_ENV['APP_URL'] . "' style='background: #e41e5b; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Visit WFN24</a>
                    </div>
                </div>
                
                <div style='text-align: center; margin-top: 30px; color: #666; font-size: 12px;'>
                    <p>© 2024 WFN24. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
    }
}
