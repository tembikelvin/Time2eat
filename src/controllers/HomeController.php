<?php

namespace controllers;

use core\BaseController;

/**
 * Home Controller
 * Handles public pages and main site functionality
 */
class HomeController extends BaseController {
    protected ?\PDO $db = null;
    private bool $dbAvailable = false;
    
    public function __construct() {
        parent::__construct();
        // Initialize database connection with error handling
        try {
        $this->db = $this->getDb();
            $this->dbAvailable = true;
        } catch (\Exception $e) {
            // Database connection failed - continue without database
            $this->dbAvailable = false;
            $this->db = null;
            error_log("Database unavailable for HomeController: " . $e->getMessage());
        }
    }
    
    public function index() {
        $data = [
            'title' => 'Time2Eat - Food Delivery in Bamenda',
            'description' => 'Order from local restaurants with real-time tracking',
            'featured_restaurants' => $this->getFeaturedRestaurants(),
            'popular_categories' => $this->getPopularCategories(),
            'popular_dishes' => $this->getPopularDishes(),
            'testimonials' => $this->getTestimonials(),
            'stats' => $this->getStats(),
            'how_it_works_steps' => $this->getHowItWorksSteps()
        ];

        $this->view('home/index_enhanced', $data);
    }
    
    public function browse() {
        $search = $this->input('search', '');
        $category = $this->input('category', '');
        $location = $this->input('location', '');
        $sort = $this->input('sort', 'rating');
        $page = (int)$this->input('page', 1);
        
        $data = [
            'title' => 'Browse Restaurants - Time2Eat',
            'restaurants' => $this->getRestaurants($search, $category, $location, $sort, $page),
            'categories' => $this->getCategories(),
            'locations' => $this->getLocations(),
            'search' => $search,
            'category' => $category,
            'location' => $location,
            'sort' => $sort,
            'page' => $page
        ];
        
        $this->view('home/browse', $data);
    }
    
    public function about() {
        $data = [
            'title' => 'About Us - Time2Eat',
            'description' => 'Learn about Time2Eat and our mission to connect Bamenda with great food',
            'stats' => $this->getStats(),
            'team_stats' => $this->getTeamStats(),
            'contact_info' => $this->getContactInfo()
        ];
        
        $this->view('home/about', $data);
    }
    
    public function contact() {
        $data = [
            'title' => 'Contact Us - Time2Eat',
            'description' => 'Get in touch with Time2Eat for support, partnerships, or general inquiries',
            'contact_info' => $this->getContactInfo(),
            'stats' => $this->getStats()
        ];
        
        $this->view('home/contact', $data);
    }
    
    public function restaurant($id) {
        try {
            $restaurant = $this->getRestaurantById($id);
            
            if (!$restaurant) {
                http_response_code(404);
                $this->view('errors/404');
                return;
            }
            
            $data = [
                'title' => $restaurant['name'] . ' - Time2Eat',
                'description' => $restaurant['description'],
                'restaurant' => $restaurant,
                'menu' => $this->getRestaurantMenu($id),
                'reviews' => $this->getRestaurantReviews($id),
                'similar_restaurants' => $this->getSimilarRestaurants($restaurant['category_id'], $id)
            ];
            
            $this->view('home/restaurant', $data);
            
        } catch (\Exception $e) {
            error_log("Error loading restaurant: " . $e->getMessage());
            http_response_code(500);
            $this->view('errors/500');
        }
    }
    
