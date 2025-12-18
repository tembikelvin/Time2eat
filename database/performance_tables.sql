-- Performance Monitoring Tables for Time2Eat
-- These tables support comprehensive performance tracking and optimization

-- Performance Metrics Table
CREATE TABLE IF NOT EXISTS performance_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    metric_name VARCHAR(100) NOT NULL,
    metric_value DECIMAL(10,3) NOT NULL,
    metric_unit VARCHAR(20) NOT NULL DEFAULT 'ms',
    url VARCHAR(500) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    user_id INT DEFAULT NULL,
    session_id VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_metric_name (metric_name),
    INDEX idx_created_at (created_at),
    INDEX idx_url (url(100)),
    INDEX idx_user_id (user_id),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Optimization Logs Table
CREATE TABLE IF NOT EXISTS optimization_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('image', 'cache', 'database', 'assets', 'full') NOT NULL,
    description TEXT NOT NULL,
    impact VARCHAR(200) DEFAULT NULL,
    before_value DECIMAL(10,3) DEFAULT NULL,
    after_value DECIMAL(10,3) DEFAULT NULL,
    improvement_percentage DECIMAL(5,2) DEFAULT NULL,
    files_processed INT DEFAULT 0,
    space_saved BIGINT DEFAULT 0,
    time_taken DECIMAL(8,3) DEFAULT NULL,
    status ENUM('success', 'partial', 'failed') DEFAULT 'success',
    error_message TEXT DEFAULT NULL,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_created_by (created_by),
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Image Optimization Stats Table
CREATE TABLE IF NOT EXISTS image_optimization_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image_path VARCHAR(500) NOT NULL,
    original_size BIGINT NOT NULL,
    optimized_size BIGINT NOT NULL,
    compression_ratio DECIMAL(5,2) NOT NULL,
    formats_created JSON DEFAULT NULL,
    optimization_time DECIMAL(8,3) DEFAULT NULL,
    last_optimized TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_image_path (image_path(255)),
    INDEX idx_compression_ratio (compression_ratio),
    INDEX idx_last_optimized (last_optimized)
);

-- Cache Performance Table
CREATE TABLE IF NOT EXISTS cache_performance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cache_type ENUM('memory', 'file', 'database', 'redis') NOT NULL,
    operation ENUM('get', 'set', 'delete', 'clear') NOT NULL,
    key_name VARCHAR(255) NOT NULL,
    hit BOOLEAN DEFAULT NULL,
    response_time DECIMAL(8,3) NOT NULL,
    data_size INT DEFAULT NULL,
    ttl INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_cache_type (cache_type),
    INDEX idx_operation (operation),
    INDEX idx_hit (hit),
    INDEX idx_response_time (response_time),
    INDEX idx_created_at (created_at)
);

-- Database Performance Table
CREATE TABLE IF NOT EXISTS database_performance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    query_type ENUM('SELECT', 'INSERT', 'UPDATE', 'DELETE', 'OTHER') NOT NULL,
    table_name VARCHAR(100) DEFAULT NULL,
    execution_time DECIMAL(8,3) NOT NULL,
    rows_affected INT DEFAULT NULL,
    query_hash VARCHAR(64) NOT NULL,
    query_sample TEXT DEFAULT NULL,
    index_used BOOLEAN DEFAULT NULL,
    full_table_scan BOOLEAN DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_query_type (query_type),
    INDEX idx_table_name (table_name),
    INDEX idx_execution_time (execution_time),
    INDEX idx_query_hash (query_hash),
    INDEX idx_created_at (created_at)
);

-- Core Web Vitals Table
CREATE TABLE IF NOT EXISTS core_web_vitals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    url VARCHAR(500) NOT NULL,
    lcp DECIMAL(8,3) DEFAULT NULL COMMENT 'Largest Contentful Paint in ms',
    fid DECIMAL(8,3) DEFAULT NULL COMMENT 'First Input Delay in ms',
    cls DECIMAL(6,4) DEFAULT NULL COMMENT 'Cumulative Layout Shift score',
    fcp DECIMAL(8,3) DEFAULT NULL COMMENT 'First Contentful Paint in ms',
    ttfb DECIMAL(8,3) DEFAULT NULL COMMENT 'Time to First Byte in ms',
    user_agent TEXT DEFAULT NULL,
    device_type ENUM('mobile', 'tablet', 'desktop') DEFAULT NULL,
    connection_type VARCHAR(50) DEFAULT NULL,
    viewport_width INT DEFAULT NULL,
    viewport_height INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_url (url(100)),
    INDEX idx_lcp (lcp),
    INDEX idx_fid (fid),
    INDEX idx_cls (cls),
    INDEX idx_device_type (device_type),
    INDEX idx_created_at (created_at)
);

