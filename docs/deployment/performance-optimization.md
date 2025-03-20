# Performance Optimization

This document provides tips and strategies for optimizing the performance of the LGBE2 application in production environments.

## Caching Strategies

### Application-Level Caching

Laravel provides several caching drivers that can be configured in `config/cache.php`:

```php
'default' => env('CACHE_DRIVER', 'file'),

'stores' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
    ],
    // Other cache stores...
],
```

For production, Redis is recommended for its performance and features:

```bash
# Install Redis
apt-get update
apt-get install redis-server

# Configure Redis for production
sed -i 's/^# maxmemory-policy noeviction/maxmemory-policy allkeys-lru/' /etc/redis/redis.conf
sed -i 's/^# maxmemory <bytes>/maxmemory 256mb/' /etc/redis/redis.conf
systemctl restart redis-server
```

### Route Caching

Cache the application's routes for faster route registration:

```bash
php artisan route:cache
```

Remember to clear and regenerate the route cache after any route changes:

```bash
php artisan route:clear
php artisan route:cache
```

### Configuration Caching

Cache the application's configuration files:

```bash
php artisan config:cache
```

Remember to clear and regenerate the configuration cache after any configuration changes:

```bash
php artisan config:clear
php artisan config:cache
```

### View Caching

Precompile all Blade templates:

```bash
php artisan view:cache
```

Clear the view cache when templates are updated:

```bash
php artisan view:clear
```

### Data Caching

Implement caching for frequently accessed data:

```php
// Cache a value for 60 minutes
Cache::put('key', 'value', 60);

// Retrieve a value with a default
$value = Cache::get('key', 'default');

// Cache a value forever
Cache::forever('key', 'value');

// Remove a value from the cache
Cache::forget('key');
```

Use the `remember` method for caching database queries:

```php
$posts = Cache::remember('community.posts.' . $communityId, 60, function () use ($communityId) {
    return Post::where('community_id', $communityId)
               ->orderBy('created_at', 'desc')
               ->take(20)
               ->get();
});
```

### HTTP Caching

Implement HTTP caching headers for static resources:

```php
return response($content)
    ->header('Cache-Control', 'public, max-age=31536000')
    ->header('Expires', gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
```

## Database Optimization

### Query Optimization

Optimize database queries:

1. Use eager loading to avoid N+1 query problems:

```php
// Instead of this (causes N+1 queries)
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->user->name;
}

// Use this (only 2 queries)
$posts = Post::with('user')->get();
foreach ($posts as $post) {
    echo $post->user->name;
}
```

2. Use query builders and raw queries for complex operations:

```php
$results = DB::table('posts')
    ->select('posts.*', DB::raw('COUNT(comments.id) as comment_count'))
    ->leftJoin('comments', 'posts.id', '=', 'comments.post_id')
    ->where('posts.community_id', $communityId)
    ->groupBy('posts.id')
    ->orderBy('comment_count', 'desc')
    ->get();
```

3. Use chunking for processing large datasets:

```php
Post::chunk(100, function ($posts) {
    foreach ($posts as $post) {
        // Process each post
    }
});
```

### Indexing

Add appropriate indexes to improve query performance:

```php
// In a migration file
Schema::table('posts', function (Blueprint $table) {
    $table->index('community_id');
    $table->index('user_id');
    $table->index('created_at');
});
```

Consider composite indexes for frequently combined columns:

```php
Schema::table('comments', function (Blueprint $table) {
    $table->index(['post_id', 'created_at']);
});
```

### Database Configuration

Optimize MySQL/MariaDB configuration in `/etc/mysql/mysql.conf.d/mysqld.cnf` (MySQL) or `/etc/mysql/mariadb.conf.d/50-server.cnf` (MariaDB):

```ini
[mysqld]
# InnoDB settings
innodb_buffer_pool_size = 1G  # Adjust based on available RAM (50-70% of RAM)
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2  # Slightly less durable but better performance
innodb_flush_method = O_DIRECT
innodb_file_per_table = 1

# Query cache settings
query_cache_type = 1
query_cache_size = 32M
query_cache_limit = 2M

# Connection settings
max_connections = 100  # Adjust based on expected concurrent users
thread_cache_size = 8
```

### Database Maintenance

Regularly perform database maintenance:

```bash
# Analyze tables
mysqlcheck -u username -p --analyze lgbe2

# Optimize tables
mysqlcheck -u username -p --optimize lgbe2
```

## Asset Optimization

### JavaScript and CSS Bundling

Use Laravel Mix (webpack) to bundle and minify assets:

```javascript
// webpack.mix.js
mix.js('resources/js/app.js', 'public/js')
   .vue()
   .sass('resources/sass/app.scss', 'public/css')
   .version();
```

Run the production build:

```bash
npm run production
```

### Image Optimization

Optimize images to reduce file sizes:

```bash
# Install image optimization tools
apt-get install jpegoptim optipng pngquant gifsicle

# Optimize JPEG images
find public/images -type f -name "*.jpg" -exec jpegoptim --strip-all --max=85 {} \;

# Optimize PNG images
find public/images -type f -name "*.png" -exec optipng -o5 {} \;
find public/images -type f -name "*.png" -exec pngquant --force --quality=65-80 {} \;

# Optimize GIF images
find public/images -type f -name "*.gif" -exec gifsicle -b -O3 {} \;
```