    private function getFeaturedRestaurants() {
        if (!$this->dbAvailable || !$this->db) {
            return [];
        }
        
        try {
            // First try to get featured restaurants or high-rated ones
            $sql = "SELECT r.*,
                           r.cuisine_type as category_name,
                           COALESCE(AVG(rv.rating), 4.5) as avg_rating,
                           COUNT(rv.id) as review_count,
                           COALESCE(r.delivery_time, '25-35') as delivery_time
                    FROM restaurants r
                    LEFT JOIN reviews rv ON r.id = rv.reviewable_id AND rv.reviewable_type = 'restaurant'
                    WHERE r.deleted_at IS NULL
                      AND (r.status = 'active' OR r.status = 'approved')
                    GROUP BY r.id
                    HAVING r.is_featured = 1 OR avg_rating >= 4.0 OR review_count >= 5
                    ORDER BY r.is_featured DESC, avg_rating DESC, review_count DESC
                    LIMIT 6";

            $stmt = $this->getDb()->query($sql);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $restaurants = [];

            // If we have featured/top-rated restaurants, process them
            if (!empty($results)) {
                foreach ($results as $restaurant) {
                    // Set image with fallback logic
                    if (empty($restaurant['image']) && !empty($restaurant['cover_image'])) {
                        $restaurant['image'] = $restaurant['cover_image'];
                    } elseif (empty($restaurant['image']) && empty($restaurant['cover_image'])) {
                        // Use fallback image
                        $restaurant['image'] = 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=600&h=400&fit=crop';
                    }

                    $restaurant['rating'] = $restaurant['avg_rating'] ?? 4.5;
                    $restaurants[] = $restaurant;
                }
            }

            // If no featured/top-rated restaurants, get any active restaurants
            if (empty($restaurants)) {
                $sql = "SELECT r.*,
                               r.cuisine_type as category_name,
                               COALESCE(AVG(rv.rating), 4.0) as avg_rating,
                               COUNT(rv.id) as review_count,
                               COALESCE(r.delivery_time, '25-35') as delivery_time
                        FROM restaurants r
                        LEFT JOIN reviews rv ON r.id = rv.reviewable_id AND rv.reviewable_type = 'restaurant'
                        WHERE r.deleted_at IS NULL
                          AND (r.status = 'active' OR r.status = 'approved')
                        GROUP BY r.id
                        ORDER BY r.created_at DESC, r.name ASC
                        LIMIT 6";

                $stmt = $this->getDb()->query($sql);
                $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                foreach ($results as $restaurant) {
                    // Set image with fallback logic
                    if (empty($restaurant['image']) && !empty($restaurant['cover_image'])) {
                        $restaurant['image'] = $restaurant['cover_image'];
                    } elseif (empty($restaurant['image']) && empty($restaurant['cover_image'])) {
                        // Use fallback image
                        $restaurant['image'] = 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=600&h=400&fit=crop';
                    }

                    $restaurant['rating'] = $restaurant['avg_rating'] ?? 4.0;
                    $restaurants[] = $restaurant;
                }
            }

            // Log for debugging
            error_log("Restaurants found: " . count($restaurants));

            return $restaurants;
        } catch (\Exception $e) {
            error_log("Error fetching restaurants: " . $e->getMessage());
            return [];
        }
    }
    
    private function getPopularCategories() {
        if (!$this->dbAvailable || !$this->db) {
            return [];
        }
        
        try {
            // First check if category_id column exists in restaurants table
            $checkColumn = $this->getDb()->prepare("SHOW COLUMNS FROM restaurants LIKE 'category_id'");
            $checkColumn->execute();
            $hasCategoryId = $checkColumn->fetch();
            
            if ($hasCategoryId) {
                // Use category_id if it exists
                $sql = "SELECT c.*, COUNT(r.id) as restaurant_count,
                               c.name, c.icon, c.description
                        FROM categories c
                        LEFT JOIN restaurants r ON c.id = r.category_id
                            AND (r.status = 'active' OR r.status = 'approved')
                            AND r.deleted_at IS NULL
                        WHERE c.deleted_at IS NULL
                        GROUP BY c.id
                        HAVING restaurant_count > 0
                        ORDER BY restaurant_count DESC
                        LIMIT 8";
            } else {
                // Use cuisine_type if category_id doesn't exist
                $sql = "SELECT DISTINCT cuisine_type as name, cuisine_type as slug, cuisine_type as id, 
                               cuisine_type as icon, '' as description, COUNT(*) as restaurant_count
                        FROM restaurants 
                        WHERE status IN ('active', 'approved') AND deleted_at IS NULL AND cuisine_type IS NOT NULL AND cuisine_type != ''
                        GROUP BY cuisine_type
                        ORDER BY restaurant_count DESC, cuisine_type ASC
                        LIMIT 8";
            }

            $stmt = $this->getDb()->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error fetching popular categories: " . $e->getMessage());
            return [];
        }
    }
    