-- Performance Alerts Table
CREATE TABLE IF NOT EXISTS performance_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alert_type ENUM('slow_page', 'high_memory', 'cache_miss', 'database_slow', 'image_large') NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    message TEXT NOT NULL,
    metric_value DECIMAL(10,3) NOT NULL,
    threshold_value DECIMAL(10,3) NOT NULL,
    url VARCHAR(500) DEFAULT NULL,
    resolved BOOLEAN DEFAULT FALSE,
    resolved_at TIMESTAMP NULL DEFAULT NULL,
    resolved_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_alert_type (alert_type),
    INDEX idx_severity (severity),
    INDEX idx_resolved (resolved),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Performance Budgets Table
CREATE TABLE IF NOT EXISTS performance_budgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_pattern VARCHAR(200) NOT NULL COMMENT 'URL pattern or page type',
    metric_name VARCHAR(100) NOT NULL,
    budget_value DECIMAL(10,3) NOT NULL,
    unit VARCHAR(20) NOT NULL DEFAULT 'ms',
    alert_threshold DECIMAL(5,2) DEFAULT 90.00 COMMENT 'Alert when budget usage exceeds this percentage',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_page_metric (page_pattern, metric_name),
    INDEX idx_page_pattern (page_pattern),
    INDEX idx_metric_name (metric_name),
    INDEX idx_is_active (is_active)
);

-- Insert default performance budgets
INSERT INTO performance_budgets (page_pattern, metric_name, budget_value, unit, alert_threshold) VALUES
('/', 'page_load_time', 3000, 'ms', 90.00),
('/', 'lcp', 2500, 'ms', 90.00),
('/', 'fid', 100, 'ms', 90.00),
('/', 'cls', 0.1, 'score', 90.00),
('/restaurant/%', 'page_load_time', 2500, 'ms', 90.00),
('/restaurant/%', 'lcp', 2000, 'ms', 90.00),
('/order/%', 'page_load_time', 2000, 'ms', 90.00),
('/dashboard/%', 'page_load_time', 2500, 'ms', 90.00),
('/api/%', 'response_time', 500, 'ms', 90.00)
ON DUPLICATE KEY UPDATE 
    budget_value = VALUES(budget_value),
    alert_threshold = VALUES(alert_threshold),
    updated_at = CURRENT_TIMESTAMP;

-- Performance Summary View
CREATE OR REPLACE VIEW performance_summary AS
SELECT 
    DATE(created_at) as date,
    metric_name,
    COUNT(*) as measurements,
    AVG(metric_value) as avg_value,
    MIN(metric_value) as min_value,
    MAX(metric_value) as max_value,
    STDDEV(metric_value) as std_deviation,
    PERCENTILE_CONT(0.5) WITHIN GROUP (ORDER BY metric_value) as median_value,
    PERCENTILE_CONT(0.95) WITHIN GROUP (ORDER BY metric_value) as p95_value,
    PERCENTILE_CONT(0.99) WITHIN GROUP (ORDER BY metric_value) as p99_value
FROM performance_metrics 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(created_at), metric_name
ORDER BY date DESC, metric_name;

