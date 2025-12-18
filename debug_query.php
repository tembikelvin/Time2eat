<?php
// Debug the restaurant query

echo "<h2>Debugging Restaurant Query</h2>";
echo "<pre>";

try {
    // Direct database connection
    $db = new PDO(
        "mysql:host=localhost;dbname=time2eat;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✓ Database connected\n\n";
    
    // Test the exact query from HomeController
    echo "=== TESTING FEATURED RESTAURANTS QUERY ===\n\n";
    
    $sql = "SELECT r.*, c.name as category_name,
                   COALESCE(AVG(rv.rating), 4.5) as avg_rating,
                   COUNT(rv.id) as review_count,
                   COALESCE(r.delivery_time, '25-35') as delivery_time
            FROM restaurants r
            LEFT JOIN categories c ON r.category_id = c.id
            LEFT JOIN reviews rv ON r.id = rv.reviewable_id AND rv.reviewable_type = 'restaurant'
            WHERE r.deleted_at IS NULL
              AND (r.status = 'active' OR r.status = 'approved')
            GROUP BY r.id
            HAVING r.is_featured = 1 OR avg_rating >= 4.0 OR review_count >= 5
            ORDER BY r.is_featured DESC, avg_rating DESC, review_count DESC
            LIMIT 6";
    
    echo "Query:\n$sql\n\n";
    
    try {
        $stmt = $db->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "✓ Query executed successfully\n";
        echo "Results returned: " . count($results) . "\n\n";
        
        if (empty($results)) {
            echo "❌ NO RESULTS FROM FEATURED QUERY\n\n";
            
            // Try simpler query
            echo "=== TESTING SIMPLE QUERY (ALL ACTIVE RESTAURANTS) ===\n\n";
            
            $sql2 = "SELECT r.*, c.name as category_name,
                           COALESCE(AVG(rv.rating), 4.0) as avg_rating,
                           COUNT(rv.id) as review_count,
                           COALESCE(r.delivery_time, '25-35') as delivery_time
                    FROM restaurants r
                    LEFT JOIN categories c ON r.category_id = c.id
                    LEFT JOIN reviews rv ON r.id = rv.reviewable_id AND rv.reviewable_type = 'restaurant'
                    WHERE r.deleted_at IS NULL
                      AND (r.status = 'active' OR r.status = 'approved')
                    GROUP BY r.id
                    ORDER BY r.created_at DESC, r.name ASC
                    LIMIT 6";
            
            $stmt2 = $db->query($sql2);
            $results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            
            echo "Results from simple query: " . count($results2) . "\n\n";
            
            if (!empty($results2)) {
                echo "✓ Simple query works! Issue is with HAVING clause\n\n";
                echo "Sample restaurant data:\n";
                print_r($results2[0]);
                
                echo "\n\n=== CHECKING WHY HAVING CLAUSE FAILS ===\n";
                echo "Checking is_featured values:\n";
                foreach ($results2 as $r) {
                    echo "- {$r['name']}: is_featured = " . ($r['is_featured'] ?? 'NULL') . 
                         ", avg_rating = {$r['avg_rating']}, review_count = {$r['review_count']}\n";
                }
            } else {
                echo "❌ Even simple query returns nothing!\n";
            }
        } else {
            echo "✓ Featured query works!\n\n";
            foreach ($results as $r) {
                echo "- {$r['name']}\n";
            }
        }
        
    } catch (PDOException $e) {
        echo "❌ Query error: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