    private function getStats() {
        if (!$this->dbAvailable || !$this->db) {
            // Return default stats when database is unavailable
            return ['restaurants' => 0, 'orders' => 0, 'customers' => 0, 'cities' => 1];
        }
        
        try {
            $stats = [];
            
            // Total active restaurants
            $stmt = $this->getDb()->prepare("SELECT COUNT(*) as count FROM restaurants WHERE status = 'active' AND deleted_at IS NULL");
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['restaurants'] = $result['count'] ?? 0;
            
            // Total successful orders (exclude cancelled/refunded)
            $stmt = $this->getDb()->prepare("SELECT COUNT(*) as count FROM orders WHERE status NOT IN ('cancelled', 'refunded')");
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['orders'] = $result['count'] ?? 0;
            
            // Happy customers (customers who have completed orders)
            $stmt = $this->getDb()->prepare("SELECT COUNT(DISTINCT customer_id) as count FROM orders WHERE status = 'delivered'");
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['customers'] = $result['count'] ?? 0;
            
            // Cities served (for now just Bamenda)
            $stats['cities'] = 1;
            
            return $stats;
        } catch (\Exception $e) {
            error_log("Error fetching stats: " . $e->getMessage());
            return ['restaurants' => 0, 'orders' => 0, 'customers' => 0, 'cities' => 1];
        }
    }
    
    private function getRestaurants($search, $category, $location, $sort, $page) {
        if (!$this->dbAvailable || !$this->db) {
            return [];
        }
        
        try {
            $perPage = 12;
            $offset = ($page - 1) * $perPage;
            
            $sql = "SELECT r.*, c.name as category_name,
                           AVG(rv.rating) as avg_rating,
                           COUNT(rv.id) as review_count
                    FROM restaurants r
                    LEFT JOIN categories c ON r.category_id = c.id
                    LEFT JOIN reviews rv ON r.id = rv.reviewable_id AND rv.reviewable_type = 'restaurant'
                    WHERE (r.status = 'active' OR r.status = 'approved') AND r.deleted_at IS NULL";
            
            $params = [];
            
            if ($search) {
                $sql .= " AND (r.name LIKE ? OR r.description LIKE ?)";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }
            
            if ($category) {
                $sql .= " AND r.category_id = ?";
                $params[] = $category;
            }
            
            if ($location) {
                $sql .= " AND r.location LIKE ?";
                $params[] = "%{$location}%";
            }
            
            $sql .= " GROUP BY r.id";
            
            // Sorting
            switch ($sort) {
                case 'rating':
                    $sql .= " ORDER BY avg_rating DESC, review_count DESC";
                    break;
                case 'name':
                    $sql .= " ORDER BY r.name ASC";
                    break;
                case 'newest':
                    $sql .= " ORDER BY r.created_at DESC";
                    break;
                default:
                    $sql .= " ORDER BY avg_rating DESC";
            }
            
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $perPage;
            $params[] = $offset;
            
            $stmt = $this->getDb()->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log("Error fetching restaurants: " . $e->getMessage());
            return [];
        }
    }
    
    private function getCategories() {
        if (!$this->dbAvailable || !$this->db) {
            return [];
        }
        
        try {
            $sql = "SELECT * FROM categories WHERE deleted_at IS NULL ORDER BY name ASC";
            $stmt = $this->getDb()->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error fetching categories: " . $e->getMessage());
            return [];
        }
    }
    
    private function getLocations() {
        if (!$this->dbAvailable || !$this->db) {
            return [];
        }
        
        try {
            $sql = "SELECT DISTINCT city as location FROM restaurants
                    WHERE (status = 'active' OR status = 'approved')
                      AND deleted_at IS NULL
                      AND city IS NOT NULL
                    ORDER BY city ASC";
            $stmt = $this->getDb()->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error fetching locations: " . $e->getMessage());
            return [];
        }
    }
    