-- Cache Hit Ratio View
CREATE OR REPLACE VIEW cache_hit_ratio AS
SELECT 
    DATE(created_at) as date,
    cache_type,
    COUNT(*) as total_operations,
    SUM(CASE WHEN hit = 1 THEN 1 ELSE 0 END) as hits,
    SUM(CASE WHEN hit = 0 THEN 1 ELSE 0 END) as misses,
    ROUND((SUM(CASE WHEN hit = 1 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as hit_ratio_percent,
    AVG(response_time) as avg_response_time
FROM cache_performance 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY DATE(created_at), cache_type
ORDER BY date DESC, cache_type;

-- Slow Queries View
CREATE OR REPLACE VIEW slow_queries AS
SELECT 
    query_hash,
    query_type,
    table_name,
    COUNT(*) as execution_count,
    AVG(execution_time) as avg_execution_time,
    MAX(execution_time) as max_execution_time,
    SUM(CASE WHEN full_table_scan = 1 THEN 1 ELSE 0 END) as full_scan_count,
    MAX(query_sample) as sample_query,
    MAX(created_at) as last_execution
FROM database_performance 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    AND execution_time > 100 -- Only queries slower than 100ms
GROUP BY query_hash, query_type, table_name
HAVING avg_execution_time > 100
ORDER BY avg_execution_time DESC, execution_count DESC
LIMIT 50;

-- Performance Trends Stored Procedure
DELIMITER //
CREATE PROCEDURE GetPerformanceTrends(
    IN metric_name_param VARCHAR(100),
    IN days_back INT DEFAULT 7
)
BEGIN
    SELECT 
        DATE(created_at) as date,
        HOUR(created_at) as hour,
        COUNT(*) as measurements,
        AVG(metric_value) as avg_value,
        MIN(metric_value) as min_value,
        MAX(metric_value) as max_value
    FROM performance_metrics 
    WHERE metric_name = metric_name_param
        AND created_at >= DATE_SUB(NOW(), INTERVAL days_back DAY)
    GROUP BY DATE(created_at), HOUR(created_at)
    ORDER BY date DESC, hour DESC;
END //
DELIMITER ;

-- Performance Alert Trigger
DELIMITER //
CREATE TRIGGER performance_alert_trigger
    AFTER INSERT ON performance_metrics
    FOR EACH ROW
BEGIN
    DECLARE budget_value DECIMAL(10,3);
    DECLARE alert_threshold DECIMAL(5,2);
    DECLARE budget_exceeded BOOLEAN DEFAULT FALSE;
    
    -- Check if there's a performance budget for this metric
    SELECT pb.budget_value, pb.alert_threshold
    INTO budget_value, alert_threshold
    FROM performance_budgets pb
    WHERE pb.metric_name = NEW.metric_name
        AND pb.is_active = TRUE
        AND (pb.page_pattern = '/' OR NEW.url LIKE pb.page_pattern)
    LIMIT 1;
    
    -- If budget exists and is exceeded
    IF budget_value IS NOT NULL AND NEW.metric_value > (budget_value * alert_threshold / 100) THEN
        INSERT INTO performance_alerts (
            alert_type, 
            severity, 
            message, 
            metric_value, 
            threshold_value, 
            url
        ) VALUES (
            CASE 
                WHEN NEW.metric_name = 'page_load_time' THEN 'slow_page'
                WHEN NEW.metric_name = 'memory_usage' THEN 'high_memory'
                ELSE 'slow_page'
            END,
            CASE 
                WHEN NEW.metric_value > (budget_value * 1.5) THEN 'critical'
                WHEN NEW.metric_value > (budget_value * 1.2) THEN 'high'
                WHEN NEW.metric_value > budget_value THEN 'medium'
                ELSE 'low'
            END,
            CONCAT('Performance budget exceeded for ', NEW.metric_name, ': ', NEW.metric_value, NEW.metric_unit, ' (budget: ', budget_value, NEW.metric_unit, ')'),
            NEW.metric_value,
            budget_value,
            NEW.url
        );
    END IF;
END //
DELIMITER ;

-- Indexes for better performance
CREATE INDEX idx_performance_metrics_composite ON performance_metrics(metric_name, created_at, metric_value);
CREATE INDEX idx_optimization_logs_composite ON optimization_logs(type, status, created_at);
CREATE INDEX idx_cache_performance_composite ON cache_performance(cache_type, operation, created_at);
CREATE INDEX idx_database_performance_composite ON database_performance(query_type, execution_time, created_at);
CREATE INDEX idx_core_web_vitals_composite ON core_web_vitals(url(100), device_type, created_at);

-- Performance monitoring is now ready!
-- Use these tables to track, analyze, and optimize Time2Eat performance