Consider using WebP format for images with a fallback to JPEG/PNG:

```html
<picture>
    <source srcset="image.webp" type="image/webp">
    <img src="image.jpg" alt="Description">
</picture>
```

### Lazy Loading

Implement lazy loading for images and components:

```html
<img loading="lazy" src="image.jpg" alt="Description">
```

For Vue components, use dynamic imports:

```javascript
const PostComponent = () => import('./components/PostComponent.vue');
```

## Load Balancing

### Nginx Load Balancing

Set up Nginx as a load balancer for multiple application servers:

```nginx
upstream lgbe2_backend {
    server backend1.example.com weight=3;
    server backend2.example.com weight=2;
    server backend3.example.com weight=1 backup;
}

server {
    listen 80;
    server_name your-domain.com;
    
    location / {
        proxy_pass http://lgbe2_backend;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### Session Management

When using load balancing, ensure sessions are shared across servers:

```php
// config/session.php
'driver' => env('SESSION_DRIVER', 'redis'),
```

### Queue Workers

Distribute queue workers across multiple servers:

```bash
# Start queue workers on each server
php artisan queue:work --tries=3 --timeout=90
```

Use a process monitor like Supervisor to keep queue workers running:

```ini
[program:lgbe2-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/lgbe2/artisan queue:work --tries=3 --timeout=90
autostart=true
autorestart=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/log/lgbe2-worker.log
```

## CDN Integration

### Setting Up a CDN

Configure a Content Delivery Network (CDN) for static assets:

1. Sign up for a CDN service (e.g., Cloudflare, AWS CloudFront, Fastly)
2. Configure your CDN to point to your origin server
3. Update your application to use the CDN URL for assets:

```php
// config/app.php
'asset_url' => env('ASSET_URL', null),
```

Set the `ASSET_URL` in your `.env` file:

```
ASSET_URL=https://cdn.your-domain.com
```

### Asset Versioning

Use versioning to ensure cache busting when assets change:

```php
<link rel="stylesheet" href="{{ mix('css/app.css') }}">
<script src="{{ mix('js/app.js') }}"></script>
```

### CDN for User-Uploaded Content

Store user-uploaded content on a CDN-enabled storage:

```php
// config/filesystems.php
'disks' => [
    's3' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
        'url' => env('AWS_URL'),
        'endpoint' => env('AWS_ENDPOINT'),
        'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
    ],
],
```

## Monitoring and Profiling

### Application Monitoring

Set up monitoring to track application performance:

1. Install Laravel Telescope for local development:

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

2. Use a production monitoring service like New Relic, Datadog, or Laravel Forge's monitoring.

### Database Query Monitoring

Monitor slow queries:

1. Enable slow query logging in MySQL/MariaDB:

```ini
slow_query_log = 1
slow_query_log_file = /var/log/mysql/mysql-slow.log
long_query_time = 1  # Log queries taking more than 1 second
```

2. Use Laravel Telescope or a similar tool to monitor queries in development.

### Performance Profiling

Use profiling tools to identify bottlenecks:

1. Install Xdebug for development:

```bash
apt-get install php8.1-xdebug
```

Configure Xdebug for profiling in `/etc/php/8.1/fpm/conf.d/20-xdebug.ini`:

```ini
xdebug.mode=profile
xdebug.output_dir=/tmp/xdebug
xdebug.profiler_output_name=cachegrind.out.%p
```

2. Use Blackfire.io for production profiling:

```bash
# Install Blackfire agent
wget -O - https://packages.blackfire.io/gpg.key | apt-key add -
echo "deb http://packages.blackfire.io/debian any main" | tee /etc/apt/sources.list.d/blackfire.list
apt-get update
apt-get install blackfire-agent
blackfire-agent --register --server-id=YOUR_SERVER_ID --server-token=YOUR_SERVER_TOKEN
```

### Real-Time Monitoring

Set up real-time monitoring for critical metrics:

1. Install and configure Prometheus and Grafana:

```bash
# Install Prometheus
apt-get install prometheus

# Install Grafana
apt-get install grafana
```

2. Configure Laravel to expose metrics for Prometheus using a package like `spatie/laravel-prometheus`.

## Scaling Strategies

### Vertical Scaling

Increase resources on existing servers:

1. Upgrade CPU, RAM, and disk space
2. Optimize PHP-FPM settings:

```ini
; /etc/php/8.1/fpm/pool.d/www.conf
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
```

### Horizontal Scaling

Add more servers to distribute the load:

1. Set up multiple application servers
2. Use a load balancer to distribute traffic
3. Ensure shared resources (sessions, cache, uploads) are accessible from all servers
4. Consider containerization with Docker and orchestration with Kubernetes for easier scaling

### Database Scaling

Scale the database for better performance:

1. Implement read replicas for read-heavy workloads:

```php
// config/database.php
'mysql' => [
    'read' => [
        'host' => [
            env('DB_READ_HOST1', '127.0.0.1'),
            env('DB_READ_HOST2', '127.0.0.1'),
        ],
    ],
    'write' => [
        'host' => env('DB_WRITE_HOST', '127.0.0.1'),
    ],
    // Other configuration...
],
```

2. Consider database sharding for very large datasets
3. Use a managed database service for easier scaling and maintenance