    private function getTeamStats() {
        if (!$this->dbAvailable || !$this->db) {
            // Return default team stats when database is unavailable
            return [
                'founded' => '2024',
                'team_size' => '10+',
                'restaurants' => 0,
                'orders' => 0,
                'customers' => 0,
                'orders_delivered' => '1000+',
                'partner_restaurants' => '50+'
            ];
        }
        
        try {
            // Get real data for about page
            $stmt = $this->getDb()->prepare("SELECT COUNT(*) as count FROM orders WHERE status = 'delivered'");
            $stmt->execute();
            $result = $stmt->fetch();
            $ordersDelivered = $result['count'] ?? 0;
            
            $stmt = $this->getDb()->prepare("SELECT COUNT(*) as count FROM restaurants WHERE status = 'active' AND deleted_at IS NULL");
            $stmt->execute();
            $result = $stmt->fetch();
            $partnerRestaurants = $result['count'] ?? 0;
            
            $stmt = $this->getDb()->prepare("SELECT COUNT(DISTINCT customer_id) as count FROM orders WHERE status = 'delivered'");
            $stmt->execute();
            $result = $stmt->fetch();
            $happyCustomers = $result['count'] ?? 0;
            
            return [
                'founded' => '2024',
                'team_size' => '10+',
                'restaurants' => $partnerRestaurants,
                'orders' => $ordersDelivered,
                'customers' => $happyCustomers,
                'orders_delivered' => number_format($ordersDelivered) . '+',
                'partner_restaurants' => number_format($partnerRestaurants) . '+'
            ];
        } catch (\Exception $e) {
            error_log("Error fetching team stats: " . $e->getMessage());
            // Fallback to static values if database fails
            return [
                'founded' => '2024',
                'team_size' => '10+',
                'restaurants' => 0,
                'orders' => 0,
                'customers' => 0,
                'orders_delivered' => '1000+',
                'partner_restaurants' => '50+'
            ];
        }
    }
    
    private function getRestaurantById($id) {
        if (!$this->dbAvailable || !$this->db) {
            return null;
        }
        
        try {
            $sql = "SELECT r.*, c.name as category_name
                    FROM restaurants r
                    LEFT JOIN categories c ON r.category_id = c.id
                    WHERE r.id = ? AND (r.status = 'active' OR r.status = 'approved') AND r.deleted_at IS NULL";
            
            $stmt = $this->getDb()->prepare($sql);
            $stmt->execute([$id]);
            
            return $stmt->fetch();
        } catch (\Exception $e) {
            error_log("Error fetching restaurant: " . $e->getMessage());
            return null;
        }
    }
    
    private function getRestaurantMenu($restaurantId) {
        if (!$this->dbAvailable || !$this->db) {
            return [];
        }
        
        try {
            $sql = "SELECT mi.*, c.name as category_name
                    FROM menu_items mi
                    LEFT JOIN menu_categories c ON mi.category_id = c.id
                    WHERE mi.restaurant_id = ? AND mi.is_available = 1 AND mi.deleted_at IS NULL
                    ORDER BY c.sort_order ASC, mi.sort_order ASC, mi.name ASC";
            
            $stmt = $this->getDb()->prepare($sql);
            $stmt->execute([$restaurantId]);
            
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log("Error fetching menu: " . $e->getMessage());
            return [];
        }
    }
    
    private function getRestaurantReviews($restaurantId) {
        if (!$this->dbAvailable || !$this->db) {
            return [];
        }
        
        try {
            $sql = "SELECT r.*, u.username, u.avatar
                    FROM reviews r
                    LEFT JOIN users u ON r.user_id = u.id
                    WHERE r.restaurant_id = ? AND r.deleted_at IS NULL
                    ORDER BY r.created_at DESC
                    LIMIT 10";
            
            $stmt = $this->getDb()->prepare($sql);
            $stmt->execute([$restaurantId]);
            
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log("Error fetching reviews: " . $e->getMessage());
            return [];
        }
    }
    
    private function getSimilarRestaurants($categoryId, $excludeId) {
        if (!$this->dbAvailable || !$this->db) {
            return [];
        }
        
        try {
            $sql = "SELECT r.*, AVG(rv.rating) as avg_rating
                    FROM restaurants r
                    LEFT JOIN reviews rv ON r.id = rv.reviewable_id AND rv.reviewable_type = 'restaurant'
                    WHERE r.category_id = ? AND r.id != ?
                      AND (r.status = 'active' OR r.status = 'approved')
                      AND r.deleted_at IS NULL
                    GROUP BY r.id
                    ORDER BY avg_rating DESC
                    LIMIT 4";
            
            $stmt = $this->getDb()->prepare($sql);
            $stmt->execute([$categoryId, $excludeId]);
            
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log("Error fetching similar restaurants: " . $e->getMessage());
            return [];
        }
    }

    private function getPopularDishes() {
        if (!$this->dbAvailable || !$this->db) {
            return [];
        }
        
        try {
            $sql = "SELECT mi.id, mi.name, mi.description, mi.image, mi.price,
                           mi.category_id, mi.restaurant_id, COALESCE(mi.rating, 4.5) as rating,
                           r.name as restaurant_name, r.cuisine_type,
                           c.name as category_name,
                           (SELECT COUNT(*) FROM order_items oi
                            INNER JOIN orders o ON oi.order_id = o.id
                            WHERE oi.menu_item_id = mi.id AND o.status = 'delivered'
                            AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as order_count,
                           (SELECT AVG(rating) FROM reviews
                            WHERE reviewable_type = 'menu_item' AND reviewable_id = mi.id) as avg_rating
                    FROM menu_items mi
                    INNER JOIN restaurants r ON mi.restaurant_id = r.id
                    LEFT JOIN categories c ON mi.category_id = c.id
                    WHERE (mi.is_available = 1 OR mi.is_available IS NULL)
                      AND r.deleted_at IS NULL
                      AND (r.status = 'active' OR r.status = 'approved')
                    ORDER BY order_count DESC, mi.rating DESC, mi.created_at DESC
                    LIMIT 8";

            $stmt = $this->getDb()->query($sql);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Convert to array and ensure proper image URLs
            $dishes = [];
            foreach ($results as $dish) {
                // Set fallback image if needed
                if (empty($dish['image']) || !filter_var($dish['image'], FILTER_VALIDATE_URL)) {
                    $dish['image'] = 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&h=400&fit=crop';
                }
                $dishes[] = $dish;
            }

            // Log for debugging
            error_log("Popular dishes found: " . count($dishes));

            return $dishes;
        } catch (\Exception $e) {
            error_log("Error fetching popular dishes: " . $e->getMessage());
            return [];
        }
    }

    private function getTestimonials() {
        if (!$this->dbAvailable || !$this->db) {
            return [];
        }
        
        try {
            $sql = "SELECT r.id, r.rating, r.comment, r.created_at,
                           u.first_name, u.last_name, u.avatar,
                           CASE
                               WHEN r.reviewable_type = 'restaurant' THEN rest.name
                               WHEN r.reviewable_type = 'menu_item' THEN mi.name
                               ELSE 'Delivery Service'
                           END as reviewed_item,
                           r.reviewable_type,
                           COALESCE(r.helpful_count, 0) as helpful_count
                    FROM reviews r
                    INNER JOIN users u ON r.user_id = u.id
                    LEFT JOIN restaurants rest ON r.reviewable_type = 'restaurant' AND r.reviewable_id = rest.id
                    LEFT JOIN menu_items mi ON r.reviewable_type = 'menu_item' AND r.reviewable_id = mi.id
                    WHERE (r.status = 'approved' OR r.is_approved = 1)
                      AND r.rating >= 4
                      AND r.comment IS NOT NULL
                      AND LENGTH(r.comment) >= 20
                    ORDER BY COALESCE(r.is_featured, 0) DESC, r.rating DESC, r.created_at DESC
                    LIMIT 6";

            $stmt = $this->getDb()->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error fetching testimonials: " . $e->getMessage());
            return [];
        }
    }

    private function getHowItWorksSteps() {
        return [
            [
                'step' => 1,
                'icon' => 'search',
                'title' => 'Discover & Browse',
                'description' => 'Explore Bamenda\'s finest restaurants and discover authentic Cameroonian dishes alongside international favorites.',
                'color' => 'tw-bg-gradient-to-br tw-from-red-500 tw-to-red-600'
            ],
            [
                'step' => 2,
                'icon' => 'shopping-cart',
                'title' => 'Customize & Order',
                'description' => 'Build your perfect meal, customize ingredients, and pay securely with mobile money or card.',
                'color' => 'tw-bg-gradient-to-br tw-from-orange-500 tw-to-orange-600'
            ],
            [
                'step' => 3,
                'icon' => 'truck',
                'title' => 'Track & Enjoy',
                'description' => 'Follow your order in real-time from kitchen to doorstep. Fresh, hot food delivered in 25 minutes average.',
                'color' => 'tw-bg-gradient-to-br tw-from-green-500 tw-to-green-600'
            ]
        ];
    }
    
    /**
     * Get contact information from database
     */
    private function getContactInfo() {
        if (!$this->dbAvailable || !$this->db) {
            // Return default contact info when database is unavailable
            return [
                'email' => 'info@time2eat.com',
                'phone' => '+237 6XX XXX XXX',
                'whatsapp' => '+237 6XX XXX XXX',
                'address' => 'Bamenda, North West Region, Cameroon',
                'hours' => 'Monday - Sunday: 8:00 AM - 10:00 PM',
                'support_email' => 'support@time2eat.com',
                'emergency_contact' => '+237 6XX XXX XXX',
                'facebook' => 'https://facebook.com/time2eat',
                'twitter' => 'https://twitter.com/time2eat',
                'instagram' => 'https://instagram.com/time2eat',
                'youtube' => 'https://youtube.com/time2eat',
                'linkedin' => 'https://linkedin.com/company/time2eat',
                'tiktok' => 'https://tiktok.com/@time2eat',
                'partnership_email' => 'partnerships@time2eat.com'
            ];
        }
        
        try {
            // Try to get contact info from site_settings table
            $contactInfo = $this->db->query("
                SELECT setting_key, setting_value 
                FROM site_settings 
                WHERE setting_key IN (
                    'contact_email', 'contact_phone', 'contact_address', 'contact_hours', 
                    'support_email', 'emergency_contact',
                    'facebook_url', 'twitter_url', 'instagram_url', 'youtube_url', 
                    'linkedin_url', 'tiktok_url', 'whatsapp_number'
                )
            ");
            
            $contact = [];
            foreach ($contactInfo as $setting) {
                $contact[$setting['setting_key']] = $setting['setting_value'];
            }
            
            // Set default values if not found in database
            return [
                'email' => $contact['contact_email'] ?? 'info@time2eat.com',
                'phone' => $contact['contact_phone'] ?? '+237 6XX XXX XXX',
                'whatsapp' => $contact['whatsapp_number'] ?? '+237 6XX XXX XXX',
                'address' => $contact['contact_address'] ?? 'Bamenda, North West Region, Cameroon',
                'hours' => $contact['contact_hours'] ?? 'Monday - Sunday: 8:00 AM - 10:00 PM',
                'support_email' => $contact['support_email'] ?? 'support@time2eat.com',
                'emergency_contact' => $contact['emergency_contact'] ?? '+237 6XX XXX XXX',
                'facebook' => $contact['facebook_url'] ?? 'https://facebook.com/time2eat',
                'twitter' => $contact['twitter_url'] ?? 'https://twitter.com/time2eat',
                'instagram' => $contact['instagram_url'] ?? 'https://instagram.com/time2eat',
                'youtube' => $contact['youtube_url'] ?? 'https://youtube.com/time2eat',
                'linkedin' => $contact['linkedin_url'] ?? 'https://linkedin.com/company/time2eat',
                'tiktok' => $contact['tiktok_url'] ?? 'https://tiktok.com/@time2eat',
                'partnership_email' => 'partnerships@time2eat.com'
            ];
        } catch (\Exception $e) {
            error_log("Error fetching contact info: " . $e->getMessage());
            
            // Return default contact info if database query fails
            return [
                'email' => 'info@time2eat.com',
                'phone' => '+237 6XX XXX XXX',
                'whatsapp' => '+237 6XX XXX XXX',
                'address' => 'Bamenda, North West Region, Cameroon',
                'hours' => 'Monday - Sunday: 8:00 AM - 10:00 PM',
                'support_email' => 'support@time2eat.com',
                'emergency_contact' => '+237 6XX XXX XXX',
                'facebook' => 'https://facebook.com/time2eat',
                'twitter' => 'https://twitter.com/time2eat',
                'instagram' => 'https://instagram.com/time2eat',
                'youtube' => 'https://youtube.com/time2eat',
                'linkedin' => 'https://linkedin.com/company/time2eat',
                'tiktok' => 'https://tiktok.com/@time2eat',
                'partnership_email' => 'partnerships@time2eat.com'
            ];
        }
    }
}
